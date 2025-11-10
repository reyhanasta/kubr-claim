<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBpjsClaimRequest extends FormRequest
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
            'no_rm' => ['required', 'string', 'max:50'],
            'tanggal_rawatan' => ['required', 'date'],
            'jenis_rawatan' => ['required', 'string', 'in:RJ,RI'],
            'no_sep' => ['required', 'string', 'max:100'],
            'scanned_docs' => ['required', 'array', 'min:1'],
            'scanned_docs.*' => ['required', 'file', 'mimes:pdf,jpg,png,jpeg', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'no_rm.required' => 'Nomor Rekam Medis wajib diisi.',
            'no_rm.max' => 'Nomor Rekam Medis maksimal 50 karakter.',
            'tanggal_rawatan.required' => 'Tanggal rawatan wajib diisi.',
            'tanggal_rawatan.date' => 'Tanggal rawatan harus berupa tanggal yang valid.',
            'jenis_rawatan.required' => 'Jenis rawatan wajib dipilih.',
            'jenis_rawatan.in' => 'Jenis rawatan harus RJ atau RI.',
            'no_sep.required' => 'Nomor SEP wajib diisi.',
            'no_sep.max' => 'Nomor SEP maksimal 100 karakter.',
            'scanned_docs.required' => 'Minimal satu file harus diunggah.',
            'scanned_docs.min' => 'Minimal satu file harus diunggah.',
            'scanned_docs.*.required' => 'File tidak boleh kosong.',
            'scanned_docs.*.file' => 'File harus berupa file yang valid.',
            'scanned_docs.*.mimes' => 'File harus berformat PDF, JPG, PNG, atau JPEG.',
            'scanned_docs.*.max' => 'Ukuran file maksimal 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'no_rm' => 'Nomor Rekam Medis',
            'tanggal_rawatan' => 'Tanggal Rawatan',
            'jenis_rawatan' => 'Jenis Rawatan',
            'no_sep' => 'Nomor SEP',
            'scanned_docs' => 'Dokumen Scan',
        ];
    }
}
