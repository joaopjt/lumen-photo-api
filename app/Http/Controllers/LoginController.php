<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\User;

class LoginController extends Controller
{
    /**
     * Authenticate user and return a token validated.
     *
     * @return void
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
