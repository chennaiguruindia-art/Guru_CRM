<?php

namespace App\Http\Controllers;

use App\Services\TodayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Real, permission-agnostic "needs attention" feed for the navbar bell.
     * Fetched on demand so it never slows down normal page loads.
     */
    public function __invoke(TodayService $today): JsonResponse
    {
        return $this->index($today);
    }

    public function index(TodayService $today): JsonResponse
    {
        $all = $today->items(50);

        return response()->json([
            'success' => true,
            'count' => count($all),
            'items' => array_slice($all, 0, 10),
        ]);
    }

    /**
     * Mark single notification or all notifications as read for current user.
     */
    public function markAsRead(Request $request, TodayService $today): JsonResponse
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $key = $request->input('key');
        $all = $request->boolean('all');

        if ($all) {
            $items = $today->items(100);
            foreach ($items as $item) {
                if (!empty($item['key'])) {
                    DB::table('notification_reads')->updateOrInsert(
                        ['user_id' => $userId, 'notification_key' => $item['key']],
                        ['updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        } elseif ($key) {
            DB::table('notification_reads')->updateOrInsert(
                ['user_id' => $userId, 'notification_key' => $key],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        $remainingCount = count($today->items(50));

        return response()->json([
            'success' => true,
            'count' => $remainingCount,
            'message' => 'Notification marked as read',
        ]);
    }
}
