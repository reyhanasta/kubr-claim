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
            'sepFile' => ['required', 'file', 'mimes:pdf', 'max:2048'],
            'sepRJFile' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
            'resumeFile' => ['required', 'file', 'mimes:pdf', 'max:2048'],
            'billingFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'labResultFile' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
            'labResultFile2' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
            'fileLIP' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
            'medical_record_number' => ['required', 'string', 'max:50'],
            'patient_name' => ['required', 'string', 'max:100'],
            'sep_number' => ['required', 'string', 'max:50', 'unique:bpjs_claims,no_sep'],
            'bpjs_serial_number' => ['required', 'string', 'max:20'],
            'sep_date' => ['required', 'date'],
            'patient_class' => ['required', 'string', 'in:1,2,3'],
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
            'sepFile.required' => 'File SEP wajib diunggah',
            'sepFile.mimes' => 'File SEP harus berformat PDF maksimal 2MB',
            'sepFile.max' => 'File SEP maksimal 2MB',
            'resumeFile.required' => 'File Resume Medis wajib diunggah',
            'resumeFile.mimes' => 'File Resume Medis harus berformat PDF maksimal 2MB',
            'resumeFile.max' => 'File Resume Medis maksimal 2MB',
            'billingFile.required' => 'File Billing wajib diunggah',
            'billingFile.mimes' => 'File Billing harus berformat PDF/JPG/PNG maksimal 2MB',
            'billingFile.max' => 'File Billing maksimal 2MB',
            'fileLIP.mimes' => 'File LIP harus berformat PDF maksimal 2MB',
            'fileLIP.max' => 'File LIP maksimal 2MB',
            'sepRJFile.mimes' => 'File SEP RJ harus berformat PDF maksimal 2MB',
            'sepRJFile.max' => 'File SEP RJ maksimal 2MB',
            'labResultFile.mimes' => 'File Hasil Labor harus berformat PDF maksimal 2MB',
            'labResultFile.max' => 'File Hasil Labor maksimal 2MB',
            'labResultFile2.mimes' => 'File Hasil Labor harus berformat PDF maksimal 2MB',
            'labResultFile2.max' => 'File Hasil Labor maksimal 2MB',
            'sep_number.required' => 'Nomor SEP wajib diisi',
            'sep_number.unique' => 'Nomor SEP sudah terdaftar',
            'sep_date.required' => 'Tanggal SEP wajib diisi',
            'sep_date.date' => 'Tanggal SEP harus berupa format tanggal yang valid',
            'medical_record_number.required' => 'Nomor RM wajib diisi',
            'patient_name.required' => 'Nama pasien wajib diisi',
            'bpjs_serial_number.required' => 'Nomor kartu BPJS wajib diisi',
            'patient_class.required' => 'Kelas rawatan wajib diisi',
            'patient_class.in' => 'Kelas rawatan harus berupa 1, 2, atau 3',
        ];
    }
}
