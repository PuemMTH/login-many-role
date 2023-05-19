<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Role\AdminInfoController;
use Illuminate\Http\Request;
use App\Models\admin_info;
use App\Models\student_info;
use App\Models\teacher_info;


class AuthController extends Controller
{
    public function login(Request $request) {
        $table_user = [admin_info::class, student_info::class, teacher_info::class];
        $user = null;
        foreach($table_user as $table) {
            $user = $table::where('email', $request->email)->first();
            if($user) break;
        }
        if(!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['These credentials do not match our records.']
            ], 404);
        }
        if($user instanceof admin_info) $role = 'admin';
        else if($user instanceof student_info) $role = 'student';
        else if($user instanceof teacher_info) $role = 'teacher';
        else return response([
            'message' => ['These credentials do not match our records.']
        ], 404);

        return response()->json([
            'user' => $user,
            'role' => $role,
            'token' => $user->createToken('token', [$role])->plainTextToken,
        ], 200);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout success'
        ], 200);
    }

    public function logoutAll(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout all success'
        ], 200);
    }

    public function me(Request $request) {
        $user = $request->user();
        $role = '';

        if($user instanceof admin_info) $role = 'admin';
        else if($user instanceof student_info) $role = 'student';
        else if($user instanceof teacher_info) $role = 'teacher';

        return response()->json([
            'user' => $user,
            'role' => $role,
        ], 200);
    }

    public function resendVerificationEmail(Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response(['message' => 'Email already verified.']);
        }
        $request->user()->sendEmailVerificationNotification();
        return response(['message' => 'Email verification link sent on your email id.']);
    }

}
