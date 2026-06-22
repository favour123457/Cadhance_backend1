<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name, $email, $title, $msg;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $email, $title, $msg)
    {
        $this->name = $name;
        $this->email = $email;
        $this->title = $title;
        $this->msg = $msg;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->to($this->email, $this->name)
            ->subject($this->title . ' | ' . config('app.name'))
            ->view('emails.general.general')
            ->with([
                'name' => $this->name,
                'title' => $this->title,
                'msg' => $this->msg,
            ]);
    }



    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
