<?php

namespace App\Http\Controllers;

use App\Models\DoctorPatient;
use Illuminate\Http\Request;

class DoctorPatientController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
        ]);

        $relation = DoctorPatient::create([
            'doctor_id' => $validated['doctor_id'],
            'patient_id' => $validated['patient_id'],
            'status' => 'active',
            'assigned_date' => now(),
        ]);

        return back()->with('success', 'Patient assigned.');
    }

    public function update(Request $request, DoctorPatient $doctorPatient)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $doctorPatient->update($validated);
        return back()->with('success', 'Relationship updated.');
    }

    public function destroy(DoctorPatient $doctorPatient)
    {
        $doctorPatient->delete();
        return back()->with('success', 'Relationship removed.');
    }

    public function makePrimary(DoctorPatient $doctorPatient)
    {
        $doctorPatient->makePrimary();
        return back()->with('success', 'Primary doctor set.');
    }
}
