<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tasks' => 'required|array|min:1',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.description' => 'required|string',
            'tasks.*.assigned_to' => 'nullable|exists:users,id',
            'tasks.*.priority' => 'required|string|in:low,medium,high',
            'tasks.*.team_id' => 'required|exists:teams,id',
            'tasks.*.start_time' => 'nullable|date',
            'tasks.*.end_time' => 'required|date|after_or_equal:tasks.*.start_time'
        ];
    }
}
