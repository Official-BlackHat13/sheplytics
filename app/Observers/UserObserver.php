<?php

namespace App\Observers;

use App\User;

class UserObserver
{
    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleting(User $user)
    {
        $user->stats()->delete();
        $user->recents()->delete();
        $user->websites()->delete();

        if ($user->isForceDeleting()) {
            // If the user previously had a subscription, attempt to cancel it
            if ($user->plan_subscription_id) {
                $user->planSubscriptionCancel();
            }
        } else {
            // If the user previously had a subscription, attempt to cancel it
            if ($user->plan_subscription_id) {
                $user->planSubscriptionCancel();
            }
        }
    }
}
