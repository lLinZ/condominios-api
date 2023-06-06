<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function get_logged_user_data(Request $request)
    {
        return response()->json(['data' => $request->user()]);
    }
    /**
     * Registrar Cliente
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'segundo_nombre' => 'string|max:255',
            'apellido' => 'required|string|max:255',
            'segundo_apellido' => 'string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'cedula' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'nombre' => $request->nombre,
            'segundo_nombre' => $request->segundo_nombre,
            'apellido' => $request->apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'cedula' => $request->cedula,
            'password' => Hash::make($request->password),
        ]);

        // Obtener status activo o crear status si no existe
        $status = Status::firstOrNew(['description' => 'Activo']);
        $status->save();
        // Se asocia el status al usuario
        $user->status()->associate($status);

        // Obtener rol cliente o crear rol si no existe
        $role = Role::firstOrNew(['description' => 'Cliente']);
        $role->save();
        // Se asocia el rol al usuario
        $user->role()->associate($role);

        // Se guarda el usuario
        $user->save();

        // Token de auth
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 200);
    }

    /**
     * Registrar administrador de condominios
     */
    public function register_admin_de_condominios(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'segundo_nombre' => 'string|max:255',
            'apellido' => 'required|string|max:255',
            'segundo_apellido' => 'string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'cedula' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'nombre' => $request->nombre,
            'segundo_nombre' => $request->segundo_nombre,
            'apellido' => $request->apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'cedula' => $request->cedula,
            'password' => Hash::make($request->password),
        ]);
        // Obtener status activo o crear status si no existe
        $status = Status::firstOrNew(['description' => 'Activo']);
        $status->save();
        // Se asocia el status al usuario
        $user->status()->associate($status);

        // Obtener rol cliente o crear rol si no existe
        $role = Role::firstOrNew(['description' => 'Administrador de Condominios']);
        $role->save();
        // Se asocia el rol al usuario
        $user->role()->associate($role);

        // Se guarda el usuario
        $user->save();

        // Token de auth
        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json(['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer',]);
    }

    /**
     * Login de usuario
     */
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Bienvenido ' . $user->name,
            'accessToken' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Obtener usuarios por roles
     */
    public function getRole(User $user)
    {
        return response()->json(["users" => $user->roleById()], 200);
    }

    /**
     * Cerrar sesion
     */
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'Has cerrado sesion exitosamente'
        ];
    }
}
