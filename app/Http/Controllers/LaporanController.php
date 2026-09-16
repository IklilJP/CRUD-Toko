<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller implements HasMiddleware
{
    private const ZONA_WAKTU = 'Asia/Jakarta';

    private const JENDELA_MINGGU = 12;
    private const MINGGU_TREN = 4;
    private const MIN_MINGGU_DATA = 4;
    private const MIN_PEMBELI = 3;
    private const MIN_MINGGU_AKTIF = 3;

    private const PERIODE_CEK_MINGGU = 1;
    private const CAKUPAN_BELANJA_MINGGU = 2;
    private const BATAS_KEBANYAKAN_MINGGU = 12;

    private const TINGKAT_LAYANAN_PERSEN = 95;
    private const NILAI_Z = [90 => 1.282, 95 => 1.645, 99 => 2.326];

    private const RASIO_TREN = 1.5;
    private const BATAS_KALI_MEDIAN = 3;

    private const KATEGORI = [
        'habis'       => ['label' => 'Habis padahal laku',     'kelompok' => 'belanja'],
        'pesan'       => ['label' => 'Pesan sekarang',         'kelompok' => 'belanja'],
        'kosong'      => ['label' => 'Stok kosong tanpa data', 'kelompok' => 'manual'],
        'sedikit'     => ['label' => 'Peminat sedikit',        'kelompok' => 'manual'],
        'belum_cukup' => ['label' => 'Data belum cukup',       'kelompok' => 'manual'],
        'kebanyakan'  => ['label' => 'Kebanyakan',             'kelompok' => 'kurangi'],
        'mati'        => ['label' => 'Barang mati',            'kelompok' => 'kurangi'],
        'aman'        => ['label' => 'Aman',                   'kelompok' => 'aman'],
    ];

    public static function middleware(): array
    {
        return ['auth', 'admin'];
    }

    public function index(Request $request)
    {
        $sekarang  = now(self::ZONA_WAKTU);
        $batasAtas = $sekarang->copy()->endOfDay();

        $mulai   = $this->tanggalValid($request->query('dari'));
        $selesai = $this->tanggalValid($request->query('sampai'));

        $selesai ??= $sekarang->copy()->startOfDay();
        $mulai   ??= ($selesai->gt($sekarang) ? $sekarang : $selesai)->copy()->subDays(29)->startOfDay();

        if ($mulai->gt($selesai)) {
            [$mulai, $selesai] = [$selesai, $mulai];
        }

        $selesai = $selesai->endOfDay();

        if ($selesai->gt($batasAtas)) {
            $selesai = $batasAtas;
        }

        if ($mulai->gt($selesai)) {
            $mulai = $selesai->copy()->startOfDay();
        }

        if ($this->jumlahHari($mulai, $selesai) > 1096) {
            $mulai = $selesai->copy()->subDays(1095)->startOfDay();
        }

        $hari     = $this->jumlahHari($mulai, $selesai);
        $perBulan = $hari > 62;

        $dari   = $mulai->toDateString();
        $sampai = $selesai->toDateString();

        $hariIni = $sekarang->toDateString();

        $preset = [
            '7 hari'    => [$sekarang->copy()->subDays(6)->toDateString(), $hariIni],
            '30 hari'   => [$sekarang->copy()->subDays(29)->toDateString(), $hariIni],
            '90 hari'   => [$sekarang->copy()->subDays(89)->toDateString(), $hariIni],
            'Bulan ini' => [$sekarang->copy()->startOfMonth()->toDateString(), $hariIni],
            'Tahun ini' => [$sekarang->copy()->startOfYear()->toDateString(), $hariIni],
        ];

        $grafik   = $this->grafikOmzet($mulai, $selesai, $perBulan);
        $prediksi = $this->prediksiStok($sekarang);

        $label      = $grafik['label'];
        $nilai      = $grafik['nilai'];
        $totalOmzet = $grafik['totalOmzet'];
        $totalNota  = $grafik['totalNota'];

        $terlaris = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi.id', '=', 'transaksi_detail.transaksi_id')
            ->join('products', 'products.id', '=', 'transaksi_detail.product_id')
            ->whereIn('transaksi.status', Transaksi::STATUS_PENJUALAN)
            ->whereBetween('transaksi.created_at', [$mulai->copy()->utc(), $selesai->copy()->utc()])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->selectRaw('products.id, products.name, products.sku')
            ->selectRaw('SUM(transaksi_detail.qty) AS qty')
            ->selectRaw('SUM(transaksi_detail.subtotal) AS omzet')
            ->orderByDesc('qty')
            ->orderBy('products.id')
            ->limit(10)
            ->get();

        $maksTanggal = $hariIni;

        return view('laporan.index', compact(
            'dari',
            'sampai',
            'hari',
            'perBulan',
            'preset',
            'maksTanggal',
            'label',
            'nilai',
            'totalOmzet',
            'totalNota',
            'terlaris',
            'prediksi',
        ));
    }

    private function tanggalValid(mixed $nilai): ?Carbon
    {
        if (! is_string($nilai) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai)) {
            return null;
        }

        $tanggal = Carbon::createFromFormat('Y-m-d', $nilai, self::ZONA_WAKTU);

        return $tanggal && $tanggal->format('Y-m-d') === $nilai && $tanggal->year >= 1970
            ? $tanggal->startOfDay()
            : null;
    }

    private function jumlahHari(Carbon $mulai, Carbon $selesai): int
    {
        return (int) $mulai->copy()->startOfDay()->diffInDays($selesai->copy()->startOfDay()) + 1;
    }

    private function kolomWib(string $kolom): string
    {
        return "({$kolom} AT TIME ZONE 'UTC' AT TIME ZONE '" . self::ZONA_WAKTU . "')";
    }

    private function tanggalWib(string $tanggal): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $tanggal, self::ZONA_WAKTU)->startOfDay();
    }

    private function grafikOmzet(Carbon $mulai, Carbon $selesai, bool $perBulan): array
    {
        $lokal    = $this->kolomWib('created_at');
        $ekspresi = $perBulan
            ? "TO_CHAR({$lokal}, 'YYYY-MM')"
            : "TO_CHAR({$lokal}, 'YYYY-MM-DD')";

        $perPeriode = Transaksi::query()
            ->whereIn('status', Transaksi::STATUS_PENJUALAN)
            ->whereBetween('created_at', [$mulai->copy()->utc(), $selesai->copy()->utc()])
            ->selectRaw("{$ekspresi} AS periode, SUM(total) AS omzet, COUNT(*) AS nota")
            ->groupByRaw($ekspresi)
            ->get()
            ->keyBy(fn ($baris) => (string) $baris->periode);

        $label = [];
        $nilai = [];

        $jalan = $perBulan
            ? $mulai->copy()->startOfMonth()
            : $mulai->copy();

        while ($jalan->lte($selesai)) {
            $kunci = $perBulan ? $jalan->format('Y-m') : $jalan->toDateString();
            $baris = $perPeriode->get($kunci);

            $label[] = $perBulan ? $jalan->format('M Y') : $jalan->format('d M');
            $nilai[] = $baris ? (float) $baris->omzet : 0.0;

            $perBulan ? $jalan->addMonth() : $jalan->addDay();
        }

        return [
            'label'      => $label,
            'nilai'      => $nilai,
            'totalOmzet' => (float) $perPeriode->sum(fn ($baris) => (float) $baris->omzet),
            'totalNota'  => (int) $perPeriode->sum(fn ($baris) => (int) $baris->nota),
        ];
    }

    private function prediksiStok(Carbon $sekarang): array
    {
        $hariIni      = $sekarang->copy()->startOfDay();
        $akhirJendela = $hariIni->copy()->subDay();
        $awalJendela  = $hariIni->copy()->subDays(self::JENDELA_MINGGU * 7);
        $awalData     = $hariIni->copy()->subDays(self::JENDELA_MINGGU * 7 * 2);

        $produk = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'stock', 'created_at', 'updated_at']);

        $penjualan = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi.id', '=', 'transaksi_detail.transaksi_id')
            ->join('users', 'users.id', '=', 'transaksi.user_id')
            ->whereIn('transaksi.status', Transaksi::STATUS_PENJUALAN)
            ->where('transaksi.created_at', '>=', $awalData->copy()->utc())
            ->where('transaksi.created_at', '<', $hariIni->copy()->utc())
            ->groupBy('transaksi_detail.product_id', 'transaksi.id', 'transaksi.user_id', 'users.name')
            ->selectRaw('transaksi_detail.product_id, transaksi.id AS transaksi_id')
            ->selectRaw('transaksi.user_id, users.name AS nama_pembeli')
            ->selectRaw("TO_CHAR({$this->kolomWib('transaksi.created_at')}, 'YYYY-MM-DD') AS tanggal")
            ->selectRaw('SUM(transaksi_detail.qty) AS qty')
            ->get()
            ->groupBy('product_id');

        $riwayat = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi.id', '=', 'transaksi_detail.transaksi_id')
            ->groupBy('transaksi_detail.product_id')
            ->selectRaw('transaksi_detail.product_id')
            ->selectRaw("TO_CHAR(MAX({$this->kolomWib('transaksi.created_at')}) FILTER (WHERE transaksi.status <> 'batal'), 'YYYY-MM-DD') AS terakhir_dipesan")
            ->selectRaw("GREATEST(MAX(transaksi.created_at), MAX(transaksi.updated_at) FILTER (WHERE transaksi.status = 'batal')) AS aktivitas_terakhir")
            ->selectRaw("COALESCE(SUM(transaksi_detail.qty) FILTER (WHERE transaksi.status = 'menunggu'), 0) AS qty_menunggu")
            ->get()
            ->keyBy('product_id');

        $kelompok = ['belanja' => [], 'manual' => [], 'kurangi' => [], 'aman' => []];

        foreach ($produk as $item) {
            $baris = $this->nilaiProduk(
                $item,
                $penjualan->get($item->id, collect()),
                $riwayat->get($item->id),
                $awalJendela,
                $akhirJendela,
            );

            $kelompok[self::KATEGORI[$baris['kategori']]['kelompok']][] = $baris;
        }

        usort($kelompok['belanja'], fn ($a, $b) => ($a['cakupan'] <=> $b['cakupan'])
            ?: ($b['laju'] <=> $a['laju']));

        usort($kelompok['manual'], fn ($a, $b) => ($a['stock'] <=> $b['stock'])
            ?: strcmp($a['name'], $b['name']));

        usort($kelompok['kurangi'], fn ($a, $b) => $b['nilaiTertahan'] <=> $a['nilaiTertahan']);

        return [
            'kelompok'       => $kelompok,
            'dari'           => $awalJendela->toDateString(),
            'sampai'         => $akhirJendela->toDateString(),
            'periodeCekHari' => self::PERIODE_CEK_MINGGU * 7,
            'cakupanHari'    => self::CAKUPAN_BELANJA_MINGGU * 7,
            'jendelaMinggu'  => self::JENDELA_MINGGU,
            'layananPersen'  => self::TINGKAT_LAYANAN_PERSEN,
        ];
    }

    private function nilaiProduk(
        Product $item,
        Collection $baris,
        ?object $riwayat,
        Carbon $awalJendela,
        Carbon $akhirJendela,
    ): array {
        $stok = (int) $item->stock;

        $diubahAdmin = $item->updated_at
            && $item->updated_at->gt($item->created_at)
            && (! $riwayat?->aktivitas_terakhir || $item->updated_at->gt(Carbon::parse($riwayat->aktivitas_terakhir)->addMinute()))
            && $item->updated_at->copy()->setTimezone(self::ZONA_WAKTU)->startOfDay()
                ->gt($akhirJendela->copy()->subDays(self::MIN_MINGGU_DATA * 7));

        $tanggalAkhir = $akhirJendela->copy();
        $tanggalHabis = null;

        if ($stok === 0 && ! $diubahAdmin && $riwayat?->terakhir_dipesan) {
            $tanggalHabis = $this->tanggalWib($riwayat->terakhir_dipesan);

            if ($tanggalHabis->gte($awalJendela) && $tanggalHabis->lt($akhirJendela)) {
                $tanggalAkhir = $tanggalHabis->copy();
            }
        }

        $tanggalDibuat = ($item->created_at ?? $awalJendela)->copy()->setTimezone(self::ZONA_WAKTU)->startOfDay();
        $jualPertama   = $baris->min('tanggal');

        if ($jualPertama !== null && $jualPertama < $tanggalDibuat->toDateString()) {
            $tanggalDibuat = $this->tanggalWib($jualPertama);
        }

        $awalAmatan   = $tanggalAkhir->copy()->subDays(self::JENDELA_MINGGU * 7 - 1);
        $tanggalMulai = $tanggalDibuat->gt($awalAmatan) ? $tanggalDibuat : $awalAmatan;
        $hariAmatan   = $tanggalMulai->gt($tanggalAkhir) ? 0 : (int) $tanggalMulai->diffInDays($tanggalAkhir) + 1;
        $nMinggu      = intdiv($hariAmatan, 7);

        $awalHitung = $nMinggu >= self::MIN_MINGGU_DATA
            ? $tanggalAkhir->copy()->subDays($nMinggu * 7 - 1)
            : $tanggalMulai;

        $dari   = $awalHitung->toDateString();
        $sampai = $tanggalAkhir->toDateString();

        $baris = $baris
            ->filter(fn ($b) => $b->tanggal >= $dari && $b->tanggal <= $sampai)
            ->map(function ($b) use ($tanggalAkhir) {
                $b->qty    = (int) $b->qty;
                $b->minggu = intdiv((int) $this->tanggalWib($b->tanggal)->diffInDays($tanggalAkhir), 7);

                return $b;
            })
            ->values();

        $qtyTerjual = (int) $baris->sum('qty');
        $pembeli    = $baris->pluck('user_id')->unique()->count();

        [$faktor, $dipotong, $adaRutin] = $this->batasiBorongan($baris);

        $mingguan   = array_fill(0, $nMinggu, 0.0);
        $qtyDipakai = 0.0;

        foreach ($baris as $b) {
            $qty = $b->qty * $faktor[$b->user_id];
            $qtyDipakai += $qty;

            if ($b->minggu < $nMinggu) {
                $mingguan[$b->minggu] += $qty;
            }
        }

        $mingguAktif = count(array_filter($mingguan, fn ($w) => $w > 0));

        $hasil = [
            'id'            => $item->id,
            'name'          => $item->name,
            'sku'           => $item->sku,
            'stock'         => $stok,
            'kategori'      => 'aman',
            'label'         => '',
            'alasan'        => '',
            'catatan'       => [],
            'saran'         => null,
            'laju'          => 0.0,
            'min'           => null,
            'cakupan'       => 0.0,
            'nilaiTertahan' => 0.0,
            'terjual'       => $qtyTerjual,
            'pembeli'       => $pembeli,
            'pesanan'       => $baris->pluck('transaksi_id')->unique()->count(),
            'mingguAktif'   => $mingguAktif,
            'nMinggu'       => $nMinggu,
        ];

        foreach ($dipotong as $potong) {
            $hasil['catatan'][] = "{$potong['nama']} beli {$potong['qty']} pcs, tapi cuma dihitung "
                . $this->angka($potong['dihitung']) . ' pcs biar gak dikira laris.';
        }

        if ($diubahAdmin) {
            $hasil['catatan'][] = 'Produk ini diedit tanggal ' . $item->updated_at->copy()->setTimezone(self::ZONA_WAKTU)->format('d M Y')
                . ' dan belum ada pesanan lagi sesudahnya. Kalau itu restock atau stoknya dinolkan, hitungan di atas belum ikut berubah.';
        }

        if ((int) $riwayat?->qty_menunggu > 0) {
            $hasil['catatan'][] = "Ada {$riwayat->qty_menunggu} pcs yang nyangkut di pesanan belum dibayar. Cek dulu sebelum belanja.";
        }

        if ($stok === 0 && $qtyTerjual === 0) {
            return $this->kategori($hasil, 'kosong', '?',
                'Stoknya kosong dan ' . self::JENDELA_MINGGU . ' minggu ini belum ada yang beli'
                . ($riwayat?->terakhir_dipesan ? ' (terakhir dipesan ' . $this->tanggalWib($riwayat->terakhir_dipesan)->format('d M Y') . ')' : '')
                . '. Bisa jadi gak laku karena memang barangnya gak ada, jadi jumlahnya gak ditebak. Coba isi sedikit dulu, atau arsipkan aja.');
        }

        $kasar = false;

        if ($nMinggu < self::MIN_MINGGU_DATA) {
            if ($pembeli < self::MIN_PEMBELI) {
                return $this->kategori($hasil, 'belum_cukup', null,
                    "Datanya baru {$hariAmatan} hari, belum cukup buat dihitung (minimal " . (self::MIN_MINGGU_DATA * 7) . ' hari). '
                    . "Sejauh ini laku {$qtyTerjual} pcs ke {$pembeli} orang."
                    . ($stok === 0 ? ' Stoknya udah habis, jumlah belanjanya kira-kira sendiri dulu ya.' : ''));
            }

            $kasar = true;
            $hasil['catatan'][] = "Masih perkiraan kasar, datanya baru {$hariAmatan} hari.";
        }

        if ($qtyTerjual === 0) {
            if ($nMinggu >= self::JENDELA_MINGGU) {
                $hasil['nilaiTertahan'] = $stok * (float) $item->price;

                return $this->kategori($hasil, 'mati', null,
                    self::JENDELA_MINGGU . " minggu gak ada yang beli, padahal stoknya masih {$stok}. Ada ± Rp "
                    . number_format($hasil['nilaiTertahan'], 0, ',', '.') . ' yang nyangkut (dari harga jual). '
                    . 'Jangan restock dulu, coba diskon atau jual paket, kalau udah habis arsipkan.');
            }

            return $this->kategori($hasil, 'belum_cukup', null,
                "Udah {$hariAmatan} hari dijual tapi belum ada yang beli. Kalau sampai "
                . self::JENDELA_MINGGU . ' minggu masih sepi, bakal masuk barang mati.');
        }

        if ($kasar) {
            $laju  = $qtyDipakai / max($hariAmatan, 7) * 7;
            $sigma = sqrt($laju);
        } else {
            $laju  = array_sum($mingguan) / $nMinggu;
            $sigma = sqrt(array_sum(array_map(fn ($w) => ($w - $laju) ** 2, $mingguan)) / ($nMinggu - 1));

            [$tren, $lajuBaru] = $this->tren($mingguan, $baris);

            if ($tren === 'naik') {
                $laju = $lajuBaru;
                $hasil['catatan'][] = 'Lagi naik, jadi hitungannya pakai rata-rata 4 minggu terakhir.';
            }

            if ($tren === 'turun') {
                $hasil['catatan'][] = '4 minggu terakhir lagi sepi. Cek dulu sebelum belanja banyak.';
            }
        }

        $cadangan = self::NILAI_Z[self::TINGKAT_LAYANAN_PERSEN] * $sigma * sqrt(self::PERIODE_CEK_MINGGU);
        $min      = $this->bulatAtas($laju * self::PERIODE_CEK_MINGGU + $cadangan);
        $max      = $min + $this->bulatAtas($laju * self::CAKUPAN_BELANJA_MINGGU);
        $batas    = max($max, $this->bulatAtas($laju * self::BATAS_KEBANYAKAN_MINGGU));

        $hasil['laju']    = $laju;
        $hasil['min']     = $min;
        $hasil['cakupan'] = $stok / $laju;

        $cakupanTeks = '± ' . $this->angka($hasil['cakupan']) . ' minggu';
        $buktiCukup  = $kasar
            || ($pembeli >= self::MIN_PEMBELI && $mingguAktif >= self::MIN_MINGGU_AKTIF)
            || $adaRutin;

        if ($kasar && $stok >= $min) {
            return $this->kategori($hasil, 'belum_cukup', null,
                "Produk baru, datanya baru {$hariAmatan} hari. Udah laku {$qtyTerjual} pcs ke {$pembeli} orang "
                . "dan stoknya cukup buat {$cakupanTeks}. Belum bisa dibilang kebanyakan, pantau dulu aja.");
        }

        if ($stok > $batas && ($buktiCukup || $nMinggu >= self::JENDELA_MINGGU)) {
            $kelebihan             = $stok - $batas;
            $hasil['nilaiTertahan'] = $kelebihan * (float) $item->price;

            return $this->kategori($hasil, 'kebanyakan', $kelebihan,
                "Stok {$stok} baru habis {$cakupanTeks} lagi, padahal batasnya " . self::BATAS_KEBANYAKAN_MINGGU . ' minggu. '
                . "Kelebihan {$kelebihan} pcs (± Rp " . number_format($hasil['nilaiTertahan'], 0, ',', '.') . '). '
                . 'Tahan dulu belanjanya, kasih promo kecil aja.');
        }

        if (! $buktiCukup) {
            return $this->kategori($hasil, 'sedikit', $stok === 0 ? 1 : null,
                "Yang beli baru {$pembeli} orang dan lakunya cuma di {$mingguAktif} minggu (minimal " . self::MIN_PEMBELI
                . ' orang di ' . self::MIN_MINGGU_AKTIF . ' minggu, atau ada 1 langganan). '
                . ($stok === 0
                    ? 'Isi 1 pcs dulu biar bisa di-checkout, jangan belanja banyak.'
                    : 'Nanti kalau habis jangan langsung belanja banyak.'));
        }

        if ($stok === 0) {
            return $this->kategori($hasil, 'habis', $max,
                'Stoknya kosong' . ($tanggalHabis ? ' sejak ' . $tanggalHabis->format('d M Y') : '') . ', padahal rata-rata laku ± ' . $this->angka($laju)
                . " pcs seminggu dari {$pembeli} orang. Belanja {$max} pcs. "
                . 'Aslinya bisa lebih laku lagi, soalnya selama kosong orang gak bisa beli.');
        }

        if ($stok < $min) {
            return $this->kategori($hasil, 'pesan', $max - $stok,
                "Stok tinggal {$stok}, padahal minimalnya {$min} (cuma cukup {$cakupanTeks}). "
                . 'Belanja ' . ($max - $stok) . " pcs biar jadi {$max}.");
        }

        return $this->kategori($hasil, 'aman', null,
            "Stok {$stok} masih aman, di antara minimal {$min} dan rekomendasi max stok {$batas}.");
    }

    private function batasiBorongan(Collection $baris): array
    {
        $perPembeli = $baris->groupBy('user_id');
        $batas      = (float) $perPembeli->map(fn ($b) => $b->sum('qty'))->median() * self::BATAS_KALI_MEDIAN;

        $faktor   = [];
        $dipotong = [];
        $adaRutin = false;

        foreach ($perPembeli as $userId => $barisPembeli) {
            $qty   = (int) $barisPembeli->sum('qty');
            $rutin = $barisPembeli->pluck('minggu')->unique()->count() >= self::MIN_MINGGU_AKTIF;
            $adaRutin = $adaRutin || $rutin;

            if (! $rutin && $qty > $batas) {
                $faktor[$userId] = $batas / $qty;
                $dipotong[]      = [
                    'nama'     => $barisPembeli->first()->nama_pembeli,
                    'qty'      => $qty,
                    'dihitung' => $batas,
                ];
            } else {
                $faktor[$userId] = 1.0;
            }
        }

        return [$faktor, $dipotong, $adaRutin];
    }

    private function tren(array $mingguan, Collection $baris): array
    {
        $nMinggu = count($mingguan);

        if ($nMinggu < self::MINGGU_TREN * 2) {
            return [null, null];
        }

        $baru     = array_sum(array_slice($mingguan, 0, self::MINGGU_TREN));
        $harapan  = array_sum(array_slice($mingguan, self::MINGGU_TREN)) / ($nMinggu - self::MINGGU_TREN) * self::MINGGU_TREN;
        $wajar    = 2 * sqrt($harapan);
        $lajuBaru = $baru / self::MINGGU_TREN;

        $pembeliBaru = $baris->where('minggu', '<', self::MINGGU_TREN)->pluck('user_id')->unique()->count();
        $pembeliLama = $baris->where('minggu', '>=', self::MINGGU_TREN)->where('minggu', '<', $nMinggu)
            ->pluck('user_id')->unique()->count();

        if ($pembeliBaru >= self::MIN_PEMBELI && $baru >= self::RASIO_TREN * $harapan && $baru - $harapan > $wajar) {
            return ['naik', $lajuBaru];
        }

        if ($pembeliLama >= self::MIN_PEMBELI && $harapan >= self::RASIO_TREN * $baru && $harapan - $baru > $wajar) {
            return ['turun', $lajuBaru];
        }

        return [null, null];
    }

    private function kategori(array $hasil, string $kategori, int|string|null $saran, string $alasan): array
    {
        $hasil['kategori'] = $kategori;
        $hasil['label']    = self::KATEGORI[$kategori]['label'];
        $hasil['saran']    = $saran;
        $hasil['alasan']   = $alasan;

        return $hasil;
    }

    private function bulatAtas(float $angka): int
    {
        return (int) ceil(round($angka, 6));
    }

    private function angka(float $angka): string
    {
        return preg_replace('/,0$/', '', number_format($angka, 1, ',', '.'));
    }
}
