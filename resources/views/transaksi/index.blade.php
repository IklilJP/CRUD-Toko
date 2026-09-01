@extends('layouts.app')

@section('judul', 'Transaksi')

@section('konten')
    @php
        $adminMode = auth()->user()->isAdmin();
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold">
            {{ $adminMode ? 'Semua Transaksi' : 'Pesanan Saya' }}
            <span class="ml-1 text-sm font-normal text-gray-500">({{ $transaksi->total() }} nota)</span>
        </h1>

        @unless ($adminMode)
            <a href="{{ route('products.index') }}" class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">
                Belanja lagi
            </a>
        @endunless
    </div>

    <form action="{{ route('transaksi.index') }}" method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ $q }}" maxlength="100"
            placeholder="{{ $adminMode ? 'Cari kode / nama / email pembeli...' : 'Cari kode pesanan...' }}"
            class="w-full max-w-xs rounded border px-3 py-2 text-sm">
        <select name="status" class="rounded border bg-white px-3 py-2 text-sm">
            <option value="">Semua status</option>

            @foreach (App\Models\Transaksi::SEMUA_STATUS as $s)
                <option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>

        <button class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Saring</button>

        @if ($statusFilter !== '' || $q !== '')
            <a href="{{ route('transaksi.index') }}"
                class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Tanggal</th>

                    @if ($adminMode)
                        <th class="px-4 py-3">Pembeli</th>
                    @endif

                    <th class="px-4 py-3 text-center">Item</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($transaksi as $trx)
                    @php
                        $warna = match ($trx->status) {
                            'menunggu' => 'bg-amber-100 text-amber-800',
                            'dibayar' => 'bg-blue-100 text-blue-800',
                            'dikirim' => 'bg-indigo-100 text-indigo-800',
                            'selesai' => 'bg-green-100 text-green-800',
                            'batal' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('transaksi.show', $trx) }}" class="font-medium text-blue-700 hover:underline">
                                {{ $trx->kode }}
                            </a>
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            {{ $trx->created_at->format('d/m/Y H:i') }}
                        </td>

                        @if ($adminMode)
                            <td class="px-4 py-3">
                                {{ $trx->user->name }}
                                <div class="text-xs text-gray-500">{{ $trx->user->email }}</div>
                            </td>
                        @endif

                        <td class="px-4 py-3 text-center">
                            <span class="rounded bg-gray-100 px-2 py-0.5 text-xs">{{ $trx->details_count }}</span>
                        </td>

                        <td class="px-4 py-3 text-right font-medium whitespace-nowrap">
                            Rp {{ number_format($trx->total, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="rounded px-2 py-0.5 text-xs font-medium {{ $warna }}">
                                {{ ucfirst($trx->status) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('transaksi.show', $trx) }}"
                                class="rounded-md border bg-white px-3 py-1 text-xs text-gray-700 hover:bg-gray-50">
                                Lihat nota
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $adminMode ? 7 : 6 }}" class="px-4 py-10 text-center text-gray-500">
                            @if ($q !== '' || $statusFilter !== '')
                                Gak ada transaksi yang cocok dengan pencarianmu.
                            @elseif ($adminMode)
                                Belum ada transaksi masuk.
                            @else
                                Kamu belum pernah pesan apa-apa.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $transaksi->links() }}
    </div>
@endsection
