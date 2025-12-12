<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $thread = $this->route('thread');
        $user = $this->user();

        if (!$thread || !$user) {
            return false;
        }

        return $thread->participants->contains($user);
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'El mensaje es obligatorio',
            'body.min' => 'El mensaje no puede estar vacío',
        ];
    }
}
