@extends('layouts.app')
@section('title', 'Masuk Admin')
@section('content')
<div class="mx-auto max-w-md px-5 py-16"><p class="text-sm font-bold uppercase tracking-widest text-leaf">Panel petugas</p><h1 class="mt-3 text-4xl font-semibold tracking-tight">Masuk admin.</h1><p class="mt-3 text-sm leading-6 text-ink/60">Gunakan akun petugas terdaftar untuk mengelola jadwal dan antrean.</p>
<form method="post" action="{{ route('login.store') }}" class="mt-8 space-y-5 rounded-3xl border border-ink/10 bg-white p-7 shadow-sm">@csrf
@if($errors->any())<div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif
<label class="block"><span class="text-sm font-semibold">Email</span><input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-2 w-full rounded-xl border border-ink/15 bg-cream/60 px-4 py-3 focus:border-leaf focus:outline-none"></label>
<label class="block"><span class="text-sm font-semibold">Kata sandi</span><input type="password" name="password" required autocomplete="current-password" class="mt-2 w-full rounded-xl border border-ink/15 bg-cream/60 px-4 py-3 focus:border-leaf focus:outline-none"></label>
<label class="flex items-center gap-2 text-sm text-ink/60"><input type="checkbox" name="remember" class="rounded border-ink/20 text-leaf focus:ring-leaf"> Ingat saya</label><button class="w-full rounded-full bg-leaf px-5 py-3.5 font-semibold text-white hover:bg-ink">Masuk ke dashboard</button></form></div>
@endsection