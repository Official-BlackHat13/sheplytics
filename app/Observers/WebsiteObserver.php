<?php

namespace App\Observers;

use App\Website;

class WebsiteObserver
{
    /**
     * Handle the Website "deleted" event.
     *
     * @param  Website  $website
     * @return void
     */

    public function deleting(Website $website)
    {
        $website->stats()->delete();
        $website->recents()->delete();
    }
}
