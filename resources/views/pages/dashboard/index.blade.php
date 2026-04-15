<?php

use Livewire\Component;
use App\Models\OutpatientVisit;

new class extends Component {
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

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No. Kunjungan</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pasien</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">SATUSEHAT ID</th>
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
                            <td class="px-5 py-4 text-sm capitalize">{{ $visit->status }}</td>
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
</div>
