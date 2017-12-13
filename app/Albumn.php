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

    public $timestamps = true;

    public function getAllPhotos($albumn_id) {
      return Photos::where('albumn_id', $albumn_id)->get();
    }
}
