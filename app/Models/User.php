<?php

namespace ChatApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model {
  use SoftDeletes;

  protected $dates = ['deleted_at'];

  protected $hidden = ['password'];

  /**
  * The rooms that user is a member
  */
  public function rooms() {
    return $this->belongsToMany('ChatApp\Models\Room', 'members')->withTimestamps()->withPivot('status')->wherePivotIn('status', [0, 1]);
  }
}
