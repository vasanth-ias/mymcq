<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Upload Questions (JSON)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Whoops!</strong>
                            <span class="block sm:inline">There were some problems with your upload:</span>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('upload_errors'))
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Partial Success / Errors:</strong>
                            <span class="block sm:inline">Some questions could not be processed:</span>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach (session('upload_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <form method="POST" action="{{ route('admin.questions.upload') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="json_file" :value="__('Select JSON File')" />
                            <input id="json_file" name="json_file" type="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept=".json" required />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload a JSON file containing an array of MCQs.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('json_file')" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Upload Questions') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                        <h4 class="font-semibold text-lg mb-2">Expected JSON Format:</h4>
                        <pre class="whitespace-pre-wrap text-sm bg-gray-100 dark:bg-gray-800 p-3 rounded overflow-auto">
[
    {
        "question": "Consider the following statements:\n1.\tIt was during the reign of Iltutmish that Chengiz Khan reached the Indus in pursuit of the fugitive Khwarazm prince.\n2.\tIt was during the reign of Muhammad bin Tughluq that Taimur occupied Multan and crossed the Indus.\n3.\tIt was during the reign of Deva Raya II of Vijayanagara Empire that Vasco da Gama reached the coast of Kerala.\nWhich of the statements given above is/are correct?",
        "options": {
            "A": "1 only",
            "B": "1 and 2",
            "C": "3 only",
            "D": "2 and 3"
        },
        "correct_answer": "A",
        "explanation": "Statement 1 is correct. Chengiz Khan did reach the Indus during Iltutmish's reign. Statement 2 is incorrect; Taimur invaded during the reign of Nasir-ud-din Mahmud Shah Tughlaq. Statement 3 is incorrect; Vasco da Gama reached during the reign of Narasimha Raya II (Saluva Dynasty), not Deva Raya II.",
        "source": "UPSC Prelims 2021, Paper 1, Q.5"
    },
    {
        "question": "Consider the following events in the history of the Maratha Empire:\n1.\tCoronation of Chhatrapati Shivaji Maharaj at Raigad Fort.\n2.\tThe Battle of Pratapgad against Afzal Khan.\n3.\tChhatrapati Shivaji Maharaj captures Gingee Fort.\n4.\tSigning of the Treaty of Purandar with Jai Singh I.\nArrange the above events in the correct chronological order.",
        "options": {
            "A": "2-4-1-3",
            "B": "4-2-1-3",
            "C": "2-1-4-3",
            "D": "4-1-2-3"
        },
        "correct_answer": "A",
        "explanation": "The correct order is: Battle of Pratapgad (1659), Treaty of Purandar (1665), Coronation at Raigad (1674), and Capture of Gingee Fort (1677).",
        "source": "UPSC Prelims 2021, Paper 1, Q.5"
    }
]
                        </pre>
                        <p class="mt-2 text-red-500 dark:text-red-300">
                            <strong>IMPORTANT:</strong> Ensure your JSON `options` are in the format `{"A": "Option text", "B": "Option text"}`.
                            If your original JSON has `["A) Option text", "B) Option text"]`, the system will attempt to parse it, but the associative array format is preferred for direct upload.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>