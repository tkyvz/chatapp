<?php

namespace ChatApp\Middleware;

use ChatApp\Models\User;
use ChatApp\Models\Room;

class RoomMemberMiddleware extends Middleware {


  public function __invoke($request, $response, $next) {
    // Get roomId
    $roomId = $request->getParam('roomId');
    if (self::isNullOrWhitespace($roomId)) {
      return $response->withJson([
        'reason' => 'Room Id cannot be empty.'
      ], 400);
    }

    // Check Room exists
    $room = Room::find($roomId);
    if (is_null($room)) {
      return $response->withJson([
        'reason' => 'Room cannot be found.'
      ], 400);
    }

    // Check currenct user is a member
    if (isset($_SESSION['user'])) {
      $user = $room->members()->find($_SESSION['user']);
      if (is_null($user)) {
        return $response->withJson([
          'reason' => 'User is not authorized to perform this operation.'
        ], 401);
      }
    }

    $response = $next($request, $response);
    return $response;
  }
}
