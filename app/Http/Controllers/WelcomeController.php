<?php

namespace App\Http\Controllers;

class WelcomeController
{
    public function welcome()
    {
        $featuredEvents = \App\Models\Event::where('approved', 1)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '>=', now())
            ->join('ticket_types', function ($join) {
                $join->on('events.event_id', '=', 'ticket_types.event_id')
                    ->where('ticket_types.is_active', '=', true)
                    ->where('ticket_types.remaining_quantity', '>', 0);
            })
            ->select('events.*')
            ->groupBy('events.event_id')
            ->orderByRaw('MAX((ticket_types.remaining_quantity / NULLIF(ticket_types.total_quantity, 0)) * 100) DESC')
            ->take(6)
            ->with(['location', 'ticketTypes' => function ($query) {
                $query->where('is_active', true)
                    ->where('remaining_quantity', '>', 0);
            }])
            ->get();

        $categories = \App\Models\EventCategory::withCount('events')->get();

        $stats = [
            'total_events' => \App\Models\Event::count(),
            'total_users' => \App\Models\User::count(),
            'total_tickets' => \App\Models\Ticket::where('payment_status', 'paid')->sum('quantity'),
            'total_favorites' => \App\Models\Favorite::count(),
        ];

        return view('welcome', compact('featuredEvents', 'categories', 'stats'));
    }
}
