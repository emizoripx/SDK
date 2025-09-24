<?php

namespace Emizor\SDK\Contracts;

interface HttpClientInterface
{
    /**
     * Set the token to be used in requests.
     */
    public function withToken(string $token): static;

    /**
     * Make a GET request to a dynamic host.
     *
     * @param string $host
     * @param string $uri
     * @param array $options
     */
    public function get(string $host, string $uri, array $options = []): array;

    /**
     * Make a POST request to a dynamic host.
     *
     * @param string $host
     * @param string $uri
     * @param array $data
     * @param array $options
     */
    public function post(string $host, string $uri, array $data = [], array $options = []): array;

    /**
     * Make a PUT request to a dynamic host.
     *
     * @param string $host
     * @param string $uri
     * @param array $data
     * @param array $options
     */
    public function put(string $host, string $uri, array $data = [], array $options = []): array;

    /**
     * Make a DELETE request to a dynamic host.
     *
     * @param string $host
     * @param string $uri
     * @param array $options
     */
    public function delete(string $host, string $uri, array $options = []): array;
}
