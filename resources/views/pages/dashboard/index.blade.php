<?php

use Livewire\Component;
use App\Models\OutpatientVisit;
use App\Models\Prescription;
use App\Models\OutPatientDiagnosis;

new class extends Component {
    public $stats = [];

    public function mount()
    {
        $this->refreshStats();
    }

    public function refreshStats()
    {
        $this->stats = [
            // Encounter
            'encounter_total' => OutpatientVisit::count(),
            'encounter_success' => OutpatientVisit::whereNotNull('satusehat_encounter_id')->count(),

            // Condition (Diagnosa)
            // Kita hitung dari tabel diagnosa/condition langsung
            'condition_total' => OutPatientDiagnosis::count(),
            'condition_success' => OutPatientDiagnosis::whereNotNull('satusehat_condition_id')->count(),

            // Medication Request (Resep)
            'prescription_total' => Prescription::count(),
            'prescription_success' => Prescription::whereNotNull('satusehat_medication_request_id')->count(),

            // Medication Dispense (Penyerahan Obat)
            'dispense_total' => Prescription::count(),
            'dispense_success' => Prescription::whereNotNull('satusehat_medication_dispense_id')->count(),
        ];
    }

    public function render()
    {
        $todayVisits = OutpatientVisit::with(['patient', 'invoice'])
            ->whereBetween('arrived_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->latest()
            ->get();
        // dd($todayVisits);

        // Hitung stats dari koleksi $todayVisits menggunakan method isSynced()
        $total = $todayVisits->count();
        $synced = $todayVisits->filter->isSynced()->count(); // Menggunakan higher order proxy
        $pending = $total - $synced;

        return $this->view([
            'todayVisits' => $todayVisits,
            'total' => $total,
            'synced' => $synced,
            'pending' => $pending,
        ]);
    }
};
?>

<div>
    <x-header header="Dashboard"
        description="Visualisasi real-time performa klinik, mulai dari volume kunjungan pasien, status antrean farmasi, hingga kesehatan integrasi API SatuSehat. Pantau data transaksi harian dan distribusi diagnosa penyakit secara akurat untuk mendukung pengambilan keputusan klinis dan operasional." />
    <div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-blue-100 p-4 rounded-lg shadow">
                <div class="text-blue-600 text-sm font-semibold">Total Pasien</div>
                <div class="text-3xl font-bold">{{ $total }}</div>
            </div>
            <div class="bg-green-100 p-4 rounded-lg shadow">
                <div class="text-green-600 text-sm font-semibold">Berhasil Sinkron SATUSEHAT</div>
                <div class="text-3xl font-bold">{{ $synced }}</div>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg shadow">
                <div class="text-yellow-600 text-sm font-semibold">Menunggu Antrean Job</div>
                <div class="text-3xl font-bold">{{ $pending }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            <div
                class="p-5 {{ $stats['encounter_total'] - $stats['encounter_success'] === 0 ? 'bg-orange-100' : 'bg-red-200' }} shadow rounded-xl border border-gray-100">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Status Encounter</h3>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-bold">
                        {{ number_format($stats['encounter_success']) }}/{{ number_format($stats['encounter_total']) }}
                    </span>
                </div>

                @php
                    $condPercent =
                        $stats['encounter_total'] > 0
                            ? ($stats['encounter_success'] / $stats['encounter_total']) * 100
                            : 0;
                @endphp

                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $condPercent }}%"></div>
                </div>

                <p class="mt-2 text-xs text-gray-400">
                    {{ number_format($stats['encounter_total'] - $stats['encounter_success']) }} encounter belum
                    tersinkron
                </p>
            </div>
            <div
                class="p-5 {{ $stats['condition_total'] - $stats['condition_success'] === 0 ? 'bg-pink-100' : 'bg-red-200' }} shadow rounded-xl border border-gray-100">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Status Condition</h3>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-bold">
                        {{ number_format($stats['condition_success']) }}/{{ number_format($stats['condition_total']) }}
                    </span>
                </div>

                @php
                    $condPercent =
                        $stats['condition_total'] > 0
                            ? ($stats['condition_success'] / $stats['condition_total']) * 100
                            : 0;
                @endphp

                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $condPercent }}%"></div>
                </div>

                <p class="mt-2 text-xs text-gray-400">
                    {{ number_format($stats['condition_total'] - $stats['condition_success']) }} diagnosa belum
                    tersinkron
                </p>
            </div>
            <div
                class="p-5 {{ $stats['prescription_total'] - $stats['prescription_success'] === 0 ? 'bg-green-100' : 'bg-red-200' }} shadow rounded-xl border border-gray-100">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Status Medication Request</h3>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-bold">
                        {{ number_format($stats['prescription_success']) }}/{{ number_format($stats['prescription_total']) }}
                    </span>
                </div>

                @php
                    $condPercent =
                        $stats['prescription_total'] > 0
                            ? ($stats['prescription_success'] / $stats['prescription_total']) * 100
                            : 0;
                @endphp

                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $condPercent }}%"></div>
                </div>

                <p class="mt-2 text-xs text-gray-400">
                    {{ number_format($stats['prescription_total'] - $stats['prescription_success']) }} medication
                    request belum
                    tersinkron
                </p>
            </div>
            <div
                class="p-5 {{ $stats['dispense_total'] - $stats['dispense_success'] === 0 ? 'bg-sky-100' : 'bg-red-200' }} shadow rounded-xl border border-gray-100">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Status Medication Dispense</h3>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-bold">
                        {{ number_format($stats['dispense_success']) }}/{{ number_format($stats['dispense_total']) }}
                    </span>
                </div>

                @php
                    $condPercent =
                        $stats['dispense_total'] > 0
                            ? ($stats['dispense_success'] / $stats['dispense_total']) * 100
                            : 0;
                @endphp

                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $condPercent }}%"></div>
                </div>

                <p class="mt-2 text-xs text-gray-400">
                    {{ number_format($stats['dispense_total'] - $stats['dispense_success']) }} medication dispense
                    belum
                    tersinkron
                </p>
            </div>

        </div>

        <div class="flex gap-3 items-center mt-12 mb-2">
            <h3 class="text-gray-800 font-bold uppercase tracking-wider">Kunjungan Bulan Ini</h3>
            <span class="px-2 py-1 bg-brand-50 text-brand-800 text-xs rounded-full font-bold">
                {{ number_format($todayVisits->count()) }} Kunjungan
            </span>
        </div>
        <div class="hidden md:block">
            <div class="bg-white rounded-lg shadow overflow-hidden mt-4 border border-zinc-300">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No. Kunjungan
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pasien</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">SATUSEHAT ID
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Invoice
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($todayVisits as $visit)
                            <tr class="border-b">
                                <td class="px-5 py-4 text-sm">{{ $visit->arrived_at->format('d-M-Y H:i') }}</td>
                                <td class="px-5 py-4 text-sm font-medium">{{ $visit->visit_number }}</td>
                                <td class="px-5 py-4 text-sm">{{ $visit->patient->name }}</td>
                                <td class="px-5 py-4 text-sm">
                                    @if ($visit->satusehat_encounter_id)
                                        <span
                                            class="text-green-600 font-mono text-xs">{{ $visit->satusehat_encounter_id }}</span>
                                    @else
                                        <span class="flex items-center text-yellow-600 text-xs">
                                            <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">...</svg>
                                            Memproses...
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm capitalize">{{ str($visit->internal_status)->headline() }}
                                </td>
                                <td class="px-5 py-4 text-sm text-right">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-mono text-right {{ $visit->invoice->payment_status === 'paid' ? 'bg-green-200' : 'bg-red-200' }}">
                                        IDR {{ number_format($visit->invoice->grand_total, 0, '.', ',') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="md:hidden space-y-4 pb-4 mt-6">
            @foreach ($todayVisits as $visit)
                <div
                    class="bg-white dark:bg-zinc-800 rounded-2xl p-4 shadow-sm border-2
                        {{ $visit->status === 'finished' ? 'border-green-200' : 'border-orange-200' }}">

                    {{-- Top Section --}}
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="font-semibold text-base leading-tight flex items-center gap-2">
                                {{ $visit->patient->name }}
                            </span>

                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($visit->date)->format('d M Y H:i') }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-lg font-semibold">
                                IDR {{ number_format($visit->invoice->grand_total) }}
                            </p>
                            <p
                                class="text-xs mt-1 font-medium
                                    {{ $visit->invoice->payment_status === 'paid' ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $visit->invoice->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                            </p>
                        </div>
                    </div>

                    {{-- Middle Section --}}
                    <div
                        class="text-sm text-gray-700 font-semibold dark:text-gray-300 space-y-1 flex justify-between align-center mb-3">
                        <div>
                            <p>
                                <span class="text-gray-400 font-normal">Dokter:</span>
                                {{ $visit->practitioner->name ?? '-' }}
                            </p>
                            <p>
                                <span class="text-gray-400 font-normal">No Kunjungan:</span>
                                {{ $visit->visit_number ?? '-' }}
                            </p>
                        </div>
                        </p>
                    </div>

                    {{-- Bottom Section --}}
                    <div class="flex justify-between items-center">
                        <p
                            class="text-xs {{ $visit->satusehat_encounter_id ? 'text-green-500' : 'text-slate-500' }} mt-1">
                            {{ $visit->satusehat_encounter_id ? 'Tersinkron dengan SATUSEHAT' : 'Belum tersinkron dengan SATUSEHAT' }}
                        </p>
                        <p class="text-sm text-gray-800 capitalize">
                            <span class="px-2 py-1 bg-brand-50 text-brand-800 text-xs rounded-full font-bold">
                                {{ str($visit->internal_status)->headline() }}
                            </span>
                        </p>

                        {{-- Actions --}}
                        {{-- <div class="flex gap-4 text-sm">

                            <a href="" class="text-yellow-500 active:scale-95 transition">
                                Edit
                            </a>

                            <button wire:click="" class="text-red-600 active:scale-95 transition">
                                Delete
                            </button>
                        </div> --}}
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>
