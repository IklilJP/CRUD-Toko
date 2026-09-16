<nav class="border-b bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">

        <div class="flex items-center gap-5">
            <a href="{{ route('home') }}" class="text-lg font-bold">Toko Kita</a>

            <a href="{{ route('home') }}"
               class="text-sm {{ request()->routeIs('home') ? 'font-semibold text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                Beranda
            </a>

            <a href="{{ route('products.index') }}"
               class="text-sm {{ request()->routeIs('products.*') && !request()->routeIs('products.arsip') ? 'font-semibold text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                Produk
            </a>

            <a href="{{ route('categories.index') }}"
               class="text-sm {{ request()->routeIs('categories.*') ? 'font-semibold text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                Kategori
            </a>

            @if (auth()->user()?->isAdmin())
                <a href="{{ route('laporan.index') }}"
                   class="text-sm {{ request()->routeIs('laporan.*') ? 'font-semibold text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                    Laporan
                </a>

                <a href="{{ route('products.arsip') }}"
                   class="text-sm {{ request()->routeIs('products.arsip') ? 'font-semibold text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                    Arsip
                </a>
            @endif

            {{-- ini masih tes --}}
            @if (auth()->user()?->isPembeli())
                <a href="{{ route('keranjang.index') }}"
                    class="text-sm {{ request()->routeIs('keranjang.*') ? 'font-semibold text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                    Keranjang

                    @php($jumlahKeranjang = auth()->user()->keranjang()->sum('qty'))

                    @if ($jumlahKeranjang > 0)
                        <span class="ml-1 rounded-full bg-blue-600 px-2 py-0.5 text-xs font-semibold text-white">
                            {{ $jumlahKeranjang }}
                        </span>
                    @endif
                </a>
            @endif

            @auth
                <a href="{{ route('transaksi.index') }}"
                    class="text-sm {{ request()->routeIs('transaksi.*') ? 'font-semibold text-blue-700' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ auth()->user()->isAdmin() ? 'Transaksi' : 'Pesanan Saya' }}
                </a>
            @endauth
            {{-- xxxxxxxxxxx --}}

        </div>

        <div class="flex items-center gap-3 text-sm">
            @auth
                <span class="text-gray-600">
                    {{ auth()->user()->name }}

                    <span
                        class="ml-1 rounded px-2 py-0.5 text-xs
                        {{ auth()->user()->isAdmin() ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                        {{ auth()->user()->role }}
                    </span>
                </span>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="rounded border px-3 py-1 hover:bg-gray-50">Keluar</button>
                </form>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="rounded border px-3 py-1 hover:bg-gray-50">Masuk</a>

                {{-- ini masih tes --}}
                <a href="{{ route('daftar') }}"
                    class="rounded bg-blue-600 px-3 py-1 font-medium text-white hover:bg-blue-700">Daftar</a>
                {{-- xxxxxxxxxxx --}}
            @endguest
        </div>
    </div>
</nav>
