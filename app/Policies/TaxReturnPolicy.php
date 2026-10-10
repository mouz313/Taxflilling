<?php

namespace App\Policies;

use App\Models\TaxReturn;
use App\Models\User;

class TaxReturnPolicy
{
    /**
     * Determine whether the user can view the return.
     */
    public function view(User $user, TaxReturn $return): bool
    {
        if ($user->isCompany()) {
            return $return->consultancy_id === $user->consultancy_id;
        }

        if ($user->isClient()) {
            return $user->client && $return->client_id === $user->client->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the return (wizard, items, etc.).
     */
    public function update(User $user, TaxReturn $return): bool
    {
        return $this->view($user, $return) && ! $return->is_locked;
    }

    /**
     * Determine whether the user can delete the return.
     */
    public function delete(User $user, TaxReturn $return): bool
    {
        if ($user->isCompany()) {
            return $return->consultancy_id === $user->consultancy_id;
        }

        if ($user->isClient()) {
            return $user->client
                && $return->client_id === $user->client->id
                && $return->status === 'draft'
                && $return->fee_status !== 'paid';
        }

        return false;
    }

    /**
     * Determine whether the user can confirm the fee payment of the return.
     */
    public function confirmFee(User $user, TaxReturn $return): bool
    {
        return $user->isCompany() && $return->consultancy_id === $user->consultancy_id;
    }

    /**
     * Determine whether the user can transition the status of the return.
     */
    public function transitionStatus(User $user, TaxReturn $return): bool
    {
        return $user->isCompany() && $return->consultancy_id === $user->consultancy_id;
    }
}
