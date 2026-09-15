<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index()
    {
        return Workout::orderByDesc('date')->get();
    }

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

        return Workout::create($data);
    }

    public function show(Workout $workout)
    {
        return $workout;
    }

    public function update(Request $request, Workout $workout)
    {
        $workout->update($request->all());
        return $workout;
    }

    public function destroy(Workout $workout)
    {
        $workout->delete();
        return response()->json(['success' => true]);
    }
}