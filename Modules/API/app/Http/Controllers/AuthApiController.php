<?php

namespace Modules\API\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Device;
use Modules\Receiver\Models\AttendanceEntry;
use Modules\Receiver\Models\PushLog;

/**
 * @group Authentication
 */
class AuthApiController extends Controller
{
    /**
     * Login
     *
     * Authenticate user and get API token.
     *
     * @bodyParam email string required User email. Example: admin@example.com
     * @bodyParam password string required User password. Example: password
     * @bodyParam device_name string Device identifier. Example: my-pc
     *
     * @response 200 {
     *   "success": true,
     *   "token": "1|abcdefghij",
     *   "user": {"id": 1, "name": "Admin", "email": "admin@example.com"}
     * }
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            "email"       => ["required", "email"],
            "password"    => ["required", "string"],
            "device_name" => ["nullable", "string"],
        ]);

        $user = User::where("email", $data["email"])->first();

        if (!$user || !Hash::check($data["password"], $user->password)) {
            throw ValidationException::withMessages([
                "email" => ["The provided credentials are incorrect."],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(["message" => "Account deactivated."], 403);
        }

        $deviceName = $data["device_name"] ?? ($request->userAgent() ?? "api-client");
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            "success" => true,
            "token"   => $token,
            "user"    => [
                "id"    => $user->id,
                "name"  => $user->name,
                "email" => $user->email,
                "role"  => $user->role,
            ],
        ]);
    }

    /**
     * Logout
     *
     * Revoke the current API token.
     *
     * @authenticated
     * @response 200 {"success": true, "message": "Logged out successfully."}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["success" => true, "message" => "Logged out successfully."]);
    }

    /**
     * Me
     *
     * Get the authenticated user details.
     *
     * @authenticated
     */
    public function me(Request $request)
    {
        $user = $request->user()->load("organizations");
        return response()->json([
            "success" => true,
            "data"    => $user,
        ]);
    }
}
