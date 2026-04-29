<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitationMail;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserInvitationController extends Controller
{
    public function index()
    {
        $invitations = UserInvitation::query()
            ->latest()
            ->paginate(30);

        return view('admin.invitations.index', compact('invitations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,editor,viewer'],
        ]);

        // Generar token plano + guardar hash
        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        // Upsert por email: si había una invitación previa no aceptada, la reemplazamos
        $invitation = UserInvitation::updateOrCreate(
            ['email' => $data['email']],
            [
                'role' => $data['role'],
                'token_hash' => $tokenHash,
                'expires_at' => now()->addHours(48),
                'accepted_at' => null,
                'invited_by' => $request->user()->id,
            ]
        );

        $acceptUrl = url('/invitations/accept?token='.$plainToken);

        Mail::to($invitation->email)->send(new UserInvitationMail($invitation, $acceptUrl));

        return back()->with('status', 'Invitación enviada a '.$invitation->email);
    }
}