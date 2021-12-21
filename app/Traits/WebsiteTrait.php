<?php


namespace App\Traits;

use App\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait WebsiteTrait
{
    /**
     * Store the Website.
     *
     * @param Request $request
     * @return Website
     */
    protected function websiteStore(Request $request)
    {
        $user = Auth::user();

        $website = new Website;

        $website->url = parse_url(str_replace('://www.', '://', $request->input('url')))['host'];
        $website->user_id = $user->id;
        $website->privacy = $request->input('privacy');
        $website->password = $request->input('password');
        $website->email = $request->input('email');
        $website->exclude_bots = ($request->has('exclude_bots') ? $request->input('exclude_bots') : 1);
        $website->exclude_params = $request->input('exclude_params');
        $website->exclude_ips = $request->input('exclude_ips');
        $website->save();

        return $website;
    }

    /**
     * Update the Website.
     *
     * @param Request $request
     * @param Website $website
     * @return Website
     */
    protected function websiteUpdate(Request $request, Website $website)
    {
        if ($request->has('privacy')) {
            $website->privacy = $request->input('privacy');
        }

        if ($request->has('email')) {
            $website->email = $request->input('email');
        }

        if ($request->has('password')) {
            $website->password = $request->input('password');
        }

        if ($request->has('exclude_bots')) {
            $website->exclude_bots = $request->input('exclude_bots');
        }

        if ($request->has('exclude_params')) {
            $website->exclude_params = $request->input('exclude_params');
        }

        if ($request->has('exclude_ips')) {
            $website->exclude_ips = $request->input('exclude_ips');
        }

        $website->save();

        return $website;
    }
}