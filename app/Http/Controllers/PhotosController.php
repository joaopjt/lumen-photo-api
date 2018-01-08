<?php

namespace App\Http\Controllers;

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
        if($req->hasHeader('authorization')) {
            if(Auth::check()) {
                $albumn = Albumn::find($albumnId);

                if($albumn) {
                    if ($albumn->hasPrivilege(Auth::user()->id)) {
                        return response()->json($albumn->photos(), 200);
                    }

                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                return response()->json(['error' => 'Not found'], 404);
            }

            return response()->json(['error' => 'Authentication has failed.'], 401);
        } else {
            $albumn = Albumn::find($albumnId);

            if($albumn) {
                if($albumn->isPublic()) {
                    return response()->json($albumn->publicPhotos(), 200);
                }

                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json(['error' => 'Not found'], 404);
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
