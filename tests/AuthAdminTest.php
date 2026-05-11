<?php

namespace PHPSupabase\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPSupabase\AuthAdmin;
use PHPSupabase\Service;
use PHPUnit\Framework\TestCase;

class AuthAdminTest extends TestCase
{
    private const SERVICE_ROLE_KEY = 'fake-service-role-key';
    private const URI_BASE = 'https://abc.supabase.co/auth/v1/';

    /** @var array<int, array{request: Request, response: Response}> */
    private array $history = [];

    private Service $service;
    private AuthAdmin $authAdmin;

    /**
     * Build a Service instance whose internal Guzzle client is replaced by a mocked one
     * driven by the provided response queue, and the AuthAdmin built on top of it.
     *
     * @param array<int, mixed> $responses Queue of Response or Throwable to be returned by the mock handler
     */
    private function setUpWithResponses(array $responses): void
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $this->history = [];
        $stack->push(Middleware::history($this->history));
        $client = new Client(['handler' => $stack]);

        $this->service = new Service(self::SERVICE_ROLE_KEY, self::URI_BASE);

        $ref = new \ReflectionProperty(Service::class, 'httpClient');
        $ref->setValue($this->service, $client);

        $this->authAdmin = $this->service->createAuthAdmin();
    }

    private function lastRequest(): Request
    {
        $last = end($this->history);
        return $last['request'];
    }

    public function testServiceCreateAuthAdminReturnsAuthAdminInstance(): void
    {
        $service = new Service(self::SERVICE_ROLE_KEY, self::URI_BASE);
        $this->assertInstanceOf(AuthAdmin::class, $service->createAuthAdmin());
    }

    public function testCreateUserSendsCorrectRequest(): void
    {
        $body = json_encode(['id' => 'user-123', 'email' => 'a@b.c']);
        $this->setUpWithResponses([new Response(200, [], $body)]);

        $this->authAdmin->createUser('a@b.c', 'pwd', true, ['name' => 'Alice']);

        $req = $this->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertStringEndsWith('/auth/v1/admin/users', (string) $req->getUri());

        $this->assertSame(self::SERVICE_ROLE_KEY, $req->getHeaderLine('apikey'));
        $this->assertSame('Bearer ' . self::SERVICE_ROLE_KEY, $req->getHeaderLine('Authorization'));
        $this->assertSame('application/json', $req->getHeaderLine('Content-Type'));

        $payload = json_decode((string) $req->getBody(), true);
        $this->assertSame('a@b.c', $payload['email']);
        $this->assertSame('pwd', $payload['password']);
        $this->assertTrue($payload['email_confirm']);
        $this->assertSame(['name' => 'Alice'], $payload['user_metadata']);

        $this->assertSame('user-123', $this->authAdmin->data()->id);
    }

    public function testCreateUserOmitsEmptyUserMetadata(): void
    {
        $this->setUpWithResponses([new Response(200, [], '{}')]);

        $this->authAdmin->createUser('a@b.c', 'pwd');

        $payload = json_decode((string) $this->lastRequest()->getBody(), true);
        $this->assertArrayNotHasKey('user_metadata', $payload);
        $this->assertTrue($payload['email_confirm']);
    }

    public function testCreateUserCanDisableEmailConfirm(): void
    {
        $this->setUpWithResponses([new Response(200, [], '{}')]);

        $this->authAdmin->createUser('a@b.c', 'pwd', false);

        $payload = json_decode((string) $this->lastRequest()->getBody(), true);
        $this->assertFalse($payload['email_confirm']);
    }

    public function testCreateUserHandlesEmailAlreadyRegistered(): void
    {
        $this->setUpWithResponses([
            new Response(422, [], json_encode([
                'msg' => 'A user with this email address has already been registered',
            ])),
        ]);

        $this->expectException(ClientException::class);

        try {
            $this->authAdmin->createUser('existing@b.c', 'pwd');
        } finally {
            $this->assertSame(
                'A user with this email address has already been registered',
                $this->authAdmin->getError()
            );
        }
    }

    public function testCreateUserHandlesNetworkError(): void
    {
        $this->setUpWithResponses([
            new ConnectException('Connection refused', new Request('POST', 'https://abc.supabase.co')),
        ]);

        $this->expectException(ConnectException::class);

        try {
            $this->authAdmin->createUser('a@b.c', 'pwd');
        } finally {
            $this->assertSame('Connection refused', $this->authAdmin->getError());
        }
    }

    public function testListUsersSendsGetWithPagination(): void
    {
        $this->setUpWithResponses([new Response(200, [], '{"users": []}')]);

        $this->authAdmin->listUsers(2, 25);

        $req = $this->lastRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertStringContainsString('/auth/v1/admin/users', (string) $req->getUri());
        $this->assertStringContainsString('page=2', $req->getUri()->getQuery());
        $this->assertStringContainsString('per_page=25', $req->getUri()->getQuery());
    }

    public function testGetUserReturnsUserData(): void
    {
        $this->setUpWithResponses([new Response(200, [], '{"id": "user-123", "email": "a@b.c"}')]);

        $this->authAdmin->getUser('user-123');

        $req = $this->lastRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertStringEndsWith('/auth/v1/admin/users/user-123', (string) $req->getUri());
        $this->assertSame('user-123', $this->authAdmin->data()->id);
        $this->assertSame('a@b.c', $this->authAdmin->data()->email);
    }

    public function testUpdateUserSendsPutWithAttributes(): void
    {
        $this->setUpWithResponses([new Response(200, [], '{"id":"user-123"}')]);

        $this->authAdmin->updateUser('user-123', [
            'email' => 'new@b.c',
            'ban_duration' => '24h',
            'user_metadata' => ['role' => 'admin'],
        ]);

        $req = $this->lastRequest();
        $this->assertSame('PUT', $req->getMethod());
        $this->assertStringEndsWith('/auth/v1/admin/users/user-123', (string) $req->getUri());

        $payload = json_decode((string) $req->getBody(), true);
        $this->assertSame('new@b.c', $payload['email']);
        $this->assertSame('24h', $payload['ban_duration']);
        $this->assertSame(['role' => 'admin'], $payload['user_metadata']);
    }

    public function testDeleteUserSendsDelete(): void
    {
        $this->setUpWithResponses([new Response(200, [], '{}')]);

        $this->authAdmin->deleteUser('user-123');

        $req = $this->lastRequest();
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertStringEndsWith('/auth/v1/admin/users/user-123', (string) $req->getUri());
    }

    public function testGenerateLinkSendsPostWithTypeAndOptions(): void
    {
        $this->setUpWithResponses([new Response(200, [], '{"action_link":"https://abc.supabase.co/recover"}')]);

        $this->authAdmin->generateLink('recovery', 'a@b.c', ['redirect_to' => 'https://app.com/reset']);

        $req = $this->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertStringEndsWith('/auth/v1/admin/generate_link', (string) $req->getUri());

        $payload = json_decode((string) $req->getBody(), true);
        $this->assertSame('recovery', $payload['type']);
        $this->assertSame('a@b.c', $payload['email']);
        $this->assertSame('https://app.com/reset', $payload['redirect_to']);
    }

    public function testServerErrorIsRethrownWithFormattedError(): void
    {
        $this->setUpWithResponses([
            new Response(500, [], json_encode(['error_description' => 'Internal server error'])),
        ]);

        $this->expectException(ServerException::class);

        try {
            $this->authAdmin->getUser('user-123');
        } finally {
            $this->assertSame('Internal server error', $this->authAdmin->getError());
        }
    }
}
