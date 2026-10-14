<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Begin impersonating the given user.
     *
     * The id of the acting (impersonating) user is stored in the session so
     * that it can be restored when impersonation is stopped.
     */
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->cannot('impersonate users'), 403);
        abort_if($user->hasRole('admin'), 403);
        abort_if($user->is($request->user()), 403);

        session(['impersonate_user_id' => $request->user()->getAuthIdentifier()]);

        Auth::loginUsingId($user->getAuthIdentifier());

        return redirect()->route('dashboard');
    }

    /**
     * Stop impersonating and restore the original acting user.
     */
    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = session('impersonate_user_id');

        if (! $impersonatorId) {
            return redirect()->route('dashboard');
        }

        session()->forget('impersonate_user_id');

        Auth::loginUsingId($impersonatorId);

        return redirect()->route('users.index');
    }
}
