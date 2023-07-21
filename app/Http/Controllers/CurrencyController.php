<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $currency = Currency::with('status')->whereHas('status', function ($query) {
            $query->where('description', 'Activo');
        })->get();

        return response()->json(['status' => true, 'data' => $currency]);
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
            // 'description' => 'required|string',
            'value' => 'required|decimal:2'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }
        if ($request->has('image')) {
            $image = $request->image;
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            $path = public_path('currency');
            $image->move($path, $image_name);
        } else {
            return response()->json(['status' => false, 'message' => 'La imagen es obligatoria'], 400);
        }
        // Busca el status 'Inactivo' o créalo si no existe
        $inactive_status = Status::firstOrCreate(['description' => 'Inactivo']);
        $active_status = Status::firstOrCreate(['description' => 'Activo']);
        // Obtén el último registro activo
        $last_active_currency = Currency::where('status_id', '!=', $inactive_status->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Si hay un registro activo, actualiza su estado a 'Inactivo'
        if ($last_active_currency) {
            $last_active_currency->status()->associate($inactive_status);
            $last_active_currency->save();
        }
        $currency = Currency::create([
            'description' => 'Dolar',
            'value' => $request->value,
            'image' => 'currency/' . $image_name,
        ]);
        $currency->status()->associate($active_status);
        $currency->save();
        return response()->json(['status' => true, 'data' => $currency], 200);
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
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function show(Currency $currency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function edit(Currency $currency)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Currency $currency)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Currency $currency)
    {
        //
    }
}
