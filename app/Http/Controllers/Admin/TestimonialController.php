<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::ordered()->paginate(10);
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'sort_order' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('client_photo')) {

    $file = $request->file('client_photo');
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
        env('SUPABASE_URL') . '/storage/v1/object/testimonials/' . $fileName
    );

    if ($response->successful()) {

        $validated['client_photo'] =
            env('SUPABASE_URL')
            . '/storage/v1/object/public/testimonials/'
            . $fileName;

    } else {

        return back()->with(
            'error',
            'Upload gagal: ' . $response->body()
        );

    }
}

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);

        Testimonial::create($validated);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial created successfully!');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'sort_order' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('client_photo')) {

    $file = $request->file('client_photo');
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
        env('SUPABASE_URL') . '/storage/v1/object/testimonials/' . $fileName
    );

    if ($response->successful()) {

        $validated['client_photo'] =
            env('SUPABASE_URL')
            . '/storage/v1/object/public/testimonials/'
            . $fileName;

    } else {

        return back()->with(
            'error',
            'Upload gagal: ' . $response->body()
        );

    }
}

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated successfully!');
    }

    public function destroy(Testimonial $testimonial)
    {
        if ($testimonial->client_photo) {
            Storage::disk('public')->delete($testimonial->client_photo);
        }
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted successfully!');
    }

    public function deleteImage(Testimonial $testimonial)
    {
        if ($testimonial->client_photo) {
            Storage::disk('public')->delete($testimonial->client_photo);
            $testimonial->client_photo = null;
            $testimonial->save();
            return back()->with('success', 'Photo deleted successfully!');
        }

        return back()->with('error', 'Photo not found.');
    }
}
