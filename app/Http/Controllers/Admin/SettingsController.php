<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = CompanySetting::getSettings();
        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'whatsapp' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,jpg|max:1024',
        ]);

        $settings = CompanySetting::first() ?? new CompanySetting();

        // Handle logo upload
        if ($request->hasFile('logo')) {

    $file = $request->file('logo');
    $fileName = uniqid() . '_' . $file->getClientOriginalName();

    $response = Http::withHeaders([
        'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
        'x-upsert' => 'true',
        'Content-Type' => $file->getMimeType(),
    ])->withBody(
        file_get_contents($file->getRealPath()),
        $file->getMimeType()
    )->post(
        env('SUPABASE_URL') . '/storage/v1/object/settings/' . $fileName
    );

    if ($response->successful()) {

        $validated['logo'] =
            env('SUPABASE_URL')
            . '/storage/v1/object/public/settings/'
            . $fileName;

    } else {

        return back()->with(
            'error',
            'Upload logo gagal: ' . $response->body()
        );
    }
}

        // Handle favicon upload
        if ($request->hasFile('favicon')) {

    $file = $request->file('favicon');
    $fileName = uniqid() . '_' . $file->getClientOriginalName();

    $response = Http::withHeaders([
        'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
        'x-upsert' => 'true',
        'Content-Type' => $file->getMimeType(),
    ])->withBody(
        file_get_contents($file->getRealPath()),
        $file->getMimeType()
    )->post(
        env('SUPABASE_URL') . '/storage/v1/object/settings/' . $fileName
    );

    if ($response->successful()) {

        $validated['favicon'] =
            env('SUPABASE_URL')
            . '/storage/v1/object/public/settings/'
            . $fileName;

    } else {

        return back()->with(
            'error',
            'Upload favicon gagal: ' . $response->body()
        );
    }
}

        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'Settings updated successfully!');
    }

    public function deleteImage(string $field)
    {
        $settings = CompanySetting::first();

        if ($settings && in_array($field, ['logo', 'favicon']) && $settings->$field) {
            Storage::disk('public')->delete($settings->$field);
            $settings->$field = null;
            $settings->save();
            return back()->with('success', ucfirst($field) . ' deleted successfully!');
        }

        return back()->with('error', 'Image not found.');
    }
}
