<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function financial()
    {
        return view('admin.reports.financial');
    }
}
