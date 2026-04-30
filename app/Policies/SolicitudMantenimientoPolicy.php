<?php

namespace App\Policies;

use App\Models\SolicitudMantenimiento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SolicitudMantenimientoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Filtering will be done in the query
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SolicitudMantenimiento $solicitud): bool
    {
        if ($user->rol === 'supervisor') {
            return true;
        }

        return $user->unidad_id === $solicitud->unidad_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->rol, ['unidad', 'supervisor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SolicitudMantenimiento $solicitud): bool
    {
        if ($user->rol === 'supervisor') {
            return true;
        }

        return $user->rol === 'unidad' && $user->unidad_id === $solicitud->unidad_id;
    }

    /**
     * Determine whether the user can change status.
     */
    public function changeStatus(User $user, SolicitudMantenimiento $solicitud): bool
    {
        if ($user->rol === 'supervisor') {
            return true;
        }

        return $user->rol === 'unidad' && $user->unidad_id === $solicitud->unidad_id;
    }
}
