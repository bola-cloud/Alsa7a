<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('user')->latest()->paginate(10);
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

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.tickets.show', $ticket);
    }
}
