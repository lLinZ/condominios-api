<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $user = $request->user();
        $pagos = Payment::whereHas('status', function ($query) {
            $query->where('description', 'Activo');
        })->where('user_id', $user->id)->get();

        return $pagos;
    }

    public function approve(Payment $payment)
    {
        if (!$payment) {
            return response()->json(['status' => false, 'errors' => 'No existe el pago'], 400);
        }
        $active_status = Status::firstOrNew(['description' => 'Activo']);
        $payment->status()->associate($active_status);
        return response()->json(['status' => true, 'data' => $payment], 200);
    }
    public function decline(Payment $payment)
    {
        if (!$payment) {
            return response()->json(['status' => false, 'errors' => 'No existe el pago'], 400);
        }
        $active_status = Status::firstOrNew(['description' => 'Inactivo']);
        $payment->status()->associate($active_status);
        return response()->json(['status' => true, 'data' => $payment], 200);
    }

    public function get_pending_payments()
    {
        $payments = Payment::with('status')->whereHas('status', function ($query) {
            $query->where('description', 'Pendiente');
        })->get();
        return response()->json(['status' => true, 'data' => $payments]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        //
        $pagos = Payment::whereHas('status', function ($query) {
            $query->where('description', 'Activo');
        })->get();
        return $pagos;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //

        if ($request->has('image')) {
            $image = $request->image;
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            $path = public_path('payment');
            $image->move($path, $image_name);
        } else {
            return response()->json(['status' => false, 'message' => 'La imagen es obligatoria'], 400);
        }

        $currencies = ['Dolares', 'Bolivares'];
        $payment_types = ['Efectivo', 'Transferencia'];

        $validator = Validator::make(
            $request->all(),
            [
                'currency' => 'required|in:' . implode(',', $currencies),
                'payment_type' => 'required|in:' . implode(',', $payment_types),
                'amount' => 'required|decimal:2',
                'description' => 'string|max:255',
            ],
            [
                'currency.required' => 'El tipo de moneda es obligatorio',
                'currency.in' => 'Moneda invalida',
                'payment_type.required' => 'El tipo de pago es obligatorio',
                'payment_type.in' => 'Tipo de pago invalido',
                'amount.required' => 'El monto es obligatorio',
                'amount.decimal' => 'Monto invalido',
                'description.max' => 'La descripcion debe ser maximo 255 caracteres'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Error de validacion', 'errors' => $validator->errors()], 400);
        }

        $payment = Payment::create([
            'currency' => $request->currency,
            'payment_type' => $request->payment_type,
            'amount' => $request->amount,
            'description' => $request->description,
            'image' => 'payment/' . $image_name,
        ]);
        $status = Status::firstOrNew(['description' => 'Pendiente']);
        $status->save();
        $user_logged = $request->user();
        $payment->user()->associate($user_logged);
        $payment->status()->associate($status);
        $payment->save();
        return response()->json(['status' => true, 'message' => 'Pago registrado', 'data' => $payment], 200);
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
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
