@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<div class="mx-auto max-w-7xl px-5 py-9 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-blue-600">Ringkasan operasional</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <a href="{{ route('admin.schedule') }}" class="rounded-full border border-blue-200 bg-white px-5 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">Atur jadwal</a>
    </div>

    @if($schedule)
        <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([['Antrean dibuat', $stats['total'], 'Kuota '.$schedule->quota], ['Menunggu', $stats['waiting'], 'Sedang dalam antrean'], ['Selesai dilayani', $stats['served'], 'Hari ini'], ['Terlewat', $stats['skipped'], 'Hari ini']] as [$label, $value, $hint])
                <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                    <div class="text-sm text-slate-500">{{ $label }}</div>
                    <div class="mt-2 text-4xl font-semibold text-slate-900">{{ $value }}</div>
                    <div class="mt-2 text-xs text-slate-400">{{ $hint }}</div>
                </div>
            @endforeach
        </div>

        <section class="mt-5 rounded-2xl bg-blue-600 p-6 text-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-5">
                <div>
                    <div class="text-sm text-blue-100">Sedang dipanggil</div>
                    <div class="mt-2 text-5xl font-semibold">{{ $current?->display_number ?? '—' }}</div>
                    <div class="mt-2">{{ $current?->name ?? 'Belum ada antrean dipanggil' }}</div>
                    <div class="mt-4 text-sm text-blue-100">{{ $schedule->location }} · {{ substr((string) $schedule->starts_at, 0, 5) }}–{{ substr((string) $schedule->ends_at, 0, 5) }} WIB</div>
                </div>
                <a href="{{ route('admin.queues') }}" class="inline-flex rounded-full bg-white px-5 py-2.5 text-sm font-bold text-blue-700 hover:bg-blue-50">Kelola antrean →</a>
            </div>
        </section>
    @else
        <div class="mt-7 rounded-2xl border border-blue-200 bg-blue-50 p-5 sm:flex sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-blue-950">Jadwal hari ini belum diatur</h2>
                <p class="mt-1 text-sm text-blue-800">Buat jadwal untuk mulai menerima antrean hari ini.</p>
            </div>
            <a href="{{ route('admin.schedule') }}" class="mt-4 inline-flex rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 sm:mt-0">Buat jadwal hari ini</a>
        </div>
    @endif

    <section class="mt-7 rounded-2xl border border-blue-100 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-widest text-blue-600">Statistik layanan</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Kunjungan masyarakat</h2>
                <p class="mt-1 text-sm text-slate-500">Jumlah nomor antrean yang diambil pada periode terpilih.</p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2">
                <label for="period-filter" class="sr-only">Periode grafik kunjungan</label>
                <select id="period-filter" name="period" class="min-w-44 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @foreach($periods as $periodKey => $periodLabel)
                        <option value="{{ $periodKey }}" @selected($selectedPeriod === $periodKey)>{{ $periodLabel }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Terapkan</button>
            </form>
        </div>

        <div class="mt-6 flex flex-wrap items-end justify-between gap-4 rounded-xl bg-blue-50 px-4 py-3">
            <div>
                <div class="text-sm text-slate-500">Total kunjungan</div>
                <div class="text-3xl font-semibold text-blue-700">{{ number_format($chartTotal, 0, ',', '.') }}</div>
            </div>
            <div class="text-sm font-medium text-slate-600">
                {{ $from->translatedFormat('d M Y') }}@if(!$from->isSameDay($to)) – {{ $to->translatedFormat('d M Y') }}@endif
            </div>
        </div>

        @php
            $maxVisits = max(1, collect($chartPoints)->max('count') ?? 0);
            $chartCoordinates = [];
            $chartCount = count($chartPoints);
            $chartLabelInterval = max(1, (int) ceil(($chartCount - 1) / 6));

            foreach ($chartPoints as $index => $point) {
                $x = $chartCount > 1 ? 44 + ($index * 912 / ($chartCount - 1)) : 500;
                $y = 190 - ($point['count'] / $maxVisits * 160);
                $chartCoordinates[] = [
                    'x' => round($x, 2),
                    'y' => round($y, 2),
                    'label' => $point['label'],
                    'count' => $point['count'],
                    'showLabel' => $index === 0 || $index === $chartCount - 1 || $index % $chartLabelInterval === 0,
                ];
            }

            $linePoints = collect($chartCoordinates)->map(fn (array $point): string => $point['x'].','.$point['y'])->implode(' ');
        @endphp

        <div class="mt-7 overflow-hidden">
            <svg class="h-64 w-full overflow-visible" viewBox="0 0 1000 240" preserveAspectRatio="none" role="img" aria-label="Grafik garis kunjungan masyarakat">
                @foreach([0, 1, 2, 3, 4] as $gridLine)
                    @php($gridY = 30 + ($gridLine * 40))
                    <line x1="44" y1="{{ $gridY }}" x2="956" y2="{{ $gridY }}" stroke="#dbeafe" stroke-width="1" />
                    <text x="34" y="{{ $gridY + 4 }}" text-anchor="end" fill="#64748b" font-size="11">{{ number_format($maxVisits * (4 - $gridLine) / 4, 0, ',', '.') }}</text>
                @endforeach

                <polyline points="{{ $linePoints }}" fill="none" stroke="#2563eb" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />

                @foreach($chartCoordinates as $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="white" stroke="#2563eb" stroke-width="3" vector-effect="non-scaling-stroke">
                        <title>{{ $point['label'] }}: {{ number_format($point['count'], 0, ',', '.') }} kunjungan</title>
                    </circle>
                    @if($point['showLabel'])
                        <text x="{{ $point['x'] }}" y="224" text-anchor="middle" fill="#64748b" font-size="11">{{ $point['label'] }}</text>
                    @endif
                @endforeach
            </svg>
        </div>
        @if($chartTotal === 0)
            <p class="mt-2 text-center text-sm text-slate-500">Belum ada kunjungan pada periode ini.</p>
        @endif
    </section>
</div>
@endsection

