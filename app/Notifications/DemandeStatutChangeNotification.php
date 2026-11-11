<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Notifications\Notification;

class DemandeStatutChangeNotification extends Notification
{
    public function __construct(
        public Demande $demande,
        public string $action,
        public ?string $commentaire = null,
        public ?string $title = null,
        public ?string $actor = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'demande_id'=>$this->demande->id,
            'numero_dma'=>$this->demande->numero_dma,
            'objet'=>$this->demande->objet_modif,
            'message'=>$this->title ?? $this->action,
            'action'=>$this->action,
            'commentaire'=>$this->commentaire,
            'statut'=>$this->demande->statut,
            'acteur'=>$this->actor,
        ];
    }
}
