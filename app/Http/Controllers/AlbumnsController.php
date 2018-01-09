<?php

namespace App\Http\Controllers;

use Validator;
use App\Albumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumnsController extends Controller
{
    /**
     * List own user albumns if authenticated
     * and the public ones if not.
     *
     * @return Illuminate\Http\Response
     */
    public function list(Request $req) {
        $validator = Validator::make($req->query(), [
            'name' => 'string|nullable',
            'limit' => 'integer|nullable',
            'offset' => 'integer|nullable',
            'sort' => 'string|nullable'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else {
            $query = new Albumn;

            if($req->query('name')) {
                $query = $query->where('name', 'like', '%' . $req->query('name') . '%');
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
                $query = $query->where('owner_id', Auth::user()->id);
            } else {
                $query = $query->where('public', true);
            }

            $albumns = Albumn::getAlbumns($query);

            return response()->json($albumns['data'], $albumns['code']);
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
            }

            if($req->hasHeader('authorization')) {
                if(Auth::check()) {
                    if($albumn->hasPrivilege(Auth::user()->id)) {
                        return response()->json($albumn, 200);
                    }
                }

                return response()->json(['error' => 'Unauthorized.'], 401);
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
            'public' => 'nullable|boolean'
        ]);

        $albumn = new Albumn();

        $albumn->name = $req->input('name');
        $albumn->public = ($req->input('public')) ? $req->input('public') : 0;
        $albumn->owner_id = $req->user()->id;

        $albumn->save();

        if ($albumn->id) {
            return response()->json($albumn, 201);
        }

        return response()->json(['error' => 'Internal server error.'], 500);
    }

    /**
     * Edit a specific albumn
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
        // After, we should delete all pictures in the albumn and only after
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
