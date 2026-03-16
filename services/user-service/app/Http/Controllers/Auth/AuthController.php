<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequestForm;
use App\Http\Requests\Auth\RefreshRequestForm;
use App\Models\Auth\User;
use App\Models\Auth\UserDevices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function login(LoginRequestForm $request)
    {
        try {

            $data = $request->validated();

            $user = $this->user->where('username', $data['data']['username'])->where('is_deleted', false)->first();

            if (!$user) {
                return $this->errorResponse('Data Karyawan Tidak Ditemukan', 401);
            }

            if (!Hash::check($data['data']['password'], $user->password)) {
                return $this->errorResponse('Password Salah', 401);
            }

            $role = $user->getRoleNames();
            $rolesArray = $role instanceof \Illuminate\Support\Collection ? $role->toArray() : (array) $role;
            $claims = [
                'roles' => $rolesArray,
                'uid' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
            ];

            $token = JWTAuth::claims($claims)->fromUser($user);

            if (!$token) {
                return $this->errorResponse('Gagal membuat token', 500);
            }

            $refreshToken = Str::random(80);

            try {
                $user->last_login = now();
                $user->user_token = Str::random(80);
                $user->refresh_token = $refreshToken;
                $user->save();
            } catch (\Throwable $e) {
                Log::warning('Failed to update last_login/user_token/refresh_token for user id ' . $user->id . ': ' . $e->getMessage());
            }

            DB::beginTransaction();
            try {
                UserDevices::updateOrCreate([
                    'user_id' => $user->id,
                ],[
                    'user_id' => $user->id,
                    'device_token' => $data['data']['device_token'] ?? null,
                    'unique_id' => $data['data']['unique_id'] ?? null,
                    'device_info' => $data['data']['device_info'] ?? null,
                    'bundle_id' => $data['data']['bundle_id'] ?? null,
                    'os' => $data['data']['os'] ?? null,
                    'app_name' => $data['data']['appName'] ?? null,
                ]);

                DB::commit();

                $ttl = auth()->factory()->getTTL() * 60;

                return $this->successResponse([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $ttl,
                    'refresh_token' => $user->refresh_token,
                    'user' => $user->only(['id', 'name', 'email', 'username', 'last_login'])
                ], 'Login berhasil', 200);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to save user device after login for user id ' . $user->id . ': ' . $e->getMessage());
                $ttl = auth()->factory()->getTTL() * 60;
                return $this->successResponse([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $ttl,
                    'refresh_token' => $refreshToken,
                    'user' => $user->only(['id', 'name', 'email', 'username', 'last_login'])
                ], 'Login berhasil (device save failed)', 200);
            }


        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function logout()
    {
        try {

            $user = auth('api')->user();

            DB::beginTransaction();

            $user->last_login = Carbon::now();
            $user->save();

            $userDevice = UserDevices::where('user_id', $user->id)->first();
            if ($userDevice) {
//                $userDevice->device_token = null;
                $userDevice->unique_id = null;
                $userDevice->device_info = null;
                $userDevice->bundle_id = null;
                $userDevice->os = null;
                $userDevice->save();
            }

            auth('api')->logout();

            DB::commit();

            return $this->successResponse(null, 'Logout berhasil', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function refresh(RefreshRequestForm $request)
    {
        try {
            $data = $request->validated();

            $user = $this->user->where('refresh_token', $data['refresh_token'])->where('is_deleted', false)->first();

            if (!$user) {
                return $this->errorResponse('Refresh token tidak valid', 401);
            }

            $role = $user->getRoleNames();
            $rolesArray = $role instanceof \Illuminate\Support\Collection ? $role->toArray() : (array) $role;
            $token = JWTAuth::claims(['roles' => $rolesArray, 'uid' => $user->id])->fromUser($user);

            if (!$token) {
                return $this->errorResponse('Gagal membuat token baru', 500);
            }

            $newRefresh = Str::random(80);

            try {
                DB::beginTransaction();
//                $user->user_token = $token;
                $user->refresh_token = $newRefresh;
                $user->save();
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::warning('Failed to rotate refresh token for user id ' . $user->id . ': ' . $e->getMessage());
            }

            $ttl = auth()->factory()->getTTL() * 60;

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl,
                'refresh_token' => $user->refresh_token,
                'user' => $user->only(['id', 'name', 'email', 'username', 'last_login'])
            ], 'Refresh berhasil', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }

    public function me(Request $request)
    {
        try {

            $user = auth('api')->user();

            if (!$user) {
                return $this->errorResponse('User tidak ditemukan', 404);
            }

            return $this->successResponse($user->only(['id', 'name', 'email', 'username', 'last_login']), 'Data user berhasil diambil', 200);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if (app()->environment('local')) {
                return $this->errorResponse($e->getMessage(), 500);
            }
            return $this->errorResponse('Terjadi Kesalahan di Server', 500);
        }
    }
}
