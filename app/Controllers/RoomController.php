<?php

namespace ChatApp\Controllers;

use ChatApp\Models\User;
use ChatApp\Models\Room;
use ChatApp\Models\Log;

class RoomController extends Controller {
  /**
  * Gets the rooms of the signed in user.
  */
  public function getRooms($request, $response) {
    if (!isset($_SESSION['user'])) {
      // impossible due to RestMiddleware
      return $resposne;
    }
    $user = User::find($_SESSION['user']);
    $response = $response->withJson($user->rooms()->get());
    return $response;
  }

  /**
  * Creates a new Room and add the user as an admin
  */
  public function addRoom($request, $response) {
    if (!isset($_SESSION['user'])) {
      // impossible due to RestMiddleware
      return $response;
    }

    $name = $request->getParam('name');
    if (self::isNullOrWhitespace($name)) { // Room->name is not set or empty string
      return $response->withJson([
        'reason' => 'Room name cannot be empty.'
      ], 400);
    }
    $room = new Room;
    $room->name = $name;
    $room->save();

    $room->users()->attach($_SESSION['user'], ['status' => 1]); // 1 is admin, 0 is member, -1 deleted
    return $response->withJson($room);
  }
}
