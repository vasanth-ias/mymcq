<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Welcome, Admin!</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Card for Question Upload -->
                        <a href="{{ route('admin.questions.upload.form') }}" class="block p-6 bg-blue-500 hover:bg-blue-600 rounded-lg shadow-md text-white font-bold text-center">
                            Upload Questions
                        </a>

                        <!-- Card for Listing Questions -->
                        <a href="{{ route('admin.questions.list') }}" class="block p-6 bg-green-500 hover:bg-green-600 rounded-lg shadow-md text-white font-bold text-center">
                            View All Questions
                        </a>

                        <!-- Placeholder for other admin functionalities -->
                        <div class="block p-6 bg-gray-400 rounded-lg shadow-md text-gray-800 text-center">
                            User Management (Coming Soon)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>