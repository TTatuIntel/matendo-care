<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['patient.user', 'doctor.user'])->latest()->paginate(20);
        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user')->get();
        return view('appointments.create', compact('patients', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'reason' => 'nullable|string',
        ]);

        $validated['status'] = 'scheduled';

        $appointment = Appointment::create($validated);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment created.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient.user', 'doctor.user']);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user')->get();
        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $appointment->update($validated);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('appointments.index')->with('success', 'Appointment deleted.');
    }

    public function confirm(Appointment $appointment)
    {
        $appointment->confirm();
        return back()->with('success', 'Appointment confirmed.');
    }

    public function cancel(Appointment $appointment)
    {
        $appointment->cancel();
        return back()->with('success', 'Appointment cancelled.');
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $appointment->complete($request->post_appointment_notes);
        return back()->with('success', 'Appointment completed.');
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Appointment rescheduled.');
    }

    public function doctorIndex()
    {
        $doctor = auth()->user()->doctor;
        $appointments = $doctor ? $doctor->appointments()->with('patient.user')->latest()->paginate(20) : collect();
        return view('doctor.appointments.index', compact('appointments'));
    }

    public function doctorCreate()
    {
        $doctor = auth()->user()->doctor;
        $patients = $doctor ? $doctor->activePatients()->with('user')->get() : [];
        return view('doctor.appointments.create', compact('patients'));
    }

    public function doctorStore(Request $request)
    {
        $doctor = auth()->user()->doctor;
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'reason' => 'nullable|string',
        ]);

        $validated['doctor_id'] = $doctor->id;
        $validated['status'] = 'scheduled';

        $appointment = Appointment::create($validated);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment created.');
    }

    public function consultations()
    {
        $doctor = auth()->user()->doctor;
        $appointments = $doctor ? $doctor->appointments()->where('status', 'completed')->with('patient.user')->paginate(20) : collect();
        return view('doctor.consultations.index', compact('appointments'));
    }

    public function adminIndex()
    {
        $appointments = Appointment::with(['patient.user', 'doctor.user'])->latest()->paginate(20);
        return view('admin.appointments.index', compact('appointments'));
    }

    public function adminCreate()
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user')->get();
        return view('admin.appointments.create', compact('patients', 'doctors'));
    }

    public function adminStore(Request $request)
    {
        return $this->store($request);
    }
}
