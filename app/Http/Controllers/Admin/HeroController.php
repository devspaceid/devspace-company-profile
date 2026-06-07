<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class HeroController extends Controller
{
    public function edit()
    {
        $hero = HeroSection::active()->first() ?? new HeroSection();
        return view('admin.hero.edit', compact('hero'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'button_text_secondary' => 'nullable|string|max:100',
            'button_link_secondary' => 'nullable|string|max:255',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'foreground_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $hero = HeroSection::first() ?? new HeroSection();

        if ($request->hasFile('background_image')) {

    $file = $request->file('background_image');
    $filename = uniqid().'_'.$file->getClientOriginalName();

    Http::withHeaders([
        'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
        'Authorization' => 'Bearer '.env('SUPABASE_SERVICE_ROLE_KEY'),
        'x-upsert' => 'true',
        'Content-Type' => $file->getMimeType(),
    ])->withBody(
        file_get_contents($file->getRealPath()),
        $file->getMimeType()
    )->post(
        env('SUPABASE_URL').'/storage/v1/object/hero/'.$filename
    );

    $validated['background_image'] =
        env('SUPABASE_URL').'/storage/v1/object/public/hero/'.$filename;
}

        if ($request->hasFile('foreground_image')) {

    $file = $request->file('foreground_image');
    $filename = uniqid().'_'.$file->getClientOriginalName();

    Http::withHeaders([
        'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
        'Authorization' => 'Bearer '.env('SUPABASE_SERVICE_ROLE_KEY'),
        'x-upsert' => 'true',
        'Content-Type' => $file->getMimeType(),
    ])->withBody(
        file_get_contents($file->getRealPath()),
        $file->getMimeType()
    )->post(
        env('SUPABASE_URL').'/storage/v1/object/hero/'.$filename
    );

    $validated['foreground_image'] =
        env('SUPABASE_URL').'/storage/v1/object/public/hero/'.$filename;
}

        $validated['is_active'] = true;
        $hero->fill($validated);
        $hero->save();

        return back()->with('success', 'Hero section updated successfully!');
    }

    public function deleteImage(string $field)
    {
        $hero = HeroSection::first();

        if ($hero && in_array($field, ['background_image', 'foreground_image']) && $hero->$field) {
            Storage::disk('public')->delete($hero->$field);
            $hero->$field = null;
            $hero->save();
            return back()->with('success', 'Image deleted successfully!');
        }

        return back()->with('error', 'Image not found.');
    }
}
