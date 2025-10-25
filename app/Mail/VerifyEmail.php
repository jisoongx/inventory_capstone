<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Owner;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $owner;

    public function __construct(Owner $owner)
    {
        $this->owner = $owner;
    }

    public function build()
    {
        $verifyUrl = url("/verify-email/{$this->owner->verification_token}");

        return $this->subject('Verify Your Shoplytix Account')
            ->view('emails.verify-owner')
            ->with([
                'owner' => $this->owner,
                'verifyUrl' => $verifyUrl,
            ]);
    }
}
