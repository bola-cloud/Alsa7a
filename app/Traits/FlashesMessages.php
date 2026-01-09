<?php

namespace App\Traits;

trait FlashesMessages
{
    /**
     * Flash a success message to the session for SweetAlert.
     *
     * @param string $message
     * @return void
     */
    protected function flashSuccess(string $message): void
    {
        session()->flash('swal_success', $message);
    }

    /**
     * Flash an error message to the session for SweetAlert.
     *
     * @param string $message
     * @return void
     */
    protected function flashError(string $message): void
    {
        session()->flash('swal_error', $message);
    }

    /**
     * Flash an info message to the session for SweetAlert.
     *
     * @param string $message
     * @return void
     */
    protected function flashInfo(string $message): void
    {
        session()->flash('swal_info', $message);
    }

    /**
     * Flash a warning message to the session for SweetAlert.
     *
     * @param string $message
     * @return void
     */
    protected function flashWarning(string $message): void
    {
        session()->flash('swal_warning', $message);
    }
}
