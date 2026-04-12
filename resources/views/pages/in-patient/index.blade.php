<?php

use Livewire\Component;
use App\Models\OutpatientVisit;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Location;
use App\Models\Practitioner;

new class extends Component {
    public $patient_id, $practitioner_id, $location_id;
    public $registration_fee = 0;

    public $showModal = false;

    public function openModal()
    {
        $this->reset(['patient_id', 'practitioner_id', 'location_id', 'registration_fee']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate([
            'patient_id' => 'required',
            'practitioner_id' => 'required',
            'location_id' => 'required',
        ]);
        // 1. Logic Generate Visit Number (KS-yymmdd-5char)
        $prefix = 'KS-' . now()->format('ymd') . '-';

        // Loop untuk memastikan tidak ada duplikasi di database
        do {
            $randomStr = strtoupper(Str::random(5));
            $visitNumber = $prefix . $randomStr;
        } while (OutpatientVisit::where('visit_number', $visitNumber)->exists());

        // 2. Simpan Kunjungan (Mulai TAT: arrived_at)
        $visit = OutpatientVisit::create([
            'visit_number' => $visitNumber,
            'patient_id' => $this->patient_id,
            'practitioner_id' => $this->practitioner_id,
            'location_id' => $this->location_id,
            'status' => 'waiting',
            'arrived_at' => now(),
        ]);

        // 3. Buat Invoice Awal (Gunakan Visit Number sebagai referensi)
        $visit->invoice()->create([
            'invoice_number' => 'INV-' . $visitNumber,
            'registration_fee' => $this->registration_fee,
            'grand_total' => $this->registration_fee,
            'payment_status' => 'unpaid',
        ]);

        // 4. Feedback & Reset
        $this->closeModal();

        // Opsional: Notifikasi sukses ala Flux/Filament
        // Flux::toast(variant: 'success', text: __('Pasien berhasil didaftarkan dengan nomor ' . $visitNumber));
    }

    public function render()
    {
        $patients = Patient::all();
        $locations = Location::all();
        $practitioners = Practitioner::all();

        return $this->view([
            'patients' => $patients,
            'locations' => $locations,
            'practitioners' => $practitioners,
        ]);
    }
};
?>

<div>
    <x-header header="Rawat Jalan" description="" />

    <x-button wire:click="openModal" class="mb-4" color="brand">Registrasi</x-button>

    <div x-data="{ open: @entangle('showModal') }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
        x-cloak>
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-xl w-full p-6 dark:bg-gray-800">
            <div class="mb-5">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Registrasi Rawat Jalan Baru</h3>
                <p class="text-sm text-gray-500">Input data kunjungan pasien baru</p>
            </div>

            <flux:separator />

            <div class="space-y-4 mt-4">
                <x-select wire:model="patient_id" name="patient_id" label="Nama Pasien">
                    <option value="">-- Pilih Pasien --</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                    @endforeach
                </x-select>

                <div class="grid grid-cols-2 gap-4">
                    <x-select wire:model="location_id" name="location_id" label="Nama Poliklinik">
                        <option value="">-- Pilih Poli --</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </x-select>

                    <x-select wire:model="practitioner_id" name="practitioner_id" label="Nama Dokter">
                        <option value="">-- Pilih Dokter --</option>
                        @foreach ($practitioners as $dr)
                            <option value="{{ $dr->id }}">{{ $dr->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-input wire:model="registration_fee" name="registration_fee" placeholder="Biaya Pendaftaran..."
                        label="Biaya Pendaftaran" />
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    Batal
                </button>
                <button wire:click="save"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Daftarkan Pasien
                </button>
            </div>
        </div>
    </div>

</div>
