<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Display session flash messages --}}
                    @if (session('status'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('status') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Warning!</strong>
                            <span class="block sm:inline">{{ session('warning') }}</span>
                        </div>
                    @endif

                    <h3 class="text-2xl font-bold mb-4">Welcome, {{ Auth::user()->name }}!</h3>

                    <div class="mt-6 text-center">
                        <a href="{{ route('quiz.start') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Start a Practice Quiz!
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10"> {{-- Changed to 2 columns --}}

                        <!-- Monthly Top Scorers Section -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-md">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 text-center">🗓️ Monthly Top Scorers 🗓️</h4>
                            @if ($monthlyToppers->isEmpty())
                                <p class="text-gray-600 dark:text-gray-300 text-center text-sm">No monthly toppers yet. Complete quizzes this month!</p>
                            @else
                                <ol class="list-decimal list-inside space-y-3">
                                    @foreach ($monthlyToppers as $index => $topper)
                                        <li class="flex items-center p-2 rounded-md {{ $index === 0 ? 'bg-blue-100 dark:bg-blue-900/30 font-semibold' : '' }}">
                                            <div class="w-10 h-10 overflow-hidden rounded-full mr-3 border-2 {{ $index === 0 ? 'border-blue-500' : 'border-gray-300 dark:border-gray-600' }}">
                                                @if ($topper->user->profile_picture)
                                                    <img src="{{ asset('storage/' . $topper->user->profile_picture) }}" alt="{{ $topper->user->name }}" class="object-cover w-full h-full">
                                                @else
                                                    <img src="{{ asset('images/default_avatar.png') }}" alt="Default Avatar" class="object-cover w-full h-full">
                                                @endif
                                            </div>
                                            <span class="flex-grow text-lg">
                                                {{ $topper->user->name }}
                                            </span>
                                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                                {{ $topper->total_monthly_score }} pts
                                            </span>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        </div>

                        <!-- "Daily Topper" - Most Recent Highest Score Quiz -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-md">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 text-center">🌟 Latest Top Quiz Score 🌟</h4>
                            @if ($mostRecentHighestScoreQuiz)
                                <div class="flex flex-col items-center justify-center p-2 rounded-md bg-green-100 dark:bg-green-900/30 font-semibold text-center">
                                    <div class="w-16 h-16 overflow-hidden rounded-full mb-3 border-4 border-green-500">
                                        @if ($mostRecentHighestScoreQuiz->user->profile_picture)
                                            <img src="{{ asset('storage/' . $mostRecentHighestScoreQuiz->user->profile_picture) }}" alt="{{ $mostRecentHighestScoreQuiz->user->name }}" class="object-cover w-full h-full">
                                        @else
                                            <img src="{{ asset('images/default_avatar.png') }}" alt="Default Avatar" class="object-cover w-full h-full">
                                        @endif
                                    </div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $mostRecentHighestScoreQuiz->user->name }}</p>
                                    <p class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                                        {{ $mostRecentHighestScoreQuiz->score }} / {{ $mostRecentHighestScoreQuiz->total_questions }} pts
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Completed: {{ $mostRecentHighestScoreQuiz->completed_at->format('M d, Y H:i A') }}
                                    </p>
                                </div>
                            @else
                                <p class="text-gray-600 dark:text-gray-300 text-center text-sm">No quizzes completed yet. Be the first to appear here!</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>