<?php

namespace ChatApp\Controllers;

use ChatApp\Models\User;
use ChatApp\Models\Room;
use ChatApp\Models\Message;
use ChatApp\Models\Log;

class RoomMemberController extends Controller {
  /**
  * Leave the given Room
  */
  public function leaveRoom($request, $response) {
    if (!isset($_SESSION['user'])) {
      // impossible due to the RestMiddleware
      return $response;
    }
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    $user = $room->members()->find($_SESSION['user']);
    if (is_null($user)) {
      return $response->withJson(json_encode([
        'reason' => 'You are not a member of the room.'
      ]), 409);
    } else {
      $room->users()->updateExistingPivot($_SESSION['user'], ['status' => -1]);
      return $response->withJson($room);
    }
  }

  /**
  * Send a message to the room
  */
  public function sendMessage($request, $response) {
    if (!isset($_SESSION['user'])) {
      // impossible due to the RestMiddleware
      return $response;
    }
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    // Check message is not empty
    $messageText = $request->getParam('message');
    if (self::isNullOrWhitespace($messageText)) {
      return $response->withJson([
        'reason' => 'Message cannot be empty.'
      ], 400);
    }

    // Create and send Message
    $message = new Message;
    $message->message = $messageText;
    $message->user_id = $_SESSION['user'];
    $room->messages()->save($message);

    return $response->withJson($message);
  }

  /**
  * Get 20 messages sent to a Room
  */
  public function getMessages($request, $response) {
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    // Get messages ordered by created time
    $messages = $room->messages()
                     ->orderBy('created_at', 'desc')
                     #->take(20) // endless scroll web method is ready, but not implemented in the front end
                     ->with('User')
                     ->get();

    return $response->withJson($messages);
  }

  /**
  * Gets messages after the given Message sent to a Room
  */
  public function refresh($request, $response) {
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    $lastId = $request->getParam('lastId');
    if (self::isNullOrWhitespace($lastId)) {
      // lastId is not provided, assume no message has been loaded in client
      $messages = $room->messages()
                       ->orderBy('created_at', 'desc')
                       ->with('User')
                       ->get();
    } else {
      // lastId is provided, retrieve messages after the given id
      $messages = $room->messages()
                       ->where('id', '>', $lastId)
                       ->orderBy('created_at', 'desc')
                       ->with('User')
                       ->get();
    }

    return $response->withJson($messages);
  }

  /**
  * Gets 20 messages before the given Message sent to a Room
  */
  public function scroll($request, $response) {
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    $firstId = $request->getParam('firstId');
    if (self::isNullOrWhitespace($firstId)) {
      // firstId is not provided, assume no message has been loaded in client
      $messages = $room->messages()
                       ->orderBy('created_at', 'desc')
                       ->take(20)
                       ->with('User')
                       ->get();
    } else {
      // firstId is provided, retrieve 20 messages before the given id
      $messages = $room->messages()
                       ->where('id', '<', $firstId)
                       ->orderBy('created_at', 'desc')
                       ->take(20)
                       ->with('User')
                       ->get();
    }

    return $response->withJson($messages);
  }

  /**
  * Gets members of a Room
  */
  public function getMembers($request, $response) {
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    return $response->withJson($room->members()->get());
  }
}
