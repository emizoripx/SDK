<?php
namespace Emizor\SDK\Http;

use Emizor\SDK\Contracts\HttpClientInterface;
use Illuminate\Support\Facades\Http;

class LaravelHttpClient implements HttpClientInterface
{

    protected ?string $token = null;

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

    public function get(string $uri, array $options = []): array
    {
        return $this->request('GET', $uri, $options);
    }

    public function post(string $uri, array $data = [], array $options = []): array
    {
        return $this->request('POST', $uri, $data);
    }

    public function put(string $uri, array $data = [], array $options = []): array
    {
        return $this->request('PUT', $uri, $data);
    }

    public function delete(string $uri, array $data = [], array $options = []): array
    {
        return $this->request('DELETE', $uri, $data);
    }

    protected function request(string $method, string $uri, array $data = []): array
    {
      /*  $this->client = $this->client->beforeSending(function ($request, $options) use ($method, $data) {
            $url = (string) $request->getUri();
            $headers = $request->getHeaders();
            $curl = "curl -X $method '$url'";
            if (in_array($method, ['POST', 'PUT', 'DELETE']) && !empty($data)) {
                $body = is_array($data) ? json_encode($data) : $data;
                $curl .= " -d '$body'";
            }
            foreach ($headers as $name => $values) {
                foreach ($values as $value) {
                    $curl .= " -H '$name: $value'";
                }
            }
            info("CURL: $curl");
        });*/
        $response = match ($method) {
            'GET' => $this->client->get($uri, $data),
            'POST' => $this->client->post($uri, $data),
            'PUT' => $this->client->put($uri, $data),
            'DELETE' => $this->client->delete($uri, $data),
        };

        return $response->json() ?? [];
    }

}
