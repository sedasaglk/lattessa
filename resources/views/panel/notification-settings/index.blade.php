@extends('layouts.panel')
@section('title', 'Bildirim Ayarları')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Bildirim Ayarları</h1>
    <p class="text-sm text-gray-500 mt-1">Hangi olaylarda SMS/WhatsApp gönderilsin, mesaj şablonlarını özelleştirin.</p>
</div>

@if(!empty($migrationNeeded))
<div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
    <p class="font-medium mb-1">⚠️ Veritabanı tablosu henüz oluşturulmadı.</p>
    <p>cPanel → "Cron Jobs" bölümünden aşağıdaki komutu bir kez çalıştırın:</p>
    <code class="block mt-2 bg-amber-100 px-3 py-2 rounded font-mono text-xs">php {{ base_path('artisan') }} migrate --force</code>
    <p class="mt-2">Ardından sayfayı yenileyin.</p>
</div>
@else

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('panel.notification-settings.update', ['tenant_slug' => $tenant->slug]) }}">
    @csrf

    <div class="space-y-4">
        @foreach($settings as $event => $s)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="events[{{ $event }}][enabled]" value="1"
                                   {{ $s['enabled'] ? 'checked' : '' }}
                                   class="sr-only peer"
                                   onchange="toggleEvent('{{ $event }}', this.checked)">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                        </label>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $s['label'] }}</p>
                            <p class="text-xs text-gray-500">{{ $s['desc'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Kanal seçimi --}}
                <div id="channel-{{ $event }}" class="{{ !$s['enabled'] ? 'opacity-40 pointer-events-none' : '' }}">
                    <select name="events[{{ $event }}][channel]"
                            class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-gray-900 outline-none bg-white">
                        <option value="auto"    {{ $s['channel'] === 'auto'    ? 'selected' : '' }}>Otomatik (WA → SMS)</option>
                        <option value="whatsapp"{{ $s['channel'] === 'whatsapp'? 'selected' : '' }}>Yalnızca WhatsApp</option>
                        <option value="sms"     {{ $s['channel'] === 'sms'     ? 'selected' : '' }}>Yalnızca SMS</option>
                    </select>
                </div>
            </div>

            {{-- Şablon --}}
            <div id="template-{{ $event }}" class="{{ !$s['enabled'] ? 'opacity-40 pointer-events-none' : '' }}">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-medium text-gray-600">Mesaj Şablonu</label>
                    <button type="button" onclick="resetTemplate('{{ $event }}')"
                            class="text-xs text-indigo-600 hover:text-indigo-800">Varsayılanı Yükle</button>
                </div>
                <textarea name="events[{{ $event }}][template]"
                          id="tpl-{{ $event }}"
                          rows="3"
                          placeholder="Boş bırakırsanız varsayılan şablon kullanılır."
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-gray-900 outline-none resize-none font-mono">{{ $s['template'] }}</textarea>
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach($s['vars'] as $var)
                    <button type="button" onclick="insertVar('{{ $event }}', '{{ $var }}')"
                            class="text-xs px-2 py-0.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded font-mono transition">{{ $var }}</button>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-1">Değişkenlere tıklayarak ekleyebilirsiniz.</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
            Ayarları Kaydet
        </button>
    </div>
</form>

{{-- Varsayılan şablonlar JS'te --}}
<script>
var defaults = @json(collect($settings)->map(fn($s) => $s['default']));

function toggleEvent(event, enabled) {
    ['channel-' + event, 'template-' + event].forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('opacity-40', !enabled);
        el.classList.toggle('pointer-events-none', !enabled);
    });
}

function resetTemplate(event) {
    var ta = document.getElementById('tpl-' + event);
    if (ta && defaults[event]) ta.value = defaults[event];
}

function insertVar(event, varName) {
    var ta = document.getElementById('tpl-' + event);
    if (!ta) return;
    var start = ta.selectionStart;
    var end = ta.selectionEnd;
    ta.value = ta.value.substring(0, start) + varName + ta.value.substring(end);
    ta.selectionStart = ta.selectionEnd = start + varName.length;
    ta.focus();
}
</script>
@endif
@endsection
