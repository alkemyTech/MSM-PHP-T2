<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|exists:usuarios,email',
            'password' => 'required'
        ]);
        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::user()->id);
            $token = $user->createToken('token')->accessToken;
            return response()->ok(['Authentication Token' => $token, 'Logged-in user' => $user]);
        }
        return response()->unauthorized();
    }
}