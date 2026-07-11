<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DownloadLinkClick;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30'); // Default 30 days
        $startDate = Carbon::now()->subDays($period)->startOfDay();

        $query = DownloadLinkClick::where('created_at', '>=', $startDate);
        
        $linkType = $request->get('link_type', 'all');
        if ($linkType !== 'all') {
            $query->where('link_type', $linkType);
        }

        // Aggregate Data
        $totalClicks = (clone $query)->count();
        $downloadClicks = (clone $query)->where('link_type', 'download')->count();
        $generalClicks = (clone $query)->where('link_type', 'general')->count();

        // By OS
        $osData = (clone $query)->select('os_type', DB::raw('count(*) as count'))
                                ->groupBy('os_type')
                                ->get();

        // By Country
        $countryData = (clone $query)->select('country', DB::raw('count(*) as count'))
                                     ->groupBy('country')
                                     ->orderBy('count', 'desc')
                                     ->limit(10)
                                     ->get();

        // By Date (Chart)
        $clicksByDate = (clone $query)->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                                      ->groupBy('date')
                                      ->orderBy('date', 'asc')
                                      ->get();

        $dates = [];
        $clicks = [];
        foreach ($clicksByDate as $c) {
            $dates[] = $c->date;
            $clicks[] = $c->count;
        }

        return view('admin.analytics.index', compact(
            'totalClicks',
            'downloadClicks',
            'generalClicks',
            'osData',
            'countryData',
            'dates',
            'clicks',
            'period',
            'linkType'
        ));
    }
}
