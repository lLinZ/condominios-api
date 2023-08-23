<?php

namespace App\Http\Controllers;

use App\Models\CommonExpense;
use App\Models\Condominium;
use App\Models\Currency;
use App\Models\Provider;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommonExpenseController extends Controller
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

    public function get_expenses_by_condominium(Condominium $condominium)
    {
        try {
            $expenses = CommonExpense::with('status', 'currency', 'provider', 'condominium')->where(['condominium_id' => $condominium->id])->whereHas('status', function ($query) {
                $query->where('description', 'Activo');
            })->get();
            if (!isset($expenses)) {
                return response()->json(['status' => false, 'errors' => ['No se encontraron gastos comunes de ese condominio']], 400);
            }
            return response()->json(['status' => true, 'message' => 'Exito, se han encontrado los gastos comunes', 'data' => $expenses], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => ['No se logro conectar a la base de datos, intente mas tarde']], 400);
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
        $validator = Validator::make(
            $request->all(),
            [
                'condominium_id' => 'required',
                'provider_id' => 'required',
                'description' => 'required|string',
                'currency_type' => 'required|string',
                'amount' => 'required|decimal:2',
            ],
            [
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
            $common_expense = CommonExpense::create([
                'description' => $request->description,
                'amount' => $request->amount,
                'currency_type' => $request->currency_type,
            ]);
            $status = Status::firstOrNew(['description' => 'Activo']);
            $currency = Currency::whereHas('status', function ($query) {
                $query->where('description', 'Activo');
            })->first();
            $common_expense->status()->associate($status);
            $common_expense->condominium()->associate($condominium);
            $common_expense->provider()->associate($provider);
            $common_expense->currency()->associate($currency);
            $common_expense->save();
            return response()->json(['status' => true, 'message' => 'Exito, se ha creado el gasto comun', 'data' => $common_expense], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => ['No se logro crear el gasto comun', $th->getMessage()]], 400);
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
     * @param  \App\Models\CommonExpense  $commonExpense
     * @return \Illuminate\Http\Response
     */
    public function show(CommonExpense $commonExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CommonExpense  $commonExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(CommonExpense $commonExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CommonExpense  $commonExpense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CommonExpense $commonExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CommonExpense  $commonExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(CommonExpense $commonExpense)
    {
        //
    }
}
