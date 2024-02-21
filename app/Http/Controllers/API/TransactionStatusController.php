<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StatusRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionStatusController extends Controller
{
    public function update($id, StatusRequest $request)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => $request->status]);

        return ResponseFormatter::success(
            $transaction,
            'Status berhasil diubah menjadi ' . $request->status
        );
    }
}
