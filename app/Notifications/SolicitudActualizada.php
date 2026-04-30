<?php

namespace App\Notifications;

use App\Models\SolicitudMantenimiento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SolicitudActualizada extends Notification
{
    use Queueable;

    public $solicitud;
    public $mensaje;

    public function __construct(SolicitudMantenimiento $solicitud, $mensaje)
    {
        $this->solicitud = $solicitud;
        $this->mensaje = $mensaje;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'solicitud_id' => $this->solicitud->id,
            'mensaje' => $this->mensaje,
            'titulo' => $this->solicitud->titulo,
            'url' => route('solicitudes.show', $this->solicitud),
        ];
    }
}
