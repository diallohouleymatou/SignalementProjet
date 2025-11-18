<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ActivityLog\Models\ActivityLog;
use App\Modules\Comment\Models\Comment;
use App\Modules\Message\Models\Message;
use App\Modules\Report\Models\Report;
use App\Modules\Signalement\Models\Signalement;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Vérifier que l'utilisateur est admin
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Get dashboard statistics
     */
    public function statistics()
    {
        $stats = [
            // Utilisateurs
            'users' => [
                'total' => User::count(),
                'active' => User::active()->count(),
                'verified' => User::verified()->count(),
                'banned' => User::banned()->count(),
                'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
            ],

            // Signalements
            'signalements' => [
                'total' => Signalement::count(),
                'en_cours' => Signalement::where('status', 'en_cours')->count(),
                'retrouves' => Signalement::where('status', 'retrouve')->count(),
                'faux' => Signalement::where('status', 'faux')->count(),
                'pending_approval' => Signalement::pending()->count(),
                'new_this_month' => Signalement::where('created_at', '>=', now()->startOfMonth())->count(),
                'new_today' => Signalement::whereDate('created_at', today())->count(),
                'by_type' => [
                    'objet' => Signalement::where('type', 'objet')->count(),
                    'personne' => Signalement::where('type', 'personne')->count(),
                ],
            ],

            // Messages
            'messages' => [
                'total' => Message::count(),
                'unread' => Message::where('read', false)->count(),
                'today' => Message::whereDate('created_at', today())->count(),
            ],

            // Commentaires
            'comments' => [
                'total' => Comment::count(),
                'approved' => Comment::approved()->count(),
                'pending' => Comment::where('is_approved', false)->count(),
                'today' => Comment::whereDate('created_at', today())->count(),
            ],

            // Reports (Modération)
            'reports' => [
                'total' => Report::count(),
                'pending' => Report::pending()->count(),
                'reviewed' => Report::reviewed()->count(),
                'resolved' => Report::resolved()->count(),
                'by_reason' => [
                    'spam' => Report::byReason('spam')->count(),
                    'inappropriate' => Report::byReason('inappropriate')->count(),
                    'fake' => Report::byReason('fake')->count(),
                ],
            ],

            // Activité récente
            'activity' => [
                'total_today' => ActivityLog::whereDate('created_at', today())->count(),
                'total_this_week' => ActivityLog::where('created_at', '>=', now()->startOfWeek())->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Get recent activity
     */
    public function recentActivity(Request $request)
    {
        $limit = $request->input('limit', 50);

        $activities = ActivityLog::with('user')
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json($activities);
    }

    /**
     * Get signalements growth chart data
     */
    public function signalementsGrowth(Request $request)
    {
        $days = $request->input('days', 30);

        $data = Signalement::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /**
     * Get users growth chart data
     */
    public function usersGrowth(Request $request)
    {
        $days = $request->input('days', 30);

        $data = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /**
     * Get top users (most signalements)
     */
    public function topUsers(Request $request)
    {
        $limit = $request->input('limit', 10);

        $users = User::withCount('signalements')
            ->orderBy('signalements_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($users);
    }

    /**
     * Get popular signalements
     */
    public function popularSignalements(Request $request)
    {
        $limit = $request->input('limit', 10);

        $signalements = Signalement::with('user')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($signalements);
    }

    /**
     * Get system health
     */
    public function systemHealth()
    {
        $health = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
        ];

        return response()->json($health);
    }

    /**
     * Check database connection
     */
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check storage
     */
    private function checkStorage()
    {
        try {
            $diskSpace = disk_free_space(storage_path());
            $diskTotal = disk_total_space(storage_path());
            $percentUsed = (($diskTotal - $diskSpace) / $diskTotal) * 100;

            return [
                'status' => $percentUsed < 90 ? 'healthy' : 'warning',
                'disk_free' => round($diskSpace / 1024 / 1024 / 1024, 2) . ' GB',
                'disk_total' => round($diskTotal / 1024 / 1024 / 1024, 2) . ' GB',
                'percent_used' => round($percentUsed, 2) . '%',
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check cache
     */
    private function checkCache()
    {
        try {
            cache()->put('health_check', true, 10);
            $result = cache()->get('health_check');

            return [
                'status' => $result ? 'healthy' : 'error',
                'message' => $result ? 'Cache working' : 'Cache not working',
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
