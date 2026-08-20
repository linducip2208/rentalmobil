@props(['file', 'label', 'icon' => '🚗', 'tone' => 'blue'])
@php $exists = file_exists(public_path('marketing/screens/'.$file)); @endphp
@if($exists)
    <img src="{{ asset('marketing/screens/'.$file) }}" alt="Tampilan nyata {{ $label }}" width="1440" height="900" loading="lazy" class="block aspect-[4/3] w-full object-cover object-top">
@else
    <div {{ $attributes->class(['aspect-[4/3] flex items-center justify-center p-8 bg-slate-100']) }}>
        <div class="text-center"><div class="mb-4 text-7xl">{{ $icon }}</div><div class="font-bold text-slate-700">{{ $label }}</div><div class="mt-1 text-sm text-slate-400">Jalankan npm run screenshots</div></div>
    </div>
@endif
