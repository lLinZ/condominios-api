<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Condominium;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class CondominiumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $condominiums = Condominium::with('status', 'building')->whereHas('status', function ($query) {
            $query->where('description', 'Activo');
        })->get();
        if ($condominiums) {
            return response()->json(['status' => true, 'data' => $condominiums], 200);
        } else {
            return response()->json(['status' => false, 'errors' => $condominiums], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'month' => 'required|string',
            'year' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }
        $building_id = intval($request->building_id);
        $building = Building::where('id', $building_id)->first();
        $condominium_existente = Condominium::where(['month' => $request->month, 'building_id' => intval($request->building_id)])->first();
        if (is_object($condominium_existente)) {
            $errors = ['Ya existe un condominio con ese mes'];
            return response()->json(['status' => false, 'errors' => $errors], 400);
        }
        if (!$building) {
            return response()->json(['status' => false, 'message' => 'No se encuentra el edificio'], 404);
        }
        $condominium = Condominium::create([
            'description' => $request->description,
            'month' => $request->month,
            'year' => $request->year,
        ]);
        $status = Status::where('description', 'Activo')->first();
        $condominium->status()->associate($status);
        $condominium->building()->associate($building);
        $condominium->save();
        return response()->json(['status' => true, 'message' => 'Condominio creado'], 200);
    }
    public function cancel_condominium(Request $request, Condominium $condominium)
    {
        try {
            $status_closed = Status::firstOrCreate('description', 'Cerrado');
            $condominium->status()->associate($status_closed);
            $condominium->save();
            return response()->json(['status' => true, 'message' => 'Condominio cerrado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => ['No se logro realizar la accion']], 400);
        }
    }
    public function close_condominium(Request $request, Condominium $condominium)
    {
        try {
            $status_inactive = Status::firstOrCreate('description', 'Inactivo');
            $condominium->status()->associate($status_inactive);
            $condominium->save();
            return response()->json(['status' => true, 'message' => 'Condominio cancelado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'erros' => ['No se logro realizar la accion']], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Condominium  $condominium
     * @return \Illuminate\Http\Response
     */
    public function show(Condominium $condominium)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Condominium  $condominium
     * @return \Illuminate\Http\Response
     */
    public function edit(Condominium $condominium)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Condominium  $condominium
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Condominium $condominium)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Condominium  $condominium
     * @return \Illuminate\Http\Response
     */
    public function destroy(Condominium $condominium)
    {
        //
    }
}
