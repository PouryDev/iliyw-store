@props([
    'label' => null,
    'name' => null,
    'checked' => false,
])

<label class="inline-flex items-center gap-2 text-sm text-gray-300 select-none">
    <input type="checkbox" name="{{ $name }}" @checked($checked) {{ $attributes->merge(['class'=>'rounded border-white/20 bg-white/10 text-cherry-600 focus:ring-cherry-600/40']) }}>
    @if($label)
        <span>{{ $label }}</span>
    @endif
</label>


