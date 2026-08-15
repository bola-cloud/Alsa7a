<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Notifications\TicketStatusNotification;

class TicketController extends Controller
{
    public function index()
    {
        $query = Ticket::with('user')->latest();

        // Ticket carries no country_id of its own, so the country is
        // inherited through the user relation. Strict on purpose, matching
        // the rest of the admin country switch.
        $adminCountryId = session('admin_country_id');
        if ($adminCountryId && $adminCountryId !== 'all') {
            $query->whereHas('user', function($q) use ($adminCountryId) {
                $adminCountryId === 'none'
                    ? $q->whereNull('country_id')
                    : $q->where('country_id', $adminCountryId);
            });
        }

        $tickets = $query->paginate(10);
        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        return view('admin.tickets.show', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'admin_notes' => 'nullable|string'
        ]);

        $ticket->update($data);

        // Notify User
        try {
            if ($ticket->user) {
                $ticket->user->notify(new TicketStatusNotification($ticket));
            }
        } catch (\Exception $e) {
            // Ignore
        }

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.tickets.show', $ticket);
    }
}
