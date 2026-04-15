<?php

use Livewire\Component;
use App\Models\Prescription;
use App\Models\OutpatientVisit;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function processAll($visitId)
    {
        $visit = OutpatientVisit::with('prescriptions')->findOrFail($visitId);

        foreach ($visit->prescriptions as $prescription) {
            $prescription->update([
                'status' => 'preparing',
                'started_at' => now(),
            ]);
        }

        $this->dispatch('toast', text: 'Semua resep mulai diproses', type: 'info');
    }

    public function sendMedicationDispense($prescriptionId)
    {
        $prescription = Prescription::findOrFail($prescriptionId);

        // Validasi stok obat
        if ($prescription->medicine->stock < $prescription->quantity) {
            $this->dispatch('toast', text: 'Stok obat tidak mencukupi!', type: 'error');
            return;
        }

        // Update status menjadi dispensed dan kurangi stok
        $prescription->update([
            'status' => 'dispensed',
            'handed_over_at' => now(),
        ]);

        $prescription->medicine->decrement('stock', $prescription->quantity);

        // Kirim data ke SatuSehat
        app(SatuSehatService::class)->sendMedicationDispense($prescription);

        $this->dispatch('toast', text: 'Obat berhasil disinkronkan ke SatuSehat.', type: 'success');
    }

    public function render()
    {
        $pharmacies = OutpatientVisit::has('prescriptions') // Hanya ambil yang ada resepnya
            ->with(['patient', 'prescriptions.medicine'])
            ->when($this->search, function ($query) {
                $query->whereHas('patient', function ($q) {
                    $q->where('name', 'ilike', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(25);

        return $this->view(['pharmacies' => $pharmacies]);
    }
};
?>

<div>
    <x-header header="Farmasi"
        description="Modul pengelolaan resep masuk, validasi stok obat, dan finalisasi penyerahan obat kepada pasien. Terintegrasi langsung dengan SatuSehat untuk pelaporan MedicationDispense secara real-time." />

    <x-input wire:model.live.debounce.100ms="search" name="search" placeholder="Cari pasien..." type="search"
        class="mb-4 md:max-w-lg w-full" />

    @foreach ($pharmacies as $visit)
        @php
            $statuses = $visit->prescriptions->pluck('status');
            $statusBorder = 'border-l-gray-300';

            if ($statuses->contains('pending')) {
                $statusBorder = 'border-l-orange-500';
            } elseif ($statuses->contains('preparing') || $statuses->contains('ready')) {
                $statusBorder = 'border-l-yellow-400';
            } elseif ($statuses->isNotEmpty() && $statuses->every(fn($s) => $s === 'dispensed')) {
                $statusBorder = 'border-l-emerald-500';
            }
        @endphp
        <div class="card mb-4 border-l-8 {{ $statusBorder }} shadow-sm">
            <div class="card-header bg-slate-50 flex justify-between items-center p-4">
                <div>
                    <h3 class="font-bold text-slate-800">{{ $visit->patient->name }}</h3>
                    <p class="text-xs text-slate-500">Kunjungan: {{ $visit->arrived_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center">
                    <span
                        class="px-3 py-1 rounded-full text-xs font-semibold {{ $visit->prescriptions->every('started_at') && !$visit->prescriptions->every('handed_over_at') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $visit->prescriptions->every('started_at') && !$visit->prescriptions->every('handed_over_at') ? 'Sedang di proses' : 'Perlu Diproses' }}
                    </span>
                    @if ($visit->prescriptions->every('started_at') && !$visit->prescriptions->every('handed_over_at'))
                        <x-button wire:click="handOverAll({{ $visit->id }})" class="ml-4 text-sm" variant="green">
                            Serahkan ke Pasien
                        </x-button>
                    @else
                        <x-button wire:click="processAll({{ $visit->id }})" class="ml-4 text-sm" variant="orange">
                            Proses Semua Obat
                        </x-button>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-400 uppercase bg-slate-100 table-fixed">
                        <tr>
                            <th class="px-4 py-2">Nama Obat</th>
                            <th class="w-30 px-4 py-2 text-center">Jumlah</th>
                            <th class="w-60 px-4 py-2">Aturan Pakai</th>
                            <th class="w-48 px-4 py-2 text-right">Status Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop Kedua: Mengambil obat dari relasi prescriptions --}}
                        @foreach ($visit->prescriptions as $item)
                            <tr class="border-b">
                                <td class="px-4 py-3 font-medium">{{ $item->medicine->name }}</td>
                                <td class="px-4 py-3 text-center">{{ $item->quantity }} {{ $item->medicine->unit }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 italic">{{ $item->instruction }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($item->handed_over_at)
                                        <span class="text-emerald-600">✔ Diserahkan</span>
                                    @else
                                        <button wire:click="processDispense({{ $item->id }})"
                                            class="text-blue-600 hover:underline">
                                            Proses
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    {{-- Pagination Links --}}
    <div class="mt-4">
        {{ $pharmacies->links() }}
    </div>
</div>
