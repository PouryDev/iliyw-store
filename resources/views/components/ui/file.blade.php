@props([
    'label' => null,
    'name' => null,
    'multiple' => false,
    'accept' => null,
])

<div class="space-y-2">
    @if($label)
        <label for="{{ $name }}" class="block text-xs text-gray-300">{{ $label }}</label>
    @endif
    <div class="relative">
        <input id="{{ $name }}" name="{{ $name }}" type="file" @if($multiple) multiple @endif @if($accept) accept="{{ $accept }}" @endif {{ $attributes->merge(['class'=>'file:mr-4 file:py-2.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:bg-cherry-600 file:text-white hover:file:bg-cherry-700 w-full rounded-lg bg-white/5 border border-white/10 focus:border-cherry-600 focus:ring-2 focus:ring-cherry-600/30 outline-none py-2 px-3 text-sm transition']) }}>
    </div>
    <div id="preview-{{ Str::slug($name,'-') }}" class="grid grid-cols-3 gap-2"></div>
    @error($name)
        <div class="text-xs text-rose-400">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const input = document.getElementById(@json($name));
  if(!input) return;
  const preview = document.getElementById('preview-{{ Str::slug($name,'-') }}');
  if(!preview) return;
  input.addEventListener('change', function(){
    preview.innerHTML = '';
    const files = Array.from(input.files || []);
    files.forEach(file => {
      if(!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'w-full aspect-square object-cover rounded-lg border border-white/10';
        preview.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  });
});
</script>
@endpush


