<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Informasi Klinik')" :subheading="__('Kelola informasi dasar klinik/faskes')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <flux:input wire:model="clinic_name" :label="__('Nama Klinik/Faskes')" type="text" required
                placeholder="Contoh: Klinik Utama Bunda Restu" />

            <flux:input wire:model="clinic_code" :label="__('Kode BPJS Faskes')" type="text"
                placeholder="Contoh: 0069S001" />

            <flux:textarea wire:model="clinic_address" :label="__('Alamat')" rows="3"
                placeholder="Alamat lengkap klinik" />

            <flux:input wire:model="clinic_phone" :label="__('Nomor Telepon')" type="text"
                placeholder="Contoh: 021-12345678" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full sm:w-auto">
                    <flux:icon.check class="size-4 mr-2" />
                    {{ __('Simpan') }}
                </flux:button>

                <x-action-message class="me-3" on="settings-saved">
                    {{ __('Tersimpan.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>