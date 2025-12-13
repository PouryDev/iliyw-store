import './bootstrap';
import { apiRequest } from './utils/api';

// Toast minimal
window.showToast = function(message, type = 'success'){
  const el = document.createElement('div');
  el.className = `fixed left-1/2 -translate-x-1/2 bottom-4 z-[9999] px-4 py-2 rounded-lg text-sm text-white ${type==='error'?'bg-red-600':'bg-cherry-600'} shadow-lg anim-fade-up`;
  el.textContent = message;
  document.body.appendChild(el);
  setTimeout(()=>{ el.remove(); }, 2500);
}

// AJAX add to cart
document.addEventListener('click', async (e) => {
  const addBtn = e.target.closest('[data-add-to-cart]');
  if (addBtn) {
    e.preventDefault();
    const url = addBtn.getAttribute('data-url');
    const quantity = addBtn.getAttribute('data-qty') || 1;
    try {
      const res = await apiRequest(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ quantity })
      });
      const data = await res.json();
      updateCartUI(data);
      showToast('به سبد افزوده شد');
    } catch (err) {
      console.error('Add to cart error:', err);
      showToast('خطا در افزودن به سبد', 'error');
    }
  }
});

function updateCartUI(payload){
  const badge = document.querySelector('[data-cart-count]');
  if (badge) {
    const count = Number(payload.count) || 0;
    badge.textContent = count > 0 ? String(count) : '';
    // Hide badge entirely if zero
    if (count === 0) {
      badge.classList.add('hidden');
    } else {
      badge.classList.remove('hidden');
    }
  }
  const dropdown = document.getElementById('cartDropdownList');
  if (dropdown) {
    dropdown.innerHTML = '';
    if ((payload.items||[]).length === 0){
      dropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-300">سبد خالی است.</div>';
    } else {
      payload.items.forEach(it => {
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between px-3 py-2 text-sm border-b border-white/10';
        row.innerHTML = `<div class="truncate">${it.title}</div><div class="text-cherry-400">${new Intl.NumberFormat('fa-IR').format(it.total)} تومان</div>`;
        dropdown.appendChild(row);
      });
      const footer = document.createElement('div');
      footer.className = 'px-3 py-2 text-sm text-right text-cherry-400';
      footer.textContent = `جمع: ${new Intl.NumberFormat('fa-IR').format(payload.total)} تومان`;
      dropdown.appendChild(footer);
    }
  }
}

// Initialize cart dropdown on load
window.addEventListener('DOMContentLoaded', async ()=>{
  try {
    const res = await apiRequest('/api/cart/json');
    const data = await res.json();
    updateCartUI(data);
  } catch (err) {
    console.error('Failed to load cart:', err);
  }
});

// Note: cart:update event is now handled by CartContext to avoid duplicate requests

// Live search for products (home page)
document.addEventListener('input', async (e)=>{
  const input = e.target.closest('input[name="q"]');
  if (!input) return;
  const q = input.value;
  const url = new URL(window.location.href);
  url.searchParams.set('q', q);
  try {
    const res = await apiRequest(url.toString());
    const html = await res.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const grid = doc.querySelector('.shop-grid');
    if (grid) {
      const currentGrid = document.querySelector('.shop-grid');
      if (currentGrid) currentGrid.replaceWith(grid);
    }
  } catch (err) {
    console.error('Live search error:', err);
  }
});
