<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AcceptInvitationController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $tokenHash = hash('sha256', $request->string('token'));
        $invitation = UserInvitation::where('token_hash', $tokenHash)->first();

        if (!$invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            abort(404);
        }

        return view('auth.accept-invitation', [
            'token' => (string) $request->string('token'),
            'email' => $invitation->email,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $tokenHash = hash('sha256', $data['token']);
        $invitation = UserInvitation::where('token_hash', $tokenHash)->first();

        if (!$invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            abort(404);
        }

        // Crear o actualizar usuario por email (sin depender de Fillable)
        $user = User::firstOrNew(['email' => $invitation->email]);

        $user->name = $data['name'];
        $user->password = Hash::make($data['password']);
        $user->role = $invitation->role;
        $user->email_verified_at = now();

        $user->save();

        $invitation->accepted_at = now();
        $invitation->save();

        Auth::login($user);

        return redirect()->route('contacts.index');
    }
}