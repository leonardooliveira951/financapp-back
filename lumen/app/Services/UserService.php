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
            return null;
        }

        $token = JWT::encode([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + 3600
        ], env('APP_KEY'));

        $response['token'] = $token;
        $response['user'] = $user;

        return $response;
    }

    public static function changeName($request)
    {
        if (!User::where('id', $request->id)->exists()) {
            return null;
        }
        return User::where('id', $request->id)->update([
            'name' => $request->all()['name']
        ]);
    }

    public static function getUser($request)
    {
        return JWT::decode($request->bearerToken(), env('APP_KEY'), array('HS256'));
    }

    public static function changePassword($request)
    {
        $user = User::where('id', $request->user()['id'])->first();

        if (is_null($user) || !Hash::check($request->current_password, $user->password)) {
            return null;
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        $data_to_login['email'] = $user->email;
        $data_to_login['password'] = $request->new_password;
        return self::loginUser($data_to_login);

    }
}
