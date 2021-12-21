<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var
     */
    public $payment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(formatTitle([$this->payment->status == 'completed' ? __('Payment completed') : __('Payment cancelled'), config('settings.title')]))
            ->markdown('vendor.notifications.email', [
                'introLines' => [$this->payment->status == 'completed' ? (__('The payment was successful.') . ' ' . __('Thank you!')) : __('The payment was cancelled.')],
                'actionText' => __('Invoice'),
                'actionUrl' => route('account.invoices.show', [$this->payment->id])
            ]);
    }
}
