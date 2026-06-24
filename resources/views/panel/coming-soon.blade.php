@extends('layouts.panel')

@section('title', $title)

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
</div>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <p class="text-gray-400 text-lg">Bu modul yakin zamanda aktif olacak.</p>
    <p class="text-gray-300 text-sm mt-2">Gelistirme devam ediyor...</p>
</div>
@endsection
