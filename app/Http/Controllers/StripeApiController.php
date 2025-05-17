<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StripeApiController extends Controller
{
    final public function createCheckout(Order $order)
    {
        try {
            DB::beginTransaction();
            $order->load(['order_detail']);
            if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
                return response()->json(['message' => 'Order already paid'], 400);
            }

            $response = (new StripeManager())->createCheckout($order);
            (new PaymentTransaction())->createTransaction($response);
            DB::commit();
            return redirect()->away($response->url);
            // return response()->json([
            //     'message' => 'Checkout created successfully',
            //     'url'     => $response->url,
            // ]);
        } catch (Throwable $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getLine(), $e->getFile());
            Log::error('Error while creating checkout', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error while creating checkout'], 500);
        }
    }


}
