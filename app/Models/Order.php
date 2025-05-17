<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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

    final public function prepare_data(Request $request, Package $package, User $user)
    {
        return [
            'company_id'     => $request->header('company_id'),
            'package_id'     => $package->id,
            'user_id'        => $user->id,
            'price'          => $package->price,
            'currency'       => Package::DEFAULT_CURRENCY,
            'discount'       => $request->input('discount', 0),
            'total'          => $package->price - $request->input('discount', 0),
            'status'         => self::STATUS_PENDING,
            'payment_status' => self::PAYMENT_STATUS_PENDING,
            'payment_method' => $request->input('payment_method', self::PAYMENT_METHOD_STRIPE),
        ];
    }


    final public function store_data(Request $request, Package $package, User | Authenticatable $user): Builder | Model
    {
        $order = self::query()->create($this->prepare_data($request, $package, $user));
        $order->order_detail()->create((new OrderDetail())->prepare_data($package, $order));
        // (new CompanyPackage())->storeCompanyPackage($order);
        return $order;
    }

    final public function updateStatus(Order $order, int $status, int $payment_status): bool
    {
        return $order->update([
            'status'         => $status,
            'payment_status' => $payment_status,
        ]);
    }
}
