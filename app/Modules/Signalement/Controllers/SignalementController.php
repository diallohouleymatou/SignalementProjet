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
}

