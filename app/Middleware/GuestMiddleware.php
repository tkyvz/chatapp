<?php

namespace ChatApp\Middleware;

class GuestMiddleware extends Middleware {


  public function __invoke($request, $response, $next) {
    if ($this->container->authenticator->check()) {
      return $response->withRedirect($this->container->router->pathFor('home'));
    }

    $response = $next($request, $response);
    return $response;
  }
}
