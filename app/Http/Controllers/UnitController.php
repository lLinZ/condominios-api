<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\Building;
use Illuminate\Http\Request;

class UnitController extends Controller
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
    public function asign_unit_type(Unit $unit, UnitType $unit_type)
    {
        $unit->unit_type()->associate($unit_type);
        $unit->save();
        return response()->json(['status' => true, 'data' => $unit_type]);
    }
    public function asign_owner(Unit $unit, User $user)
    {
        $unit->user()->associate($user);
        $unit->save();
        return response()->json(['status' => true, 'data' => $user]);
    }
    /**
     * Display a listing of the resource corresponding to the logged user.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_units(Request $request)
    {
        //
        $user = $request->user();
        $units = Unit::where(['user_id' => $user->id])->get();
        return response()->json(['status' => true, 'data' => $units, 'user' => $user]);
    }

    public function get_units_by_building(Request $request, Building $building)
    {
        $units = Unit::with('unit_type', 'building', 'user', 'status')->where(['building_id' => $building->id])->get();
        return response()->json(['status' => true, 'data' => $units]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function edit(Unit $unit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Unit $unit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Unit $unit)
    {
        //
    }
}
