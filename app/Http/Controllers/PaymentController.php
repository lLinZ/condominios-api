<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Payment;
use App\Models\Status;
use App\Models\Unit;
use App\Models\User;
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

    public function approve(Payment $payment, Request $request)
    {

        // if (!$payment) {
        //     return response()->json(['status' => false, 'errors' => 'No existe el pago'], 400);
        // }
        // $active_status = Status::firstOrNew(['description' => 'Aprobado']);
        // $payment->status()->associate($active_status);
        // return response()->json(['status' => true, 'data' => $payment], 200);

        $user = User::where('id', $payment->user_id)->first();
        $debts = Debt::with('status', 'unit')->where(['user_id' => $user->id, 'unit_id' => $payment->id])->where('calculable_amount', '>', 0)->whereHas('status', function ($query) {
            $query->where(['description' => 'Activo']);
        })->get();
        $payments = Payment::where('calculable_amount', '>', 0)->where(['user_id' => $payment->user_id, 'unit_id' => $payment->id])->get();
        $transaction = [];
        // foreach ($debts as $current_debt) {
        //     $break_payment_loop = 0;
        //     foreach ($payments as $current_payment) {
        //         // if ($break_payment_loop > 0) return;
        //         // if ($current_payment->calculable_amount == 0) return;
        //         // if ($current_debt->calculable_amount == 0) {
        //         // $break_payment_loop = 1;
        //         // } else {
        //         $result = $current_debt->calculable_amount - $current_payment->calculable_amount;
        //         $current_debt->calculable_amount = $current_debt->calculable_amount < $current_payment->calculable_amount ? 0 : $current_debt->calculable_amount - $current_payment->calculable_amount;
        //         $current_payment->calculable_amount = $current_payment->calculable_amount < $current_debt->calculable_amount ? 0 : $current_debt->calculable_amount - $current_payment->calculable_amount;
        //         $transaction['Deuda ' . $current_debt->id][] = $current_debt->calculable_amount;
        //         $transaction['Pago ' . $current_payment->id][] = $current_payment->calculable_amount;
        //         // }
        //     }
        //     $transaction['deuda'][$current_debt->id] = $current_debt->calculable_amount;
        // }
        return response()->json(['status' => true, 'data' => [$debts, $payments]], 200);
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
    public function count_pending_payments(Request $request)
    {
        $results = Payment::where(['user_id' => $request->user()->id])->whereHas('status', function ($query) {
            $query->where(['description' => 'Pendiente']);
        })->get();
        $data = $results->count();
        return response()->json(['status' => true, 'data' => $data], 200);
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
        try {
            //code...
            $status = Status::firstOrNew(['description' => 'Pendiente']);
            $status->save();
            $unit = Unit::where('id', $request->unit_id)->first();
            $user_logged = $request->user();
            $payment = Payment::create([
                'currency' => $request->currency,
                'payment_type' => $request->payment_type,
                'amount' => $request->amount,
                'calculable_amount' => $request->amount,
                'description' => $request->description,
                'image' => 'payment/' . $image_name,
            ]);
            $payment->user()->associate($user_logged);
            $payment->unit()->associate($unit);
            $payment->status()->associate($status);
            $payment->save();
            return response()->json(['status' => true, 'message' => 'Pago registrado', 'data' => $payment], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'errors' => ['Error al intentar registrar el pago']], 400);
            //throw $th;
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
