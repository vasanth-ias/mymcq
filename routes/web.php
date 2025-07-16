<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController; 
use App\Http\Controllers\QuizController; 


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // <--- Change this line
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
});
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/questions/upload', [AdminController::class, 'showUploadForm'])->name('questions.upload.form');
    Route::post('/questions/upload', [AdminController::class, 'uploadQuestions'])->name('questions.upload');
    Route::get('/questions', [AdminController::class, 'listQuestions'])->name('questions.list');
    // Add more admin routes here (e.g., edit, delete questions, user management)
});

Route::middleware(['auth'])->group(function () {
    // ... other authenticated routes like dashboard

    Route::get('/quiz/start', [QuizController::class, 'startQuiz'])->name('quiz.start');
    Route::get('/quiz/{attempt_id}/question/{question_number}', [QuizController::class, 'showQuestion'])->name('quiz.show_question');
    Route::post('/quiz/{attempt_id}/submit-answer', [QuizController::class, 'submitAnswer'])->name('quiz.submit_answer');
    Route::get('/quiz/{attempt_id}/results', [QuizController::class, 'showResults'])->name('quiz.results');
    Route::get('/quiz/{attempt_id}/review', [QuizController::class, 'reviewQuiz'])->name('quiz.review'); // For reviewing all questions after completion
});

require __DIR__.'/auth.php';
