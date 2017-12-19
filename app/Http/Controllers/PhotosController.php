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
     * Get a specific photo
     *
     * @return Illuminate\Http\Response
     */
    public function get(Request $req, $photoId) {
        if($req->hasHeader('authorization')) {
            if (Auth::check()) {
                $photo = Photo::find($photoId);

                if($photo) {
                    if($photo->isPublic()) {
                        return response()->json($photo, 200);
                    } else {
                        if($photo->hasPrivilege(Auth::user()->id)) {
                            return response()->json($photo, 200);
                        }

                        return response()->json(['error' => 'Unauthorized.'], 401);
                    }
                }

                return response()->json(['error' => 'Not found.'], 404);
            } else {
                return response()->json(['error' => 'Authentication has failed.'], 401);
            }
        } else {
            $photo = Photo::find($photoId);

            if($photo && $photo->isPublic()) {
                return response()->json($photo, 200);
            }

            return response()->json(['error' => 'Unauthorized.'], 401);
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
     * edit a specific albumn
     *
     * @return Illuminate\Http\Response
     */
    public function edit(Request $req, $albumnId) {
        $this->validate($req, [
            'name' => 'string',
            'public' => 'boolean'
        ]);

        $albumn = Albumn::find($albumnId);

        if($albumn) {
            if ($albumn->hasPrivilege($req->user()->id)) {
                if ($req->has('name')) {
                    $albumn->name = $req->input('name');
                }

                if ($req->has('public')) {
                    $albumn->public = $req->input('public');
                }

                $albumn->save();

                return response()->json($albumn, 200);
            } else {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }
        }

        return response()->json(['error' => 'Not found.'], 404);
    }

    /**
     * Remove a albumn
     *
     * @return Illuminate\Http\Response
     */
    public function remove(Request $req, $albumnId) {
        // First, shoud verify if the authenticated user is the owner of the albumn.
        // After, should delete all pictures in the albumn and only after
        // delete the album.

        $albumn = Albumn::find($albumnId);

        if($albumn) {
            if($albumn->hasPrivilege($req->user()->id)) {
                $albumn->delete();
                return response('', 204);
            }

            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        return response()->json(['error' => 'Not found.'], 404);
    }
}
