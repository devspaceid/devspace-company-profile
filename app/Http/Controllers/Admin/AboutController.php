<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class AboutController extends Controller
{
    public function edit()
    {
        $about = AboutSection::active()->first() ?? new AboutSection();
        return view('admin.about.edit', compact('about'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'mission_title' => 'nullable|string|max:255',
            'mission_content' => 'nullable|string',
            'vision_title' => 'nullable|string|max:255',
            'vision_content' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'projects_completed' => 'nullable|integer|min:0',
            'happy_clients' => 'nullable|integer|min:0',
            'team_members' => 'nullable|integer|min:0',
        ]);

        $about = AboutSection::first() ?? new AboutSection();

        if ($request->hasFile('image')) {

    $file = $request->file('image');

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
        env('SUPABASE_URL') . '/storage/v1/object/about/' . $fileName
    );

    if ($response->successful()) {

        $validated['image'] =
            env('SUPABASE_URL')
            . '/storage/v1/object/public/about/'
            . $fileName;

    } else {

        return back()->with(
    'error',
    'Upload gagal: ' . $response->body()
);

    }
}

        $validated['is_active'] = true;
        $about->fill($validated);
        $about->save();

        return back()->with('success', 'About section updated successfully!');
    }

    public function deleteImage()
    {
        $about = AboutSection::first();

        if ($about && $about->image) {
            Storage::disk('public')->delete($about->image);
            $about->image = null;
            $about->save();
            return back()->with('success', 'Image deleted successfully!');
        }

        return back()->with('error', 'Image not found.');
    }
}
