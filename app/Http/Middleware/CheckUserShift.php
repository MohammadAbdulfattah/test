<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckUserShift
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        
        if($user && $user->role_name!= 'Admin')
        {
            // Get current day and time
            $now = Carbon::now();
            $currentDay = $now->format('l'); // Full day name: Sunday, Monday, etc.
            $currentTime = $now->format('H:i');

            // Fetch shifts for today
            $shifts = $user->shifts()->where('day', $currentDay)->get();

            // If no shifts set for today, block access
            if ($shifts->isEmpty()) {

                return redirect()->route('shift.denied');
            }

            // Check if current time falls within ANY of today’s shifts
            $inShift = $shifts->contains(function ($shift) use ($currentTime) {
               
                return $shift->start_time->format('H:i') <= $currentTime && $shift->end_time->format('H:i') > $currentTime;
            });
           
            if (! $inShift) {
                return redirect()->route('shift.denied');;
            }
            return $next($request);
        }
         
        return $next($request);
    }
}
