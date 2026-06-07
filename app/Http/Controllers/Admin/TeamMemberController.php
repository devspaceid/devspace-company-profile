<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class TeamMemberController extends Controller
{
    public function index()
    {
        $members = TeamMember::ordered()->paginate(10);
        return view('admin.team.index', compact('members'));
    }

    public function create()
    {
        return view('admin.team.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {

    $file = $request->file('photo');

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
        env('SUPABASE_URL') . '/storage/v1/object/team/' . $fileName
    );

    if ($response->successful()) {

        $validated['photo'] =
            env('SUPABASE_URL')
            . '/storage/v1/object/public/team/'
            . $fileName;

    } else {

        return back()->with(
            'error',
            'Upload gagal: ' . $response->body()
        );
    }
}

        $validated['is_active'] = $request->boolean('is_active', true);

        TeamMember::create($validated);

        return redirect()->route('admin.team.index')->with('success', 'Team member added successfully!');
    }

    public function edit(TeamMember $team)
    {
        return view('admin.team.edit', ['member' => $team]);
    }

    public function update(Request $request, TeamMember $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {

    $file = $request->file('photo');

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
        env('SUPABASE_URL') . '/storage/v1/object/team/' . $fileName
    );

    if ($response->successful()) {

        $validated['photo'] =
            env('SUPABASE_URL')
            . '/storage/v1/object/public/team/'
            . $fileName;

    } else {

        return back()->with(
            'error',
            'Upload gagal: ' . $response->body()
        );
    }
}

        $validated['is_active'] = $request->boolean('is_active');

        $team->update($validated);

        return redirect()->route('admin.team.index')->with('success', 'Team member updated successfully!');
    }

    public function destroy(TeamMember $team)
    {
        if ($team->photo) {
            Storage::disk('public')->delete($team->photo);
        }
        $team->delete();

        return redirect()->route('admin.team.index')->with('success', 'Team member deleted successfully!');
    }

    public function deleteImage(TeamMember $team)
    {
        if ($team->photo) {
            Storage::disk('public')->delete($team->photo);
            $team->photo = null;
            $team->save();
            return back()->with('success', 'Photo deleted successfully!');
        }

        return back()->with('error', 'Photo not found.');
    }
}
