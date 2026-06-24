@extends('layouts.guest')

@section('title', 'Giris Yap - ' . $tenant->company_name)

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $tenant->company_name }}</h1>
        <p class="text-gray-500 mt-2 text-sm">Panele giris yapin</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm"
                   placeholder="ornek@email.com" required autofocus>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sifre</label>
            <input type="password" name="password"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 outline-none text-sm"
                   placeholder="Sifreniz" required>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="remember" class="rounded border-gray-300">
            Beni hatirla
        </label>

        <button type="submit"
                class="w-full bg-gray-900 text-white py-3 rounded-lg font-medium text-sm hover:bg-gray-800 transition">
            Giris Yap
        </button>
    </form>
</div>
@endsection
