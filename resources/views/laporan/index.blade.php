@extends('layouts.app')

@section('judul', 'Laporan')

@section('konten')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold">
            Laporan Penjualan
            <span class="ml-1 text-sm font-normal text-gray-500">({{ $hari }} hari)</span>
        </h1>

        <span class="text-sm text-gray-500">
            {{ \Illuminate\Support\Carbon::parse($dari)->format('d M Y') }}
            &ndash;
            {{ \Illuminate\Support\Carbon::parse($sampai)->format('d M Y') }}
        </span>
    </div>

    <form action="{{ route('laporan.index') }}" method="GET" class="mb-3 flex flex-wrap items-end gap-2">
        <label class="text-sm">
            <span class="mb-1 block text-xs text-gray-500">Dari</span>
            <input type="date" name="dari" value="{{ $dari }}" max="{{ $maksTanggal }}"
                class="rounded border px-3 py-2 text-sm">
        </label>

        <label class="text-sm">
            <span class="mb-1 block text-xs text-gray-500">Sampai</span>
            <input type="date" name="sampai" value="{{ $sampai }}" max="{{ $maksTanggal }}"
                class="rounded border px-3 py-2 text-sm">
        </label>

        <button class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Terapkan</button>

        <a href="{{ route('laporan.index') }}"
            class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
    </form>

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ($preset as $nama => [$presetDari, $presetSampai])
            @php($aktif = $dari === $presetDari && $sampai === $presetSampai)

            <a href="{{ route('laporan.index', ['dari' => $presetDari, 'sampai' => $presetSampai]) }}"
                class="rounded-full border px-3 py-1 text-xs
                {{ $aktif ? 'border-blue-600 bg-blue-600 font-semibold text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                {{ $nama }}
            </a>
        @endforeach
    </div>

    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg bg-white p-4 shadow">
            <div class="text-xs uppercase text-gray-500">Omzet</div>
            <div class="mt-1 text-2xl font-bold">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow">
            <div class="text-xs uppercase text-gray-500">Nota</div>
            <div class="mt-1 text-2xl font-bold">{{ number_format($totalNota, 0, ',', '.') }}</div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow">
            <div class="text-xs uppercase text-gray-500">Rata-rata per nota</div>
            <div class="mt-1 text-2xl font-bold">
                Rp {{ number_format($totalNota > 0 ? $totalOmzet / $totalNota : 0, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="mb-5 rounded-lg bg-white p-4 shadow">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold">Omzet per {{ $perBulan ? 'bulan' : 'hari' }}</h2>

            <span class="text-xs text-gray-500">
                Status yang dihitung: dibayar, dikirim, selesai &middot; jam WIB
            </span>
        </div>

        @if ($totalNota === 0)
            <p class="py-8 text-center text-sm text-gray-500">
                Belum ada penjualan di rentang tanggal ini.
            </p>
        @else
            <div class="h-72">
                <canvas id="chartOmzet"></canvas>
            </div>
        @endif
    </div>

    <div class="mb-5 rounded-lg bg-white shadow">
        <h2 class="border-b px-4 py-3 font-semibold">Produk Terlaris</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3 text-center">Terjual</th>
                        <th class="px-4 py-3 text-right">Omzet</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse ($terlaris as $i => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>

                            <td class="px-4 py-3">
                                <a href="{{ route('products.show', $item->id) }}"
                                    class="font-medium text-blue-700 hover:underline">
                                    {{ $item->name }}
                                </a>
                            </td>

                            <td class="px-4 py-3 text-gray-500">{{ $item->sku }}</td>

                            <td class="px-4 py-3 text-center">
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs">{{ $item->qty }}</span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                Rp {{ number_format($item->omzet, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Belum ada produk yang terjual di rentang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
        <h2 class="font-semibold">Prediksi Stok</h2>

        <p class="mt-1">
            Dihitung dari penjualan
            <strong>
                {{ \Illuminate\Support\Carbon::parse($prediksi['dari'])->format('d M Y') }}
                &ndash;
                {{ \Illuminate\Support\Carbon::parse($prediksi['sampai'])->format('d M Y') }}
            </strong>
            ({{ $prediksi['jendelaMinggu'] }} minggu terakhir, jam WIB) dan <strong>gak ikut filter tanggal di atas</strong>.
            Hitungannya anggap kamu cek &amp; belanja tiap {{ $prediksi['periodeCekHari'] }} hari, sekali belanja
            buat {{ $prediksi['cakupanHari'] }} hari, dengan cadangan {{ $prediksi['layananPersen'] }}%.
        </p>

        <p class="mt-1 text-xs text-blue-800">
            Habis belanja langsung update stoknya ya, biar sarannya gak dobel.
            Minggu yang gak ada penjualannya bisa jadi karena stoknya lagi kosong.
        </p>
    </div>

    @php($bagian = [
        'belanja' => ['judul' => 'Perlu Belanja', 'warna' => 'text-red-700', 'kolom' => 'Saran', 'kosong' => 'Aman, gak ada yang perlu dibelanjakan.'],
        'manual'  => ['judul' => 'Perlu Dicek Dulu', 'warna' => 'text-gray-800', 'kolom' => 'Saran', 'kosong' => 'Gak ada yang perlu dicek.'],
        'kurangi' => ['judul' => 'Stoknya Kebanyakan', 'warna' => 'text-amber-700', 'kolom' => 'Kelebihan', 'kosong' => 'Gak ada stok yang menumpuk.'],
    ])

    @foreach ($bagian as $kunci => $info)
        <div class="mb-5 rounded-lg bg-white shadow">
            <h2 class="border-b px-4 py-3 font-semibold {{ $info['warna'] }}">
                {{ $info['judul'] }}
                <span class="ml-1 text-sm font-normal text-gray-500">({{ count($prediksi['kelompok'][$kunci]) }})</span>
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-center">Laku / minggu</th>
                            <th class="px-4 py-3 text-center">{{ $info['kolom'] }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse ($prediksi['kelompok'][$kunci] as $item)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('products.edit', $item['id']) }}"
                                        class="font-medium text-blue-700 hover:underline">
                                        {{ $item['name'] }}
                                    </a>

                                    <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">
                                        {{ $item['label'] }}
                                    </span>

                                    <div class="mt-1 text-xs text-gray-600">{{ $item['alasan'] }}</div>

                                    @foreach ($item['catatan'] as $catatan)
                                        <div class="text-xs text-amber-700">{{ $catatan }}</div>
                                    @endforeach

                                    <div class="mt-1 text-xs text-gray-400">
                                        Dibeli {{ $item['pembeli'] }} orang &middot; {{ $item['pesanan'] }} pesanan
                                        @if ($item['nMinggu'] > 0)
                                            &middot; laku di {{ $item['mingguAktif'] }}/{{ $item['nMinggu'] }} minggu
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">{{ $item['stock'] }}</td>

                                <td class="px-4 py-3 text-center text-gray-600">
                                    {{ $item['laju'] > 0 ? number_format($item['laju'], 1, ',', '.') : '–' }}
                                </td>

                                <td class="px-4 py-3 text-center font-semibold">
                                    @if ($item['saran'] === null)
                                        <span class="text-gray-400">–</span>
                                    @elseif ($item['saran'] === '?')
                                        <span class="text-gray-400" title="Belum ada data penjualan, jadi jumlahnya gak bisa dihitung">?</span>
                                    @elseif ($kunci === 'kurangi')
                                        <span class="text-amber-700">{{ $item['saran'] }}</span>
                                    @else
                                        <span class="text-green-700">+{{ $item['saran'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ $info['kosong'] }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <details class="mb-5 rounded-lg bg-white p-4 shadow">
        <summary class="cursor-pointer font-semibold text-green-700">
            Stok aman ({{ count($prediksi['kelompok']['aman']) }} produk)
        </summary>

        <ul class="mt-3 divide-y text-sm">
            @forelse ($prediksi['kelompok']['aman'] as $item)
                <li class="py-2">
                    <span class="font-medium">{{ $item['name'] }}</span>
                    <span class="text-xs text-gray-500">&middot; {{ $item['alasan'] }}</span>

                    @foreach ($item['catatan'] as $catatan)
                        <div class="text-xs text-amber-700">{{ $catatan }}</div>
                    @endforeach
                </li>
            @empty
                <li class="py-2 text-gray-500">Belum ada.</li>
            @endforelse
        </ul>
    </details>
@endsection

@push('scripts')
    @if ($totalNota > 0)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

        <script>
            new Chart(document.getElementById('chartOmzet'), {
                type: 'bar',
                data: {
                    labels: @json($label),
                    datasets: [{
                        label: 'Omzet',
                        data: @json($nilai),
                        backgroundColor: '#2563eb',
                        borderRadius: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (nilai) => 'Rp ' + nilai.toLocaleString('id-ID'),
                            },
                        },
                    },
                },
            });
        </script>
    @endif
@endpush
