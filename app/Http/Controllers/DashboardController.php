<?php

namespace App\Http\Controllers;

use App\Stat;
use App\Traits\DateRangeTrait;
use App\Website;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use DateRangeTrait;

    /**
     * Show the Dashboard page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        // If the user previously selected a plan
        if (!empty($request->session()->get('plan_redirect'))) {
            return redirect()->route('checkout.index', ['id' => $request->session()->get('plan_redirect')['id'], 'interval' => $request->session()->get('plan_redirect')['interval']]);
        }
        
        $range = $this->range();

        $visitors = Stat::whereIn('website_id', Website::select('id')->where('user_id', '=', $request->user()->id))
            ->where('name', '=', 'visitors')
            ->whereBetween('date', [$range['from'], $range['to']])
            ->sum('count');

        $visitorsOld = Stat::whereIn('website_id', Website::select('id')->where('user_id', '=', $request->user()->id))
            ->where('name', '=', 'visitors')
            ->whereBetween('date', [$range['from_old'], $range['to_old']])
            ->sum('count');

        $pageviews = Stat::whereIn('website_id', Website::select('id')->where('user_id', '=', $request->user()->id))
            ->where('name', '=', 'pageviews')
            ->whereBetween('date', [$range['from'], $range['to']])
            ->sum('count');

        $pageviewsOld = Stat::whereIn('website_id', Website::select('id')->where('user_id', '=', $request->user()->id))
            ->where('name', '=', 'pageviews')
            ->whereBetween('date', [$range['from_old'], $range['to_old']])
            ->sum('count');

        $search = $request->input('search');
        $sort = ($request->input('sort') == 'desc' ? 'desc' : 'asc');

        $websites = Website::with([
                'visitors' => function ($query) use($range) {
                    $query->whereBetween('date', [$range['from'], $range['to']]);
                },
                'pageviews' => function ($query) use($range) {
                    $query->whereBetween('date', [$range['from'], $range['to']]);
                }]
            )
            ->where('user_id', $request->user()->id)
            ->when($search, function($query) use ($search) {
                return $query->searchUrl($search);
            })
            ->orderBy('url', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'sort' => $sort, 'from' => $range['from'], 'to' => $range['to']]);

        return view('dashboard.content', ['visitors' => $visitors, 'visitorsOld' => $visitorsOld, 'pageviews' => $pageviews, 'pageviewsOld' => $pageviewsOld, 'range' => $range, 'websites' => $websites]);
    }
}
