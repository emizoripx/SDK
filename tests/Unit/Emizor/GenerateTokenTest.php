<?php

use Emizor\SDK\Exceptions\EmizorApiConnectionTimeoutException;
use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Services\TokenService;
use Emizor\SDK\Exceptions\EmizorApiTokenException;
use Pest\Faker;

it('throws exception if connection timeout', function () {

    $fakeHttp = new class implements HttpClientInterface {
        public function get(string $uri, array $options = []): array { return []; }
        public function post(string $uri, array $data = [], array $options = []): array {
            throw new EmizorApiConnectionTimeoutException("Connection timed out");
        }
        public function put(string $uri, array $data = [], array $options = []): array { return []; }
        public function delete(string $uri, array $options = []): array { return []; }
    };

    $service = new TokenService($fakeHttp);


    $service->generate('id', 'secret');

})->throws(EmizorApiConnectionTimeoutException::class);


it('throws exception if token invalid', function () {

    $fakeHttp = new class implements HttpClientInterface {
        public function get(string $uri, array $options = []): array { return []; }
        public function post(string $uri, array $data = [], array $options = []): array {
            throw new EmizorApiTokenException("Invalid token");
        }
        public function put(string $uri, array $data = [], array $options = []): array { return []; }
        public function delete(string $uri, array $options = []): array { return []; }
    };

    $service = new TokenService($fakeHttp);

    $service->generate('id', 'secret');

})->throws(EmizorApiTokenException::class);
