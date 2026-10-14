<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class WelcomeNewUser extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $password
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = URL::route('login');

        return (new MailMessage)
            ->subject(__('Welcome to :app — Your Account Has Been Created', ['app' => config('app.name')]))
            ->greeting(__('Hello :name!', ['name' => $notifiable->name]))
            ->line(__('Your account has been created.').' '.__('An administrator has set you up with a new account.').' '.__('Your temporary password is below.').' '.__('Please log in and change your password immediately after your first sign-in.').'.')
            ->line(__('**Email:** :email', ['email' => $notifiable->email]))
            ->line(__('**Password:** :password', ['password' => $this->password]))
            ->action(__('Log in'), $loginUrl)
            ->line(__('Thank you for using our application!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'password' => true,
        ];
    }
}
