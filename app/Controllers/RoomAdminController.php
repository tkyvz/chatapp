<?php

namespace ChatApp\Controllers;

use ChatApp\Models\User;
use ChatApp\Models\Room;
use ChatApp\Models\Log;

class RoomAdminController extends Controller {
  /**
  * Adds a User to a given Room as a member.
  */
  public function addMember($request, $response) {
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    // Get userId to be added
    $userId = $request->getParam('userId');
    if (self::isNullOrWhitespace($userId)) {
      return $response->withJson([
        'reason' => 'User Id cannot be empty.'
      ], 400);
    }

    // Check User is not a member. If removed, then update. Else add.
    $user = $room->oldMembers()->find($userId);
    if (!is_null($user)) { // former member
      $room->users()->updateExistingPivot($userId, ['status' => 0]);
      return $response->withJson($room);
    } else {
      $member = $room->members()->find($userId);
      if (!is_null($member)) { // already a member
        return $response->withJson([
          'reason' => 'User is already a member.'
        ], 409);
      } else { // new member
        $room->users()->attach($userId, ['status' => 0]);
        return $response->withJson($room);
      }
    }
  }

  /**
  * Kicks a User form the Room
  */
  public function kickMember($request, $response) {
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    // Get userId
    $userId = $request->getParam('userId');
    if (self::isNullOrWhitespace($userId)) {
      return $response->withJson([
        'reason' => 'User Id cannot be empty.'
      ], 400);
    }

    // Check User is a member. If so, update status, else do nothing.
    $user = $room->oldMembers()->find($userId);
    if (!is_null($user)) { // former member
      return $response->withJson([
        'reason' => 'User is not in the room.'
      ], 409);
    } else {
      $room->users()->updateExistingPivot($userId, ['status' => -1]);
      return $response->withJson($room);
    }
  }

  /**
  * Promotes a User to adminship
  */
  public function makeAdmin($request, $response) {
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    // Get userId
    $userId = $request->getParam('userId');
    if (self::isNullOrWhitespace($userId)) {
      return $response->withJson([
        'reason' => 'User Id cannot be empty.'
      ], 400);
    }

    // Check the User is a member
    $user = $room->members()->find($userId);
    if (is_null($user)) {
      return $response->withJson([
        'reason' => 'User is not in the room.'
      ], 409);
    }

    // Check the User is already an admin
    $admin = $room->admins()->find($userId);
    if (!is_null($admin)) {
      return $response->withJson([
        'reason' => 'User is already an admin.'
      ], 409);
    }

    // Promote User to adminship
    $room->users()->updateExistingPivot($userId, ['status' => 1]);
    return $response->withJson($room);
  }

  /**
  * Demotes a User from adminship
  */
  public function fireAdmin($request, $response) {
    // Get Room
    $room = Room::find($request->getParam('roomId'));

    // Get userId
    $userId = $request->getParam('userId');
    if (self::isNullOrWhitespace($userId)) {
      return $response->withJson([
        'reason' => 'User Id cannot be empty.'
      ], 400);
    }

    // Check the User is a member
    $user = $room->members()->find($userId);
    if (is_null($user)) {
      return $response->withJson([
        'reason' => 'User is not in the room.'
      ], 409);
    }

    // Check the User is already an admin
    $admin = $room->admins()->find($userId);
    if (is_null($admin)) {
      return $response->withJson([
        'reason' => 'User is not an admin.'
      ], 409);
    }

    // Promote User to adminship
    $room->users()->updateExistingPivot($userId, ['status' => 0]);
    return $response->withJson($room);
  }
}
