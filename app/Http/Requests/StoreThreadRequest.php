<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:1'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['integer', 'exists:users,id', 'different:' . auth('api')->id()],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'El asunto es obligatorio',
            'subject.max' => 'El asunto no puede exceder 255 caracteres',
            'body.required' => 'El mensaje es obligatorio',
            'body.min' => 'El mensaje no puede estar vacío',
            'participants.required' => 'Debe agregar al menos un participante',
            'participants.min' => 'Debe agregar al menos un participante',
            'participants.*.exists' => 'Uno o más participantes no existen',
            'participants.*.different' => 'No puedes agregarte a ti mismo como participante',
        ];
    }
}
