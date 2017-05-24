<?php

namespace ChatApp\Middleware;

class AuthMiddleware extends Middleware {


  public function __invoke($request, $response, $next) {
    if (!$this->container->authenticator->check()) {
      return $response->withRedirect($this->container->router->pathFor('auth.signin'));
    }

    $response = $next($request, $response);
    return $response;
  }
}
