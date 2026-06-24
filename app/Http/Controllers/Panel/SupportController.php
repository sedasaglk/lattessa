<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SupportController extends Controller
{
    public function index(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();

        $tickets = DB::table('support_tickets')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        return view('panel.support.index', compact('tenant', 'tickets'));
    }

    public function create(TenantContext $ctx, string $tenant_slug): View
    {
        $tenant = $ctx->get();
        return view('panel.support.create', compact('tenant'));
    }

    public function store(Request $request, TenantContext $ctx, string $tenant_slug): RedirectResponse
    {
        $tenant = $ctx->get();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:20'],
            'priority' => ['required', 'in:low,medium,high'],
        ], [
            'subject.required' => 'Konu zorunludur.',
            'message.required' => 'Mesaj zorunludur.',
            'message.min' => 'Mesaj en az 20 karakter olmalidir.',
        ]);

        $ticketId = DB::table('support_tickets')->insertGetId([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('panel.support.show', ['tenant_slug' => $tenant->slug, 'id' => $ticketId])
            ->with('success', 'Destek talebiniz olusturuldu. En kisa surede yanit verecegiz.');
    }

    public function show(TenantContext $ctx, string $tenant_slug, string $id): View
    {
        $tenant = $ctx->get();

        $ticket = DB::table('support_tickets')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$ticket) abort(404);

        $replies = DB::table('support_ticket_replies')
            ->leftJoin('users', 'support_ticket_replies.user_id', '=', 'users.id')
            ->where('support_ticket_replies.ticket_id', $id)
            ->select('support_ticket_replies.*', 'users.name as user_name')
            ->orderBy('support_ticket_replies.created_at')
            ->get();

        return view('panel.support.show', compact('tenant', 'ticket', 'replies'));
    }

    public function reply(Request $request, TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        $ticket = DB::table('support_tickets')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$ticket) abort(404);
        if ($ticket->status === 'closed') {
            return back()->with('error', 'Kapali bir talebe yanit gonderilemez.');
        }

        $request->validate([
            'message' => ['required', 'string', 'min:5'],
        ]);

        DB::table('support_ticket_replies')->insert([
            'ticket_id' => $id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin_reply' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('support_tickets')->where('id', $id)->update([
            'status' => 'open',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Yanitiniz gonderildi.');
    }

    public function close(TenantContext $ctx, string $tenant_slug, string $id): RedirectResponse
    {
        $tenant = $ctx->get();

        DB::table('support_tickets')
            ->where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->update([
                'status' => 'closed',
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Destek talebi kapatildi.');
    }
}
