<?php

namespace App\Http\Controllers;

use App\Albumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumnsController extends Controller
{
    /**
     * List user albumns if authenticated
     * and the public ones if not.
     *
     * @return Illuminate\Http\Response
     */
    public function list(Request $req) {
        if($req->header('authorization')) {
            Auth::check(); // Make the user authentication

            $albumns = Albumn::where('owner_id', Auth::user()->id)->get();

            return response()->json($albumns, 200);
        } else {
            $albumns = Albumn::where('public', 1)->get();

            return response()->json($albumns, 200);
        }
    }

    /**
     * Get a specific album
     *
     * @return Illuminate\Http\Response
     */
    public function get(Request $req, $albumnId) {
        $albumn = Albumn::find($albumnId);

        if($albumn) {
            if($albumn->public) {
                return response()->json($albumn, 200);
            } else {
                if($req->user()->id == $albumn->owner_id) {
                    return response()->json($albumn, 200);
                } else {
                    return response()->json(['error' => 'Unauthorized.'], 401);
                }
            }
        }

        return response()->json(['error' => 'Not found.'], 404);
    }

    /**
     * Add a new albumn
     *
     * @return Illuminate\Http\Response
     */
    public function add(Request $req) {
        $this->validate($req, [
            'name' => 'required|string',
            'public' => 'nullable|integer'
        ]);

        $albumn = new Albumn();

        $albumn->name = $req->input('name');
        $albumn->public = ($req->input('public')) ? $req->input('public') : 0;
        $albumn->owner_id = $req->user()->id;

        $albumn->save();

        if ($albumn->id) {
            return response()->json($albumn, 201);
        } else {
            return response()->json(['error' => 'Internal server error.'], 500);
        }
    }

    /**
     * edit a specific albumn
     *
     * @return Illuminate\Http\Response
     */
    public function edit(Request $req, $albumnId) {
        $this->validate($req, [
            'name' => 'nullable|string',
            'public' => 'boolean'
        ]);

        $albumn = Albumn::find($albumnId);

        if($albumn) {
            if ($albumn->owner_id == $req->user()->id) {
                if ($req->input('name')) {
                    $albumn->name = $req->input('name');
                }

                var_dump($req->input('public'));

                if (!empty($req->input('public'))) {
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
            if($req->user()->id == $albumn->owner_id) {
                $albumn->delete();
                return response('', 204);
            } else {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }
        }

        return response()->json(['error' => 'Not found.'], 404);
    }
}
