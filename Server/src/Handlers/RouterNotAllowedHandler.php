<?php

namespace App\Handlers;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class RouterNotAllowedHandler {
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $methods) {
        return $response->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html')
            ->write('<p>405 - Method not allowed!</p><p>Allowed methods are: ' . implode(', ', $methods) . '</p>');
    }
}