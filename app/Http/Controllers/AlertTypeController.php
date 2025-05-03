<?php

namespace App\Http\Controllers;

use App\Models\AlertType;
use Illuminate\Http\Request;

class AlertTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alertTypes = AlertType::get();
        return response()->json([
            'alert_types' => $alertTypes
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
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:1000',
        ]);

        

        $alertType = AlertType::create([
            'name' => $request->name,
            'icon' => $request->icon,
        ]);

        return response()->json([
            'alert_type' => $alertType,
            'message' => 'Alert type created successfully',
            'status' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(AlertType $alertType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AlertType $alertType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AlertType $alertType)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:1000',
        ]);

        $alertType->update([
            'name' => $request->name,
            'icon' => $request->icon,
        ]);

        return response()->json([
            'alert_type' => $alertType,
            'message' => 'Alert type updated successfully',
            'status' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlertType $alertType)
    {
        $alertType->delete();

        return response()->json([
            'message' => 'Alert type deleted successfully',
            'status' => 'success'
        ]);
    }
}
