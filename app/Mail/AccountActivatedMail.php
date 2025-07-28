<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Rank;

class AccountActivatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public ?Rank $rank; // El rango puede ser nulo si no se asignó uno

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?Rank $rank)
    {
        $this->user = $user;
        $this->rank = $rank;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu cuenta ha sido activada!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user.account-activated',
            with: [
                'userName' => $this->user->first_name,
                'rankName' => $this->rank ? $this->rank->name : 'N/A',
                'loginUrl' => route('login'), // Asumiendo que tienes una ruta llamada 'login'
            ],
        );
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
