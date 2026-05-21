<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'case_id'               => 'required|exists:cases,id',
            'title'                 => 'required|string|max:255',
            'file'                  => 'required|file|max:20480', // 20MB max
            'category'              => 'required|in:pleading,evidence,contract,court_order,correspondence,invoice,requested,other',
            'is_visible_to_client'  => 'boolean',
            'notes'                 => 'nullable|string',
            'parent_document_id'    => 'nullable|exists:documents,id',
        ];
    }
}