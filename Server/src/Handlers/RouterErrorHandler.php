<?php

namespace App\Handlers;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class RouterErrorHandler {
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception) {
        return $response
            ->withStatus($response->getStatusCode())
            ->withHeader('Content-Type', 'text/html')
            ->write($response->getStatusCode().' - '.$response->getReasonPhrase());
    }
}