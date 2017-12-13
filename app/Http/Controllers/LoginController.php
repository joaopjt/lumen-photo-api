<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Authenticate user and return a token validated.
     *
     * @return Illuminate\Http\Response
     */
    public function makeLogin(Request $req) {
        $this->validate($req, [
            'email' => 'required|email',
            'pass' => 'required'
        ]);

        $user = User::where('email', $req->email)
            ->where('pass', $req->pass)
            ->first();

        if ($user) {
            return response()->json(['token' => $user->api_key], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
