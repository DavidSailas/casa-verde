<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The history of changes made in the admin (owners only). */
class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->integer('user') ?: null;

        return view('admin.activity.index', [
            'entries' => ActivityLog::query()
                ->when($userId, fn ($q) => $q->where('user_id', $userId))
                ->latest('created_at')->latest('id')
                ->simplePaginate(30)->withQueryString(),
            'staff' => User::where('is_admin', true)->orderBy('name')->get(['id', 'name']),
            'selected' => $userId,
        ]);
    }
}
