<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Quiz Time!') }} (Question {{ $question_number }} of {{ $total_questions }})
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if (session('warning'))
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Heads Up!</strong>
                            <span class="block sm:inline">{{ session('warning') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold">Question {{ $question_number }}</h3>
                        <div id="quiz-timer" class="text-2xl font-bold text-red-500 dark:text-red-400">
                            Loading Timer...
                        </div>
                    </div>

                    <div class="prose dark:prose-invert max-w-none mb-6">
                        <p class="text-lg">{{ nl2br($question->question_text) }}</p>
                    </div>

                    <form id="quiz-form" method="POST" action="{{ route('quiz.submit_answer', $attempt->id) }}">
                        @csrf
                        <input type="hidden" name="question_id" value="{{ $question->id }}">
                        <input type="hidden" name="time_taken_ms" id="time_taken_ms" value="0"> {{-- Hidden field for time --}}

                        <div class="space-y-4">
                            @foreach ($question->options as $key => $value)
                                <label for="option_{{ $key }}" class="flex items-center p-4 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 ease-in-out">
                                    <input type="radio" id="option_{{ $key }}" name="selected_option" value="{{ $key }}" class="form-radio h-5 w-5 text-indigo-600 dark:text-indigo-400 transition duration-150 ease-in-out">
                                    <span class="ml-3 text-lg font-medium">{{ $key }}) {{ $value }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <x-primary-button id="submit-answer-btn">
                                {{ __('Next Question') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timerDisplay = document.getElementById('quiz-timer');
            const timeTakenInput = document.getElementById('time_taken_ms');
            const quizForm = document.getElementById('quiz-form');
            const submitButton = document.getElementById('submit-answer-btn');

            let timeLeft = {{ $time_limit_ms / 1000 }}; // Initial time in seconds
            const totalTime = timeLeft; // Store total time for calculations
            let interval;
            let startTime = Date.now();

            function updateTimer() {
                const now = Date.now();
                const elapsedMs = now - startTime;
                const remainingMs = {{ $time_limit_ms }} - elapsedMs;

                if (remainingMs <= 0) {
                    clearInterval(interval);
                    timerDisplay.textContent = "Time's Up!";
                    submitButton.disabled = true; // Disable button to prevent manual submission
                    quizForm.submit(); // Automatically submit the form
                    return;
                }

                timeLeft = Math.max(0, Math.floor(remainingMs / 1000)); // Update timeLeft for display
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            interval = setInterval(updateTimer, 1000); // Update every second
            updateTimer(); // Initial call to display immediately

            // When the form is submitted (either by user or timeout)
            quizForm.addEventListener('submit', function(event) {
                clearInterval(interval); // Stop the timer
                timeTakenInput.value = Date.now() - startTime; // Calculate actual time taken
                // Optionally, add a check if no option selected
                const selectedOption = quizForm.elements['selected_option'].value;
                if (!selectedOption) {
                    // event.preventDefault(); // Prevent submission if not selected and you want to force selection
                    // alert('Please select an option.');
                    // For now, allow submission without selection (unanswered)
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
```*   **Timer Logic (JavaScript in `@push('scripts')`):**
    *   A client-side JavaScript timer displays the countdown.
    *   `time_limit_ms` is passed from the controller (72 seconds = 72000 ms).
    *   When time runs out, the form is automatically submitted.
    *   A hidden input `time_taken_ms` records the actual time taken by the user (from opening the page to submission). This is sent to the server for a basic validation against cheating.
*   **Form Structure:** Uses radio buttons for options. The `name="selected_option"` ensures only one can be chosen.
*   `nl2br($question->question_text)`: Converts newlines in the question text to `<br>` tags for proper display.

**4.2. `resources/views/user/quiz/results.blade.php`**

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Quiz Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                    <h3 class="text-3xl font-bold mb-4">Quiz Completed!</h3>

                    <p class="text-lg mb-2">You scored:</p>
                    <p class="text-5xl font-extrabold text-indigo-600 dark:text-indigo-400 mb-6">
                        {{ $attempt_id->score }} / {{ $attempt_id->total_questions }}
                    </p>

                    @php
                        $percentage = ($attempt_id->score / $attempt_id->total_questions) * 100;
                    @endphp
                    <p class="text-2xl font-semibold mb-6">
                        Percentage: {{ number_format($percentage, 2) }}%
                    </p>

                    @if ($duration)
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                            Time Taken: {{ gmdate("H:i:s", $duration) }}
                        </p>
                    @endif

                    <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8">
                        <a href="{{ route('quiz.review', $attempt_id->id) }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Review Answers
                        </a>
                        <a href="{{ route('quiz.start') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            Start New Quiz
                        </a>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>