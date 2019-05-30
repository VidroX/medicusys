<?php

namespace App\Handlers;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class RouterNotFoundHandler {
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('404 - Page not found');
    }
}