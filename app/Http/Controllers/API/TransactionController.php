<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\BonusRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\TopupRequest;
use App\Models\Address;
use App\Models\Midtrans;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['items.product'])->find($id);

            if ($transaction)
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ada',
                    404
                );
        }

        $transaction = Transaction::with(['items.product.productCategory', 'items.product.galleries', 'paymentMethod', 'addressCategory'])->where('user_id', Auth::user()->id);

        if ($status)
            $transaction->where('status', $status);

        return ResponseFormatter::success(
            // $transaction->paginate($limit),
            $transaction->orderByDesc('updated_at')->get(),
            'Data list transaksi berhasil diambil'
        );
    }

    public function checkout(CheckoutRequest $request)
    {
        $user = User::find(Auth::user()->id);
        $payment_method = PaymentMethod::find($request->payment_method_id);

        if ($payment_method->name == 'Wallet') {
            if ($user->balance < ($request->total_price + $request->shipping_price)) {
                return ResponseFormatter::error([
                    'message' => 'Your balance is not enough',
                ], 'Transaction Failed', 500);
            }
            $user->update(['balance' => $user->balance - ($request->total_price + $request->shipping_price)]);
        }

        $address = Address::findOrFail($request->address_id);
        $address_category_id = $address->address_category_id;
        $province = $address->province->name;
        $city = $address->city;
        $cityType = $city->cityType->name;
        $cityName = $city->name;
        $name = $address->name;
        $phone = $address->phone;
        $detail = $address->detail;
        $additional = $address->additional;

        if (!$additional) {
            $address = $detail . ', ' . $cityType . ' ' . $cityName . ', ' . $province;
        } else {
            $address = $detail . ' (' . $additional . '), ' . $cityType . ' ' . $cityName . ', ' . $province;
        }

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'address_category_id' => $address_category_id,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'payment_method_id' => $request->payment_method_id,
            'status' => $request->status
        ]);

        foreach ($request->items as $product) {
            TransactionItem::create([
                'user_id' => $user->id,
                'product_id' => $product['id'],
                'transaction_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        if ($payment_method->name == 'Transfer') {
            $midtrans = Midtrans::create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'order_id' => rand(),
                'type' => 'checkout',
            ]);

            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = array(
                'transaction_details' => array(
                    'order_id' => $midtrans->order_id,
                    'gross_amount' => $transaction->total_price + $transaction->shipping_price,
                ),
                'customer_details' => array(
                    'first_name' => $user->name,
                    'last_name' => '',
                    'email' => $user->email,
                    'phone' => $user->phone,
                ),
            );

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            return ResponseFormatter::success([
                'snapToken' => $snapToken,
                'transaction' => $transaction->load('items.product')
            ], 'Pesanan berhasil dibuat');
        }

        return ResponseFormatter::success([
            'transaction' => $transaction->load('items.product')
        ], 'Pesanan berhasil dibuat');
    }

    public function topup(TopupRequest $request)
    {
        $user = Auth::user();

        $midtrans = Midtrans::create([
            'user_id' => $user->id,
            'order_id' => rand(),
            'type' => 'topup',
        ]);

        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = array(
            'transaction_details' => array(
                'order_id' => $midtrans->order_id,
                'gross_amount' => $request->amount,
            ),
            'customer_details' => array(
                'first_name' => $user->name,
                'last_name' => '',
                'email' => $user->email,
                'phone' => $user->phone,
            ),
        );

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return ResponseFormatter::success([
            'snapToken' => $snapToken,
            'amount' => $request->amount
        ], 'Pesanan berhasil dibuat');
    }

    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                $midtrans = Midtrans::whereOrderId($request->order_id)->first();
                if ($midtrans->type == 'checkout') {
                    $midtrans->transaction->update(['status' => 'SHIPPING']);
                }
                if ($midtrans->type == 'topup') {
                    $user = $midtrans->user;
                    $user->update(['balance' => $user->balance + $request->gross_amount]);
                }
            }
        }
    }

    public function addBonus(BonusRequest $request)
    {
        $user = User::find(Auth::user()->id);
        $user->update(['balance' => $user->balance + $request->amount]);

        return ResponseFormatter::success(
            $user,
            'Berhasil mendapat bonus Rp. ' . $request->amount
        );
    }
}
