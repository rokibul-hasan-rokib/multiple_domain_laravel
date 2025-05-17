<?php

namespace App\Manager;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StripeManager
{
    private string $secretKey;
    private string $frontEndUrl;
    private string $currency;
    private string $mode;
    private string $uiMode;

    public function __construct()
    {
        $this->secretKey   = env('STRIPE_SECRET_KEY');
        $this->frontEndUrl = 'https://app.fortinvo.com';
        // $this->frontEndUrl = 'http://localhost:3000';
        $this->currency    = 'sek';
        $this->mode        = 'subscription';
        $this->uiMode      = 'hosted';
    }

    private function prepareItems(Order $order)
    {
        return  [
            [
                'price_data' => [
                    'currency'     => $this->currency,
                    'unit_amount'  => $order->total * 100,
                    'recurring'    => [
                        'interval'       => 'day',
                        'interval_count' => $order->order_detail?->validity,
                    ],
                    'product_data' => [
                        'name' => $order->order_detail?->name,
                    ],
                ],
                'quantity' => 1,
            ],
        ];
    }

    private function prepareCheckOutData(Order $order): array
    {
        $data = [
            'success_url' => $this->frontEndUrl . '/order-success',
            'cancel_url'  => $this->frontEndUrl . '/order-failed',
            'line_items'  => $this->prepareItems($order),
            'mode'        => $this->mode,
            'ui_mode'     => $this->uiMode,
        ];

        if ((new Order())->isFirstPurchase($order->company_id)) {
            $data['subscription_data'] = [
                'trial_period_days' => Order::DEFAULT_TRIAL_DAYS,
            ];
        }

        return $data;
    }

    public function createCheckout(Order $order)
    {
        $stripe   = new \Stripe\StripeClient($this->secretKey);
        $response = $stripe->checkout->sessions->create($this->prepareCheckOutData($order));
        Log::info('CHECKOUT_SESSION_CREATED', ['response' => $response]);
        return $response;
    }

    final public function getCheckoutSession($sessionId)
    {
        $stripe   = new \Stripe\StripeClient($this->secretKey);
        $response = $stripe->checkout->sessions->retrieve($sessionId);
        Log::info('CHECKOUT_SESSION_RETRIEVED', ['response' => $response]);
        return $response;
    }

    final public function getInvoice($invoiceId)
    {
        $stripe   = new \Stripe\StripeClient($this->secretKey);
        $response = $stripe->invoices->retrieve($invoiceId);
        Log::info('INVOICE_RETRIEVED', ['response' => $response]);
        return $response;
    }
}