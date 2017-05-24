<?php

namespace ChatApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model {
  use SoftDeletes;

  protected $dates = ['deleted_at'];

  /**
  * The users in the Room
  */
  public function users() {
    return $this->belongsToMany('ChatApp\Models\User', 'members')->withTimestamps()->withPivot('status');
  }

  /**
  * The admins in the Room
  */
  public function admins() {
    return $this->belongsToMany('ChatApp\Models\User', 'members')->withTimestamps()->wherePivot('status', 1);
  }

  /**
  * The members in the Room
  */
  public function members() {
    return $this->belongsToMany('ChatApp\Models\User', 'members')->withTimestamps()->wherePivotIn('status', [0, 1])->withPivot('status');
  }

  /**
  * The left users in the Room
  */
  public function oldMembers() {
    return $this->belongsToMany('ChatApp\Models\User', 'members')->withTimestamps()->wherePivot('status', -1);
  }

  /**
  * The messages in the Room
  */
  public function messages() {
    return $this->hasMany('ChatApp\Models\Message');
  }
}
