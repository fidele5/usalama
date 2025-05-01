<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alerts = Alert::with(['alertType', 'medias', 'user'])->paginate(10); // Paginate results
        return response()->json([
            'alerts' => $alerts
        ]);
    }

    public function getAlertTypes()
    {
        return response()->json([
            'types' => AlertType::get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'alert_type_id' => 'required|exists:alert_types,id',
            'description' => 'required|string|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'media.*' => 'nullable|file|max:10240' // Allow multiple files
        ]);

        DB::beginTransaction();

        try {
            $alert = Alert::create([
                'user_id' => Auth::user()->id,
                'alert_type_id' => $validated['alert_type_id'],
                'description' => $validated['description'],
                'location' => DB::raw("ST_GeomFromText('POINT({$validated['longitude']} {$validated['latitude']})')"),
                'address' => $validated['address'],
                'contact_phone' => $request->input('contact_phone', null),
            ]);

            if ($request->hasFile('media')) {
                $mediaPaths = [];
                foreach ($request->file('media') as $file) {
                    $path = $file->store('alerts/media');
                    $mediaPaths[] = $path;
                }
                foreach ($mediaPaths as $path) {
                    $alert->media()->create([
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'alert' => $alert->load('alertType')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Alert $alert)
    {
        return response()->json([
            'alert' => $alert->load(['alertType', 'media', 'user'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Alert $alert)
    {
        return response()->json([
            'alert' => $alert->load(['alertType', 'media', 'user'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Alert $alert)
    {
        $validated = $request->validate([
            'alert_type_id' => 'nullable|exists:alert_types,id',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'media.*' => 'nullable|file|max:10240' // Allow multiple files
        ]);

        DB::beginTransaction();

        try {
            $alert->update([
                'alert_type_id' => $validated['alert_type_id'] ?? $alert->alert_type_id,
                'description' => $validated['description'] ?? $alert->description,
                'location' => isset($validated['latitude'], $validated['longitude'])
                    ? DB::raw("ST_GeomFromText('POINT({$validated['longitude']} {$validated['latitude']})')")
                    : $alert->location,
                'address' => $validated['address'] ?? $alert->address,
                'contact_phone' => $request->input('contact_phone', $alert->contact_phone),
            ]);

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('alerts/media');
                    $alert->media()->create([
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'alert' => $alert->load(['alertType', 'media', 'user'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alert $alert)
    {
        DB::beginTransaction();

        try {
            // Delete associated media files
            foreach ($alert->media as $media) {
                Storage::delete($media->file_path);
                $media->delete();
            }

            $alert->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alert deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
