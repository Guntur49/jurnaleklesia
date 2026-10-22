<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $data = [];

        if ($user->role === 'staff') {
            $data['todayJournal'] = $user->journals()->whereDate('journal_date', today())->first();
            $data['canWrite'] = $user->canSubmitJournalAt(now());
            $data['recentJournals'] = $user->journals()->latest('journal_date')->limit(5)->get();
        } elseif ($user->role === 'owner') {
            $data['recentJournals'] = Journal::with('user')
                ->whereHas('user', fn ($query) => $query->where('role', 'staff'))
                ->latest('created_at')
                ->limit(8)
                ->get();
            $data['staffCount'] = User::where('role', 'staff')->count();
        } else {
            $data['staffCount'] = User::where('role', 'staff')->count();
            $data['ownerCount'] = User::where('role', 'owner')->count();
            $data['recentJournals'] = Journal::with('user')
                ->whereHas('user', fn ($query) => $query->where('role', 'staff'))
                ->latest('created_at')
                ->limit(6)
                ->get();
        }

        return view('dashboard', $data);
    }
}
