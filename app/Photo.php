<?php

namespace App;

use Exception;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Photo extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = 'photos';
    protected $guarded = ['owner_id'];

    public $timestamps = true;

    public function hasPrivilege(int $userId)
    {
      return $userId == $this->owner_id;
    }

    public function isPublic()
    {
      return $this->public;
    }

    public function albumn()
    {
      return $this->belongsTo('App\Albumn');
    }

    public static function getPhotos($query) {
        $r = ['code' => 200];

        try {
            $r['data'] = $query->get()->all();
        } catch(Exception $e) {
            $r['code'] = 500;
            $r['data'] = [
                'error' => 'Internal server error',
                'details' => $e->getMessage()
            ];
        }

        return $r;
    }
}
