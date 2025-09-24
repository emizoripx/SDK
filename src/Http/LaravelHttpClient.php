<?php
namespace Emizor\SDK\Http;

use Emizor\SDK\Contracts\HttpClientInterface;
use Illuminate\Support\Facades\Http;

class LaravelHttpClient implements HttpClientInterface
{
    protected ?string $token = null;

    public function withToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function get(string $host, string $uri, array $options = []): array
    {
        return $this->request('GET', $host, $uri, $options);
    }

    public function post(string $host, string $uri, array $data = [], array $options = []): array
    {
        return $this->request('POST', $host, $uri, $data);
    }

    protected function request(string $method, string $host, string $uri, array $data = []): array
    {
        $client = Http::acceptJson()->baseUrl($host);

        if ($this->token) {
            $client = $client->withToken($this->token);
        }

        $response = match ($method) {
            'GET' => $client->get($uri, $data),
            'POST' => $client->post($uri, $data),
            'PUT' => $client->put($uri, $data),
            'DELETE' => $client->delete($uri, $data),
        };

        return $response->json() ?? [];
    }

    public function put(string $host, string $uri, array $data = [], array $options = []): array
    {
        return $this->request('PUT', $host, $uri, $data);
    }

    public function delete(string $host, string $uri, array $options = []): array
    {
        return $this->request('DELETE', $host, $uri);
    }
}
