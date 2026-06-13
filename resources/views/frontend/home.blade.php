@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-24 text-center">
        <h1 class="text-4xl font-extrabold tracking-tight" style="color: var(--brand-color)">
            {{ config('ranga.brand.name') }}
        </h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
            {{ config('ranga.brand.tagline') }}
        </p>
    </section>
@endsection
