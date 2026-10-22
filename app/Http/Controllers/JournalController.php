<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'staff')],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $user = $request->user();
        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $selectedStaffId = $filters['staff_id'] ?? ($user->role === 'staff' ? $user->id : null);
        $staff = User::where('role', 'staff')->orderBy('name')->get();
        $days = [];
        $calendarWeeks = [];
        $selectedStaff = $selectedStaffId ? $staff->firstWhere('id', (int) $selectedStaffId) : null;

        if ($selectedStaff) {
            $monthStart = now()->setDate($year, $month, 1)->startOfDay();
            $entries = $selectedStaff->journals()
                ->whereBetween('journal_date', [$monthStart->toDateString(), $monthStart->copy()->endOfMonth()->toDateString()])
                ->get()
                ->keyBy(fn (Journal $journal) => $journal->journal_date->format('Y-m-d'));

            for ($day = 1; $day <= $monthStart->daysInMonth; $day++) {
                $date = $monthStart->copy()->day($day);
                $days[] = [
                    'date' => $date,
                    'journal' => $entries->get($date->toDateString()),
                ];
            }

            $calendarCells = array_fill(0, $monthStart->dayOfWeekIso - 1, null);
            array_push($calendarCells, ...$days);

            while (count($calendarCells) % 7 !== 0) {
                $calendarCells[] = null;
            }

            $calendarWeeks = array_chunk($calendarCells, 7);
        }

        return view('journals.index', [
            'staff' => $staff,
            'selectedStaff' => $selectedStaff,
            'selectedStaffId' => $selectedStaffId,
            'month' => $month,
            'year' => $year,
            'calendarWeeks' => $calendarWeeks,
            'months' => collect(range(1, 12))->mapWithKeys(fn ($number) => [
                $number => Carbon::create(2000, $number, 1)->locale('id')->translatedFormat('F'),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canSubmitJournalAt(now())) {
            return redirect()->route('dashboard')->with('error', 'Jurnal hanya dapat dikirim pada jam kerja yang ditentukan admin.');
        }

        $attributes = $request->validate([
            'content' => ['required', 'string', 'min:5', 'max:10000'],
            'result' => ['required', 'string', 'min:3', 'max:10000'],
        ]);

        Journal::updateOrCreate(
            ['user_id' => $user->id, 'journal_date' => today()->toDateString()],
            [
                'content' => $attributes['content'],
                'result' => $attributes['result'],
            ],
        );

        return redirect()->route('dashboard')->with('status', 'Jurnal hari ini berhasil disimpan.');
    }

    public function pdf(User $user, Request $request)
    {
        abort_unless($user->role === 'staff', 404);

        $filters = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);
        $monthStart = now()->setDate((int) $filters['year'], (int) $filters['month'], 1)->startOfDay();
        $entries = $user->journals()
            ->whereBetween('journal_date', [$monthStart->toDateString(), $monthStart->copy()->endOfMonth()->toDateString()])
            ->orderBy('journal_date')
            ->get()
            ->keyBy(fn (Journal $journal) => $journal->journal_date->format('Y-m-d'));
        $days = [];

        for ($day = 1; $day <= $monthStart->daysInMonth; $day++) {
            $date = $monthStart->copy()->day($day);
            $days[] = [
                'date' => $date,
                'journal' => $entries->get($date->toDateString()),
            ];
        }

        return Pdf::loadView('journals.pdf', [
            'user' => $user,
            'monthLabel' => $monthStart->locale('id')->translatedFormat('F Y'),
            'days' => $days,
        ])->setPaper('a4')->download('jurnal-'.$user->username.'-'.$monthStart->format('Y-m').'.pdf');
    }
}
