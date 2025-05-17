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

    final public function payGate(Order $order)
    {
        try {
            $redirect_url = (new PayGatePaymentManager())->getPayGatePaymentData($order);
            return response()->json([
                'message' => 'Redirect to PayGate',
                'url'     => $redirect_url,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getLine(), $e->getFile());
            Log::error('Error while creating checkout', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error while creating checkout'], 500);
        }
    }

    final public function paygateCallback(Request $request)
    {
        Log::info('PAYGATE_CALLBACK_RECEIVED', ['data' => $request->all()]);
        try {
            DB::beginTransaction();
            $response = $request->all();
            $transaction = (new PaymentTransaction())->getPendingTransaction('transaction_id', $response['address_in']);
            if (!$transaction) {
                Log::error('PAYGATE_CALLBACK_API_ERROR', ['message' => 'Payment transaction not found', 'tran_id' => $response['address_in'], 'request' => $request->all()]);
                return response()->json(['message' => 'Payment transaction not found'], 404);
            }
            $transaction->update([
                'response' => json_encode($response),
            ]);

            (new PaymentTransaction())->updateSuccess($request, $transaction);
            (new Order())->updateStatus($transaction->order, Order::STATUS_COMPLETED, Order::PAYMENT_STATUS_PAID);
            (new CompanyPackage())->storeCompanyPackage($transaction->order);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('ERROR_WHILE_UPDATING_PAYMENT_ATTEMPT', ['message' => $e->getMessage(), 'tran_id' => $response['address_in'], 'data' => $request->all()]);
            return response()->json(['message' => 'Error while updating payment attempt'], 500);
        }
    }


    final public function webhookSuccess(Request $request)
    {
        Log::info('WEBHOOK_SUCCESS_PAYMENT_RECEIVED', ['data' => $request->all()]);
        try {
            DB::beginTransaction();
            $response = (new StripeManager())->getCheckoutSession($request->data['object']['id'] ?? null);
            if ($response?->payment_status === 'paid') {
                Log::info('WEBHOOK_SUCCESS_PAYMENT_PAID', ['response' => $response]);
                $transaction = (new PaymentTransaction())->getPendingTransaction('transaction_id', $response->id);
                if (!$transaction) {
                    Log::error('WEBHOOK_SUCCESS_API_ERROR', ['message' => 'Payment transaction not found', 'tran_id' => $response->id, 'request' => $request->all(), 'response' => $response]);
                    return response()->json(['message' => 'Payment transaction not found'], 404);
                }
                $transaction->update([
                    'invoice_id' => $request->data['object']['invoice'] ?? null,
                ]);
                // $transaction->load(['order']);
                // (new PaymentTransaction())->updateSuccess($request, $transaction);
                // (new Order())->updateStatus($transaction->order, Order::STATUS_COMPLETED, Order::PAYMENT_STATUS_PAID);
                // (new CompanyPackage())->storeCompanyPackage($transaction->order);
            } else {
                Log::error('WEBHOOK_SUCCESS_PAYMENT_NOT_PAID', ['response' => $response, 'request' => $request->all()]);
                return response()->json(['message' => 'Payment not paid'], 400);
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('ERROR_WHILE_UPDATING_PAYMENT_ATTEMPT', ['message' => $e->getMessage(), 'tran_id' => $request->data['object']['id'], 'data' => $request->all()]);
            return response()->json(['message' => 'Error while updating payment attempt'], 500);
        }

        return response()->json(['message' => 'Payment success webhook received']);
    }


    final public function invoicePaid(Request $request)
    {
        Log::info('INVOICE_PAID_WEBHOOK_RECEIVED', ['data' => $request->all()]);
        try {
            DB::beginTransaction();
            $response = (new StripeManager())->getInvoice($request->data['object']['id'] ?? null);
            if ($response?->status === 'paid') {
                Log::info('INVOICE_PAID_PAYMENT_PAID', ['response' => $response]);
                $transaction = (new PaymentTransaction())->getByColumn('invoice_id', $response->id);
                if (!$transaction) {
                    Log::error('INVOICE_PAID_API_ERROR', ['message' => 'Payment transaction not found', 'tran_id' => $response->id, 'request' => $request->all(), 'response' => $response]);
                    return response()->json(['message' => 'Payment transaction not found'], 404);
                }
                $transaction->load(['order']);
                (new PaymentTransaction())->updateSuccess($request, $transaction);
                (new Order())->updateStatus($transaction->order, Order::STATUS_COMPLETED, Order::PAYMENT_STATUS_PAID);
                (new CompanyPackage())->storeCompanyPackage($transaction->order);
            } else {
                Log::error('INVOICE_PAID_PAYMENT_NOT_PAID', ['response' => $response, 'request' => $request->all()]);
                return response()->json(['message' => 'Payment not paid'], 400);
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('ERROR_WHILE_UPDATING_PAYMENT_ATTEMPT', ['message' => $e->getMessage(), 'tran_id' => $request->data['object']['id'], 'data' => $request->all()]);
            return response()->json(['message' => 'Error while updating payment attempt'], 500);
        }

        return response()->json(['message' => 'Payment success webhook received']);
    }

}
