<?php

namespace App\Http\Controllers\Transaction;

use App\Services\Transaction\TransactionService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * @param Request $request
     * @return View|\Illuminate\Foundation\Application|Factory|Application
     */
    public function transactionView(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return TransactionService::transactionView($request);
    }
}
