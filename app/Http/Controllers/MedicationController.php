<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Patient;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    public function index()
    {
        $medications = Medication::with('patient.user')->paginate(20);
        return view('medications.index', compact('medications'));
    }

    public function create()
    {
        $patients = Patient::with('user')->get();
        return view('medications.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'name' => 'required|string',
            'dosage' => 'nullable|string',
            'frequency' => 'nullable|string',
        ]);

        $validated['is_active'] = true;
        $medication = Medication::create($validated);

        return redirect()->route('medications.show', $medication)->with('success', 'Medication created.');
    }

    public function show(Medication $medication)
    {
        $medication->load('patient.user');
        return view('medications.show', compact('medication'));
    }

    public function edit(Medication $medication)
    {
        $patients = Patient::with('user')->get();
        return view('medications.edit', compact('medication', 'patients'));
    }

    public function update(Request $request, Medication $medication)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'name' => 'required|string',
            'dosage' => 'nullable|string',
            'frequency' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $medication->update($validated);

        return redirect()->route('medications.show', $medication)->with('success', 'Medication updated.');
    }

    public function destroy(Medication $medication)
    {
        $medication->delete();
        return redirect()->route('medications.index')->with('success', 'Medication deleted.');
    }

    public function markAsTaken(Medication $medication)
    {
        $medication->markAsTaken();
        return back()->with('success', 'Dose recorded.');
    }

    public function markAsSkipped(Medication $medication)
    {
        $medication->update(['last_taken_at' => now()]);
        return back()->with('success', 'Dose skipped.');
    }

    public function patientIndex()
    {
        $patient = auth()->user()->patient;
        $medications = $patient ? $patient->medications()->paginate(20) : collect();
        return view('patient.medications.index', compact('medications', 'patient'));
    }

    public function recordAdherence(Request $request, Medication $medication)
    {
        $medication->update(['adherence_score' => $request->input('score')]);
        return back()->with('success', 'Adherence recorded.');
    }

    public function doctorIndex()
    {
        $doctor = auth()->user()->doctor;
        $patientIds = $doctor ? $doctor->activePatients()->pluck('patients.id') : [];
        $medications = Medication::whereIn('patient_id', $patientIds)->paginate(20);
        return view('doctor.medications.index', compact('medications'));
    }

    public function prescribeForm()
    {
        $doctor = auth()->user()->doctor;
        $patients = $doctor ? $doctor->activePatients()->with('user')->get() : [];
        return view('doctor.medications.prescribe', compact('patients'));
    }

    public function prescribe(Request $request)
    {
        $doctor = auth()->user()->doctor;
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'name' => 'required|string',
            'dosage' => 'nullable|string',
            'frequency' => 'nullable|string',
        ]);

        $validated['prescribed_by'] = $doctor->id;
        Medication::create($validated);

        return redirect()->route('doctor.medications.index')->with('success', 'Medication prescribed.');
    }

    public function adminIndex()
    {
        $medications = Medication::with(['patient.user', 'prescriber.user'])->paginate(20);
        return view('admin.medications.index', compact('medications'));
    }
}
