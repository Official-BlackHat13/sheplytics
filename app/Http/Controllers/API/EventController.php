<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use GeoIp2\Database\Reader as GeoIP;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use IPTools\IP;
use IPTools\Range;
use Symfony\Component\HttpFoundation\IpUtils;
use WhichBrowser\Parser as UserAgent;

class EventController extends Controller
{
    /**
     * The tracking mechanism.
     *
     * @param Request $request
     * @return int
     */
    public function index(Request $request)
    {
        $page = $this->parseUrl($request->input('page'));

        $website = DB::table('websites')
            ->select(['websites.id', 'websites.url', 'websites.user_id', 'websites.exclude_bots', 'websites.exclude_ips', 'websites.exclude_params', 'users.can_track'])
            ->join('users', 'users.id', '=', 'websites.user_id')
            ->where('websites.url', '=', $page['non_www_host'] ?? null)
            ->first();

        // If the user's account exceeded the limit
        if (isset($website->can_track) && !$website->can_track) {
            return 403;
        }

        // If the website does not exist
        if (isset($website->can_track) == false) {
            return 404;
        }

        // If the website has any excluded IPs
        if ($website->exclude_ips) {
            $excludedIps = preg_split('/\n|\r/', $website->exclude_ips, -1, PREG_SPLIT_NO_EMPTY);

            if (IpUtils::checkIp($request->ip(), $excludedIps)) {
                return 403;
            }
        }

        $ua = new UserAgent(getallheaders());

        // If the website is excluding bots
        if ($website->exclude_bots) {
            if ($ua->device->type == 'bot') {
                return 403;
            }
        }

        // If the UA is not of a BOT
        $data = $values = [];

        $now = Carbon::now();

        $date = $now->format('Y-m-d');
        $time = $now->format('H');

        if ($request->input('event')) {
            $event = $request->input('event');

            if (isset($event['name']) == false) {
                return;
            }

            $event = [str_replace(':', ' ', $event['name']), (isset($event['value']) && (int)$event['value'] > 0 && strlen($event['value']) <= 10 ? $event['value'] : null), (isset($event['unit']) && mb_strlen($event['unit']) <= 32 ? $event['unit'] : null)];

            $data['event'] = implode(':', $event);
        } else {
            // If the request has a referrer that's not an internal page
            $referrer = $this->parseUrl($request->input('referrer'));

            // Add the pageview
            $data['pageviews'] = $date;

            // Add the pageview by hour
            $data['pageviews_hours'] = $time;

            // Parse the query data
            parse_str($page['query'] ?? null, $params);

            // If the website has any excluded query parameters
            if ($website->exclude_params) {
                $excludeQueries = preg_split('/\n|\r/', $website->exclude_params, -1, PREG_SPLIT_NO_EMPTY);

                // If a match all rule is set
                if (in_array('&', $excludeQueries)) {
                    // Remove all parameters
                    $page['query'] = null;
                } else {
                    foreach ($excludeQueries as $param) {
                        // If the excluded parameter exists
                        if (isset($params[$param])) {
                            // Remove the excluded parameter
                            unset($params[$param]);
                        }
                    }

                    // Rebuild the query parameters
                    $page['query'] = http_build_query($params);
                }
            }

            // Add the page
            $data['page'] = mb_substr((isset($page['query']) && !empty($page['query']) ? $page['path'].'?'.$page['query'] : $page['path'] ?? '/'), 0, 255);

            // Get the user's geolocation
            try {
                $geoip = (new GeoIP(storage_path('app/geoip/GeoLite2-City.mmdb')))->city($request->ip());

                $continent = $geoip->continent->code.':'.$geoip->continent->name;
                $country = $geoip->country->isoCode.':'.$geoip->country->name;
                $city = $geoip->country->isoCode.':'. $geoip->city->name .(isset($geoip->mostSpecificSubdivision->isoCode) ? ', '.$geoip->mostSpecificSubdivision->isoCode : '');
            } catch (\Exception $e) {
                $continent = $country = $city = null;
            }

            $browser = mb_substr($ua->browser->name ?? null, 0, 64);
            $os = mb_substr($ua->os->name ?? null, 0, 64);
            $device = mb_substr($ua->device->type ?? null, 0, 64);
            $language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $screenResolution = $request->input('screen_resolution') ?? null;

            // If the request is unique
            if (isset($referrer['non_www_host']) == false || $referrer['non_www_host'] != $website->url) {
                // Add the campaign
                if (isset($params['utm_campaign']) && !empty($params['utm_campaign'])) {
                    $data['campaign'] = $params['utm_campaign'];
                }

                // Add the continent
                $data['continent'] = $continent;

                // Add the country
                $data['country'] = $country;

                // Add the city
                $data['city'] = $city;

                // Add the browser
                $data['browser'] = $browser;

                // Add the OS
                $data['os'] = $os;

                // Add the device
                $data['device'] = $device;

                // Add the language
                $data['language'] = $language;

                // Add the visitor
                $data['visitors'] = $date;

                // Add the visitor by hour
                $data['visitors_hours'] = $time;

                // Add the screen-resolution
                $data['resolution'] = $screenResolution;

                // Add the landing page
                $data['landing_page'] = $data['page'];

                // Add the referrer
                $data['referrer'] = mb_substr($referrer['host'] ?? null, 0, 255);
            }
        }

        foreach ($data as $name => $value) {
            $values[] = "({$website->id}, '{$name}', " . DB::connection()->getPdo()->quote(mb_substr($value, 0, 255)) . ", '{$date}')";
        }

        $values = implode(', ', $values);

        // Stats
        DB::statement("INSERT INTO `stats` (`website_id`, `name`, `value`, `date`) VALUES {$values} ON DUPLICATE KEY UPDATE `count` = `count` + 1;");

        if (empty($request->input('event'))) {
            // Recent traffic
            DB::statement("INSERT INTO `recents` (`id`, `website_id`, `page`, `referrer`, `os`, `browser`, `device`, `country`, `city`, `language`, `created_at`) VALUES (NULL, :website_id, :page, :referrer, :os, :browser, :device, :country, :city, :language, :timestamp)", ['website_id' => $website->id, 'page' => $data['page'], 'referrer' => $referrer['host'] ?? null, 'os' => $os, 'browser' => $browser, 'device' => $device, 'country' => $country, 'city' => $city, 'language' => $language, 'timestamp' => $now]);
        }

        return 200;
    }

    /**
     * Returns the parsed URL, including an always "non-www." version of the host.
     *
     * @param $url
     * @return mixed|null
     */
    private function parseUrl($url)
    {
        $url = parse_url($url);

        // If the URL has a host
        if (isset($url['host'])) {
            // If the URL starts with "www."
            if(substr($url['host'], 0, 4 ) == "www.") {
                $url['non_www_host'] = str_replace('www.', '', $url['host']);
            } else {
                $url['non_www_host'] = $url['host'];
            }
            return $url;
        } else {
            return null;
        }
    }
}
