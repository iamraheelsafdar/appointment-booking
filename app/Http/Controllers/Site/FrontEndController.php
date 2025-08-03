<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Site\FrontEndService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class FrontEndController extends Controller
{

    /**
     * @return View|Factory|Application|\Illuminate\Contracts\Foundation\Application
     */
    public function frontendView(): View|Factory|Application|\Illuminate\Contracts\Foundation\Application
    {
        return FrontEndService::frontendView();
    }
}
