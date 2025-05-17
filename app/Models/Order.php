<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = [];

    public const DEFAULT_TRIAL_DAYS = 7;

    public const PAYMMENT_PENDING = 2;
    public const PAYMENT_COMPELTED = 1;

    public const PAYMENT_METHOD_STRIPE      = 1;
    public const PAYMENT_METHOD_PAYPAL      = 2;
    public const PAYMENT_METHOD_PAYGATE     = 3;
    public const PAYMENT_METHOD_READIES     = 4;
    public const PAYMENT_METHOD_NOWPAYMENTS = 5;

    public const PAYMENT_METHOD_LIST = [
        self::PAYMENT_METHOD_STRIPE      => 'Stripe',
        self::PAYMENT_METHOD_PAYGATE     => 'PayGate',
        self::PAYMENT_METHOD_READIES     => 'Readies',
        self::PAYMENT_METHOD_NOWPAYMENTS => 'NowPayments',
    ];
}
