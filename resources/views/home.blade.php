@extends('layouts.app')
@section('title', 'Antrean Samsat Keliling')
@section('content')
<section class="bg-gradient-to-br from-blue-50 via-white to-sky-100/60">
<div class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-14 md:grid-cols-[1.1fr_.9fr] md:py-20 lg:px-8">
<div>
<div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-blue-700"><span class="size-2 rounded-full bg-blue-500"></span> Layanan Samsat Keliling</div>
<h1 class="max-w-2xl text-5xl font-semibold leading-[1.05] tracking-tight sm:text-6xl">Urus kendaraan, <span class="text-blue-600">tanpa menebak antrean.</span></h1>
<p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">Ambil nomor dari ponsel, lihat posisi dan perkiraan waktu dilayani, lalu datang sesuai giliran Anda.</p>
<div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('queue.create') }}" class="rounded-full bg-blue-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-blue-600/15 hover:bg-blue-700">Ambil nomor antrean →</a><a href="{{ route('schedule.public') }}" class="rounded-full border border-blue-200 bg-white px-6 py-3.5 font-semibold text-blue-800 hover:bg-blue-50">Lihat jadwal</a></div>
<div class="mt-10 flex items-center gap-3 text-sm text-slate-600"><span class="grid size-9 place-items-center rounded-full bg-blue-100 text-blue-700">✓</span> Tanpa akun · Estimasi tunggu maksimal 35 menit</div>
</div>
<div class="relative"><div class="absolute -inset-5 rounded-[2.5rem] bg-blue-200/50 blur-xl"></div><div class="relative rounded-[2rem] bg-gradient-to-br from-blue-500 to-blue-700 p-7 text-white shadow-2xl shadow-blue-700/20 sm:p-10">
<div class="text-sm font-medium text-white/75">{{ $schedule?->info_title ?? 'Jadwal terdekat' }}</div><div class="mt-1 text-2xl font-semibold">{{ $schedule?->service_date?->translatedFormat('l, d F Y') ?? 'Belum tersedia' }}</div>
@if($schedule)<div class="my-8 h-px bg-white/25"></div><div class="grid grid-cols-2 gap-5"><div><div class="text-xs uppercase tracking-wider text-white/65">Lokasi</div><div class="mt-2 font-medium">{{ $schedule->location }}</div></div><div><div class="text-xs uppercase tracking-wider text-white/65">Jam layanan</div><div class="mt-2 font-medium">{{ substr((string)$schedule->starts_at, 0, 5) }}–{{ substr((string)$schedule->ends_at, 0, 5) }} WIB</div></div></div>
@if($schedule->info_content)<p class="mt-6 whitespace-pre-line text-sm leading-6 text-white/80">{{ $schedule->info_content }}</p>@endif<a href="{{ route('schedule.public') }}" class="mt-8 inline-flex gap-2 font-semibold">Detail jadwal →</a>
@else<p class="mt-6 text-white/80">Jadwal berikutnya akan diumumkan di halaman ini.</p>@endif
</div></div>
</div>
</section>

@if($sections->isNotEmpty())
<section class="mx-auto max-w-7xl px-5 py-14 lg:px-8">
<div class="mb-8 max-w-2xl"><p class="text-sm font-bold uppercase tracking-widest text-blue-600">Informasi layanan</p><h2 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">Samsat Keliling, lebih dekat dengan Anda.</h2><p class="mt-3 text-slate-600">Kenali layanan, alur pendaftaran, serta dokumen yang perlu disiapkan sebelum berkunjung.</p></div>
<div class="grid items-stretch gap-5 md:grid-cols-2">
@foreach($sections as $section)
<article class="rounded-3xl border border-blue-100 bg-white p-6 shadow-sm shadow-blue-950/[.03] sm:p-8">
<h3 class="flex items-center gap-3 border-b border-slate-200 pb-5 text-xl font-semibold text-blue-700"><span class="text-3xl" aria-hidden="true">{{ $section->icon }}</span>{{ $section->title }}</h3>
@if($section->description)<p class="mt-5 whitespace-pre-line text-[15px] leading-7 text-slate-600">{{ $section->description }}</p>@endif
@if($section->subheading)<div class="mt-5 font-semibold text-slate-700">{{ $section->subheading }}</div>@endif
@if($section->subheading_text)<p class="mt-2 text-[15px] leading-7 text-slate-600">{{ $section->subheading_text }}</p>@endif
@if($section->items_title)<div class="mt-6 font-semibold text-slate-700">{{ $section->items_title }}</div>@endif
@if($section->items)<ul class="mt-4 space-y-3">@foreach($section->items as $item)<li class="relative pl-6 text-[15px] leading-7 text-slate-600"><span class="absolute left-0 top-0 text-lg font-bold text-blue-500">•</span>{{ $item }}</li>@endforeach</ul>@endif
</article>
@endforeach
</div>
</section>
@endif

<section class="border-y border-blue-100 bg-white"><div class="mx-auto grid max-w-7xl gap-8 px-5 py-12 md:grid-cols-3 lg:px-8">
<div><div class="text-3xl font-semibold text-blue-500">01</div><h2 class="mt-3 font-semibold">Pilih keperluan</h2><p class="mt-1 text-sm leading-6 text-slate-600">Isi nama dan nomor ponsel untuk membuat tiket.</p></div>
<div><div class="text-3xl font-semibold text-blue-500">02</div><h2 class="mt-3 font-semibold">Pantau giliran</h2><p class="mt-1 text-sm leading-6 text-slate-600">Lihat sisa antrean dan estimasi waktu secara langsung.</p></div>
<div><div class="text-3xl font-semibold text-blue-500">03</div><h2 class="mt-3 font-semibold">Datang tepat waktu</h2><p class="mt-1 text-sm leading-6 text-slate-600">Tunjukkan tiket ketika nomor Anda dipanggil petugas.</p></div>
</div></section>
@endsection