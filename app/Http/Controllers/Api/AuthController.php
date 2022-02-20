<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Enums\UserType;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController {

    public function __construct() {
        $this->middleware('auth:api', array('except' => array('login', 'userRegistration', 'verifyOtp', 'userLogin')));
    }

    public function userRegistration(UserRegistrationRequest $request) {
        $data = $request->validated();
        //Delete Old OPTS
        Otp::where($request->only('phone'))->delete();

        $otp = random_int(100000, 999999);
        $data['otp'] = $otp;
        $data = Otp::create($data);

        return $this->success($data, "OTP created for 1 hour");
    }

    public function userLogin(UserLoginRequest $request) {
        $data = $request->validated();
        $user = User::where('phone', $data['phone'])->first();
        if (!$user) {
            return $this->failed(null, "No user Found with the mobile number");
        }
        //Delete Old OPTS
        Otp::where($request->only('phone'))->delete();

        $otp = random_int(100000, 999999);
        //TODO remove otp
        $data['otp'] = $otp;
        $data = Otp::create($data);
        return $this->success($data, "OTP created");
    }

    public function verifyOtp(VerifyOtpRequest $request) {
        //TODO add time validation
        $data = $request->validated();
        $otp = Otp::where($request->only('phone'))->latest()->first();

        if (!$otp) {
            return $this->failed(null, "No user Found with the mobile number");
        }
        if ($data['otp'] != $otp['otp']) {
            return $this->failed(null, "Otp did not match");
        }

        $user = User::where('phone', $data['phone'])->first();

        if ($user) {
            $otp->delete();
            return $this->respondWithToken($this->auth()->login($user));
        } else {
            $data['name'] = $otp['name'];
            $data['phone_verified_at'] = now();
            $user = User::create($data);
            $user->attachRoles(array(UserType::CUSTOMER));
            $otp->delete();
            return $this->respondWithToken($this->auth()->login($user));
        }
    }

    public function login(AdminLoginRequest $request) {
        $user = User::where($request->only('phone'))->first();
        if (!$user) {
            return $this->failed([], "No user Found with the mobile number"); // signal that the phone doesn't exist in db
        }
        if (!Hash::check($request->input('password'), $user->password) || $user->status !== Status::ACTIVE) {
            return $this->unauthorized(); // phone number exists, but the token doesn't match
        }

        return $this->respondWithToken($this->auth()->login($user)); // everything ok, lets login
    }

    public function logout() {
        $this->auth()->logout();

        return response()->json(array('message' => 'Successfully logged out'));
    }

    public function refresh() {
        return $this->respondWithToken($this->auth()->refresh());
    }

    protected function respondWithToken($token) {
        return $this->success(array(
            'access_token' => $token ?: 'NAN',
            'token_type'   => 'Bearer',
            'expires_in'   => $this->auth()->factory()->getTTL(),
        ));
    }
}
