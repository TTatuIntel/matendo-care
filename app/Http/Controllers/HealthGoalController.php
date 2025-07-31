<?php

namespace App\Http\Controllers;

use App\Models\HealthGoal;
use App\Models\Patient;
use Illuminate\Http\Request;

class HealthGoalController extends Controller
{
    public function index()
    {
        $goals = HealthGoal::with('patient.user')->paginate(20);
        return view('health-goals.index', compact('goals'));
    }

    public function create()
    {
        $patients = Patient::with('user')->get();
        return view('health-goals.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'title' => 'required|string',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'target_date' => 'nullable|date',
            'priority' => 'nullable|string',
        ]);

        $goal = HealthGoal::create($validated);

        return redirect()->route('health-goals.show', $goal)->with('success', 'Goal created.');
    }

    public function show(HealthGoal $healthGoal)
    {
        $healthGoal->load('patient.user');
        return view('health-goals.show', compact('healthGoal'));
    }

    public function edit(HealthGoal $healthGoal)
    {
        $patients = Patient::with('user')->get();
        return view('health-goals.edit', compact('healthGoal', 'patients'));
    }

    public function update(Request $request, HealthGoal $healthGoal)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'title' => 'required|string',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'target_date' => 'nullable|date',
            'priority' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $healthGoal->update($validated);

        return redirect()->route('health-goals.show', $healthGoal)->with('success', 'Goal updated.');
    }

    public function destroy(HealthGoal $healthGoal)
    {
        $healthGoal->delete();
        return redirect()->route('health-goals.index')->with('success', 'Goal deleted.');
    }

    public function updateProgress(Request $request, HealthGoal $healthGoal)
    {
        $healthGoal->update(['current_value' => $request->current_value]);
        $healthGoal->updateProgress();
        return back()->with('success', 'Progress updated.');
    }

    public function patientIndex()
    {
        $patient = auth()->user()->patient;
        $goals = $patient ? $patient->healthGoals()->paginate(20) : collect();
        return view('patient.goals.index', compact('goals', 'patient'));
    }

    public function patientCreate()
    {
        return view('patient.goals.create');
    }

    public function patientStore(Request $request)
    {
        $patient = auth()->user()->patient;
        $validated = $request->validate([
            'title' => 'required|string',
            'target_value' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'target_date' => 'nullable|date',
            'priority' => 'nullable|string',
        ]);
        $validated['patient_id'] = $patient->id;
        $goal = HealthGoal::create($validated);

        return redirect()->route('patient.goals.index')->with('success', 'Goal created.');
    }
}
