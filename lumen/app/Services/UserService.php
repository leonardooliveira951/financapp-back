<?php


namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public static function createUser($data)
    {
        if (User::where('email', $data['email'])->exists()) {
            return null;
        }
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data["password"]);
        $user->save();
        return $user;
    }

    public static function loginUser($data)
    {
        $user = User::where('email', $data['email'])->first();

        if (is_null($user) || !Hash::check($data['password'], $user->password)) {
            return response()->json('Usuário ou senha inválidos', 401);
        }

        $token = JWT::encode([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + 3600
        ], env('APP_KEY'));
        return $token;
    }

}
