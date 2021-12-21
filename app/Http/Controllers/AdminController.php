<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Cronjob;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\StoreTaxRateRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateSettingAppearanceRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Requests\UpdateSettingLicenseRequest;
use App\Http\Requests\UpdateSettingPaymentProcessorsRequest;
use App\Http\Requests\UpdateSettingGeneralRequest;
use App\Http\Requests\UpdateTaxRateRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UpdateWebsiteRequest;
use App\Language;
use App\Mail\PaymentMail;
use App\Page;
use App\Payment;
use App\Plan;
use App\Setting;
use App\TaxRate;
use App\Traits\UserTrait;
use App\Traits\WebsiteTrait;
use App\User;
use App\Website;
use Carbon\Carbon;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use UserTrait, WebsiteTrait;

    /**
     * Show the Dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboard()
    {
        $stats = [
            'users' => User::withTrashed()->count(),
            'plans' => Plan::withTrashed()->count(),
            'payments' => Payment::count(),
            'websites' => Website::count()
        ];

        $users = User::withTrashed()->orderBy('id', 'desc')->limit(5)->get();

        $payments = $websites = [];
        if (paymentProcessors()) {
            $payments = Payment::with('plan')->orderBy('id', 'desc')->limit(5)->get();
        } else {
            $websites = Website::orderBy('id', 'desc')->limit(5)->get();
        }

        return view('admin.dashboard.content', ['stats' => $stats, 'users' => $users, 'payments' => $payments, 'websites' => $websites]);
    }

    /**
     * Show the General settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function general()
    {
        return view('admin.content', ['view' => 'admin.general']);
    }

    /**
     * Update the General settings.
     *
     * @param UpdateSettingGeneralRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGeneral(UpdateSettingGeneralRequest $request)
    {
        // The rows to be updated
        $rows = ['title', 'tagline', 'index', 'paginate', 'timezone', 'tracking_code'];

        foreach ($rows as $row) {
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Appearance settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function appearance()
    {
        return view('admin.content', ['view' => 'admin.appearance']);
    }

    /**
     * Update the Appearance settings.
     *
     * @param UpdateSettingAppearanceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAppearance(UpdateSettingAppearanceRequest $request)
    {
        if ($request->validated()) {
            // The rows to be updated
            $rows = ['logo', 'favicon'];

            foreach ($rows as $row) {
                if ($request->has($row)) {
                    if($request->hasFile($row)) {
                        $fileName = $request->file($row)->hashName();

                        // Check if the file exists
                        if (file_exists(public_path('uploads/brand/' . config('settings.' . $row)))) {
                            unlink(public_path('uploads/brand/' . config('settings.' . $row)));
                        }

                        // Save the file
                        $request->file($row)->move(public_path('uploads/brand'), $fileName);
                    }

                    // Update the database
                    Setting::where('name', $row)->update(['value' => $fileName]);
                }
            }

            // The rows to be updated
            $rows = ['theme', 'custom_css'];

            foreach ($rows as $row) {
                // Update the database
                Setting::where('name', $row)->update(['value' => $request->input($row)]);
            }
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Email settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function email()
    {
        return view('admin.content', ['view' => 'admin.email']);
    }

    /**
     * Update the Email settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEmail(Request $request)
    {
        // The rows to be updated
        $rows = ['email_driver', 'email_host', 'email_port', 'email_encryption', 'email_address', 'email_username', 'email_password', 'contact_email'];

        foreach ($rows as $row) {
            // Update the database
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Social settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function social()
    {
        return view('admin.content', ['view' => 'admin.social']);
    }

    /**
     * Update the Social settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSocial(Request $request)
    {
        // The rows to be updated
        $rows = ['social_facebook', 'social_twitter', 'social_instagram', 'social_youtube'];

        foreach ($rows as $row) {
            // Update the database
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Registration settings form
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function registration()
    {
        return view('admin.content', ['view' => 'admin.registration']);
    }

    /**
     * Update the Registration settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRegistration(Request $request)
    {
        // The rows to be updated
        $rows = ['registration', 'registration_captcha', 'registration_verification'];

        foreach ($rows as $row) {
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Announcements settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function announcements()
    {
        return view('admin.content', ['view' => 'admin.announcements']);
    }

    /**
     * Update the Announcements settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAnnouncements(Request $request)
    {
        // The rows to be updated
        $rows = ['announcement_guest', 'announcement_guest_type', 'announcement_guest_id', 'announcement_user', 'announcement_user_type', 'announcement_user_id'];

        foreach ($rows as $row) {
            // Update the database
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Payment Processors settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function paymentProcessors()
    {
        return view('admin.content', ['view' => 'admin.payment-processors']);
    }

    /**
     * Update the Payment Processors settings.
     *
     * @param UpdateSettingPaymentProcessorsRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePaymentProcessors(UpdateSettingPaymentProcessorsRequest $request)
    {
        // The rows to be updated
        $rows = [
            'stripe', 'stripe_key', 'stripe_secret', 'stripe_wh_secret',
            'paypal', 'paypal_mode', 'paypal_client_id', 'paypal_secret', 'paypal_webhook_id',
            'coinbase', 'coinbase_key', 'coinbase_wh_secret',
            'bank', 'bank_account_owner', 'bank_account_number', 'bank_name', 'bank_routing_number', 'bank_iban', 'bank_bic_swift'
        ];

        foreach ($rows as $row) {
            // Update the database
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Billing Information settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function billingInformation()
    {
        return view('admin.content', ['view' => 'admin.billing-information']);
    }

    /**
     * Update the Billing Information settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBillingInformation(Request $request)
    {
        // The rows to be updated
        $rows = ['billing_invoice_prefix', 'billing_vendor', 'billing_address', 'billing_city', 'billing_state', 'billing_postal_code', 'billing_country', 'billing_phone', 'billing_vat_number'];

        foreach ($rows as $row) {
            // Update the database
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Legal settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function legal()
    {
        return view('admin.content', ['view' => 'admin.legal']);
    }

    /**
     * Update the Legal settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLegal(Request $request)
    {
        // The rows to be updated
        $rows = ['legal_terms_url', 'legal_privacy_url', 'legal_cookie_url'];

        foreach ($rows as $row) {
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Captcha settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function captcha()
    {
        return view('admin.content', ['view' => 'admin.captcha']);
    }

    /**
     * Update the Captcha settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCaptcha(Request $request)
    {
        // The rows to be updated
        $rows = ['captcha_site_key', 'captcha_secret_key', 'captcha_registration', 'captcha_contact'];

        foreach ($rows as $row) {
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Cron Jobs form and listing.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cronJobs(Request $request)
    {
        $search = $request->input('search');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');

        $cronjobs = Cronjob::when($search, function($query) use($search) {
                return $query->searchName($search);
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'sort' => $sort]);

        return view('admin.content', ['view' => 'admin.cronjobs', 'cronjobs' => $cronjobs]);
    }

    /**
     * Update the Cron Jobs settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCronJobs()
    {
        Setting::where('name', '=', 'cronjob_key')->update(['value' => Str::random(60)]);

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the Analytics settings form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function analytics()
    {
        return view('admin.content', ['view' => 'admin.analytics']);
    }

    /**
     * Update the Analytics settings.
     *
     * @param UpdateSettingAppearanceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAnalytics(UpdateSettingAppearanceRequest $request)
    {
        // The rows to be updated
        $rows = ['demo_url', 'cdn_url'];

        foreach ($rows as $row) {
            Setting::where('name', $row)->update(['value' => $request->input($row)]);
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Show the License form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function license()
    {
        return view('admin.content', ['view' => 'admin.license']);
    }

    /**
     * Validate and update the license.
     *
     * @param UpdateSettingLicenseRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateLicense(UpdateSettingLicenseRequest $request)
    {
        $httpClient = new HttpClient(['timeout' => 10/*, 'verify' => false*/]);

        try {
            $response = $httpClient->request('POST', 'https://api.lunatio.com/api/v1/license/validate',
                [
                    'form_params' => [
                        'license_id' => $request->input('license_key'),
                        'product_id' => 6,
                        'url' => env('APP_URL')
                    ]
                ]
            );

            $output = json_decode($response->getBody()->getContents());

            if ($output->status == 200) {
                Setting::where('name', '=', 'license_key')->update(['value' => $request->input('license_key')]);
                Setting::where('name', '=', 'license_type')->update(['value' => $output->type]);
            }

            return redirect()->route('admin.dashboard');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * List the Payments.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexPayments(Request $request)
    {
        $user = $request->input('user');
        $search = $request->input('search');
        $plan = $request->input('plan');
        $interval = $request->input('interval');
        $processor = $request->input('processor');
        $status = $request->input('status');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');
        $by = $request->input('by');

        $payments = Payment::with('user')
            ->when(isset($plan) && !empty($plan), function($query) use ($plan) {
                return $query->ofPlan($plan);
            })
            ->when($user, function($query) use($user) {
                return $query->ofUser($user);
            })
            ->when($interval, function($query) use($interval) {
                return $query->ofInterval($interval);
            })
            ->when($processor, function($query) use($processor) {
                return $query->ofProcessor($processor);
            })
            ->when($status, function($query) use($status) {
                return $query->ofStatus($status);
            })
            ->when($search, function($query) use ($search, $by) {
                if($by == 'invoice') {
                    return $query->searchInvoice($search);
                }
                return $query->searchPayment($search);
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'sort' => $sort, 'by' => $by, 'status' => $status, 'interval' => $interval, 'processor' => $processor, 'plan' => $plan, 'user' => $user]);

        // Get all the plans
        $plans = Plan::where([['amount_month', '>', 0], ['amount_year', '>', 0]])->withTrashed()->get();

        $filters = [];

        if ($user) {
            $user = User::where('id', '=', $user)->first();
            if ($user) {
                $filters['user'] = $user->name;
            }
        }

        return view('admin.content', ['view' => 'admin.payments.list', 'payments' => $payments, 'interval' => $interval, 'plans' => $plans, 'filters' => $filters]);
    }

    /**
     * Show the edit Payment form.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editPayment($id)
    {
        $payment = Payment::where('id', $id)->firstOrFail();

        return view('admin.content', ['view' => 'account.payments.edit', 'payment' => $payment]);
    }

    /**
     * Approve the Payment.
     *
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function approvePayment(Request $request, $id)
    {
        $payment = Payment::where([['id', '=', $id], ['status', '=', 'pending']])->firstOrFail();

        $user = User::where('id', $payment->user_id)->first();

        $payment->status = 'completed';
        $payment->save();

        // Assign the plan to the user
        if ($user) {
            if ($user->plan_subscription_id) {
                $user->planSubscriptionCancel();
            }

            $user->plan_id = $payment->plan->id;
            $user->plan_interval = $payment->interval;
            $user->plan_currency = $payment->currency;
            $user->plan_amount = $payment->amount;
            $user->plan_payment_processor = $payment->processor;
            $user->plan_subscription_id = null;
            $user->plan_subscription_status = null;
            $user->plan_created_at = Carbon::now();
            $user->plan_recurring_at = null;
            $user->plan_trial_ends_at = $user->plan_trial_ends_at ? Carbon::now() : null;
            $user->plan_ends_at = $payment->interval == 'month' ? Carbon::now()->addMonth() : Carbon::now()->addYear();
            $user->save();

            // If a coupon was used
            if (isset($payment->coupon->id)) {
                $coupon = Coupon::find($payment->coupon->id);

                // If a coupon was found
                if ($coupon) {
                    // Increase the coupon usage
                    $coupon->increment('redeems', 1);
                }
            }

            // Attempt to send an email notification
            try {
                Mail::to($user->email)->locale($user->locale)->send(new PaymentMail($payment));
            }
            catch (\Exception $e) {}
        }

        return redirect()->route('admin.payments.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Cancel the Payment.
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function cancelPayment(Request $request, $id)
    {
        $payment = Payment::where([['id', '=', $id], ['status', '=', 'pending']])->firstOrFail();
        $payment->status = 'cancelled';
        $payment->save();

        $user = User::where('id', $payment->user_id)->first();

        if ($user) {
            // Attempt to send an email notification
            try {
                Mail::to($user->email)->locale($user->locale)->send(new PaymentMail($payment));
            }
            catch (\Exception $e) {}
        }

        return redirect()->route('admin.payments.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the Payment.
     *
     * @param $id
     * @return mixed
     */
    public function destroyPayment($id)
    {
        $payment = Payment::where([['id', '=', $id], ['status', '=', 'pending']])->firstOrFail();
        $payment->delete();

        return redirect()->route('admin.payments')->with('success', __(':name has been deleted.', ['name' => $payment->id]));
    }

    /**
     * Show the Invoice.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showInvoice(Request $request, $id)
    {
        $payment = Payment::where([['id', '=', $id], ['status', '!=', 'pending']])->firstOrFail();

        // Sum the inclusive tax rates
        $inclTaxRatesPercentage = collect($payment->tax_rates)->where('type', '=', 0)->sum('percentage');

        // Sum the exclusive tax rates
        $exclTaxRatesPercentage = collect($payment->tax_rates)->where('type', '=', 1)->sum('percentage');

        return view('admin.content', ['view' => 'account.payments.invoice', 'payment' => $payment, 'inclTaxRatesPercentage' => $inclTaxRatesPercentage, 'exclTaxRatesPercentage' => $exclTaxRatesPercentage]);
    }

    /**
     * List the plans.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexPlans(Request $request)
    {
        $search = $request->input('search');
        $visibility = $request->input('visibility');
        $status = $request->input('status');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');

        $plans = Plan::withTrashed()
            ->when($search, function($query) use($search) {
                return $query->searchName($search);
            })
            ->when(isset($visibility) && is_numeric($visibility), function($query) use ($visibility) {
                return $query->ofVisibility((int)$visibility);
            })
            ->when(isset($status) && is_numeric($status), function($query) use ($status) {
                if ($status) {
                    $query->whereNotNull('deleted_at');
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'visibility' => $visibility, 'status' => $status, 'sort' => $sort]);

        return view('admin.content', ['view' => 'admin.plans.list', 'plans' => $plans]);
    }

    /**
     * Show the create Plan form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createPlan()
    {
        $coupons = Coupon::all();

        $taxRates = TaxRate::all();

        return view('admin.content', ['view' => 'admin.plans.new', 'coupons' => $coupons, 'taxRates' => $taxRates]);
    }

    /**
     * Show the edit Plan form.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editPlan($id)
    {
        $plan = Plan::withTrashed()->where('id', $id)->firstOrFail();

        $coupons = Coupon::all();

        $taxRates = TaxRate::all();

        return view('admin.content', ['view' => 'admin.plans.edit', 'plan' => $plan, 'coupons' => $coupons, 'taxRates' => $taxRates]);
    }

    /**
     * Store the Plan.
     *
     * @param StorePlanRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePlan(StorePlanRequest $request)
    {
        $plan = new Plan;
        $plan->name = $request->input('name');
        $plan->description = $request->input('description');
        $plan->amount_month = $request->input('amount_month');
        $plan->amount_year = $request->input('amount_year');
        $plan->currency = $request->input('currency');
        $plan->coupons = $request->input('coupons');
        $plan->tax_rates = $request->input('tax_rates');
        $plan->trial_days = $request->input('trial_days');
        $plan->visibility = $request->input('visibility');
        $plan->features = $request->input('features');
        $plan->save();

        return redirect()->route('admin.plans')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the Plan.
     *
     * @param UpdatePlanRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePlan(UpdatePlanRequest $request, $id)
    {
        $plan = Plan::withTrashed()->findOrFail($id);

        if ($plan->hasPrice()) {
            $plan->amount_month = $request->input('amount_month');
            $plan->amount_year = $request->input('amount_year');
            $plan->currency = $request->input('currency');
            $plan->coupons = $request->input('coupons');
            $plan->tax_rates = $request->input('tax_rates');
            $plan->trial_days = $request->input('trial_days');
        }
        $plan->name = $request->input('name');
        $plan->description = $request->input('description');
        $plan->visibility = $request->input('visibility');
        $plan->features = $request->input('features');
        $plan->save();

        return redirect()->route('admin.plans.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Soft delete the Plan.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function disablePlan($id)
    {
        $plan = Plan::findOrFail($id);

        // Do not delete the default plan
        if (!$plan->hasPrice()) {
            return redirect()->route('admin.plans.edit', $id)->with('error', __('The default plan can\'t be disabled.'));
        }

        $plan->delete();

        return redirect()->route('admin.plans.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Restore the Plan.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restorePlan($id)
    {
        $plan = Plan::withTrashed()->findOrFail($id);
        $plan->restore();

        return redirect()->route('admin.plans.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * List the Coupons.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexCoupons(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $type = $request->input('type');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');

        $coupons = Coupon::withTrashed()
            ->when($search, function($query) use($search) {
                return $query->searchCoupon($search);
            })
            ->when(isset($type) && is_numeric($type), function($query) use($type) {
                return $query->ofType($type);
            })
            ->when(isset($status) && is_numeric($status), function($query) use ($status) {
                if ($status) {
                    $query->whereNotNull('deleted_at');
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'status' => $status, 'sort' => $sort]);

        return view('admin.content', ['view' => 'admin.coupons.list', 'coupons' => $coupons]);
    }

    /**
     * Show the create Coupon form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createCoupon()
    {
        return view('admin.content', ['view' => 'admin.coupons.new']);
    }

    /**
     * Show the edit Coupon form.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editCoupon($id)
    {
        $coupon = Coupon::where('id', $id)->withTrashed()->firstOrFail();

        return view('admin.content', ['view' => 'admin.coupons.edit', 'coupon' => $coupon]);
    }

    /**
     * Store the Coupon.
     *
     * @param StoreCouponRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCoupon(StoreCouponRequest $request)
    {
        $coupon = new Coupon;

        $coupon->name = $request->input('name');
        $coupon->code = $request->input('code');
        $coupon->type = $request->input('type');
        $coupon->days = $request->input('days');
        $coupon->percentage = $request->input('type') ? 100 : $request->input('percentage');
        $coupon->quantity = $request->input('quantity');

        $coupon->save();

        return redirect()->route('admin.coupons')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the Coupon.
     *
     * @param UpdateCouponRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCoupon(UpdateCouponRequest $request, $id)
    {
        $coupon = Coupon::withTrashed()->findOrFail($id);

        $coupon->code = $request->input('code');
        $coupon->days = $request->input('days');
        $coupon->quantity = $request->input('quantity');

        $coupon->save();

        return redirect()->route('admin.coupons.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Soft delete the Coupon.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('admin.coupons.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Restore the Coupon.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreCoupon($id)
    {
        $coupon = Coupon::withTrashed()->findOrFail($id);
        $coupon->restore();

        return redirect()->route('admin.coupons.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * List the Tax Rates.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexTaxRates(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $type = $request->input('type');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');

        $taxRates = TaxRate::withTrashed()
            ->when($search, function($query) use($search) {
                return $query->searchName($search);
            })
            ->when(isset($type) && is_numeric($type), function($query) use($type) {
                return $query->ofType($type);
            })
            ->when(isset($status) && is_numeric($status), function($query) use ($status) {
                if ($status) {
                    $query->whereNotNull('deleted_at');
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'status' => $status, 'sort' => $sort]);

        return view('admin.content', ['view' => 'admin.tax-rates.list', 'taxRates' => $taxRates]);
    }

    /**
     * Show the create Tax Rate form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createTaxRate()
    {
        return view('admin.content', ['view' => 'admin.tax-rates.new']);
    }

    /**
     * Show the edit Tax Rate form.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editTaxRate($id)
    {
        $taxRate = TaxRate::where('id', $id)->withTrashed()->firstOrFail();

        return view('admin.content', ['view' => 'admin.tax-rates.edit', 'taxRate' => $taxRate]);
    }

    /**
     * Store the Tax Rate.
     *
     * @param StoreTaxRateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeTaxRate(StoreTaxRateRequest $request)
    {
        $taxRate = new TaxRate;

        $taxRate->name = $request->input('name');
        $taxRate->type = $request->input('type');
        $taxRate->percentage = $request->input('percentage');
        $taxRate->regions = $request->input('regions');

        $taxRate->save();

        return redirect()->route('admin.tax_rates')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the Tax Rate.
     *
     * @param UpdateTaxRateRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTaxRate(UpdateTaxRateRequest $request, $id)
    {
        $taxRate = TaxRate::withTrashed()->findOrFail($id);

        $taxRate->regions = $request->input('regions');

        $taxRate->save();

        return redirect()->route('admin.tax_rates.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Soft delete the Tax Rate.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableTaxRate($id)
    {
        $taxRate = TaxRate::findOrFail($id);
        $taxRate->delete();

        return redirect()->route('admin.tax_rates.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Restore the Tax Rate.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreTaxRate($id)
    {
        $taxRate = TaxRate::withTrashed()->findOrFail($id);
        $taxRate->restore();

        return redirect()->route('admin.tax_rates.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * List the Languages.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexLanguages(Request $request)
    {
        $search = $request->input('search');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');

        $languages = Language::when($search, function($query) use($search) {
                return $query->searchName($search);
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'sort' => $sort]);

        return view('admin.content', ['view' => 'admin.languages.list', 'languages' => $languages]);
    }

    /**
     * Show the create Language form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createLanguage()
    {
        return view('admin.content', ['view' => 'admin.languages.new']);
    }

    /**
     * Show the edit Language form.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editLanguage($id)
    {
        $language = Language::where('id', $id)->firstOrFail();

        return view('admin.content', ['view' => 'admin.languages.edit', 'id' => $id, 'languages' => Language::all(), 'language' => $language]);
    }

    /**
     * Upload language files.
     *
     * @param StoreLanguageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeLanguage(StoreLanguageRequest $request)
    {
        if ($request->validated()) {

            $file = $this->readLanguage($request);
            $this->uploadLanguage($request, $file);

            // Update the database
            Language::updateOrCreate(['code' => $file->lang_code], ['name' => $file->lang_name, 'dir' => $file->lang_dir]);
        }

        return redirect()->route('admin.languages')->with('success', __(':name language uploaded.', ['name' => $file->lang_name]));
    }

    /**
     * Read the Language file contents.
     *
     * @param Request $request
     * @return mixed
     */
    private function readLanguage(Request $request)
    {
        $uploadedFile = file_get_contents($request->file('language'));
        $file = json_decode($uploadedFile);

        return $file;
    }

    /**
     * Upload the language file on disk.
     *
     * @param Request $request
     * @param $file
     */
    private function uploadLanguage(Request $request, $file)
    {
        Storage::disk('languages')->put($file->lang_code . '.json', File::get($request->file('language')));
    }

    /**
     * Update the Language file.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLanguage(Request $request, $id)
    {
        // Get the language
        $language = Language::findOrFail($id);

        // If the current language is not default
        if ($language->default == 0) {
            if ($request->has('default')) {
                // Reset the default language
                Language::query()->update(['default' => 0]);

                // Set the new default language
                $language->default = 1;
                $language->save();
            }
        }

        return redirect()->route('admin.languages.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the Language file.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroyLanguage($id)
    {
        // If there's more than 1 language available
        if (Language::count() > 1) {
            // Get the language
            $language = Language::findOrFail($id);

            // If the language to be deletes is set as default
            if ($language->default) {
                $redirect = redirect()->route('admin.languages.edit', $id)->with('error', __('The default language can\'t be deleted.'));
            } else {
                // Delete the database record
                $language->delete();

                // Delete the file
                Storage::disk('languages')->delete($id . '.json');

                $redirect = redirect()->route('admin.languages')->with('success', __(':name has been deleted.', ['name' => $language->name]));
            }
        } else {
            $redirect = redirect()->route('admin.languages.edit', $id)->with('error', __('The default language can\'t be deleted.'));
        }

        return $redirect;
    }

    /**
     * List the Users.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexUsers(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');
        $by = $request->input('by');

        $users = User::withTrashed()
            ->when(isset($role) && is_numeric($role), function($query) use ($role) {
                return $query->ofRole($role);
            })
            ->when($search, function($query) use ($search, $by) {
                if($by == 'email') {
                    return $query->searchEmail($search);
                }
                return $query->searchName($search);
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'by' => $by, 'role' => $role, 'sort' => $sort]);

        return view('admin.content', ['view' => 'admin.users.list', 'users' => $users]);
    }

    /**
     * Show the create User form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createUser()
    {
        return view('admin.content', ['view' => 'admin.users.new']);
    }

    /**
     * Show the edit User form.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editUser($id)
    {
        $user = User::withTrashed()
            ->where('id', $id)
            ->firstOrFail();

        $stats = [
            'payments' => Payment::where('user_id', $user->id)->count(),
            'websites' => Website::where('user_id', $user->id)->count()
        ];

        $plans = Plan::all();

        return view('admin.content', ['view' => 'account.profile', 'user' => $user, 'stats' => $stats, 'plans' => $plans]);
    }

    /**
     * Store the User.
     *
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(StoreUserRequest $request)
    {
        $user = new User;

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->locale = app()->getLocale();
        $user->timezone = config('settings.timezone');
        $user->api_token = Str::random(60);

        $user->save();

        $user->markEmailAsVerified();

        return redirect()->route('admin.users')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the User.
     *
     * @param UpdateUserProfileRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(UpdateUserProfileRequest $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($request->user()->id == $user->id && $request->input('role') == 0) {
            return redirect()->route('admin.users.edit', $id)->with('error', __('Operation denied.'));
        }

        $this->userUpdate($request, $user);

        return redirect()->route('admin.users.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the User.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyUser(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($request->user()->id == $user->id && $user->role == 1) {
            return redirect()->route('admin.users.edit', $id)->with('error', __('Operation denied.'));
        }

        $user->forceDelete();

        return redirect()->route('admin.users')->with('success', __(':name has been deleted.', ['name' => $user->name]));
    }

    /**
     * Soft delete the User.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function disableUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->user()->id == $user->id && $user->role == 1) {
            return redirect()->route('admin.users.edit', $id)->with('error', __('Operation denied.'));
        }

        $user->delete();

        return redirect()->route('admin.users.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Restore the soft deleted User.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * List the Pages.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexPages(Request $request)
    {
        $search = $request->input('search');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');

        $pages = Page::when($search, function($query) use($search) {
                return $query->searchName($search);
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'sort' => $sort]);

        return view('admin.content', ['view' => 'admin.pages.list', 'pages' => $pages]);
    }

    /**
     * Show the create Page form.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createPage()
    {
        return view('admin.content', ['view' => 'admin.pages.new']);
    }

    /**
     * Show the edit Page form.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editPage($id)
    {
        $page = Page::where('id', $id)->firstOrFail();

        return view('admin.content', ['view' => 'admin.pages.edit', 'page' => $page]);
    }

    /**
     * Store the Page.
     *
     * @param StorePageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePage(StorePageRequest $request)
    {
        $page = new Page;

        $page->name = $request->input('name');
        $page->slug = $request->input('slug');
        $page->footer = $request->input('footer') == 1 ? 1 : 0;
        $page->content = $request->input('content');

        $page->save();

        return redirect()->route('admin.pages')->with('success', __(':name has been created.', ['name' => $request->input('name')]));
    }

    /**
     * Update the Page.
     *
     * @param UpdatePageRequest $request
     * @param $id
     * @return mixed
     */
    public function updatePage(UpdatePageRequest $request, $id)
    {
        $page = Page::findOrFail($id);

        $page->name = $request->input('name');
        $page->slug = $request->input('slug');
        $page->footer = $request->input('footer') == 1 ? 1 : 0;
        $page->content = $request->input('content');

        $page->save();

        return redirect()->route('admin.pages.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the Page.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroyPage($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return redirect()->route('admin.pages')->with('success', __(':name has been deleted.', ['name' => $page->name]));
    }

    /**
     * List the Websites.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function indexWebsites(Request $request)
    {
        $user = $request->input('user');
        $search = $request->input('search');
        $sort = ($request->input('sort') == 'asc' ? 'asc' : 'desc');

        $websites = Website::with('user')
            ->when($search, function($query) use($search) {
                return $query->searchUrl($search);
            })
            ->when($user, function($query) use($user) {
                return $query->ofUser($user);
            })
            ->orderBy('id', $sort)
            ->paginate(config('settings.paginate'))
            ->appends(['search' => $search, 'sort' => $sort, 'user' => $user]);

        $filters = [];

        if ($user) {
            $user = User::where('id', '=', $user)->first();
            if ($user) {
                $filters['user'] = $user->name;
            }
        }

        return view('admin.content', ['view' => 'admin.websites.list', 'websites' => $websites, 'filters' => $filters]);
    }

    /**
     * Show the edit Website form.
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editWebsite($id)
    {
        $website = Website::where('id', $id)->firstOrFail();

        return view('admin.content', ['view' => 'websites.edit', 'website' => $website]);
    }

    /**
     * Update the Website.
     *
     * @param UpdateWebsiteRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateWebsite(UpdateWebsiteRequest $request, $id)
    {
        $website = Website::where('id', $id)->firstOrFail();

        $this->websiteUpdate($request, $website);

        return redirect()->route('admin.websites.edit', $id)->with('success', __('Settings saved.'));
    }

    /**
     * Delete the Website.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroyWebsite($id)
    {
        $website = Website::where('id', $id)->firstOrFail();
        $website->delete();

        $user = User::find($website->user_id);
        $user->has_websites = Website::where('user_id', '=', $user->id)->count() > 0;
        $user->save();

        return redirect()->route('admin.websites')->with('success', __(':name has been deleted.', ['name' => $website->url]));
    }
}
