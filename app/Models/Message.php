<?php

namespace ChatApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model {
  use SoftDeletes;

  protected $dates = ['deleted_at'];

  // A new message should update room updated_at timestamp
  protected $touches = ['room'];

  /**
  * The room in which the message is sent
  */
  public function room() {
    return $this->belongsTo('ChatApp\Models\Room');
  }

  /**
  * The user who sent the message
  */
  public function user() {
    return $this->belongsTo('ChatApp\Models\User');
  }

}
