<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $roles = Role::all();
        return $roles;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //        
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255|unique:roles',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }
        $role = Role::create([
            'description' => $request->description,
        ]);
        return response()->json(['message' => 'Rol creado exitosamente', 'data' => $role, 'status' => true]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
        return response()->json(['message' => 'Lista de usuarios por rol', 'data' => $role->users], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // $errors = [];
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255|unique:roles,description,' . $role->id,
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['message' => 'No se logro actualizar', 'errors' => $validator->errors()]);
        }
        $role->description = $data['description'];
        $role->save();
        return response()->json(['message' => 'El rol se ha actualizado', 'data' => $role, 'status' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
    }
}
