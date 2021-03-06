<?php

namespace App;

use Exception;
use App\Photo;
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

    public function removePhoto(int $id) {
        $photo = Photo::where('albumn_id', $this->id)
            ->where('id', $id);

        $photo->delete();

        return $photo;
    }

    public function photo(int $id) {
        $photo = $this->hasMany('App\Photo')->where('id', $id)->firstOrFail();

        return $photo;
    }

    public function photos() {
      return $this->hasMany('App\Photo')->get();
    }

    public function publicPhotos() {
      return $this->hasMany('App\Photo')->where('public', '1')->get();
    }

    public static function getAlbumns($query) {
        $r = ['code' => 200];

        try {
            $r['data'] = $query->get()->all();

            return $r;
        } catch (Exception $e) {
            return [
                'code' => 500,
                'data' => [
                    'message' => 'Internal server error',
                    'details' => $e->getMessage()
                ]
            ];
        }
    }
}
