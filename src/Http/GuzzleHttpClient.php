<?php

namespace Emizor\SDK\Http;

use Emizor\SDK\Contracts\HttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use Emizor\SDK\Exceptions\EmizorApiConnectionTimeoutException;
use Emizor\SDK\Exceptions\EmizorApiTokenException;

class GuzzleHttpClient implements HttpClientInterface
{
    protected Client $client;
    protected ?string $token = null;

    public function __construct()
    {

        $this->client = new Client([
                'http_errors' => false,
                "connect_timeout" => 8,
                "timeout" => 10,
                'redirect.strict' => true,
                'headers' => array(
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    "emizor-header" => 'true',
                ),
            ]
        );

    }


    public function get(string $uri, array $options = []): array
    {
        return $this->request('GET', $uri, $options);
    }

    public function post(string $uri, array $data = [], array $options = []): array
    {
        $options['json'] = $data;
        return $this->request('POST', $uri, $options);
    }

    public function put(string $uri, array $data = [], array $options = []): array
    {
        $options['json'] = $data;
        return $this->request('PUT', $uri, $options);
    }

    public function delete(string $uri, array $options = []): array
    {
        return $this->request('DELETE', $uri, $options);
    }

    public function setBaseUri(string $uri): void
    {
        $this->client = new Client(array_merge($this->client->getConfig(), ['base_uri' => $uri]));
    }

    protected function request(string $method, string $uri, array $options = []): array
    {
        if ($this->token) {
            $options['headers']['Authorization'] = "Bearer {$this->token}";
        }

        try {
            $response = $this->client->request($method, $uri, $options);
            return json_decode($response->getBody()->getContents(), true) ?? [];

        } catch (ConnectException $e) {
            throw new EmizorApiConnectionTimeoutException("Connection timed out", 0, $e);

        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
                throw new EmizorApiTokenException("Invalid or expired token", 0, $e);
            }
            throw $e;
        }
    }
}