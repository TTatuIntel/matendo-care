<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function profile()
    {
        $doctor = auth()->user()->doctor;
        return view('doctor.profile', compact('doctor'));
    }

    public function updateProfile(Request $request)
    {
        $doctor = auth()->user()->doctor;
        $validated = $request->validate([
            'specialization' => 'nullable|string',
            'license_number' => 'nullable|string',
            'hospital_affiliation' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'years_of_experience' => 'nullable|integer',
        ]);

        $doctor->update($validated);

        return redirect()->route('doctor.profile.edit')->with('success', 'Profile updated.');
    }

    public function settings()
    {
        $doctor = auth()->user()->doctor;
        return view('doctor.settings', compact('doctor'));
    }

    public function updateSettings(Request $request)
    {
        $doctor = auth()->user()->doctor;
        $validated = $request->validate([
            'consultation_start_time' => 'nullable',
            'consultation_end_time' => 'nullable',
            'consultation_fee' => 'nullable|numeric',
            'is_available' => 'nullable|boolean',
            'accepts_emergency' => 'nullable|boolean',
        ]);

        $doctor->update($validated);

        return redirect()->route('doctor.settings')->with('success', 'Settings updated.');
    }

    public function adminIndex()
    {
        $doctors = Doctor::with('user')->paginate(20);
        return view('admin.doctors.index', compact('doctors'));
    }

    public function adminCreate()
    {
        return view('admin.doctors.create');
    }

    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'specialization' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'usertype' => 'doctor',
        ]);

        Doctor::create([
            'user_id' => $user->id,
            'specialization' => $validated['specialization'] ?? null,
        ]);

        return redirect()->route('admin.doctors.index')->with('success', 'Doctor created.');
    }

    public function adminEdit(Doctor $doctor)
    {
        $doctor->load('user');
        return view('admin.doctors.edit', compact('doctor'));
    }

    public function adminUpdate(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $doctor->user_id,
            'specialization' => 'nullable|string',
        ]);

        $doctor->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $doctor->update([
            'specialization' => $validated['specialization'] ?? null,
        ]);

        return redirect()->route('admin.doctors.index')->with('success', 'Doctor updated.');
    }

    public function adminDestroy(Doctor $doctor)
    {
        $doctor->user->delete();
        $doctor->delete();
        return redirect()->route('admin.doctors.index')->with('success', 'Doctor deleted.');
    }
}
