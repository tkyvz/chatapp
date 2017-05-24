<?php

namespace ChatApp\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use ChatApp\Models\User;

class EmailAvailable extends AbstractRule {

  public function validate($input) {
    return User::where('email', $input)->count() === 0;
  }
}
