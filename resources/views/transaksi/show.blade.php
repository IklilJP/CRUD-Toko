@extends('layouts.app')

@section('judul', 'Nota ' . $transaksi->kode)

@section('konten')
    @php
        $adminMode = auth()->user()->isAdmin();

        $warna = match ($transaksi->status) {
            'menunggu' => 'bg-amber-100 text-amber-800',
            'dibayar'  => 'bg-blue-100 text-blue-800',
            'dikirim'  => 'bg-indigo-100 text-indigo-800',
            'selesai'  => 'bg-green-100 text-green-800',
            'batal'    => 'bg-red-100 text-red-800',
            default    => 'bg-gray-100 text-gray-700',
        };
    @endphp

    <div class="mx-auto max-w-4xl">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">{{ $transaksi->kode }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Dibuat {{ $transaksi->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <a href="{{ route('transaksi.index') }}" class="rounded border bg-white px-4 py-2 text-sm hover:bg-gray-50">
                &larr; {{ $adminMode ? 'Semua transaksi' : 'Pesanan saya' }}
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-5">

            <div class="md:col-span-3">
                <div class="overflow-x-auto rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-right">Harga</th>
                                <th class="px-4 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach ($transaksi->details as $detail)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">

                                            @if ($detail->product?->primaryImage)
                                                <img src="{{ $detail->product->primaryImage->url }}"
                                                    alt="{{ $detail->nama_produk }}" loading="lazy"
                                                    class="h-10 w-10 rounded border object-cover">
                                            @endif

                                            <div>

                                                <div class="font-medium">{{ $detail->nama_produk }}</div>

                                                @if ($detail->product)
                                                    <a href="{{ route('products.show', $detail->product) }}"
                                                        class="text-xs text-blue-600 hover:underline">
                                                        lihat produknya sekarang
                                                    </a>
                                                @else
                                                    <div class="text-xs text-gray-400">produk sudah gak ada</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        Rp {{ number_format($detail->harga, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-center">{{ $detail->qty }}</td>

                                    <td class="px-4 py-3 text-right font-medium whitespace-nowrap">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot class="border-t bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right font-medium">Total</td>
                                <td class="px-4 py-3 text-right text-base font-bold text-blue-700 whitespace-nowrap">
                                    Rp {{ number_format($transaksi->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="md:col-span-2 space-y-6">

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-3 font-semibold">Status</h2>

                    <span class="inline-block rounded px-3 py-1 text-sm font-medium {{ $warna }}">
                        {{ ucfirst($transaksi->status) }}
                    </span>

                    @if ($adminMode && $transaksi->status !== 'batal')
                        <form action="{{ route('transaksi.status', $transaksi) }}" method="POST" class="mt-4">
                            @csrf

                            <label for="status" class="block text-sm font-medium">Ubah status</label>

                            <select id="status" name="status" required
                                class="mt-1 w-full rounded border bg-white px-3 py-2 text-sm @error('status') border-red-500 @enderror">

                                @foreach (App\Models\Transaksi::STATUS_ADMIN as $s)
                                    <option value="{{ $s }}" @selected($transaksi->status === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>

                            <p class="mt-1 text-sm text-red-600">@error('status'){{ $message }}@enderror</p>

                            <button class="mt-2 w-full rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Simpan status
                            </button>
                        </form>
                    @endif

                    @if ($transaksi->bisaDibatalkan())
                        <form action="{{ route('transaksi.batal', $transaksi) }}" method="POST" class="mt-3"
                            onsubmit="return confirm('Batalkan pesanan ini? Stok produknya dikembalikan dan status jadi batal permanen.')">
                            @csrf
                            <button class="w-full rounded border border-red-200 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Batalkan pesanan
                            </button>
                        </form>
                    @endif
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-3 font-semibold">Pengiriman</h2>

                    <dl class="space-y-2 text-sm">

                        @if ($adminMode)
                            <div>
                                <dt class="text-gray-500">Pembeli</dt>
                                <dd class="font-medium">{{ $transaksi->user->name }} ({{ $transaksi->user->email }})</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-gray-500">Nama penerima</dt>
                            <dd class="font-medium">{{ $transaksi->nama_penerima }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Telepon</dt>
                            <dd class="font-medium">{{ $transaksi->telepon }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-500">Alamat</dt>

                            <dd class="whitespace-pre-line font-medium">{{ $transaksi->alamat }}</dd>
                        </div>

                        @if ($transaksi->catatan)
                            <div>
                                <dt class="text-gray-500">Catatan</dt>
                                <dd class="whitespace-pre-line font-medium">{{ $transaksi->catatan }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
