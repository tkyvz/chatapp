<?php

namespace ChatApp\Controllers;

use ChatApp\Models\User;
use Slim\Views\Twig as View;

class HomeController extends Controller {

  public function index($request, $response) {
    if(!isset($_SESSION['user'])) {
      return $response->withRedirect($this->router->pathFor('auth.signin'));
    }
    return $this->view->render($response, 'home.twig');
  }
}
