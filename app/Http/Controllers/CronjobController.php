<?php

namespace App\Http\Controllers;

use App\Cronjob;
use App\Mail\LimitExceededMail;
use App\Mail\ReportMail;
use App\Recent;
use App\Stat;
use App\User;
use App\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class CronjobController extends Controller
{
    /**
     * Check the account limit stats of the users and takes the required actions,
     * such as disabling the tracking access and sends out warning notification emails.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function check()
    {
        $now = Carbon::now();

        foreach (User::where('has_websites', '=', 1)->cursor() as $user) {
            // Get the total pageviews count of user's account for the required period
            $pageviews = Stat::where('name', '=', 'pageviews')
                ->whereIn('website_id', Website::select('id')->where('user_id', '=', $user->id))
                ->whereBetween('date', [(clone $now)->startOfMonth(), (clone $now)->endOfMonth()])
                ->sum('count');

            // If the pageviews have exceeded the user's current limits
            if ($user->plan->features->pageviews != -1 && $pageviews >= $user->plan->features->pageviews) {
                // If the user's tracking was not previously disabled
                if ($user->can_track) {
                    $user->can_track = false;
                    $user->save();

                    // If the website & the user has the option to be emailed when the plan exceeds the limits
                    if ($user->email_account_limit) {
                        // Send out the email
                        try {
                            Mail::to($user->email)->locale($user->locale)->send(new LimitExceededMail());
                        } catch (\Exception $e) {}
                    }
                }
            } else {
                // If the user's tracking was not previously enabled
                if (!$user->can_track) {
                    $user->can_track = true;
                    $user->save();
                }
            }
        }

        $cronjob = new Cronjob;
        $cronjob->name = 'check';
        $cronjob->save();

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Sends the periodic stats emails.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function email(Request $request)
    {
        $now = Carbon::now();

        if ($request->has('weekly')) {
            $from = (clone $now)->startOfWeek()->subWeek();
            $to = (clone $now)->endOfWeek()->subWeek();
        } else {
            $from = (clone $now)->startOfMonth()->subMonthsNoOverflow(1);
            $to = (clone $now)->endOfMonth()->subMonthsNoOverflow(1);
        }

        foreach (User::where('has_websites', '=', 1)->cursor() as $user) {
            $websites = Website::with([
                    'visitors' => function ($query) use($from, $to) {
                        $query->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);
                    },
                    'pageviews' => function ($query) use($from, $to) {
                        $query->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);
                    }]
                )
                ->where([['user_id', '=', $user->id], ['email', '=', 1]])->get();

            $stats = [];
            foreach ($websites as $website) {
                $stats[] = ['url' => $website->url, 'visitors' => $website->visitors->sum('count') ?? 0, 'pageviews' => $website->pageviews->sum('count') ?? 0];
            }

            // If the user has any websites with email notifications enabled
            if ($stats) {
                try {
                    Mail::to($user->email)->locale($user->locale)->send(new ReportMail($stats, ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]));
                } catch (\Exception $e) {}
            }
        }

        $cronjob = new Cronjob;
        $cronjob->name = 'email';
        $cronjob->save();

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Clean the `recent` traffic.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clean()
    {
        // Delete the recent visitors
        Recent::truncate();

        $cronjob = new Cronjob;
        $cronjob->name = 'clean';
        $cronjob->save();

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Clear the app's cache.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cache()
    {
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        $cronjob = new Cronjob;
        $cronjob->name = 'cache';
        $cronjob->save();

        return response()->json([
            'status' => 200
        ], 200);
    }
}
