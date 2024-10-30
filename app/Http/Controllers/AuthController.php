<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
   /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response('Ошибка авторизации', 401);
        }

        $usr = auth()->user();
        if(!$usr->is_active){
            return response('Доступ запрещён. Обратитесь к администратору', 403);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = new UserResource(auth()->user());

        return response()->json($user);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request){
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|max:255',
            'password' => 'required|same:passwordConfirmation',
            'passwordConfirmation' => 'required|same:password',
        ]);

        $colors = ['108, 117, 125', '25, 135, 84', '13, 202, 240', '255, 193, 7', '220, 53, 69'];

        $new_user = null;
        $new_user['name'] = $request->name;
        $new_user['email'] = $request->email;
        $new_user['password'] = Hash::make($request->password);
        $new_user['color'] = $colors[array_rand($colors, 1)];
        User::create($new_user);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

}
