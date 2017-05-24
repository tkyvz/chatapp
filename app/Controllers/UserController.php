<?php

namespace ChatApp\Controllers;

use ChatApp\Models\User;
use ChatApp\Models\Log;

class UserController extends Controller {

  /**
  * Searchs users with the 'query' parameter in the database and returns them.
  */
  public function search($request, $response) {
    // Get request parameter
    $data = $request->getParam('query');

    if (is_null($data)) {
      $response = $response->withJson([
        'reason' => 'Not found'
      ], 204);
      return $response;
    }

    $users = User::where('email', 'LIKE', "%$data%")
      ->orWhere('name', 'LIKE', "%$data%")
      ->get();

    $response = $response->withJson($users);
    return $response;
  }
}
