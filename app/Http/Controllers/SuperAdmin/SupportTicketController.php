<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        $status = request('status', 'open');
        $priority = request('priority');

        $tickets = DB::table('support_tickets')
            ->join('tenants', 'support_tickets.tenant_id', '=', 'tenants.id')
            ->join('users', 'support_tickets.user_id', '=', 'users.id')
            ->when($status !== 'all', fn($q) => $q->where('support_tickets.status', $status))
            ->when($priority, fn($q) => $q->where('support_tickets.priority', $priority))
            ->select(
                'support_tickets.*',
                'tenants.company_name',
                'tenants.slug',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderByRaw("FIELD(support_tickets.priority, 'high', 'medium', 'low')")
            ->orderByDesc('support_tickets.created_at')
            ->paginate(20);

        $counts = DB::table('support_tickets')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('super-admin.support.index', compact('tickets', 'counts', 'status', 'priority'));
    }

    public function show(string $id): View
    {
        $ticket = DB::table('support_tickets')
            ->join('tenants', 'support_tickets.tenant_id', '=', 'tenants.id')
            ->join('users', 'support_tickets.user_id', '=', 'users.id')
            ->where('support_tickets.id', $id)
            ->select(
                'support_tickets.*',
                'tenants.company_name',
                'tenants.slug',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->first();

        if (!$ticket) abort(404);

        $replies = DB::table('support_ticket_replies')
            ->leftJoin('users', 'support_ticket_replies.user_id', '=', 'users.id')
            ->where('support_ticket_replies.ticket_id', $id)
            ->select('support_ticket_replies.*', 'users.name as user_name')
            ->orderBy('support_ticket_replies.created_at')
            ->get();

        return view('super-admin.support.show', compact('ticket', 'replies'));
    }

    public function reply(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string'],
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket = DB::table('support_tickets')->where('id', $id)->first();
        if (!$ticket) abort(404);

        DB::table('support_ticket_replies')->insert([
            'ticket_id' => $id,
            'admin_id' => session('super_admin_id'),
            'message' => $request->message,
            'is_admin_reply' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('support_tickets')->where('id', $id)->update([
            'status' => $request->status,
            'resolved_at' => in_array($request->status, ['resolved', 'closed']) ? now() : null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Yanit gonderildi.');
    }

    public function updatePriority(Request $request, string $id): RedirectResponse
    {
        $request->validate(['priority' => ['required', 'in:low,medium,high']]);

        DB::table('support_tickets')->where('id', $id)->update([
            'priority' => $request->priority,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Oncelik guncellendi.');
    }
}
