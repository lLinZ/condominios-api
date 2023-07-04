<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Unit;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return response()->json(['status' => true, 'data' => Building::all()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function mapping(Request $request)
    {
        $abc_hash = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H',
            9 => 'I',
            10 => 'J',
            11 => 'K',
            12 => 'L',
            13 => 'M',
            14 => 'N',
            15 => 'O',
            16 => 'P',
            17 => 'Q',
            18 => 'R',
            19 => 'S',
            20 => 'T',
            21 => 'U',
            22 => 'V',
            23 => 'W',
            24 => 'X',
            25 => 'Y',
            26 => 'Z',
        ];
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|unique:buildings',
            'units_qty' => 'required|numeric',
            'floor_qty' => 'required|numeric',
            'units_per_floor' => 'required|numeric',
            'prefix' => 'required|string',
            'naming_type' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors(), 'data' => $request->all()], 400);
        }
        $building = Building::create([
            'name' => $request->name,
            'floor_qty' => $request->floor_qty,
            'units_qty' => $request->units_qty,
        ]);
        $status = Status::where(['description' => 'Activo'])->first();

        $building->status()->associate($status);
        $building->save();

        $user = $request->user();
        $errors = [];
        $units = [];
        switch ($request->naming_type) {
            case 'numerico':
                for ($floor = 1; $floor <= intval($request->floor_qty); $floor++) {
                    for ($unit_number = 1; $unit_number <= intval($request->units_per_floor); $unit_number++) {
                        $nombre_unidad = $request->prefix . '-' . $floor . '-' . $unit_number;
                        try {
                            $unit = Unit::create([
                                'name' => $nombre_unidad,
                            ]);
                            $unit->status()->associate($status);
                            $unit->building()->associate($building);
                            $unit->save();
                            $units[] = $unit;
                        } catch (\Throwable $th) {
                            $errors[$nombre_unidad][] = $status;
                            $errors[$nombre_unidad][] = $building;
                            $errors[$nombre_unidad][] = $unit;
                        }
                    }
                }
                if (sizeof($errors) > 0) {
                    return response()->json(['status' => false, 'message' => 'Ocurrio un error con las unidades', 'errors' => $errors, 'units' => $units], 400);
                }
                return response()->json(['status' => true, 'message' => 'Unidades creadas', 'units' => $units]);
            case 'alfabetico':
                for ($floor = 1; $floor <= $request->floor_qty; $floor++) {
                    $letras_counter = 1;
                    for ($unit_number = 1; $unit_number <= $request->units_per_floor; $unit_number++) {
                        $sufix = $unit_number < 26 ? $abc_hash[$letras_counter] : $abc_hash[$letras_counter] . $abc_hash[$letras_counter];
                        $nombre_unidad = $request->prefix . '-' . $floor . '-' . $sufix;

                        try {
                            $unit = Unit::create([
                                'name' => $nombre_unidad,
                            ]);
                            $unit->status()->associate($status);
                            $unit->building()->associate($building);
                            $unit->save();
                            $units[] = $unit;
                        } catch (\Throwable $th) {
                            $errors[$nombre_unidad][] = $status;
                            $errors[$nombre_unidad][] = $building;
                            $errors[$nombre_unidad][] = $unit;
                        }
                        $letras_counter++;
                        if ($letras_counter > 26) {
                            $letras_counter = 1;
                        }
                    }
                }
                if (sizeof($errors) > 0) {
                    return response()->json(['status' => false, 'message' => 'Ocurrio un error con las unidades', 'errors' => $errors]);
                }
                return response()->json(['status' => true, 'message' => 'Unidades creadas', 'units' => $units]);
            case 'alfanumerico':
                for ($floor = 1; $floor <= $request->floor_qty; $floor++) {
                    $letras_counter = 1;
                    for ($unit_number = 1; $unit_number <= $request->units_per_floor; $unit_number++) {

                        $sufix = $unit_number < 26 ? ($abc_hash[$letras_counter] . $unit_number) : ($abc_hash[$letras_counter] . $abc_hash[$letras_counter] . $unit_number);
                        $nombre_unidad = $request->prefix . '-' . $floor . '-' . $sufix;

                        try {
                            $unit = Unit::create([
                                'name' => $nombre_unidad,
                            ]);
                            $unit->status()->associate($status);
                            $unit->building()->associate($building);
                            $unit->save();
                            $units[] = $unit;
                        } catch (\Throwable $th) {
                            $errors[$nombre_unidad][] = $status;
                            $errors[$nombre_unidad][] = $building;
                            $errors[$nombre_unidad][] = $unit;
                        }
                        $letras_counter++;
                        if ($letras_counter > 26) {
                            $letras_counter = 1;
                        }
                    }
                }
                if (sizeof($errors) > 0) {
                    return response()->json(['status' => false, 'message' => 'Ocurrio un error con las unidades', 'errors' => $errors]);
                }
                return response()->json(['status' => true, 'message' => 'Unidades creadas', 'units' => $units]);
            default:
                return response()->json(['status' => false, 'message' => 'Tipo de nombrado invalido', 'data' => $request]);
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
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|unique:buildings',
            'units_qty' => 'required|numeric',
            'floor_qty' => 'required|numeric',
        ], [
            'name.unique' => 'Ya existe un edificio con ese nombre',
            'name.required' => 'El nombre del edificio es obligatorio',
            'units_qty.numeric' => 'Cantidad de unidades invalido',
            'units_qty.required' => 'La cantidad de unidades es obligatorio',
            'floor_qty.numeric' => 'Cantidad de pisos invalido',
            'floor_qty.required' => 'La cantidad de pisos es obligatorio',

        ]);

        if ($validation->fails()) {
            return response()->json(['status' => false, 'message' => $validation->errors()], 400);
        }

        $building = Building::create([
            'name' => $request->name,
            'units_qty' => $request->units_qty,
            'floor_qty' => $request->floor_qty,
        ]);

        $status = Status::where(['description' => 'Activo'])->first();

        $building->status()->associate($status);
        $building->save();

        return response()->json(['status' => true, 'message' => 'Edificio registrado', 'data' => $building]);
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
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function show(Building $building)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function edit(Building $building)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Building $building)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function destroy(Building $building)
    {
        //
    }
}
