<?php

namespace ChatApp\Authentication;

use ChatApp\Models\User;

class Authenticator {

  public function attempt($email, $password) {
    $user = User::where('email', $email)->first();

    if (!$user) {
      return false;
    }

    if (!password_verify($password, $user->password)) {
      return false;
    }

    if (isset($_SESSION['user'])) {
      unset($_SESSION['user']);
    }
    $_SESSION['user'] = $user->id;
    return true;
  }

  public function check() {
    return isset($_SESSION['user']);
  }

  public function user() {
    if (isset($_SESSION['user'])) {
      return User::find($_SESSION['user']);
    }
    return NULL;
  }

  public function logout() {
    if (isset($_SESSION['user'])) {
      unset($_SESSION['user']);
    }
  }
}
