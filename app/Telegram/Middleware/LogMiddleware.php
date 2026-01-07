<?php

namespace App\Telegram\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class LogMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Log the incoming request
        $this->logRequest($request);

        // Process the request and get the response
        $response = $handler->handle($request);

        // Log the outgoing response
        $this->logResponse($response);

        return $response;
    }

    private function logRequest(ServerRequestInterface $request): void
    {
        // Here you can implement your logging logic
        // For example, log the request method and URI
        error_log(sprintf('Request: %s %s', $request->getMethod(), $request->getUri()));
    }

    private function logResponse(ResponseInterface $response): void
    {
        // Here you can implement your logging logic
        // For example, log the response status code
        error_log(sprintf('Response: %s', $response->getStatusCode()));
    }
}
