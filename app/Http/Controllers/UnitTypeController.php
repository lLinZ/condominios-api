<?php

namespace App\Http\Controllers;

use App\Models\UnitType;
use App\Models\Building;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function get_unit_types_by_building(Building $building)
    {
        $unit_types = UnitType::whereHas('status', function ($query) {
            $query->where('description', 'Activo');
        })->where(['building_id' => $building->id])->get();
        return response()->json(['status' => true, 'data' => $unit_types]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Building $building)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'description' => 'required|string|unique:unit_types',
                'size' => 'required|decimal:2',
                'aliquot' => 'required|decimal:2',
            ],
            [
                'description.unique' => 'Ya existe un tipo de unidad con esta descripcion',
                'description.required' => 'La descripcion es obligatoria',
                'size.unique' => 'El metraje es obligatorio',
                'size.decimal' => 'Metraje invalido, solo numeros',
                'aliquot.unique' => 'La alicuota es obligatoria',
                'aliquot.decimal' => 'Alicuota invalida, solo numeros',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Error de validacion', 'errors' => $validator->errors()], 400);
        }
        try {
            $unit_type = UnitType::create([
                'description' => $request->description,
                'size' => $request->size,
                'aliquot' => $request->aliquot,
            ]);
            $status = Status::where(['description' => 'Activo'])->first();
            $unit_type->building()->associate($building);
            $unit_type->status()->associate($status);
            $unit_type->save();
            return response()->json(['status' => true, 'message' => 'Se ha registrado el tipo de unidad correctamente', 'data' => $unit_type], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'No se logro registrar el tipo de unidad'], 500);
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
     * @param  \App\Models\UnitType  $unitType
     * @return \Illuminate\Http\Response
     */
    public function show(UnitType $unitType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UnitType  $unitType
     * @return \Illuminate\Http\Response
     */
    public function edit(Building $building, UnitType $unit_type, Request $request)
    {
        //
        $validator = Validator::make(
            $request->all(),
            [
                'description' => 'required|string|unique:unit_types,description,' . $unit_type->id,
                'size' => 'required|decimal:2',
                'aliquot' => 'required|decimal:2',
            ],
            [
                'description.unique' => 'Ya existe un tipo de unidad con esta descripcion',
                'description.required' => 'La descripcion es obligatoria',
                'size.required' => 'El metraje es obligatorio',
                'size.unique' => 'Metraje invalido',
                'size.decimal' => 'Metraje invalido, solo numeros',
                'aliquot.required' => 'La alicuota es obligatoria',
                'aliquot.unique' => 'Alicuota invalido',
                'aliquot.decimal' => 'Alicuota invalida, solo numeros',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Error de validacion', 'errors' => $validator->errors()], 400);
        }
        try {
            $unit_type->description = $request->description;
            $unit_type->size = $request->size;
            $unit_type->aliquot = $request->aliquot;
            $unit_type->save();
            return response()->json(['status' => true, 'message' => 'Se ha editado el tipo de unidad correctamente', 'data' => $unit_type], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'No se logro editar el tipo de unidad'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UnitType  $unitType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UnitType $unitType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UnitType  $unitType
     * @return \Illuminate\Http\Response
     */
    public function destroy(UnitType $unitType)
    {
        //
    }
}
