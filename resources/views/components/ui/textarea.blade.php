@props([
    'label' => null,
    'name' => null,
    'rows' => 4,
    'placeholder' => null,
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-xs text-gray-300">{{ $label }}</label>
    @endif
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}" {{ $attributes->merge(['class'=>'w-full rounded-lg bg-white/5 border border-white/10 focus:border-cherry-600 focus:ring-2 focus:ring-cherry-600/30 outline-none py-2.5 px-3 text-sm transition']) }}>{{ $slot }}</textarea>
    @error($name)
        <div class="text-xs text-rose-400">{{ $message }}</div>
    @enderror
</div>


