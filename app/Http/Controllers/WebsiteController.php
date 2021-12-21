<?php

namespace App\Http\Controllers;

use App\Traits\DateRangeTrait;
use App\Website;
use App\Http\Requests\StoreWebsiteRequest;
use App\Http\Requests\UpdateWebsiteRequest;
use App\Traits\WebsiteTrait;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    use WebsiteTrait, DateRangeTrait;

    /**
     * List the Websites.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = ($request->input('sort') == 'desc' ? 'desc' : 'asc');

        $websites = Website::where('user_id', '=', $request->user()->id)->orderBy('url', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'sort' => $sort]);

        return view('websites.content', ['view' => 'list', 'websites' => $websites, 'range' => $this->range()]);
    }

    /**
     * Show the create Website form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('websites.content', ['view' => 'new']);
    }

    /**
     * Show the edit Website form.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        $website = Website::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('websites.content', ['view' => 'edit', 'website' => $website]);
    }

    /**
     * Store the Website.
     *
     * @param StoreWebsiteRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreWebsiteRequest $request)
    {
        $this->websiteStore($request);

        $request->user()->has_websites = true;
        $request->user()->save();

        return redirect()->route('dashboard')->with('success', __(':name has been created.', ['name' => parse_url($request->input('url'))['host']]));
    }

    /**
     * Update the Website.
     *
     * @param UpdateWebsiteRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateWebsiteRequest $request, $id)
    {
        $website = Website::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->websiteUpdate($request, $website);

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Delete the Website.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
        $website = Website::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $website->delete();

        $request->user()->has_websites = Website::where('user_id', '=', $request->user()->id)->count() > 0;
        $request->user()->save();

        return redirect()->route('dashboard')->with('success', __(':name has been deleted.', ['name' => $website->url]));
    }
}
