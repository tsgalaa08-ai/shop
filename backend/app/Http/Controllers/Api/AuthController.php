<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) return response()->json(['message' => 'Нэвтрэх мэдээлэл буруу байна.'], 422);
        return ['data' => ['user' => $user, 'token' => $user->createToken('web')->plainTextToken]];
    }
    public function register(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'email' => 'required|email|unique:users,email', 'phone' => 'nullable|string|max:30', 'password' => 'required|string|min:8|confirmed']);
        $user = User::create([...$data, 'password' => Hash::make($data['password'])]);
        return response()->json(['data' => ['user' => $user, 'token' => $user->createToken('web')->plainTextToken]], 201);
    }
    public function logout(Request $request) { $request->user()->currentAccessToken()?->delete(); return ['message' => 'Амжилттай гарлаа.']; }
}