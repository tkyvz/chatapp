<?php

namespace ChatApp\Middleware;

class SignupMiddleware extends Middleware {

  public function __invoke($request, $response, $next) {

    if (isset($_SESSION['signup_data'])) {
      $this->container->view->getEnvironment()->addGlobal('signup_data', $_SESSION['signup_data']);
    }
    $_SESSION['signup_data'] = $request->getParams();

    $response = $next($request, $response);
    return $response;
  }
}
