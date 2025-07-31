<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index()
    {
        $records = MedicalRecord::with(['patient.user', 'recorder'])->latest()->paginate(20);
        return view('medical-records.index', compact('records'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'category' => 'required|string',
            'data' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        $validated['recorded_by'] = auth()->id();

        $record = MedicalRecord::create($validated);

        return redirect()->route('medical-records.show', $record)->with('success', 'Record created.');
    }

    public function show(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['patient.user', 'recorder']);
        return view('medical-records.show', compact('medicalRecord'));
    }

    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'data' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        $old = $medicalRecord->toArray();
        $medicalRecord->update($validated);

        return redirect()->route('medical-records.show', $medicalRecord)->with('success', 'Record updated.');
    }

    public function destroy(MedicalRecord $medicalRecord)
    {
        $medicalRecord->delete();
        return redirect()->route('medical-records.index')->with('success', 'Record deleted.');
    }

    public function markAsReviewed(MedicalRecord $medicalRecord)
    {
        $medicalRecord->markAsReviewed();
        return back()->with('success', 'Record reviewed.');
    }

    public function patientIndex()
    {
        $patient = auth()->user()->patient;
        $records = $patient ? $patient->medicalRecords()->latest()->paginate(20) : collect();
        return view('patient.records.index', compact('records', 'patient'));
    }

    public function pendingReviews()
    {
        $doctor = auth()->user()->doctor;
        $patientIds = $doctor ? $doctor->activePatients()->pluck('patients.id') : [];
        $records = MedicalRecord::whereIn('patient_id', $patientIds)->unreviewed()->paginate(20);
        return view('doctor.reviews.pending', compact('records'));
    }

    public function adminIndex()
    {
        $records = MedicalRecord::with(['patient.user'])->latest()->paginate(20);
        return view('admin.medical-records.index', compact('records'));
    }
}
