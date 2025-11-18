<?php

namespace App\Modules\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Report\Models\Report;
use App\Modules\Report\Resources\ReportResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Create a new report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reportable_type' => 'required|string|in:App\Modules\Signalement\Models\Signalement,App\Modules\Comment\Models\Comment,App\Modules\Message\Models\Message',
            'reportable_id' => 'required|integer',
            'reason' => 'required|string|in:spam,inappropriate,fake,duplicate,harassment,other',
            'description' => 'nullable|string|max:1000',
        ]);

        // Vérifier que le modèle existe
        $reportable = $validated['reportable_type']::findOrFail($validated['reportable_id']);

        // Vérifier si l'utilisateur n'a pas déjà signalé ce contenu
        $existingReport = Report::where('user_id', Auth::id())
            ->where('reportable_type', $validated['reportable_type'])
            ->where('reportable_id', $validated['reportable_id'])
            ->where('status', Report::STATUS_PENDING)
            ->first();

        if ($existingReport) {
            return response()->json([
                'message' => 'You have already reported this content'
            ], 422);
        }

        $report = Report::create([
            'user_id' => Auth::id(),
            'reportable_type' => $validated['reportable_type'],
            'reportable_id' => $validated['reportable_id'],
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
        ]);

        return new ReportResource($report->load('user'));
    }

    /**
     * Get all reports (Moderator only)
     */
    public function index(Request $request)
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Report::with(['user', 'reviewer', 'reportable']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by reason
        if ($request->has('reason')) {
            $query->byReason($request->reason);
        }

        // Filter by reportable type
        if ($request->has('reportable_type')) {
            $query->where('reportable_type', $request->reportable_type);
        }

        $reports = $query->latest()->paginate($request->input('per_page', 20));

        return ReportResource::collection($reports);
    }

    /**
     * Get pending reports count (Moderator only)
     */
    public function pendingCount()
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $count = Report::pending()->count();

        return response()->json(['pending_count' => $count]);
    }

    /**
     * Get a specific report (Moderator only)
     */
    public function show($id)
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $report = Report::with(['user', 'reviewer', 'reportable'])->findOrFail($id);

        return new ReportResource($report);
    }

    /**
     * Mark report as reviewed (Moderator only)
     */
    public function markAsReviewed(Request $request, $id)
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $report->markAsReviewed(Auth::id(), $validated['admin_notes'] ?? null);

        return new ReportResource($report->fresh(['user', 'reviewer']));
    }

    /**
     * Mark report as resolved (Moderator only)
     */
    public function markAsResolved(Request $request, $id)
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $report->markAsResolved(Auth::id(), $validated['admin_notes'] ?? null);

        return new ReportResource($report->fresh(['user', 'reviewer']));
    }

    /**
     * Mark report as rejected (Moderator only)
     */
    public function markAsRejected(Request $request, $id)
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $report->markAsRejected(Auth::id(), $validated['admin_notes'] ?? null);

        return new ReportResource($report->fresh(['user', 'reviewer']));
    }

    /**
     * Delete a report (Moderator only)
     */
    public function destroy($id)
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }
}
