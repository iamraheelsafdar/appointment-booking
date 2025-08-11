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
}
