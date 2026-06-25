@if(session('success'))
    <div class="alert-success mb-4 flex items-center gap-2">
        <span>✓</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert-error mb-4 flex items-center gap-2">
        <span>✕</span> {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="alert-error mb-4">
        <ul class="space-y-1">
            @foreach($errors->all() as $error)
                <li class="flex items-center gap-2"><span>✕</span> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
