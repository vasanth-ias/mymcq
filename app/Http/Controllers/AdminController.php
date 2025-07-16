<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function showUploadForm()
    {
        return view('admin.questions.upload');
    }

    public function uploadQuestions(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file|mimes:json|max:5000', // Max 5MB JSON file
        ]);

        $file = $request->file('json_file');
        $jsonData = json_decode($file->getContents(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['json_file' => 'Invalid JSON file format.'])->withInput();
        }

        // Assume the JSON is an array of question objects
        if (!is_array($jsonData)) {
            return back()->withErrors(['json_file' => 'JSON file must contain an array of questions.'])->withInput();
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($jsonData as $index => $questionData) {
            // Validate each question's structure
            $validator = Validator::make($questionData, [
                'question' => 'required|string',
                'options' => 'required|array|min:2', // At least two options
                'options.*' => 'required|string', // Each option must be a string
                'correct_answer' => 'required|string',
                'explanation' => 'nullable|string',
                'source' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $errorCount++;
                $errors[] = "Question " . ($index + 1) . ": " . $validator->errors()->first();
                continue;
            }

            // Check if options are an associative array for storing as JSON
            // The prompt example shows "A) Option 1", "B) Option 2" as an array of strings.
            // We need to convert this into an associative array for our `options` column.
            $formattedOptions = [];
            foreach ($questionData['options'] as $optionString) {
                // Try to parse "A) Option text" into ["A" => "Option text"]
                preg_match('/^([A-Z])\)\s*(.*)$/', $optionString, $matches);
                if (count($matches) === 3) {
                    $formattedOptions[$matches[1]] = trim($matches[2]);
                } else {
                    // Fallback if format is not "A) text", just use a sequential key
                    // Or handle this error specifically. For now, just add as is.
                    $formattedOptions[] = $optionString;
                }
            }

            try {
                Question::create([
                    'question_text' => $questionData['question'],
                    'options' => json_encode($formattedOptions), // Store as JSON string
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => $questionData['explanation'] ?? null,
                    'source' => $questionData['source'] ?? null,
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Question " . ($index + 1) . ": Database save error - " . $e->getMessage();
            }
        }

        $message = "$successCount questions uploaded successfully.";
        if ($errorCount > 0) {
            $message .= " $errorCount questions failed to upload. See errors below.";
        }

        return redirect()->route('admin.questions.list')->with('success', $message)->with('upload_errors', $errors);
    }

    public function listQuestions()
    {
        $questions = Question::orderBy('created_at', 'desc')->paginate(10); // Paginate for large datasets
        return view('admin.questions.list', compact('questions'));
    }
}