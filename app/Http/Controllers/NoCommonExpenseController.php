<?php

namespace App\Http\Controllers;

use App\Models\Condominium;
use App\Models\Currency;
use App\Models\NoCommonExpense;
use App\Models\Provider;
use App\Models\Status;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoCommonExpenseController extends Controller
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $validator = Validator::make(
            $request->all(),
            [
                'unit_id' => 'required',
                'condominium_id' => 'required',
                'provider_id' => 'required',
                'description' => 'required|string',
                'currency_type' => 'required|string',
                'amount' => 'required|decimal:2',
            ],
            [
                'unit_id.required' => 'El id de la unidad es obligatorio',
                'condominium_id.required' => 'El id del condominio es obligatorio',
                'provider_id.required' => 'El id del provider es obligatorio',
                'description.required' => 'La descripcion es obligatoria',
                'description.string' => 'La descripcion es invalida',
                'amount.required' => 'El monto es obligatorio',
                'amount.decimal' => 'El monto es invalido, debe tener 2 decimales',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }
        try {
            $condominium = Condominium::where('id', $request->condominium_id)->first();
            $provider = Provider::where('id', $request->provider_id)->first();
            $common_expense = NoCommonExpense::create([
                'description' => $request->description,
                'amount' => $request->amount,
                'currency_type' => $request->currency_type,
            ]);
            $status = Status::firstOrNew(['description' => 'Activo']);
            $unit = Unit::where(['id' => $request->unit_id])->first();
            $currency = Currency::whereHas('status', function ($query) {
                $query->where('description', 'Activo');
            })->first();
            $common_expense->status()->associate($status);
            $common_expense->condominium()->associate($condominium);
            $common_expense->provider()->associate($provider);
            $common_expense->currency()->associate($currency);
            $common_expense->unit()->associate($unit);
            $common_expense->save();
            return response()->json(['status' => true, 'message' => 'Exito, se ha creado el gasto no comun', 'data' => $common_expense], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => ['No se logro crear el gasto no comun', $th->getMessage()]], 400);
        }
    }

    public function get_expenses_by_condominium(Condominium $condominium)
    {
        try {
            $expenses = NoCommonExpense::with('status', 'unit', 'currency', 'provider', 'condominium')->where(['condominium_id' => $condominium->id])->whereHas('status', function ($query) {
                $query->where('description', 'Activo');
            })->get();
            if (!isset($expenses)) {
                return response()->json(['status' => false, 'errors' => ['No se encontraron gastos no comunes de ese condominio']], 400);
            }
            return response()->json(['status' => true, 'message' => 'Exito, se han encontrado los gastos no comunes', 'data' => $expenses], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => ['No se logro conectar a la base de datos, intente mas tarde']], 400);
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
     * @param  \App\Models\NoCommonExpense  $noCommonExpense
     * @return \Illuminate\Http\Response
     */
    public function show(NoCommonExpense $noCommonExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NoCommonExpense  $noCommonExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(NoCommonExpense $noCommonExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NoCommonExpense  $noCommonExpense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NoCommonExpense $noCommonExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NoCommonExpense  $noCommonExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(NoCommonExpense $noCommonExpense)
    {
        //
    }
}
