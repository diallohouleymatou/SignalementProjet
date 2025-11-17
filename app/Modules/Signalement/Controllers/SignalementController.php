<?php

namespace App\Modules\Signalement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Signalement\Models\Signalement;
use App\Modules\Signalement\Requests\SignalementRequest;
use App\Modules\Signalement\Resources\SignalementResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignalementController extends Controller
{
    public function index()
    {
        $signalements = Signalement::latest()->get();
        return SignalementResource::collection($signalements);
    }

    public function show($id)
    {
        $signalement = Signalement::findOrFail($id);
        return new SignalementResource($signalement);
    }

    public function store(SignalementRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $signalement = Signalement::create($data);
        return new SignalementResource($signalement);
    }

    public function update(SignalementRequest $request, $id)
    {
        $signalement = Signalement::findOrFail($id);
        $this->authorize('update', $signalement);
        $signalement->update($request->validated());
        return new SignalementResource($signalement);
    }

    public function destroy($id)
    {
        $signalement = Signalement::findOrFail($id);
        $this->authorize('delete', $signalement);
        $signalement->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    // Get authenticated user's signalements
    public function mySignalements()
    {
        $signalements = Signalement::where('user_id', Auth::id())
            ->latest()
            ->get();
        return SignalementResource::collection($signalements);
    }

    // Search signalements
    public function search(Request $request)
    {
        $query = Signalement::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from')) {
            $query->where('date_loss', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date_loss', '<=', $request->date_to);
        }

        $signalements = $query->latest()->get();
        return SignalementResource::collection($signalements);
    }

    // Get signalements by status
    public function byStatus($status)
    {
        $signalements = Signalement::where('status', $status)
            ->latest()
            ->get();
        return SignalementResource::collection($signalements);
    }

    // Get signalements by type
    public function byType($type)
    {
        $signalements = Signalement::where('type', $type)
            ->latest()
            ->get();
        return SignalementResource::collection($signalements);
    }
}

