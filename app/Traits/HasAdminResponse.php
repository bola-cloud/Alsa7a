<?php

namespace App\Traits;

trait HasAdminResponse
{
    /**
     * Redirect with a success modal message.
     * key 'swal_success' triggers the SweetAlert success modal.
     */
    protected function successResponse($route, $message, $params = [])
    {
        return redirect()->route($route, $params)->with('swal_success', $message);
    }

    /**
     * Redirect back with an error modal message.
     * key 'swal_error' triggers the SweetAlert error modal.
     */
    protected function errorResponse($message)
    {
        return redirect()->back()->withInput()->with('swal_error', $message);
    }
}
