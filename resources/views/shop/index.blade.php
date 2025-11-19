<x-layouts.app :title="'ایلی استور - اکسسوری‌های خاص و ترند'">
    <!-- Simple Search -->
    <form class="w-full" id="search-form">
        <input name="q" value="{{ request('q') }}" placeholder="جستجوی محصول" class="w-full bg-white/5 border border-white/10 rounded-lg py-2.5 px-3 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-600" id="search-input" />
    </form>

    <!-- Campaigns Section -->
    @if($activeCampaigns->count() > 0)
        <section class="mt-6 campaigns-section">
            @foreach($activeCampaigns as $campaign)
                @if($campaign->banner_image)
                    <div class="relative rounded-xl overflow-hidden border border-cherry-500/20 bg-gradient-to-r from-cherry-500/10 to-violet-500/10 mb-4">
                        <img src="{{ asset('storage/' . $campaign->banner_image) }}" alt="{{ $campaign->name }}" class="w-full h-24 md:h-32 lg:h-40 object-cover">
                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                            <div class="text-center text-white px-4">
                                <h2 class="text-lg md:text-xl lg:text-2xl font-bold mb-2">{{ $campaign->name }}</h2>
                                @if($campaign->badge_text)
                                    <span class="inline-block bg-cherry-500 text-white px-2 md:px-3 py-1 rounded-full text-xs md:text-sm font-semibold">
                                        {{ $campaign->badge_text }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-cherry-500/20 bg-gradient-to-r from-cherry-500/10 to-violet-500/10 p-3 md:p-4 mb-4">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-2">
                            <div class="flex-1">
                                <h2 class="text-base md:text-lg font-bold text-cherry-300">{{ $campaign->name }}</h2>
                                @if($campaign->description)
                                    <p class="text-xs md:text-sm text-gray-300 mt-1 line-clamp-2">{{ $campaign->description }}</p>
                                @endif
                            </div>
                            @if($campaign->badge_text)
                                <span class="bg-cherry-500 text-white px-2 md:px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                                    {{ $campaign->badge_text }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </section>
    @endif

    <!-- Loading Indicator -->
    <div id="search-loading" class="hidden text-center py-8">
        <div class="inline-flex items-center gap-2 text-gray-400">
            <div class="animate-spin w-5 h-5 border-2 border-gray-400 border-t-transparent rounded-full"></div>
            در حال جستجو...
        </div>
    </div>

    <!-- Products Grid -->
    @if($products->isEmpty())
        <div class="mt-8 rounded-xl border border-white/10 bg-white/5 p-8 text-center text-gray-300">
            محصولی پیدا نشد. جستجو را تغییر دهید یا به زودی سر بزنید.
        </div>
    @else
        <div class="shop-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5 mt-6" id="products-grid">
            @include('shop.partials.products')
        </div>

        <!-- Load More Button (for infinite scroll) -->
        <div class="mt-8 text-center" id="load-more-container">
            @if($products->hasPages())
                <button id="load-more-btn" class="bg-cherry-600 hover:bg-cherry-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                    <span class="load-more-text">مشاهده محصولات بیشتر</span>
                    <span class="load-more-spinner hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        در حال بارگذاری...
                    </span>
                </button>
            @endif
        </div>
    @endif

    <!-- Hidden pagination for infinite scroll -->
    <div class="hidden">{{ $products->links() }}</div>
</x-layouts.app>

<!-- JavaScript for animations and infinite scroll -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit to ensure DOM is fully loaded
    setTimeout(function() {
        let isLoading = false;
        let hasMorePages = true;
        let currentPage = 1;
        let currentSearchQuery = '';

        // Get DOM elements first
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const loadMoreBtn = document.getElementById('load-more-btn');
        const productsGrid = document.getElementById('products-grid') || document.querySelector('.shop-grid');
        const loadMoreSpinner = document.querySelector('.load-more-spinner');
        const loadMoreText = document.querySelector('.load-more-text');
        
        console.log('DOM elements found:', {
            searchInput: !!searchInput,
            searchForm: !!searchForm,
            loadMoreBtn: !!loadMoreBtn,
            productsGrid: !!productsGrid,
            loadMoreSpinner: !!loadMoreSpinner,
            loadMoreText: !!loadMoreText
        });
    
    // Animate product cards on load (after getting productsGrid)
    if (productsGrid) {
        animateProductCards();
    }
    
    // Initialize hasMorePages based on current pagination
    const nextLink = document.querySelector('.pagination a[rel="next"]');
    hasMorePages = nextLink !== null;
    
    // Initialize current search query from URL
    const urlParams = new URLSearchParams(window.location.search);
    currentSearchQuery = urlParams.get('q') || '';
    if (searchInput) {
        searchInput.value = currentSearchQuery;
    }
    let searchTimeout;

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearchQuery = this.value;
                currentPage = 1;
                hasMorePages = true;
                console.log('Search triggered:', currentSearchQuery);
                performSearch();
            }, 500); // 500ms delay
        });

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            currentSearchQuery = searchInput.value;
            currentPage = 1;
            hasMorePages = true;
            console.log('Form submit triggered:', currentSearchQuery);
            performSearch();
            return false;
        });
    }

    // Infinite scroll functionality

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMoreProducts);
    }

    // Intersection Observer for infinite scroll
    const infiniteScrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !isLoading && hasMorePages) {
                console.log('Load more triggered by intersection observer');
                loadMoreProducts();
            }
        });
    }, { threshold: 0.1 });

    if (loadMoreBtn) {
        infiniteScrollObserver.observe(loadMoreBtn);
    }

    function animateProductCards() {
        const productCards = document.querySelectorAll('.product-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        productCards.forEach(card => {
            observer.observe(card);
        });
    }

    function performSearch() {
        if (isLoading) return;
        
        if (!productsGrid) {
            console.error('productsGrid not found');
            return;
        }
        
        isLoading = true;
        showLoadingState();

        // Build API URL
        const apiUrl = window.location.origin + '/api/search';
        const params = new URLSearchParams();
        
        if (currentSearchQuery) {
            params.set('q', currentSearchQuery);
        }
        
        if (currentPage > 1) {
            params.set('page', currentPage);
        }
        
        const url = apiUrl + '?' + params.toString();
        
        console.log('Fetching URL:', url);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status, 'ok:', response.ok);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            
            if (!data.success) {
                throw new Error('API returned error');
            }
            
            if (currentPage === 1) {
                // Replace products grid for new search
                productsGrid.innerHTML = data.html;
                
                // Update hasMorePages
                hasMorePages = data.hasMorePages;
                if (hasMorePages) {
                    document.getElementById('load-more-container').style.display = 'block';
                } else {
                    document.getElementById('load-more-container').style.display = 'none';
                }

                // Update URL without page refresh
                let newUrl = window.location.href.split('?')[0];
                const newParams = new URLSearchParams();
                if (currentSearchQuery) {
                    newParams.set('q', currentSearchQuery);
                }
                if (newParams.toString()) {
                    newUrl += '?' + newParams.toString();
                }
                window.history.pushState({}, '', newUrl);
                
                // Animate new products
                animateProductCards();
            } else {
                // Append products for infinite scroll
                const parser = new DOMParser();
                const doc = parser.parseFromString(data.html, 'text/html');
                const newProducts = doc.querySelectorAll('.product-card');
                
                console.log('Appending', newProducts.length, 'products for infinite scroll');
                newProducts.forEach((product, index) => {
                    product.style.opacity = '0';
                    product.style.transform = 'translateY(20px)';
                    productsGrid.appendChild(product);
                    
                    setTimeout(() => {
                        product.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                        product.style.opacity = '1';
                        product.style.transform = 'translateY(0)';
                    }, index * 50);
                });

                // Update hasMorePages
                hasMorePages = data.hasMorePages;
                if (!hasMorePages) {
                    document.getElementById('load-more-container').style.display = 'none';
                    console.log('No more pages');
                } else {
                    console.log('More pages available');
                }
            }
            
            if (currentPage === 1) {
                currentPage = 2; // Next page for infinite scroll
            } else {
                currentPage++;
            }
            isLoading = false;
            hideLoadingState();
        })
        .catch(error => {
            console.error('Error performing search:', error);
            isLoading = false;
            hideLoadingState();
            
            // Show error message instead of reloading
            if (productsGrid) {
                productsGrid.innerHTML = '<div class="col-span-full text-center py-8 text-red-400">خطا در جستجو. لطفاً دوباره تلاش کنید.</div>';
            }
        });
    }

    function loadMoreProducts() {
        console.log('loadMoreProducts called, isLoading:', isLoading, 'hasMorePages:', hasMorePages, 'currentPage:', currentPage);
        if (isLoading || !hasMorePages) return;
        performSearch();
    }

    function showLoadingState() {
        const searchLoading = document.getElementById('search-loading');
        if (currentPage === 1 && searchLoading) {
            searchLoading.classList.remove('hidden');
        }
        
        if (loadMoreBtn) {
            loadMoreBtn.disabled = true;
            loadMoreSpinner.classList.remove('hidden');
            loadMoreText.classList.add('hidden');
        }
    }

    function hideLoadingState() {
        const searchLoading = document.getElementById('search-loading');
        if (searchLoading) {
            searchLoading.classList.add('hidden');
        }
        
        if (loadMoreBtn) {
            loadMoreBtn.disabled = false;
            loadMoreSpinner.classList.add('hidden');
            loadMoreText.classList.remove('hidden');
        }
    }
    }, 100); // 100ms delay to ensure DOM is ready
});
</script>

<style>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* RTL pagination fix */
.pagination {
    direction: ltr;
}

.pagination .page-item:first-child .page-link {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.pagination .page-item:last-child .page-link {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

/* Product card hover effects */
.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

/* Responsive improvements */
@media (max-width: 640px) {
    .product-card {
        border-radius: 0.75rem;
    }
    
    .product-card img {
        border-radius: 0.75rem 0.75rem 0 0;
    }
}
</style>


