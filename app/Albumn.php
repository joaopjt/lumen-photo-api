<?php

namespace App;

use Photo;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Albumn extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = 'albumns';
    protected $guarded = ['owner_id'];

    public $timestamps = true;

    public function hasPrivilege(int $userId) {
      return $userId == $this->owner_id;
    }

    public function isPublic() {
        return $this->public;
    }

    public function photos() {
      return $this->hasMany('App\Photo')->get();
    }

    public function publicPhotos() {
      return $this->hasMany('App\Photo')->where('public', '1')->get();
    }
}
