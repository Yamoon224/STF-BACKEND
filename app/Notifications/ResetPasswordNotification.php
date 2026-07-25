<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(protected string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.frontend_url'), '/')
            .'/reinitialiser-mot-de-passe?token='.$this->token
            .'&email='.urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe STF')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("Vous avez demandé la réinitialisation du mot de passe de votre compte STF.")
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien expire dans 60 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail — votre mot de passe restera inchangé.");
    }
}
