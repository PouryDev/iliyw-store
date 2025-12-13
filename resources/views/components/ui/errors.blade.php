@if ($errors->any())
    <div class="rounded-lg border border-rose-500/50 bg-rose-500/10 p-3 mb-4">
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-rose-400 mb-1">{{ $title ?? 'خطاهای اعتبارسنجی:' }}</h3>
                <ul class="text-xs text-rose-300 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

