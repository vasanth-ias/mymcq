<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Quiz Review') }} (Score: {{ $attempt_id->score }} / {{ $attempt_id->total_questions }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-6">Detailed Review of Questions</h3>

                    @foreach ($reviewedQuestions as $index => $details)
                        <div class="mb-8 p-6 border rounded-lg shadow-sm {{ $details['is_correct'] ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : 'border-red-300 bg-red-50 dark:bg-red-900/20' }}">
                            <h4 class="text-xl font-semibold mb-3">Question {{ $index + 1 }}:</h4>
                            <p class="text-lg mb-4">{{ nl2br($details['question_text']) }}</p>

                            <div class="space-y-2 mb-4">
                                @foreach ($details['options'] as $key => $value)
                                    <div class="flex items-center text-base p-2 rounded-md
                                        @if ($key == $details['correct_answer'])
                                            bg-green-200 dark:bg-green-700 text-green-800 dark:text-green-200 font-bold
                                        @elseif ($key == $details['selected_option'] && !$details['is_correct'])
                                            bg-red-200 dark:bg-red-700 text-red-800 dark:text-red-200 font-bold
                                        @else
                                            text-gray-700 dark:text-gray-300
                                        @endif
                                    ">
                                        <span class="font-medium mr-2">{{ $key }})</span> {{ $value }}
                                        @if ($key == $details['selected_option'])
                                            <span class="ml-auto text-sm {{ $details['is_correct'] ? 'text-green-700 dark:text-green-200' : 'text-red-700 dark:text-red-200' }}">
                                                (Your Answer)
                                            </span>
                                        @endif
                                        @if ($key == $details['correct_answer'] && $key != $details['selected_option'])
                                            <span class="ml-auto text-sm text-green-700 dark:text-green-200">
                                                (Correct Answer)
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($details['explanation'])
                                <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-700 border-l-4 border-blue-500 dark:border-blue-400 rounded-r-md">
                                    <h5 class="font-semibold text-blue-700 dark:text-blue-200 mb-1">Explanation:</h5>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $details['explanation'] }}</p>
                                </div>
                            @endif

                            @if ($details['source'])
                                <p class="text-right text-sm text-gray-500 dark:text-gray-400 mt-2">
                                    Source: {{ $details['source'] }}
                                </p>
                            @endif
                        </div>
                    @endforeach

                    <div class="flex justify-center mt-8">
                        <a href="{{ route('quiz.start') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            Start Another Quiz
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>