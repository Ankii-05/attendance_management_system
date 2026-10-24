<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class SettingController extends Controller
{
    // Update office timing settings
    public function updateOfficeTiming(Request $request)
    {
        $request->validate([
            'office_start_time' => ['required', 'date_format:H:i'],
            'office_end_time' => ['required', 'date_format:H:i', 'after:office_start_time'],
        ], [
            'office_start_time.required' => 'Office start time is required.',
            'office_end_time.required' => 'Office end time is required.',
            'office_end_time.after' => 'Office end time must be later than start time.',
        ]);

        Setting::set('office_start_time', $request->office_start_time);
        Setting::set('office_end_time', $request->office_end_time);

        return back()->with('success', 'Office timings updated successfully.');
    }
}
