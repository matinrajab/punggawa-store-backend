<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Transaction;
use App\Models\TransactionItem;
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

        $transaction = Transaction::with(['items.product.category', 'items.product.galleries'])->where('user_id', Auth::user()->id);

        if ($status)
            $transaction->where('status', $status);

        return ResponseFormatter::success(
            // $transaction->paginate($limit),
            $transaction->latest()->get(),
            'Data list transaksi berhasil diambil'
        );
    }

    public function checkout(CheckoutRequest $request)
    {
        $transaction = Transaction::create([
            'midtrans_order_id' => rand(),
            'user_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status
        ]);

        foreach ($request->items as $product) {
            TransactionItem::create([
                'user_id' => Auth::user()->id,
                'product_id' => $product['id'],
                'transaction_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = array(
            'transaction_details' => array(
                'order_id' => $transaction->midtrans_order_id,
                'gross_amount' => $transaction->total_price + $transaction->shipping_price,
            ),
            'customer_details' => array(
                'first_name' => Auth::user()->name,
                'last_name' => '',
                'email' => Auth::user()->email,
                'phone' => Auth::user()->phone,
            ),
        );

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return ResponseFormatter::success([
            'snapToken' => $snapToken,
            'transaction' => $transaction->load('items.product')
        ], 'Pesanan berhasil dibuat');
    }

    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                $transaction = Transaction::whereMidtransOrderId($request->order_id);
                $transaction->update(['status' => 'SHIPPING']);
            }
        }
    }
}
