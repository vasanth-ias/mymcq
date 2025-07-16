<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // For flash messages
use Illuminate\Support\Carbon; // For handling time

class QuizController extends Controller
{
    // Total questions to fetch for a quiz (adjust as needed)
    const QUESTIONS_PER_QUIZ = 10;
    // Time per question in seconds (1 minute 12 seconds = 72 seconds)
    const TIME_PER_QUESTION_SECONDS = 72;

    /**
     * Start a new quiz session.
     */
    public function startQuiz()
    {
        // Fetch random questions
        // For a commercial site, you might want categories, difficulty levels, etc.
        $questions = Question::inRandomOrder()->limit(self::QUESTIONS_PER_QUIZ)->get();

        if ($questions->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No questions available to start a quiz. Please ask an admin to upload some.');
        }

        // Create a new quiz attempt record
        $quizAttempt = QuizAttempt::create([
            'user_id' => Auth::id(),
            'total_questions' => $questions->count(),
            'started_at' => Carbon::now(),
            'attempt_details' => [], // Initialize with empty details
        ]);

        // Store question IDs and their order in the session for consistency
        // This is important to ensure the same questions are shown throughout the quiz
        Session::put('current_quiz_questions_' . $quizAttempt->id, $questions->pluck('id')->toArray());
        Session::put('current_quiz_question_index_' . $quizAttempt->id, 0); // Start at the first question

        return redirect()->route('quiz.show_question', [
            'attempt_id' => $quizAttempt->id,
            'question_number' => 1 // Start with question 1
        ]);
    }

    /**
     * Display a specific question for the quiz.
     */
    public function showQuestion(QuizAttempt $attempt_id, $question_number)
    {
        // Ensure the quiz attempt belongs to the current user
        if ($attempt_id->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to quiz attempt.');
        }

        // Check if quiz is already completed
        if ($attempt_id->completed_at) {
            return redirect()->route('quiz.results', $attempt_id->id)->with('info', 'This quiz has already been completed.');
        }

        // Retrieve question IDs from session
        $questionIds = Session::get('current_quiz_questions_' . $attempt_id->id);
        $currentQuestionIndex = Session::get('current_quiz_question_index_' . $attempt_id->id);

        // Check for valid question number/index
        if ($question_number < 1 || $question_number > count($questionIds)) {
            return redirect()->route('dashboard')->with('error', 'Invalid question number.');
        }

        // Ensure we are on the correct question in sequence
        if (($question_number - 1) !== $currentQuestionIndex) {
             // User tried to skip or go back/forward out of order
             // For strict tests, force them to the current index
             return redirect()->route('quiz.show_question', [
                 'attempt_id' => $attempt_id->id,
                 'question_number' => $currentQuestionIndex + 1
             ])->with('warning', 'Please proceed in order.');
        }

        // Get the current question
        $question = Question::find($questionIds[$currentQuestionIndex]);

        if (!$question) {
            // This shouldn't happen if questions were fetched correctly initially
            return redirect()->route('dashboard')->with('error', 'Question not found.');
        }

        // Calculate remaining time for the current question
        // This is client-side driven for UX, but server-side validation is still needed.
        $time_limit_per_question_ms = self::TIME_PER_QUESTION_SECONDS * 1000;

        return view('user.quiz.question', [
            'question' => $question,
            'attempt' => $attempt_id,
            'question_number' => $question_number,
            'total_questions' => count($questionIds),
            'time_limit_ms' => $time_limit_per_question_ms,
        ]);
    }

