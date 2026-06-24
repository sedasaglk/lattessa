@extends('layouts.panel')
@section('title', 'Kasa')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">Kasa</h1>
</div>

{{-- Ozet Kartlar --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Toplam Gelir</p>
        <p class="text-2xl font-semibold text-green-600 mt-1">{{ number_format($totalIncome, 2, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Toplam Gider</p>
        <p class="text-2xl font-semibold text-red-600 mt-1">{{ number_format($totalExpense, 2, ',', '.') }} TL</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Net Bakiye</p>
        <p class="text-2xl font-semibold {{ $netBalance >= 0 ? 'text-gray-900' : 'text-red-600' }} mt-1">
            {{ number_format($netBalance, 2, ',', '.') }} TL
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Sol: Islem Listesi --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Ay Filtresi --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" class="flex items-center gap-3">
                <input type="month" name="month" value="{{ $month }}"
                       class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    Filtrele
                </button>
                <a href="?month={{ now()->format('Y-m') }}" class="text-sm text-gray-500 hover:text-gray-900">Bu Ay</a>
            </form>
        </div>

        {{-- Islem Listesi --}}
        <div class="bg-white rounded-xl border border-gray-200">
            @if($transactions->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-400">Bu ay islem bulunmuyor.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($transactions as $tx)
                    <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $tx->type === 'income' ? 'bg-green-100' : 'bg-red-100' }}">
                                <span class="{{ $tx->type === 'income' ? 'text-green-600' : 'text-red-600' }} text-sm font-bold">
                                    {{ $tx->type === 'income' ? '+' : '-' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $tx->description ?: ($tx->category_name ?: ($tx->type === 'income' ? 'Gelir' : 'Gider')) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d.m.Y') }}
                                    @if($tx->category_name) &bull; {{ $tx->category_name }} @endif
                                    @if($tx->customer_name) &bull; {{ $tx->customer_name }} @endif
                                    &bull;
                                    {{ match($tx->payment_method) {
                                        'cash' => 'Nakit',
                                        'card' => 'Kart',
                                        'transfer' => 'Havale',
                                        default => $tx->payment_method
                                    } }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-semibold {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $tx->type === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2, ',', '.') }} TL
                            </span>
                            <form method="POST" action="{{ route('panel.cash.destroy', ['tenant_slug' => $tenant->slug, 'id' => $tx->id]) }}"
                                  onsubmit="return confirm('Bu islemi silmek istediginizden emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-gray-400 hover:text-red-500">Sil</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Sag: Islem Ekle --}}
    <div class="space-y-4">

        {{-- Yeni Islem Formu --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Islem Ekle</h2>

            @if ($errors->any())
                <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('panel.cash.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-3">
                @csrf

                {{-- Gelir/Gider Toggle --}}
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center justify-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:border-green-500 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 transition">
                        <input type="radio" name="type" value="income" {{ old('type', 'income') === 'income' ? 'checked' : '' }} class="text-green-600">
                        <span class="text-sm font-medium text-green-700">Gelir</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:border-red-500 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 transition">
                        <input type="radio" name="type" value="expense" {{ old('type') === 'expense' ? 'checked' : '' }} class="text-red-600">
                        <span class="text-sm font-medium text-red-700">Gider</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tutar (TL)</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"
                           placeholder="0.00">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tarih</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="">Kategori secin</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->type === 'income' ? 'Gelir' : 'Gider' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Odeme Yontemi</label>
                    <select name="payment_method" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Nakit</option>
                        <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Kart</option>
                        <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Havale</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Sube</label>
                    <select name="branch_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Aciklama (opsiyonel)</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none"
                           placeholder="Islem aciklamasi...">
                </div>

                <button type="submit"
                        class="w-full bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    Kaydet
                </button>
            </form>
        </div>

        {{-- Kategori Ekle --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Kategori Ekle</h2>
            <form method="POST" action="{{ route('panel.cash.category.store', ['tenant_slug' => $tenant->slug]) }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Kategori adi" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                <select name="type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 outline-none">
                    <option value="income">Gelir kategorisi</option>
                    <option value="expense">Gider kategorisi</option>
                </select>
                <button type="submit"
                        class="w-full border border-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Kategori Ekle
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
