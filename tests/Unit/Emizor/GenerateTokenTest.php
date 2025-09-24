<?php

use Emizor\SDK\Exceptions\EmizorApiConnectionTimeoutException;
use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Services\TokenService;
use Emizor\SDK\Exceptions\EmizorApiTokenException;
use Pest\Faker;

it('throws exception if connection timeout', function () {
    $fakeHttp = new class implements HttpClientInterface {
        public function withToken(string $token): static
        {
            return $this;
        }

        public function get(string $host, string $uri, array $options = []): array
        {
            return [];
        }

        public function post(string $host, string $uri, array $data = [], array $options = []): array
        {
            throw new EmizorApiConnectionTimeoutException("Connection timed out");
        }

        public function put(string $host, string $uri, array $data = [], array $options = []): array
        {
            return [];
        }

        public function delete(string $host, string $uri, array $options = []): array
        {
            return [];
        }
    };

    $service = new TokenService($fakeHttp);

    $service->generate('https://api.emizor.com', 'CLIENT_ID', 'CLIENT_SECRET');
})->throws(EmizorApiConnectionTimeoutException::class);


it('throws exception if token invalid', function () {
    $fakeHttp = new class implements HttpClientInterface {
        public function withToken(string $token): static
        {
            return $this;
        }

        public function get(string $host, string $uri, array $options = []): array
        {
            return [];
        }

        public function post(string $host, string $uri, array $data = [], array $options = []): array
        {
            throw new EmizorApiTokenException("Invalid token");
        }

        public function put(string $host, string $uri, array $data = [], array $options = []): array
        {
            return [];
        }

        public function delete(string $host, string $uri, array $options = []): array
        {
            return [];
        }
    };

    $service = new TokenService($fakeHttp);

    $service->generate('https://api.emizor.com', 'CLIENT_ID', 'CLIENT_SECRET');
})->throws(EmizorApiTokenException::class);
