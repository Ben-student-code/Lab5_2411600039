<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutController extends Controller
{
    /**
     * List only the current user's workouts.
     */
    public function index()
    {
        return Workout::where('user_id', Auth::id())
            ->orderByDesc('date')
            ->get();
    }

    /**
     * Store a workout for the current user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'activity'  => 'required|string|max:100',
            'type'      => 'required|in:cardio,strength,flexibility,hiit',
            'intensity' => 'required|in:low,medium,high',
            'status'    => 'required|in:Completed,Ongoing,Failed',
            'calories'  => 'required|integer|min:0',
            'duration'  => 'required|integer|min:0',
            'notes'     => 'nullable|string',
            'date'      => 'required|date',
        ]);

        // Attach the logged-in user
        $data['user_id'] = Auth::id();

        return Workout::create($data);
    }

    /**
     * Show a workout — only if it belongs to the current user.
     */
    public function show(Workout $workout)
    {
        if ($workout->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }
        return $workout;
    }

    /**
     * Update a workout — only if it belongs to the current user.
     */
    public function update(Request $request, Workout $workout)
    {
        if ($workout->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'activity'  => 'sometimes|string|max:100',
            'type'      => 'sometimes|in:cardio,strength,flexibility,hiit',
            'intensity' => 'sometimes|in:low,medium,high',
            'status'    => 'sometimes|in:Completed,Ongoing,Failed',
            'calories'  => 'sometimes|integer|min:0',
            'duration'  => 'sometimes|integer|min:0',
            'notes'     => 'nullable|string',
            'date'      => 'sometimes|date',
        ]);

        $workout->update($data);
        return $workout->fresh();
    }

    /**
     * Delete a workout — only if it belongs to the current user.
     */
    public function destroy(Workout $workout)
    {
        if ($workout->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $workout->delete();
        return response()->json(['success' => true]);
    }
}