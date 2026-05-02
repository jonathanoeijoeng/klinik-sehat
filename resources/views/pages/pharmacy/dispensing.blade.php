<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SyncMedicationDispenseToSatuSehat;
use App\Jobs\FinalizeVisitJob;
use App\Models\Invoice;
use App\Models\OutpatientVisit;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public $visit;
    public $showConfirmModal = false;
    public $message = '';
    public $currentRoute;
    public $patient_name,
        $patient_phone,
        $doctor_name,
        $medicines = [];

    public function mount()
    {
        // Simpan nama route saat halaman pertama kali dibuka
        $this->currentRoute = request()->route()->getName();
    }

    public function confirmDispense($visitId)
    {
        $this->visit = OutpatientVisit::with('patient')->findOrFail($visitId);
        $this->patient_name = $this->visit->patient->name;
        $this->showConfirmModal = true;
        $this->message = "Apakah Anda yakin ingin memproses penyerahan obat untuk pasien <b>{$this->patient_name}</b>?";
    }

    public function processDispense()
    {
        $this->showConfirmModal = false;
        $this->sendMedicationDispense($this->visit->id);
    }

    public function sendMedicationDispense($visitId)
    {
        $this->visit = OutpatientVisit::with('prescriptions.medicine')->findOrFail($visitId);
        Bus::chain([new SyncMedicationDispenseToSatuSehat($this->visit), new FinalizeVisitJob($this->visit)])->dispatch();

        $this->visit->prescriptions()->update([
            'status' => 'dispensed',
            'dispensed_at' => now(),
        ]);

        $this->visit->update([
            'internal_status' => 'finished',
            'dispensed_at' => now(),
        ]);

        $this->dispatch('toast', text: 'Obat berhasil disinkronkan ke SatuSehat.', type: 'success');
    }

    public function render()
    {
        $pharmacies = OutpatientVisit::has('prescriptions') // Hanya ambil yang ada resepnya
            ->with(['patient', 'prescriptions.medicine'])
            ->where('internal_status', 'paid')
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
    @include('pages.pharmacy.route')

    @foreach ($pharmacies as $visit)
        @php
            $statuses = $visit->prescriptions->pluck('status');

            // Konsistensi Border: Orange (Paid), Yellow (Processing), Emerald (Dispensed/Done)
            $statusBorder = 'border-l-gray-300';
            if ($statuses->contains('paid')) {
                $statusBorder = 'border-l-orange-500';
            } elseif ($statuses->contains('pharmacy_processing') || $statuses->contains('sent-for-payment')) {
                $statusBorder = 'border-l-yellow-400';
            } elseif ($statuses->isNotEmpty() && $statuses->every(fn($s) => $s === 'dispensed')) {
                $statusBorder = 'border-l-emerald-500';
            }
        @endphp

        <div class="card mb-6 border-l-8 {{ $statusBorder }} shadow-sm bg-white rounded-lg overflow-hidden">
            {{-- Header: Stacked di Mobile --}}
            <div
                class="card-header bg-slate-50 flex flex-col md:flex-row justify-between items-start md:items-center p-4 gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-800 text-lg">{{ $visit->patient->name }}</h3>
                        <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded uppercase font-bold">
                            {{ $visit->patient->record_number ?? 'No RM' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        <i class="fa-regular fa-calendar-check mr-1"></i>
                        Kunjungan: {{ $visit->arrived_at->format('d/m/Y H:i') }}
                    </p>
                </div>

                <div class="w-full md:w-auto">
                    @if ($visit->prescriptions->every('paid_at'))
                        <x-button wire:click="confirmDispense({{ $visit->id }})"
                            class="w-full md:w-auto text-sm py-2.5 justify-center shadow-sm" variant="green">
                            <i class="fa-solid fa-hand-holding-medical mr-2"></i> SERAHKAN OBAT
                        </x-button>
                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                {{-- DESKTOP VIEW: Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-100">
                            <tr>
                                <th class="px-4 py-3">Nama Obat</th>
                                <th class="w-32 px-4 py-3 text-center">Jumlah</th>
                                <th class="px-4 py-3">Aturan Pakai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($visit->prescriptions as $item)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-4 py-3 font-medium text-slate-700">
                                        {{ $item->medicine->name }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-900">
                                        {{ number_format($item->qty_ordered, 0, ',', ',') }} {{ $item->medicine->unit }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 italic text-xs">
                                        {{ $item->instruction }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE VIEW: Stacked List --}}
                <div class="block md:hidden divide-y divide-slate-100">
                    @foreach ($visit->prescriptions as $item)
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-bold text-slate-800">{{ $item->medicine->name }}</div>
                                <div class="shrink-0">
                                    <span
                                        class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-2 py-1 rounded text-xs font-black">
                                        {{ number_format($item->qty_ordered, 0, ',', ',') }}
                                        {{ $item->medicine->unit }}
                                    </span>
                                </div>
                            </div>
                            <div
                                class="text-xs text-slate-600 bg-amber-50/50 p-2 rounded-md border border-amber-100 italic">
                                <i class="fa-solid fa-bullhorn mr-1 text-amber-400"></i> {{ $item->instruction }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="mt-6">
        {{ $pharmacies->links() }}
    </div>

    {{-- Modal Konfirmasi --}}
    <x-confirm wire:model="showConfirmModal" title="Konfirmasi Penyerahan Obat" :message="$message"
        confirmText="Ya, Sudah diserahkan" cancelText="Batal" action="processDispense" />
</div>
