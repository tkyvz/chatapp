<?php

namespace ChatApp\Middleware;

class RestMiddleware extends Middleware {


  public function __invoke($request, $response, $next) {
    if (!$this->container->authenticator->check()) {
      return $response->withJson([
        'reason' => 'You need to be logged in to perform this operation.',
        'redirect' => 'signin',
      ], 401);
    }

    $response = $next($request, $response);
    return $response;
  }
}
