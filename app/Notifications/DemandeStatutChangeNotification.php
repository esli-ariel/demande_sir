<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Demande;

class DemandeStatutChangeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $demande;
    public $action;
    public $commentaire;

    public function __construct(Demande $demande, $action, $commentaire = null)
    {
        //
        $this->demande = $demande;
        $this->action = $action;
        $this->commentaire = $commentaire;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];// uniquement in-app
    }

    /**
     * Get the mail representation of the notification.
     */
   // public function toMail(object $notifiable): MailMessage
   // {
   //     return (new MailMessage)
   //        ->line('The introduction to the notification.')
    //       ->action('Notification Action', url('/'))
    //       ->line('Thank you for using our application!');
    //}


    public function toDatabase($notifiable)
    {
    return [
            'demande_id' => $this->demande->id,
            'objet' => $this->demande->objet_modif,
            'action' => $this->action,
            'commentaire' => $this->commentaire,
            'statut' => $this->demande->statut,
        ];
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
