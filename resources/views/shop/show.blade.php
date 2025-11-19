<x-layouts.app :title="$product->title">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="self-start">
            @if($product->images->count())
                <div class="group rounded-2xl overflow-hidden border border-white/10">
                    <img id="product-main-image" src="{{ $product->images->first()->url }}" class="w-full aspect-square object-cover transition group-hover:scale-[1.01]" />
                </div>
                <div class="mt-3">
                    <div id="product-thumbs" class="flex flex-wrap gap-2 py-1">
                        @foreach($product->images as $idx => $img)
                            <button type="button" data-src="{{ $img->url }}" class="thumb-item w-16 h-16 rounded-lg overflow-hidden border {{ $idx === 0 ? 'border-cherry-500/50' : 'border-white/10' }} hover:border-cherry-500/40 transition focus:outline-none focus:ring-2 focus:ring-cherry-600/30">
                                <img src="{{ $img->url }}" class="w-full h-full object-cover" />
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="rounded-2xl aspect-square bg-white/5 flex items-center justify-center text-6xl">💍</div>
            @endif
        </div>
        <div class="self-start">
            <div class="flex items-start justify-between gap-4">
                <h1 class="text-3xl font-extrabold">{{ $product->title }}</h1>
                @if($product->stock > 0)
                    <span class="inline-flex items-center gap-1 text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md text-xs">موجود</span>
                @else
                    <span class="inline-flex items-center gap-1 text-rose-300 bg-rose-500/10 border border-rose-500/20 px-2 py-0.5 rounded-md text-xs">ناموجود</span>
                @endif
            </div>
            <div class="text-cherry-400 font-extrabold my-3 text-2xl">
                <span id="product-price">{{ number_format($product->price) }}</span> <span class="text-sm">تومان</span>
                <div id="variant-price-display" class="hidden text-lg mt-1">
                    <span class="text-gray-400 line-through" id="original-price">{{ number_format($product->price) }}</span>
                    <span class="text-cherry-400" id="variant-price-value"></span> <span class="text-sm">تومان</span>
                </div>
            </div>
            <p class="mb-6 leading-7 text-gray-200">{{ $product->description }}</p>
            
            @if($product->has_variants)
                <div class="mb-6 space-y-4">
                    @if($product->has_colors && $product->available_colors->count() > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">رنگ:</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->available_colors as $color)
                                    <button type="button" 
                                            data-color-id="{{ $color->id }}" 
                                            data-color-name="{{ $color->name }}"
                                            class="color-option flex items-center gap-2 px-3 py-2 rounded-lg border border-white/10 hover:border-cherry-500/50 transition {{ $loop->first ? 'border-cherry-500/50 bg-cherry-500/10' : '' }}">
                                        @if($color->hex_code)
                                            <div class="w-4 h-4 rounded-full border border-white/20" style="background-color: {{ $color->hex_code }}"></div>
                                        @endif
                                        <span class="text-sm">{{ $color->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if($product->has_sizes && $product->available_sizes->count() > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">سایز:</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->available_sizes as $size)
                                    <button type="button" 
                                            data-size-id="{{ $size->id }}" 
                                            data-size-name="{{ $size->name }}"
                                            class="size-option px-3 py-2 rounded-lg border border-white/10 hover:border-cherry-500/50 transition {{ $loop->first ? 'border-cherry-500/50 bg-cherry-500/10' : '' }}">
                                        <span class="text-sm">{{ $size->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div id="variant-info" class="text-sm text-gray-400">
                        <div id="variant-stock" class="mb-1">
                            موجودی: <span class="text-green-300">{{ $product->total_stock }}</span>
                        </div>
                    </div>
                </div>
            @endif
            
            <form method="post" action="{{ route('cart.add',$product) }}" class="flex flex-wrap items-center gap-3">
                @csrf
                <input type="hidden" name="color_id" id="selected-color-id" value="">
                <input type="hidden" name="size_id" id="selected-size-id" value="">
                <x-ui.input type="number" name="quantity" label="تعداد" value="1" min="1" class="w-28" />
                <x-ui.button type="submit" size="lg" id="add-to-cart-btn">افزودن به سبد</x-ui.button>
            </form>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-xl border border-white/10 bg-white/5 p-3 text-sm">
                    ارسال سریع به سراسر کشور
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3 text-sm">
                    ضمانت کیفیت و اصالت کالا
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3 text-sm">
                    ارسال سه روزه به هرکجای کشور 
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function(){
      const main = document.getElementById('product-main-image');
      const strip = document.getElementById('product-thumbs');
      if(!main || !strip) return;
      strip.addEventListener('click', function(e){
        const btn = e.target.closest('.thumb-item');
        if(!btn) return;
        const src = btn.getAttribute('data-src');
        if(!src) return;
        // swap image with a quick fade
        main.style.opacity = '0.85';
        const prevActive = strip.querySelector('.thumb-item.border-cherry-500\\/50');
        if(prevActive){ prevActive.classList.remove('border-cherry-500/50'); prevActive.classList.add('border-white/10'); }
        btn.classList.remove('border-white/10');
        btn.classList.add('border-cherry-500/50');
        const img = new Image();
        img.onload = () => {
          main.src = src;
          main.style.transition = 'opacity .18s ease';
          requestAnimationFrame(()=>{ main.style.opacity = '1'; });
        };
        img.src = src;
      });
    })();
    
    @if($product->has_variants)
    // Variant selection logic
    const productVariants = @json($product->activeVariants);
    const colorOptions = document.querySelectorAll('.color-option');
    const sizeOptions = document.querySelectorAll('.size-option');
    const selectedColorId = document.getElementById('selected-color-id');
    const selectedSizeId = document.getElementById('selected-size-id');
    const variantStock = document.getElementById('variant-stock');
    const productPrice = document.getElementById('product-price');
    const variantPriceDisplay = document.getElementById('variant-price-display');
    const originalPrice = document.getElementById('original-price');
    const variantPriceValue = document.getElementById('variant-price-value');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    
    let selectedColor = null;
    let selectedSize = null;
    
    // Initialize with first selections
    if (colorOptions.length > 0) {
        selectedColor = colorOptions[0].dataset.colorId;
        selectedColorId.value = selectedColor;
    }
    if (sizeOptions.length > 0) {
        selectedSize = sizeOptions[0].dataset.sizeId;
        selectedSizeId.value = selectedSize;
    }
    
    // Color selection
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all color options
            colorOptions.forEach(opt => {
                opt.classList.remove('border-cherry-500/50', 'bg-cherry-500/10');
                opt.classList.add('border-white/10');
            });
            
            // Add active class to selected option
            this.classList.remove('border-white/10');
            this.classList.add('border-cherry-500/50', 'bg-cherry-500/10');
            
            selectedColor = this.dataset.colorId;
            selectedColorId.value = selectedColor;
            updateVariantInfo();
        });
    });
    
    // Size selection
    sizeOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all size options
            sizeOptions.forEach(opt => {
                opt.classList.remove('border-cherry-500/50', 'bg-cherry-500/10');
                opt.classList.add('border-white/10');
            });
            
            // Add active class to selected option
            this.classList.remove('border-white/10');
            this.classList.add('border-cherry-500/50', 'bg-cherry-500/10');
            
            selectedSize = this.dataset.sizeId;
            selectedSizeId.value = selectedSize;
            updateVariantInfo();
        });
    });
    
    function updateVariantInfo() {
        // Find matching variant
        const variant = productVariants.find(v => {
            const colorMatch = !selectedColor || v.color_id == selectedColor;
            const sizeMatch = !selectedSize || v.size_id == selectedSize;
            return colorMatch && sizeMatch;
        });
        
        if (variant) {
            // Update stock
            const stockElement = variantStock.querySelector('span');
            stockElement.textContent = variant.stock;
            stockElement.className = variant.stock > 0 ? 'text-green-300' : 'text-red-300';
            
            // Update price display
            if (variant.price && variant.price !== {{ $product->price }}) {
                // Show variant price with original price crossed out
                variantPriceDisplay.classList.remove('hidden');
                originalPrice.textContent = new Intl.NumberFormat('fa-IR').format({{ $product->price }}) + ' تومان';
                variantPriceValue.textContent = new Intl.NumberFormat('fa-IR').format(variant.price) + ' تومان';
                productPrice.textContent = new Intl.NumberFormat('fa-IR').format(variant.price);
            } else {
                // Show only original price
                variantPriceDisplay.classList.add('hidden');
                productPrice.textContent = new Intl.NumberFormat('fa-IR').format({{ $product->price }});
            }
            
            // Enable/disable add to cart button
            if (variant.stock > 0) {
                addToCartBtn.disabled = false;
                addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                addToCartBtn.disabled = true;
                addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        } else {
            // No variant found
            variantStock.querySelector('span').textContent = '0';
            variantStock.querySelector('span').className = 'text-red-300';
            variantPriceDisplay.classList.add('hidden');
            productPrice.textContent = new Intl.NumberFormat('fa-IR').format({{ $product->price }});
            addToCartBtn.disabled = true;
            addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
    
    // Initial update
    updateVariantInfo();
    @endif
    </script>
    @endpush
</x-layouts.app>


