<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Mail\PaymentMail;
use App\Traits\PaymentTrait;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebhookController extends Controller
{
    use PaymentTrait;

    /**
     * Handle the Stripe webhook.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stripe(Request $request)
    {
        // Attempt to validate the Webhook
        try {
            $stripeEvent = \Stripe\Webhook::constructEvent($request->getContent(), $request->server('HTTP_STRIPE_SIGNATURE'), config('settings.stripe_wh_secret'));
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            Log::info($e->getMessage());

            return response()->json([
                'status' => 400
            ], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::info($e->getMessage());

            return response()->json([
                'status' => 400
            ], 400);
        }

        // Get the metadata
        $metadata = $stripeEvent->data->object->lines->data[0]->metadata ?? ($stripeEvent->data->object->metadata ?? null);

        if (isset($metadata->user)) {
            if ($stripeEvent->type != 'customer.subscription.created' && stripos($stripeEvent->type, 'customer.subscription.') !== false) {
                // Provide enough time for the subscription created event to be handled
                sleep(3);
            }

            $user = User::where('id', '=', $metadata->user)->first();

            // If a user was found
            if ($user) {
                if ($stripeEvent->type == 'customer.subscription.created') {
                    // If the user previously had a subscription, attempt to cancel it
                    if ($user->plan_subscription_id) {
                        $user->planSubscriptionCancel();
                    }

                    // If the user had tracking disabled previously
                    if (!$user->can_track) {
                        $user->can_track = true;
                    }

                    $user->plan_id = $metadata->plan;
                    $user->plan_amount = $metadata->amount;
                    $user->plan_currency = $metadata->currency;
                    $user->plan_interval = $metadata->interval;
                    $user->plan_payment_processor = 'stripe';
                    $user->plan_subscription_id = $stripeEvent->data->object->id;
                    $user->plan_subscription_status = $stripeEvent->data->object->status;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = $stripeEvent->data->object->current_period_end ? Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end) : null;
                    $user->plan_ends_at = null;
                    $user->save();

                    // If a coupon was used
                    if (isset($metadata->coupon) && $metadata->coupon) {
                        $coupon = Coupon::find($metadata->coupon);

                        // If a coupon was found
                        if ($coupon) {
                            // Increase the coupon usage
                            $coupon->increment('redeems', 1);
                        }
                    }
                } elseif (stripos($stripeEvent->type, 'customer.subscription.') !== false) {
                    // If the subscription exists
                    if ($user->plan_payment_processor == 'stripe' && $user->plan_subscription_id == $stripeEvent->data->object->id) {
                        // Update the recurring date
                        if ($stripeEvent->data->object->current_period_end) {
                            $user->plan_recurring_at = Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end);
                        }

                        // Update the subscription status
                        if ($stripeEvent->data->object->status) {
                            $user->plan_subscription_status = $stripeEvent->data->object->status;
                        }

                        // Update the subscription end date
                        if ($stripeEvent->data->object->cancel_at_period_end) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end);
                        } elseif ($stripeEvent->data->object->cancel_at) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->cancel_at);
                        } elseif ($stripeEvent->data->object->canceled_at) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->canceled_at);
                        } else {
                            $user->plan_ends_at = null;
                        }

                        // Reset the subscription recurring date
                        if (!empty($user->plan_ends_at)) {
                            $user->plan_recurring_at = null;
                        }

                        $user->save();
                    }
                } elseif ($stripeEvent->type == 'invoice.paid') {
                    // Make sure the invoice contains the payment id
                    if ($stripeEvent->data->object->charge) {
                        $payment = $this->paymentStore([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $stripeEvent->data->object->charge,
                            'processor' => 'stripe',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        // Attempt to send the payment confirmation email
                        try {
                            Mail::to($user->email)->locale($user->locale)->send(new PaymentMail($payment));
                        }
                        catch (\Exception $e) {}
                    } else {
                        return response()->json([
                            'status' => 400
                        ], 400);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the PayPal webhook.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function paypal(Request $request)
    {
        $httpClient = new HttpClient();

        $httpBaseUrl = 'https://'.(config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m').'.paypal.com/';

        // Attempt to retrieve the auth token
        try {
            $payPalAuthRequest = $httpClient->request('POST', $httpBaseUrl . 'v1/oauth2/token', [
                    'auth' => [config('settings.paypal_client_id'), config('settings.paypal_secret')],
                    'form_params' => [
                        'grant_type' => 'client_credentials'
                    ]
                ]
            );

            $payPalAuth = json_decode($payPalAuthRequest->getBody()->getContents());
        } catch (BadResponseException $e) {
            Log::info($e->getResponse()->getBody()->getContents());

            return response()->json([
                'status' => 400
            ], 400);
        }

        // Get the payload's content
        $payload = json_decode($request->getContent());

        // Attempt to validate the webhook signature
        try {
            $payPalWHSignatureRequest = $httpClient->request('POST', $httpBaseUrl . 'v1/notifications/verify-webhook-signature', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $payPalAuth->access_token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode([
                        'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                        'cert_url' => $request->header('PAYPAL-CERT-URL'),
                        'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                        'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                        'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                        'webhook_id' => config('settings.paypal_webhook_id'),
                        'webhook_event' => $payload
                    ])
                ]
            );

            $payPalWHSignature = json_decode($payPalWHSignatureRequest->getBody()->getContents());
        } catch (BadResponseException $e) {
            Log::info($e->getResponse()->getBody()->getContents());

            return response()->json([
                'status' => 400
            ], 400);
        }

        // Check if the webhook's signature status is successful
        if ($payPalWHSignature->verification_status != 'SUCCESS') {
            Log::info('PayPal signature validation failed.');

            return response()->json([
                'status' => 400
            ], 400);
        }

        // Parse the custom metadata parameters
        parse_str($payload->resource->custom_id ?? ($payload->resource->custom ?? null), $metadata);

        if ($metadata) {
            $user = User::where('id', '=', $metadata['user'])->first();

            // If a user was found
            if ($user) {
                if ($payload->event_type == 'BILLING.SUBSCRIPTION.CREATED') {
                    // If the user previously had a subscription, attempt to cancel it
                    if ($user->plan_subscription_id) {
                        $user->planSubscriptionCancel();
                    }

                    // If the user had tracking disabled previously
                    if (!$user->can_track) {
                        $user->can_track = true;
                    }

                    $user->plan_id = $metadata['plan'];
                    $user->plan_amount = $metadata['amount'];
                    $user->plan_currency = $metadata['currency'];
                    $user->plan_interval = $metadata['interval'];
                    $user->plan_payment_processor = 'paypal';
                    $user->plan_subscription_id = $payload->resource->id;
                    $user->plan_subscription_status = $payload->resource->status;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = null;
                    $user->plan_ends_at = null;
                    $user->save();

                    // If a coupon was used
                    if (isset($metadata['coupon']) && $metadata['coupon']) {
                        $coupon = Coupon::find($metadata['coupon']);

                        // If a coupon was found
                        if ($coupon) {
                            // Increase the coupon usage
                            $coupon->increment('redeems', 1);
                        }
                    }
                } elseif (stripos($payload->event_type, 'BILLING.SUBSCRIPTION.') !== false) {
                    // If the subscription exists
                    if ($user->plan_payment_processor == 'paypal' && $user->plan_subscription_id == $payload->resource->id) {
                        // Update the recurring date
                        if (isset($payload->resource->billing_info->next_billing_time)) {
                            $user->plan_recurring_at = Carbon::create($payload->resource->billing_info->next_billing_time);
                        }

                        // Update the subscription status
                        if (isset($payload->resource->status)) {
                            $user->plan_subscription_status = $payload->resource->status;
                        }

                        // If the subscription has been cancelled
                        if ($payload->event_type == 'BILLING.SUBSCRIPTION.CANCELLED') {
                            // Update the subscription end date
                            $user->plan_ends_at = $user->plan_recurring_at;
                        }

                        if (!empty($user->plan_ends_at)) {
                            // Reset the subscription recurring date
                            $user->plan_recurring_at = null;
                        }

                        $user->save();
                    }
                } elseif ($payload->event_type == 'PAYMENT.SALE.COMPLETED') {
                    $payment = $this->paymentStore([
                        'user_id' => $user->id,
                        'plan_id' => $metadata['plan'],
                        'payment_id' => $payload->resource->id,
                        'processor' => 'paypal',
                        'amount' => $metadata['amount'],
                        'currency' => $metadata['currency'],
                        'interval' => $metadata['interval'],
                        'status' => 'completed',
                        'coupon' => $metadata['coupon'] ?? null,
                        'tax_rates' => $metadata['tax_rates'] ?? null,
                        'customer' => $user->billing_information,
                    ]);

                    // Attempt to send the payment confirmation email
                    try {
                        Mail::to($user->email)->locale($user->locale)->send(new PaymentMail($payment));
                    }
                    catch (\Exception $e) {}
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the Coinbase webhook.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function coinbase(Request $request)
    {
        $payload = json_decode($request->getContent());

        $computedSignature = hash_hmac('sha256', $request->getContent(), config('settings.coinbase_wh_secret'));

        // Validate the webhook signature
        if (hash_equals($computedSignature, $request->server('HTTP_X_CC_WEBHOOK_SIGNATURE'))) {
            // If the payment was successful
            $metadata = $payload->event->data->metadata ?? null;

            if (isset($metadata->user)) {
                $user = User::where('id', '=', $metadata->user)->first();

                // If a user was found
                if ($user) {
                    if ($payload->event->type == 'charge:confirmed' || $payload->event->type == 'charge:resolved') {
                        // If the user previously had a subscription, attempt to cancel it
                        if ($user->plan_subscription_id) {
                            $user->planSubscriptionCancel();
                        }

                        // If the user had tracking disabled previously
                        if (!$user->can_track) {
                            $user->can_track = true;
                        }

                        $user->plan_id = $metadata->plan;
                        $user->plan_amount = $metadata->amount;
                        $user->plan_currency = $metadata->currency;
                        $user->plan_interval = $metadata->interval;
                        $user->plan_payment_processor = 'coinbase';
                        $user->plan_subscription_id = $payload->event->data->code;
                        $user->plan_subscription_status = null;
                        $user->plan_created_at = Carbon::now();
                        $user->plan_recurring_at = null;
                        $user->plan_ends_at = $metadata->interval == 'month' ? Carbon::now()->addMonth() : Carbon::now()->addYear();
                        $user->save();

                        // If a coupon was used
                        if (isset($metadata->coupon) && $metadata->coupon) {
                            $coupon = Coupon::find($metadata->coupon);

                            // If a coupon was found
                            if ($coupon) {
                                // Increase the coupon usage
                                $coupon->increment('redeems', 1);
                            }
                        }

                        $payment = $this->paymentStore([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $payload->event->data->code,
                            'processor' => 'coinbase',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        // Attempt to send the payment confirmation email
                        try {
                            Mail::to($user->email)->locale($user->locale)->send(new PaymentMail($payment));
                        }
                        catch (\Exception $e) {}
                    }
                }
            }
        } else {
            Log::info('Coinbase signature validation failed.');

            return response()->json([
                'status' => 400
            ], 400);
        }

        return response()->json([
            'status' => 200
        ], 200);
    }
}