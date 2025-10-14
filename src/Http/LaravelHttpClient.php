<?php
namespace Emizor\SDK\Http;

use Emizor\SDK\Contracts\HttpClientInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class LaravelHttpClient implements HttpClientInterface
{

    protected ?string $token = null;
    private Response $response;

    public function __construct() {
        $this->client = Http::acceptJson();
    }

    public function withBaseUri(string $host): static
    {
        $this->client->baseUrl($host);
        return $this;
    }

    public function withToken(string $token): static
    {
        $this->client->withToken( $token );
        return $this;
    }

    public function get(string $uri, array $options = []): void
    {
        $this->request('GET', $uri, $options);
    }

    public function post(string $uri, array $data = [], array $options = []): void
    {
        $this->request('POST', $uri, $data);
    }

    public function put(string $uri, array $data = [], array $options = []): void
    {
        $this->request('PUT', $uri, $data);
    }

    public function delete(string $uri, array $data = [], array $options = []): void
    {
        $this->request('DELETE', $uri, $data);
    }

    protected function request(string $method, string $uri, array $data = []): void
    {

        $response = match ($method) {
            'GET' => $this->client->get($uri, $data),
            'POST' => $this->client->post($uri, $data),
            'PUT' => $this->client->put($uri, $data),
            'DELETE' => $this->client->delete($uri, $data),
        };
        $this->setResponse($response);
    }

    private function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): array
    {
        return $this->response->json() ?? [];
    }

    public function getStatus(): int
    {
        return $this->response->status();
    }
}
