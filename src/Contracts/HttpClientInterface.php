<?php

namespace Emizor\SDK\Contracts;

interface HttpClientInterface
{
    /**
     * Set the host to be used in requests.
     *
     * @param string $host
     */
    public function withBaseUri(string $host): static;

    /**
     * Set the token to be used in requests.
     *
     * @param string $token
     */
    public function withToken(string $token): static;

    /**
     * Make a GET request to a dynamic host.
     *
     * @param string $uri
     * @param array $options
     */
    public function get(string $uri, array $options = []): array;

    /**
     * Make a POST request to a dynamic host.
     *
     * @param string $uri
     * @param array $data
     * @param array $options
     */
    public function post(string $uri, array $data = [], array $options = []): array;

    /**
     * Make a PUT request to a dynamic host.
     *
     * @param string $uri
     * @param array $data
     * @param array $options
     */
    public function put(string $uri, array $data = [], array $options = []): array;

    /**
     * Make a DELETE request to a dynamic host.
     *
     * @param string $uri
     * @param array $options
     */
    public function delete(string $uri, array $options = []): array;
}
