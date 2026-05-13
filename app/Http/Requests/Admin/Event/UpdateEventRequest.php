<?php

namespace App\Http\Requests\Admin\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.ar' => 'required|string|max:255',
            'description' => 'nullable|array',
            'sport_id' => 'nullable|exists:sports,id',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'venue' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'nullable|string|in:pending,approved,rejected',
        ];
    }
}
