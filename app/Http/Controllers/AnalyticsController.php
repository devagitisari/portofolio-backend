<?php

namespace App\Http\Controllers;

use App\Models\Analytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // Public endpoint to track page views
    public function track(Request $request)
    {
        $analytics = Analytics::create([
            'page' => $request->input('page'),
            'path' => $request->input('path'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
        ]);

        return response()->json(['message' => 'Page tracked successfully'], 201);
    }

    // Admin endpoint to get analytics overview
    public function overview()
    {
        // Get total unique visitors (by IP)
        $uniqueVisitors = Analytics::distinct('ip_address')->count();

        // Get total page views
        $totalPageViews = Analytics::count();

        // Get page views in last 7 days
        $pageViewsLast7Days = Analytics::where('visited_at', '>=', now()->subDays(7))->count();

        // Get page views in last 30 days
        $pageViewsLast30Days = Analytics::where('visited_at', '>=', now()->subDays(30))->count();

        // Get most visited pages
        $topPages = Analytics::select('page', DB::raw('COUNT(*) as views'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // Get daily views for the last 7 days
        $dailyViews = Analytics::select(DB::raw('DATE(visited_at) as date'), DB::raw('COUNT(*) as views'))
            ->where('visited_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'data' => [
                'uniqueVisitors' => $uniqueVisitors,
                'totalPageViews' => $totalPageViews,
                'pageViewsLast7Days' => $pageViewsLast7Days,
                'pageViewsLast30Days' => $pageViewsLast30Days,
                'topPages' => $topPages,
                'dailyViews' => $dailyViews,
            ],
        ]);
    }
}
