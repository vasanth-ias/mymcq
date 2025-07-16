<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuizAttempt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard.
     */
    public function index()
    {
        // Monthly High Score Toppers (cumulative total score, current month)
        $monthlyToppers = QuizAttempt::selectRaw('user_id, SUM(score) as total_monthly_score, MAX(completed_at) as last_attempt_at_month')
                                    ->whereMonth('completed_at', Carbon::now()->month) // Only attempts from current month
                                    ->whereYear('completed_at', Carbon::now()->year) // Only attempts from current year
                                    ->whereNotNull('completed_at')
                                    ->groupBy('user_id')
                                    ->orderByDesc('total_monthly_score')
                                    ->orderBy('last_attempt_at_month', 'asc') // Tie-breaker for cumulative: earlier last completion
                                    ->with('user')
                                    ->limit(3)
                                    ->get();

        // "Daily Topper" - interpreted as the highest score from the single most recently completed quiz overall.
        // This query finds the single latest completed quiz attempt, then if there are multiple with the exact same 'completed_at',
        // it picks the one with the highest score.
        $mostRecentHighestScoreQuiz = QuizAttempt::whereNotNull('completed_at')
                                                ->orderByDesc('completed_at') // Sort by most recent first
                                                ->orderByDesc('score')       // If completed_at is same, sort by score
                                                ->with('user')
                                                ->first(); // Get only the very first one (most recent, highest score)


        return view('dashboard', compact('monthlyToppers', 'mostRecentHighestScoreQuiz'));
    }
}