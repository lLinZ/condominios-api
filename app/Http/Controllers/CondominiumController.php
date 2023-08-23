<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Debt;
use App\Models\Building;
use App\Models\CommonExpense;
use App\Models\Condominium;
use App\Models\NoCommonExpense;
use App\Models\Status;
use App\Models\User;
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
    public function get_units_by_condominium(Condominium $condominium, Request $request)
    {

        $building =  Building::where(['id' => $condominium->building_id])->first();
        $units = Unit::with('status', 'unit_type', 'user')->whereHas('status', function ($query) {
            $query->where(['description' => 'Activo']);
        })->where(['building_id' => $building->id])->get();
        return response()->json(['status' => true, 'data' => $units, 'building' => $building]);
    }
    public function close_condominium(Request $request, Condominium $condominium)
    {
        $gastos = [];
        $edificio = Building::where('id', $condominium->building_id)->first();
        $units = Unit::with('unit_type')->where('building_id', $edificio->id)->get();
        $common_expenses = CommonExpense::where(['condominium_id' => $condominium->id])->get();
        $status = Status::firstOrCreate(['description' => 'Activo']);
        $errors = [];
        foreach ($common_expenses as $ce) {
            $gastos['common_total'][0] = isset($gastos['common_total'][0]) ? floatval($gastos['common_total'][0]) + floatval($ce->amount) : floatval($ce->amount);
        }
        foreach ($units as $unit) {
            foreach ($common_expenses as $ce) {
                $gastos['common'][$unit->id][0] = ($gastos['common_total'][0] * $unit->unit_type->aliquot) / 100;
            }
            $uncommon_expenses = NoCommonExpense::where(['condominium_id' => $condominium->id, 'unit_id' => $unit->id])->get();
            foreach ($uncommon_expenses as $ue) {
                $gastos['uncommon'][$unit->id][0] = isset($gastos['uncommon'][$unit->id][0]) ? $gastos['uncommon'][$unit->id][0] + floatval($ue->amount) : floatval($ue->amount);
            }
            try {
                $debt = Debt::create([
                    'description' => $condominium->description . ' ' . $condominium->month . '/' . $condominium->year . ' - ' . $unit->name,
                    'common_expenses' => $gastos['common'][$unit->id][0],
                    'no_common_expenses' => isset($gastos['uncommon'][$unit->id][0]) ? $gastos['uncommon'][$unit->id][0] : 0,
                    'total_debt' => isset($gastos['uncommon'][$unit->id][0]) ? floatval($gastos['uncommon'][$unit->id][0]) + floatval($gastos['common'][$unit->id][0]) : floatval($gastos['common'][$unit->id][0]),
                    'calculable_amount' => isset($gastos['uncommon'][$unit->id][0]) ? floatval($gastos['uncommon'][$unit->id][0]) + floatval($gastos['common'][$unit->id][0]) : floatval($gastos['common'][$unit->id][0]),
                ]);
                $user = User::where('id', $unit->user_id)->first();
                if (is_object($user)) {
                    $debt->user()->associate($user);
                }
                $debt->unit()->associate($unit);
                $debt->status()->associate($status);
                $debt->save();
            } catch (\Throwable $th) {
                $errors[] = $unit->name . ' No se logro crear la deuda';
            }
        }
        $status_closed = Status::firstOrCreate(['description' => 'Cerrado']);
        $condominium->status()->associate($status_closed);
        $condominium->save();
        if (count($errors) > 0) {
            return response()->json(['status' => false, 'errors' => $errors], 400);
        } else {
            return response()->json(['status' => true, 'data' => $gastos], 200);
        }
    }
    public function cancel_condominium(Request $request, Condominium $condominium)
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
