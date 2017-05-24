<?php

namespace ChatApp\Controllers;

use ChatApp\Models\User;
use Respect\Validation\Validator as v;

class AuthController extends Controller {

  public function getSignUp($request, $response) {
    return $this->view->render($response, 'signup.twig');
  }

  public function postSignUp($request, $response) {

    $validation = $this->validator->validate($request, [
      'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
      'name' => v::notEmpty(),
      'password' => v::noWhitespace()->notEmpty(),
    ]);

    if ($validation->failed()) {
      return $response->withRedirect($this->router->pathFor('auth.signup'));
    }

    $user = new User;
    $user->email = $request->getParam('email');
    $user->name = $request->getParam('name');
    $user->password = password_hash($request->getParam('password'), PASSWORD_DEFAULT, ['cost' => 14]);

    $user->save();

    $this->flash->addMessage('info', 'Signup is successful!');

    $this->authenticator->attempt(
      $request->getParam('email'),
      $request->getParam('password')
    );

    return $response->withRedirect($this->router->pathFor('home'));
  }

  public function getSignIn($request, $response) {
    return $this->view->render($response, 'signin.twig');
  }

  public function postSignIn($request, $response) {

    $result = $this->authenticator->attempt(
      $request->getParam('email'),
      $request->getParam('password')
    );

    if (!$result) {
      $this->flash->addMessage('error', 'Could not sign up. Check email / username.');
      return $response->withRedirect($this->router->pathFor('auth.signin'));
    }

    return $response->withRedirect($this->router->pathFor('home'));
  }

  public function getSignOut($request, $response) {
    $this->authenticator->logout();
    return $response->withRedirect($this->router->pathFor('home'));
  }
}
