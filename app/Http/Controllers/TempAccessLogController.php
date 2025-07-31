<?php

namespace App\Http\Controllers;

use App\Models\TempAccessLog;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TempAccessLogController extends Controller
{
    public function index()
    {
        $logs = TempAccessLog::where('doctor_id', auth()->user()->doctor->id)->latest()->paginate(20);
        return view('temp-access.index', compact('logs'));
    }

    public function create()
    {
        $patients = Patient::with('user')->get();
        return view('temp-access.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'external_doctor_name' => 'required|string',
            'external_doctor_email' => 'required|email',
            'hospital_name' => 'nullable|string',
            'expires_at' => 'required|date',
        ]);

        $validated['doctor_id'] = auth()->user()->doctor->id;
        $validated['token'] = Str::random(40);

        TempAccessLog::create($validated);

        return redirect()->route('temp-access.index')->with('success', 'Temporary access granted.');
    }

    public function destroy(TempAccessLog $tempAccess)
    {
        $tempAccess->delete();
        return back()->with('success', 'Access revoked.');
    }

    public function access($token)
    {
        $log = TempAccessLog::where('token', $token)->where('is_active', true)->firstOrFail();
        $log->increment('access_count');
        $log->update(['last_accessed_at' => now(), 'first_accessed_at' => $log->first_accessed_at ?? now()]);
        $patient = $log->patient()->with('user')->first();
        return view('temp-access.view', compact('patient', 'log'));
    }

    public function doctorCreate()
    {
        return $this->create();
    }

    public function doctorStore(Request $request)
    {
        return $this->store($request);
    }

    public function adminIndex()
    {
        $logs = TempAccessLog::latest()->paginate(20);
        return view('admin.temp-access.index', compact('logs'));
    }
}
