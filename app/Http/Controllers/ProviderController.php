<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $providers = Provider::whereHas('status', function ($query) {
            $query->where('description', 'Activo');
        })->get();
        if (isset($providers)) {
            return response()->json(['status' => true, 'data' => $providers], 200);
        } else {
            return response()->json(['status' => false, 'errors' => ['No se encontraron proveedores']], 400);
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
            'name' => 'required|string',
            'rif' => 'required|string',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'name.string' => 'Nombre invalido',
            'rif.required' => 'El rif es obligatorio',
            'rif.string' => 'Rif invalido',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $provider = Provider::create([
                'name' => $request->name,
                'rif' => $request->rif,
            ]);
            $status = Status::firstOrNew(['description' => 'Activo']);
            $provider->status()->associate($status);
            $provider->save();
            return response()->json(['status' => true, 'message' => 'Se ha creado el proveedor', 'data' => $provider], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => ['No se logro crear el proveedor']], 400);
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
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function show(Provider $provider)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function edit(Provider $provider)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Provider $provider)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy(Provider $provider)
    {
        //
    }
}
