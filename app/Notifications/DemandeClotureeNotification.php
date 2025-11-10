<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Demande;


class DemandeClotureeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    public function __construct(public Demande $demande) 
    {}

    public function via($notifiable)
    {
        return ['database']; // notification in-app uniquement
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Votre demande #{$this->demande->id} a été clôturée et est prête pour réception.",
            'demande_id' => $this->demande->id,
            'statut' => $this->demande->statut,
        ];
    }
   

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
   

    /**
     * Get the mail representation of the notification.
     
    *public function toMail(object $notifiable): MailMessage
    *{
     *   return (new MailMessage)
    *        ->line('The introduction to the notification.')
     *       ->action('Notification Action', url('/'))
     *       ->line('Thank you for using our application!');
    *}
    */
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
   /* public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
    */
}
