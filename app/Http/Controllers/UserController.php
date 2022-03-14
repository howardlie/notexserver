<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Ramsey\Uuid\Uuid;
use Google\AccessToken\Verify as GoogleVerifier;
use DeviceDetector\DeviceDetector;

class UserController extends Controller
{
    public function login(Request $request)
    {
        //$credentials = $request->only('email', 'password');
        $payload = $request->all();
        $gv = new GoogleVerifier();
        $results = $gv->verifyIdToken($payload['payload']['idToken']);

        if (empty($results['name']) || empty($results['email']) || empty($results['picture'])) {
            return response()->json(['error' => 'invalid_credentials'], 400);
        }

        //check if account exists
        $account = Account::firstOrCreate(
            [
                'email' => $results['email']
            ],
        [
            'id' => Uuid::uuid6(),
            'name' => $results['name'],
            'email' => $results['email'],
            'picture' => $results['picture']
        ]);





        $dd = new DeviceDetector($_SERVER['HTTP_USER_AGENT']);
        $dd->parse();
        $user = User::create([
            'name' => $results['name'],
            'email' => $results['email'],
            'device_name' => $dd->getDeviceName(),
            'account_id' => $account['id'],
        ]);
        $token = JWTAuth::fromUser($user);

        return response()->json(compact('account', 'token'));

        /*
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }*/
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }

    public function removeAllAccess() {
        $current_user = auth()->user();
        // remove all access token from DB except the one logged in
        $users = User::where('account_id', $current_user['account_id'])->where('id', '!=', $current_user['id'])->delete();
        return response()->json(['status' => "OK"]);
    }

    public function listAccess() {
        $current_user = auth()->user();
        //get all token with same account from logged in
        $users = User::where('account_id', $current_user['account_id'])->get();

        return response()->json(['status' => "OK", 'devices' => $users]);
    }

    public function logout()
    {

        $current_user = auth()->user();
        auth()->logout();
        User::where('id', $current_user['id'])->delete();
        return response()->json(['message' => 'Successfully logged out', 'status' => "OK"]);
    }
}