    /**
     * Submit an answer for a question.
     */
    public function submitAnswer(Request $request, QuizAttempt $attempt_id)
    {
        // Ensure the quiz attempt belongs to the current user
        if ($attempt_id->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to quiz attempt.');
        }

        if ($attempt_id->completed_at) {
            return redirect()->route('quiz.results', $attempt_id->id);
        }

        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'selected_option' => 'nullable|string|max:1', // Option A, B, C, D (can be null for unanswered)
            'time_taken_ms' => 'required|integer|min:0', // Time taken on the client side
        ]);

        $question = Question::find($request->question_id);
        $selectedOption = $request->selected_option;
        $timeTaken = $request->time_taken_ms;

        // Server-side time validation (basic, could be more robust)
        if ($timeTaken > (self::TIME_PER_QUESTION_SECONDS * 1000 + 5000)) { // Allow a small buffer (5s)
            // Optionally, mark as invalid attempt or don't count score for this question
            // For a commercial site, this is a cheating prevention measure.
            // For now, we'll proceed but log it or handle it.
            Session::flash('warning', 'Question submitted outside time limit.');
        }

        // Determine if the answer is correct
        $isCorrect = false;
        if ($selectedOption === $question->correct_answer) {
            $isCorrect = true;
        }

        // Update quiz attempt details
        $attemptDetails = $attempt_id->attempt_details;
        $attemptDetails[$question->id] = [
            'selected_option' => $selectedOption,
            'is_correct' => $isCorrect,
            'time_taken_ms' => $timeTaken,
            'correct_answer' => $question->correct_answer, // Store for review
            'explanation' => $question->explanation, // Store for review
            'source' => $question->source, // Store for review
            'question_text' => $question->question_text, // Store for review
            'options' => $question->options, // Store for review
        ];
        $attempt_id->attempt_details = $attemptDetails;

        // Update score
        if ($isCorrect) {
            $attempt_id->score++;
        }
        $attempt_id->save();

        // Move to the next question
        $questionIds = Session::get('current_quiz_questions_' . $attempt_id->id);
        $currentQuestionIndex = Session::get('current_quiz_question_index_' . $attempt_id->id);

        $nextQuestionIndex = $currentQuestionIndex + 1;
        Session::put('current_quiz_question_index_' . $attempt_id->id, $nextQuestionIndex);

        if ($nextQuestionIndex < $attempt_id->total_questions) {
            // Redirect to the next question
            return redirect()->route('quiz.show_question', [
                'attempt_id' => $attempt_id->id,
                'question_number' => $nextQuestionIndex + 1
            ]);
        } else {
            // Quiz completed
            $attempt_id->completed_at = Carbon::now();
            $attempt_id->save();

            Session::forget('current_quiz_questions_' . $attempt_id->id);
            Session::forget('current_quiz_question_index_' . $attempt_id->id);

            return redirect()->route('quiz.results', $attempt_id->id);
        }
    }

    /**
     * Display the quiz results.
     */
    public function showResults(QuizAttempt $attempt_id)
    {
        if ($attempt_id->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to quiz results.');
        }

        if (!$attempt_id->completed_at) {
            return redirect()->route('quiz.show_question', [
                'attempt_id' => $attempt_id->id,
                'question_number' => Session::get('current_quiz_question_index_' . $attempt_id->id, 0) + 1
            ])->with('warning', 'Quiz not yet completed. Please finish it first.');
        }

        $duration = null;
        if ($attempt_id->started_at && $attempt_id->completed_at) {
            $duration = $attempt_id->completed_at->diffInSeconds($attempt_id->started_at);
        }

        return view('user.quiz.results', compact('attempt_id', 'duration'));
    }

    /**
     * Allow user to review the quiz with correct answers and explanations.
     */
    public function reviewQuiz(QuizAttempt $attempt_id)
    {
        if ($attempt_id->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to quiz review.');
        }

        if (!$attempt_id->completed_at) {
            return redirect()->route('dashboard')->with('warning', 'This quiz is not yet completed.');
        }

        // The attempt_details already contain all necessary info for review
        $reviewedQuestions = [];
        foreach ($attempt_id->attempt_details as $question_id => $details) {
            // Ensure options are an array, as they are stored as JSON
            $details['options'] = is_array($details['options']) ? $details['options'] : json_decode($details['options'], true);
            $reviewedQuestions[] = $details;
        }

        return view('user.quiz.review', compact('attempt_id', 'reviewedQuestions'));
    }
}