<?php

namespace App\Http\Controllers;

use Validator;
use App\Photo;
use App\Albumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PhotosController extends Controller
{
    /**
     * List albumn photos
     *
     * @return Illuminate\Http\Response
     */
    public function list(Request $req, $albumnId) {
        $validator = Validator::make($req->query(), [
            'name' => 'string|nullable',
            'url' => 'string|nullable',
            'limit' => 'integer|nullable',
            'offset' => 'integer|nullable',
            'sort' => 'string|nullable'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else {
            $query = new Photo;
            $albumn = Albumn::find($albumnId);

            if(!$albumn) {
                return response()->json(['error' => 'Albumn not found'], 404);
            }

            if(!$albumn->isPublic()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if($req->query('name')) {
                $query = $query->where('name', 'like', '%' . $req->query('name') . '%');
            }

            if($req->query('url')) {
                $query = $query->where('url', 'like', '%' . $req->query('url') . '%');
            }

            if($req->query('limit')) {
                $query = $query->limit($req->query('limit'));
            }

            if($req->query('offset')) {
                $query = $query->offset($req->query('offset'));
            }

            if($req->query('sort')) {
                $columns = $req->query('sort');

                foreach(explode(',', $columns) as $column) {
                    if($column) {
                        $sort = $column;
                        $order = 'asc';
                        $value = explode('-', $sort, 2);

                        if(count($value) == 2) {
                            $sort = $value[1];
                            $order = 'desc';
                        }

                        $query = $query->orderBy($sort, $order);
                    }
                }
            }

            if($req->hasHeader('authorization') && Auth::check()) {
                if(!$albumn->hasPrivilege(Auth::user()->id)) {
                    $query = $query->where('public', 1);
                }
            } else {
                $query = $query->where('public', 1);
            }

            $photos = Photo::getPhotos($query);

            return response()->json($photos['data'], $photos['code']);
        }

    }

    /**
     * Get a specific albumn photo record
     *
     * @return Illuminate\Http\Response
     */
    public function get(Request $req, $albumnId, $photoId) {
        if($req->hasHeader('authorization')) {
            if (Auth::check()) {
                $albumn = Albumn::find($albumnId);

                if($albumn) {
                    if($albumn->isPublic()) {
                        $photo = $albumn->photo($photoId);

                        if($photo) {
                            return response()->json($photo, 200);
                        }

                        return response()->json(['error' => 'Photo not found.'], 404);
                    } else {
                        if($albumn->hasPrivilege(Auth::user()->id)) {
                            $photo = $albumn->photo($photoId);

                            if($photo->hasPrivilege(Auth::user()->id)) {
                                return response()->json($photo, 200);
                            }
                        }

                        return response()->json(['error' => 'Unauthorized.'], 401);
                    }
                }

                return response()->json(['error' => 'Albumn not found.'], 404);
            } else {
                return response()->json(['error' => 'Authentication has failed.'], 401);
            }
        } else {
            $albumn = Albumn::find($albumnId);

            if($albumn) {
                if($albumn->isPublic()) {
                    $photo = $albumn->photo($photoId);

                    if ($photo) {
                        if($photo->public) {
                            return response()->json($photo, 200);
                        }

                        return response()->json(['error' => 'Unauthorized.'], 401);
                    }

                    return response()->json(['error' => 'Photo not found.'], 404);
                }

                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            return response()->json(['error' => 'Albumn not found.'], 404);
        }
    }

    /**
     * Add a new photo to albumn
     *
     * @return Illuminate\Http\Response
     */
    public function add(Request $req, $albumnId) {
        $this->validate($req, [
            'name' => 'required|string',
            'url' => 'required|string|active_url',
            'public' => 'nullable|boolean'
        ]);

        try {
            $albumn = Albumn::find($albumnId)->where('owner_id', $req->user()->id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Invalid or unexistent albumn',
                'details' => $e->getMessage()
            ], 400);
        }

        if ($albumn) {
            $photo = new Photo();

            $photo->name = $req->input('name');
            $photo->url = $req->input('url');
            $photo->public = ($req->input('public')) ? $req->input('public') : 0;
            $photo->albumn_id = $albumn->id;
            $photo->owner_id = $req->user()->id;

            $photo->save();

            if ($photo->id) {
                return response()->json($photo, 201);
            } else {
                return response()->json(['error' => 'Internal server error.'], 500);
            }
        }
    }

    /**
     * edit a specific albumn photo
     *
     * @return Illuminate\Http\Response
     */
    public function edit(Request $req, $albumnId, $photoId) {
        $this->validate($req, [
            'name' => 'string|nullable',
            'url' => 'string|active_url|nullable',
            'public' => 'boolean|nullable'
        ]);

        $albumn = Albumn::find($albumnId);

        if($albumn) {
            if ($albumn->hasPrivilege($req->user()->id)) {
                try {
                    $photo = $albumn->photo($photoId);
                } catch(ModelNotFoundException $e) {
                    return response()->json([
                        'error' => 'Photo not found.',
                        'details' => $e->getMessage()
                    ], 404);
                }

                if($photo) {
                    if ($req->has('name')) {
                        $photo->name = $req->input('name');
                    }

                    if ($req->has('url')) {
                        $photo->url = $req->input('url');
                    }

                    if ($req->has('public')) {
                        $photo->public = $req->input('public');
                    }

                    $photo->save();

                    return response()->json($photo, 200);
                }
            }

            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        return response()->json(['error' => 'Albumn not found.'], 404);
    }

    /**
     * Remove a photo from albumn
     *
     * @return Illuminate\Http\Response
     */
    public function remove(Request $req, $albumnId, $photoId) {
        $albumn = Albumn::find($albumnId);

        if($albumn) {
            if($albumn->hasPrivilege($req->user()->id)) {
                $albumn->removePhoto($photoId);

                return response('', 204);
            }

            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        return response()->json(['error' => 'Not found.'], 404);
    }
}
