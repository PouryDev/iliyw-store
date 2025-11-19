@foreach($products as $index => $product)
    @php 
        $img = optional($product->images->first())->url ?? null;
        $campaign = $product->best_campaign;
        $hasCampaign = $campaign && $campaign->isActive();
        $originalPrice = $product->price;
        $campaignPrice = $product->campaign_price;
        $discountAmount = $product->campaign_discount_amount;
    @endphp

    <div class="product-card group relative rounded-xl overflow-hidden border border-white/10 bg-white/5 {{ $hasCampaign ? 'border-cherry-500/30' : '' }} opacity-0 translate-y-4 transition-all duration-500 ease-out"
         style="animation-delay: {{ $index * 100 }}ms">
        @if($hasCampaign && $campaign->badge_text)
            <div class="absolute top-2 left-2 z-20">
                <span class="bg-cherry-500 text-white px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                    {{ $campaign->badge_text }}
                </span>
            </div>
        @endif
        <a href="{{ route('shop.show', $product) }}" class="block z-0">
            @if($img)
                <img src="{{ $img }}" alt="{{ $product->title }}" class="w-full aspect-square object-cover transition group-hover:scale-[1.03] duration-300" />
            @else
                <div class="w-full aspect-square bg-white/5 flex items-center justify-center text-4xl">🪞</div>
            @endif
            <div class="p-2 md:p-3 pointer-events-none">
                <div class="font-semibold text-sm md:text-base truncate">{{ $product->title }}</div>

                <!-- Price Section - Stacked on mobile -->
                <div class="mt-1 space-y-1">
                    @if($hasCampaign)
                        <div class="flex flex-col md:flex-row md:items-center md:gap-2">
                            <div class="text-gray-400 text-xs line-through">{{ number_format($originalPrice) }}</div>
                            <div class="text-cherry-400 text-sm font-semibold">{{ number_format($campaignPrice) }} تومان</div>
                        </div>
                        @if($discountAmount > 0)
                            <div class="text-xs text-green-400">
                                {{ number_format($discountAmount) }} تومان تخفیف
                            </div>
                        @endif
                    @else
                        <div class="text-cherry-400 text-sm font-semibold">{{ number_format($originalPrice) }} تومان</div>
                    @endif
                </div>
            </div>
        </a>
        <div class="absolute left-2 right-2 bottom-2 md:left-3 md:right-3 md:bottom-3 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 z-10">
            <button data-add-to-cart data-url="{{ route('cart.add',$product) }}" data-qty="1" class="w-full bg-cherry-600/90 hover:bg-cherry-700 text-white rounded-md py-1.5 text-xs shadow-lg backdrop-blur transition-colors duration-200">افزودن به سبد</button>
        </div>
    </div>
@endforeach
