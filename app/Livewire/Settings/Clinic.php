<?php

namespace App\Livewire\Settings;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Clinic extends Component
{
    public string $clinic_name = '';

    public string $clinic_code = '';

    public string $clinic_address = '';

    public string $clinic_phone = '';

    public function mount(): void
    {
        // Only admin can access
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $settings = AppSetting::getByGroup('clinic');

        $this->clinic_name = $settings['clinic_name'] ?? '';
        $this->clinic_code = $settings['clinic_code'] ?? '';
        $this->clinic_address = $settings['clinic_address'] ?? '';
        $this->clinic_phone = $settings['clinic_phone'] ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_code' => ['nullable', 'string', 'max:50'],
            'clinic_address' => ['nullable', 'string', 'max:500'],
            'clinic_phone' => ['nullable', 'string', 'max:20'],
        ]);

        AppSetting::set('clinic_name', $this->clinic_name);
        AppSetting::set('clinic_code', $this->clinic_code);
        AppSetting::set('clinic_address', $this->clinic_address);
        AppSetting::set('clinic_phone', $this->clinic_phone);

        $this->dispatch('settings-saved');
    }

    public function render()
    {
        return view('livewire.settings.clinic');
    }
}
