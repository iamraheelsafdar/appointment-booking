<?php

namespace App\Services\Transaction;

use App\Http\Resources\Transaction\GetTransactionResource;
use App\Interfaces\Transaction\TransactionInterface;
use App\Filters\Transation\SearchTransactionFilter;
use Illuminate\Contracts\Foundation\Application;
use App\Http\Resources\DataCollection;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pipeline\Pipeline;
use App\Models\Transaction;

class TransactionService implements TransactionInterface
{
    public static function transactionView($request): Factory|View|\Illuminate\Foundation\Application|Application
    {
        $transactions = app(Pipeline::class)
            ->send(Transaction::query())
            ->through([
                SearchTransactionFilter::class,
            ])
            ->thenReturn()
//            ->where('id', auth()->user()->id)
            ->latest()
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 10);

        $transactionCollection = new DataCollection($transactions);
        $transactionCollection->setResourceClass(GetTransactionResource::class);
        $transactions = $transactionCollection->toArray($request);
        return view('backend.transaction.transaction', ['transactions' => $transactions]);
    }

    /**
     * Update transaction status manually
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function updateTransactionStatus($request): \Illuminate\Http\JsonResponse
    {
        try {
            $transaction = Transaction::find($request->transaction_id);
            
            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found.'], 404);
            }

            // Only admin can update transaction status
            if (auth()->user()->user_type !== 'Admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }

            $transaction->update([
                'status' => $request->status,
                'notes' => $request->notes ?? null,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Transaction status updated successfully.',
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
