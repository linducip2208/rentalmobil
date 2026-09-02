@if($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-center gap-2">
        @if($paginator->onFirstPage())
            <span class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-slate-300" aria-hidden="true">&larr;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-300 bg-white font-bold text-slate-700 transition hover:bg-slate-50" aria-label="Halaman sebelumnya">&larr;</a>
        @endif

        @foreach($elements as $element)
            @if(is_string($element))
                <span class="px-2 text-slate-400">{{ $element }}</span>
            @endif
            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span aria-current="page" class="grid h-10 w-10 place-items-center rounded-lg bg-fleet-950 font-extrabold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-300 bg-white font-bold text-slate-700 transition hover:bg-slate-50">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-300 bg-white font-bold text-slate-700 transition hover:bg-slate-50" aria-label="Halaman berikutnya">&rarr;</a>
        @else
            <span class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-slate-300" aria-hidden="true">&rarr;</span>
        @endif
    </nav>
@endif
