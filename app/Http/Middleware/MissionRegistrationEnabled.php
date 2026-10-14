<?php

namespace App\Http\Middleware;

use App\Models\Mission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MissionRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $mission = $request->route('mission');

        if (is_numeric($mission)) {
            $mission = Mission::findOrFail($mission);
        }

        if (! $mission || ! $mission->enable_registration) {
            abort(403, __('Registration is not enabled for this mission.'));
        }

        return $next($request);
    }
}
