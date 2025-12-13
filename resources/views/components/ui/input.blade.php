@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => null,
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-xs text-gray-300">{{ $label }}</label>
    @endif
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" placeholder="{{ $placeholder }}" {{ $attributes->merge(['class'=>'w-full rounded-lg bg-white/5 border border-white/10 focus:border-cherry-600 focus:ring-2 focus:ring-cherry-600/30 outline-none py-2.5 px-3 text-sm transition font-[inherit]']) }}>
    @error($name)
        <div class="text-xs text-rose-400">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const el = document.getElementById(@json($name));
  if(!el) return;
  if(el.type === 'number'){
    const normalizeDigits = (val)=>{
      if(typeof val !== 'string') val = String(val ?? '');
      const map = {'۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9','٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
      return val.replace(/[۰-۹٠-٩]/g, d => map[d] || d);
    };
    ['input','change','blur','paste'].forEach(evt => {
      el.addEventListener(evt, ()=>{ el.value = normalizeDigits(el.value); });
    });
    el.style.fontFeatureSettings = 'lnum';
  }
});
</script>
@endpush


