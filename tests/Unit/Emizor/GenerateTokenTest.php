<?php

use Emizor\SDK\Exceptions\EmizorApiConnectionTimeoutException;
use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Services\TokenService;
use Emizor\SDK\Exceptions\EmizorApiTokenException;

it('throws exception if connection timeout', function () {
    $fakeHttp = new class implements EmizorApiHttpContract {
        public function __construct($http = null) {}
        public function generateToken(string $host, string $clientId, string $clientSecret): array {
            throw new EmizorApiConnectionTimeoutException("Connection timed out");
        }
        public function checkNit(string $host, string $token, $nit): array { return []; }
        public function sendInvoice(string $host, string $token, array $data): array { return []; }
        public function getDetailInvoice(string $host, string $token, string $ticket): array { return []; }
        public function revocateInvoice(string $host, string $token, string $ticket, int $revocationReasonCode): array { return []; }
        public function getParametrics(string $host, string $token, string $type): array { return []; }
    };

    $service = new TokenService($fakeHttp);

    $service->generate('HOST', 'CLIENT_ID', 'CLIENT_SECRET');
})->throws(EmizorApiConnectionTimeoutException::class);


it('throws exception if token invalid', function () {
    $fakeHttp = new class implements EmizorApiHttpContract {
        public function __construct($http = null) {}
        public function generateToken(string $host, string $clientId, string $clientSecret): array {
            throw new EmizorApiTokenException("Invalid token");
        }
        public function checkNit(string $host, string $token, $nit): array { return []; }
        public function sendInvoice(string $host, string $token, array $data): array { return []; }
        public function getDetailInvoice(string $host, string $token, string $ticket): array { return []; }
        public function revocateInvoice(string $host, string $token, string $ticket, int $revocationReasonCode): array { return []; }
        public function getParametrics(string $host, string $token, string $type): array { return []; }
    };

    $service = new TokenService($fakeHttp);

    $service->generate('HOST', 'CLIENT_ID', 'CLIENT_SECRET');
})->throws(EmizorApiTokenException::class);

it('generates token successfully', function () {
    $fakeHttp = new class implements EmizorApiHttpContract {
        public function __construct($http = null) {}
        public function generateToken(string $host, string $clientId, string $clientSecret): array {
            return [
                'access_token' => 'generated-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer'
            ];
        }
        public function checkNit(string $host, string $token, $nit): array { return []; }
        public function sendInvoice(string $host, string $token, array $data): array { return []; }
        public function getDetailInvoice(string $host, string $token, string $ticket): array { return []; }
        public function revocateInvoice(string $host, string $token, string $ticket, int $revocationReasonCode): array { return []; }
        public function getParametrics(string $host, string $token, string $type): array { return []; }
    };

    $service = new TokenService($fakeHttp);

    $result = $service->generate('HOST', 'CLIENT_ID', 'CLIENT_SECRET');

    expect($result)->toBeArray();
    expect($result)->toHaveKey('token');
    expect($result)->toHaveKey('expires_at');
    expect($result['token'])->toBe('generated-token');
});
