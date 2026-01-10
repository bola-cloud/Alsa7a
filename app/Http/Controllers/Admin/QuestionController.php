<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Category;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Question::with('category')->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Assuming translatable question field or just 'question' column
                $q->where('question', 'like', "%{$search}%");
            });
        }

        $questions = $query->paginate(20)->withQueryString();
        $categories = Category::all();

        return view('admin.questions.index', compact('questions', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.questions.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question_en' => 'required|string',
            'question_ar' => 'required|string',
            'type' => 'required|in:text,boolean,rating,multiple_choice',
            'category_id' => 'required|exists:categories,id',
            'choices' => 'nullable|string', // Validated as json if needed, but simple string is easier for now
        ]);

        $data = $request->only(['type', 'category_id']);
        $data['question'] = [
            'en' => $request->question_en,
            'ar' => $request->question_ar
        ];

        if ($request->filled('choices')) {
            // Try to decode to ensure valid JSON or store as is if field expects array cast
            // Assuming model casts choices to array, we should pass an array or valid JSON string.
            // If input is a raw JSON string from textarea:
            $choices = json_decode($request->choices, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['choices'] = $choices;
            }
        }

        Question::create($data);

        return redirect()->route('admin.questions.index')->with('swal_success', __('admin.messages.created_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Question $question)
    {
        $categories = Category::all();
        return view('admin.questions.edit', compact('question', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question_en' => 'required|string',
            'question_ar' => 'required|string',
            'type' => 'required|in:text,boolean,rating,multiple_choice',
            'category_id' => 'required|exists:categories,id',
            'choices' => 'nullable|string',
        ]);

        $data = $request->only(['type', 'category_id']);
        $data['question'] = [
            'en' => $request->question_en,
            'ar' => $request->question_ar
        ];

        if ($request->filled('choices')) {
            $choices = json_decode($request->choices, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['choices'] = $choices;
            }
        } else {
            $data['choices'] = null;
        }

        // We use query 'update' or model 'update'? Model update works with casts.
        // However, Spatie Translatable trait usually hooks into setAttribute.
        // We need to verify if $question->update($data) works with the array for 'question'.
        // It should if $fillable and casts are set up.

        $question->question = $data['question']; // Explicitly set translation
        $question->type = $data['type'];
        $question->category_id = $data['category_id'];
        $question->choices = $data['choices'] ?? null;
        $question->save();

        return redirect()->route('admin.questions.index')->with('swal_success', __('admin.messages.updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->back()->with('swal_success', __('admin.messages.deleted_successfully'));
    }
}
