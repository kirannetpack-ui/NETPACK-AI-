<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function customer()
    {
        return view('dashboards.customer');
    }

    public function seller()
    {
        return view('dashboards.seller');
    }

    public function rider()
    {
        return view('dashboards.rider');
    }

    public function partner()
    {
        return view('dashboards.partner');
    }
}