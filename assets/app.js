import './stimulus_bootstrap.js';
import './styles/app.css';
import '@hotwired/turbo';

// Cart management: localStorage as cache, session as source of truth
const Cart = {
    get() {
        return JSON.parse(localStorage.getItem('surpreenda_cart') || '[]');
    },
    add(item) {
        const cart = this.get();
        const existing = cart.find(c => c.id === item.id);
        if (existing) {
            existing.qty = (existing.qty || 1) + (item.qty || 1);
        } else {
            cart.push({ ...item, qty: item.qty || 1 });
        }
        localStorage.setItem('surpreenda_cart', JSON.stringify(cart));
        this.updateBadge();
        return cart;
    },
    remove(id) {
        const cart = this.get().filter(c => c.id !== id);
        localStorage.setItem('surpreenda_cart', JSON.stringify(cart));
        this.updateBadge();
    },
    count() {
        return this.get().reduce((sum, item) => sum + (item.qty || 0), 0);
    },
    updateBadge() {
        const badge = document.getElementById('cart-badge');
        const liveRegion = document.getElementById('cart-live-region');
        if (badge) {
            const count = this.count();
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
            badge.animate([
                { transform: 'scale(0)' },
                { transform: 'scale(1.3)', offset: 0.6 },
                { transform: 'scale(1)' }
            ], { duration: 200, easing: 'ease-out' });
        }
        if (liveRegion) {
            liveRegion.textContent = count > 0
                ? `Carrinho atualizado: ${count} ${count === 1 ? 'item' : 'itens'}`
                : 'Carrinho vazio';
        }
    },
    syncFromServer(count) {
        localStorage.setItem('surpreenda_cart_count', count);
        this.updateBadge();
    },
    updateQuantityLocal(id, qty) {
        const cart = this.get();
        const item = cart.find(c => c.id === id);
        if (item) {
            item.qty = qty;
        }
        localStorage.setItem('surpreenda_cart', JSON.stringify(cart));
        this.updateBadge();
    },
    async syncToServer() {
        const localCart = this.get();
        if (!localCart.length) {
            await this.fetchCount();
            return;
        }
        try {
            const response = await fetch('/api/cart/sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ items: localCart }),
            });
            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('surpreenda_cart_count', data.count);
                this.updateBadge();
            }
        } catch {
            // Silently fail — localStorage is the fallback
        }
    },
    async addToServer(id, type = 'kit', qty = 1) {
        try {
            const response = await fetch('/api/cart/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, type, qty }),
            });
            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('surpreenda_cart_count', data.count);
                this.updateBadge();
                return true;
            }
        } catch {
            // Fallback to localStorage
            this.add({ id, type, qty });
            return true;
        }
        return false;
    },
    async removeFromServer(id) {
        try {
            const response = await fetch(`/api/cart/remove/${encodeURIComponent(id)}`, {
                method: 'POST',
            });
            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('surpreenda_cart_count', data.count);
                this.updateBadge();
            }
        } catch {
            this.remove(id);
        }
    },
    async updateOnServer(id, qty) {
        try {
            const response = await fetch(`/api/cart/update/${encodeURIComponent(id)}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ qty }),
            });
            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('surpreenda_cart_count', data.count);
                this.updateBadge();
            }
        } catch {
            if (qty <= 0) {
                this.remove(id);
            }
        }
    },
    async clearServer() {
        try {
            await fetch('/api/cart/clear', { method: 'POST' });
        } catch {
            // Ignore
        }
        localStorage.removeItem('surpreenda_cart');
        this.updateBadge();
    },
    async fetchCount() {
        try {
            const response = await fetch('/api/cart/count');
            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('surpreenda_cart_count', data.count);
            }
        } catch {
            // Ignore
        }
    }
};

// Toast notifications
const TOAST_ICONS = {
    success: `<svg class="toast-icon" viewBox="0 0 20 20" fill="var(--color-green)" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-10.293a1 1 0 00-1.414-1.414L9 9.586 7.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>`,
    error: `<svg class="toast-icon" viewBox="0 0 20 20" fill="var(--color-red-error)" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>`,
    warning: `<svg class="toast-icon" viewBox="0 0 20 20" fill="var(--color-orange)" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`,
    info: `<svg class="toast-icon" viewBox="0 0 20 20" fill="var(--color-green-sage)" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 019 9zm0 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>`
};

function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const isPermanent = type === 'error';
    const timer = isPermanent ? 0 : duration;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', isPermanent ? 'alert' : 'status');
    toast.innerHTML = `
        ${TOAST_ICONS[type] || TOAST_ICONS.info}
        <div class="toast-body">
            <span class="toast-message">${message}</span>
        </div>
        <button class="toast-close" aria-label="Fechar notificação">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4.646 4.646a.5.5 0 01.708 0L8 7.293l2.646-2.647a.5.5 0 01.708.708L8.707 8l2.647 2.646a.5.5 0 01-.708.708L8 8.707l-2.646 2.647a.5.5 0 01-.708-.708L7.293 8 4.646 5.354a.5.5 0 010-.708z"/></svg>
        </button>
        ${timer > 0 ? `<div class="toast-progress" style="animation-duration:${timer}ms"></div>` : ''}
    `;

    container.appendChild(toast);

    const dismissToast = () => {
        toast.style.animation = 'toast-out 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    };

    toast.querySelector('.toast-close').addEventListener('click', dismissToast);

    if (timer > 0) {
        setTimeout(dismissToast, timer);
    }
}

// Read flash data from hidden div and convert to toasts
function processFlashMessages() {
    const el = document.getElementById('flash-data');
    if (!el) return;
    try {
        const flashes = JSON.parse(el.dataset.flashes || '[]');
        flashes.forEach(f => {
            const type = f.type === 'danger' ? 'error' : f.type;
            showToast(f.message, type);
        });
    } catch { /* Silently ignore malformed flash data */ }
}

// Keyboard navigation for dropdown
function initKeyboardDropdown() {
    document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
        const content = dropdown.querySelector('.nav-dropdown-content');
        const items = content.querySelectorAll('.nav-dropdown-item');

        toggle.addEventListener('keydown', e => {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                if (!expanded) {
                    dropdown.focus();
                }
            }
        });

        dropdown.addEventListener('focus', () => {
            toggle.setAttribute('aria-expanded', 'true');
            content.style.display = 'flex';
            content.style.opacity = '1';
            content.style.transform = 'translateY(0)';
        });

        dropdown.addEventListener('blur', e => {
            if (!dropdown.contains(e.relatedTarget)) {
                toggle.setAttribute('aria-expanded', 'false');
                content.style.opacity = '0';
                content.style.transform = 'translateY(-4px)';
                setTimeout(() => { content.style.display = 'none'; }, 200);
            }
        });

        items.forEach((item, index) => {
            item.addEventListener('keydown', e => {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = items[(index + 1) % items.length];
                    next.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = items[(index - 1 + items.length) % items.length];
                    prev.focus();
                } else if (e.key === 'Escape') {
                    toggle.focus();
                }
            });
        });
    });
}

// Add to cart buttons
function initAddToCartButtons() {
    document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.dataset.addToCart;
            const type = btn.dataset.itemType || 'kit';
            const name = btn.dataset.itemName || 'Item';
            const qty = parseInt(btn.dataset.quantity) || 1;

            btn.classList.add('adding');
            if ('disabled' in btn) btn.disabled = true;

            // Update localStorage cache immediately
            Cart.add({ id, type, name, qty });
            // Sync to server
            await Cart.addToServer(id, type, qty);

            // PDP / Kit PDP button feedback
            const originalHTML = btn.innerHTML;
            if (btn.classList.contains('pdp-btn-buy') || btn.classList.contains('kit-pdp-btn-buy')) {
                btn.classList.remove('adding');
                btn.classList.add('added');
                const label = btn.classList.contains('kit-pdp-btn-buy') ? 'Assinado!' : 'Adicionado!';
                btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg> ' + label;
            }

            // Cart icon bounce
            const cartIcon = document.querySelector('.nav-cart-link');
            if (cartIcon) {
                cartIcon.classList.add('cart-bounce');
                setTimeout(() => cartIcon.classList.remove('cart-bounce'), 500);
            }

            showToast(`${name} adicionado ao carrinho!`, 'success');

            setTimeout(() => {
                btn.classList.remove('adding', 'added');
                if (btn.classList.contains('pdp-btn-buy') || btn.classList.contains('kit-pdp-btn-buy')) btn.innerHTML = originalHTML;
                if ('disabled' in btn) btn.disabled = false;
            }, 1500);
        });

        // Keyboard support for role="button" spans
        if (btn.getAttribute('role') === 'button') {
            btn.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    e.stopPropagation();
                    btn.click();
                }
            });
        }
    });
}

// Remove item from cart (button)
function initRemoveFromCart() {
    document.querySelectorAll('[data-cart-remove]').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.preventDefault();
            const itemId = btn.dataset.cartRemove;
            await Cart.removeFromServer(itemId);
            Cart.remove(itemId);

            const itemEl = document.querySelector(`[data-cart-item-id="${itemId}"]`);
            if (itemEl) {
                itemEl.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                itemEl.style.opacity = '0';
                itemEl.style.transform = 'translateX(-20px)';
                setTimeout(() => itemEl.remove(), 300);
            }

            showToast('Item removido do carrinho', 'info');
        });
    });
}

// Clear cart button
function initClearCart() {
    const btn = document.getElementById('btn-clear-cart');
    if (btn) {
        btn.addEventListener('click', async () => {
            if (!confirm('Limpar todo o carrinho?')) return;
            await Cart.clearServer();
            showToast('Carrinho limpo', 'info');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
    }
}

// Quantity controls in cart
function initQuantityControls() {
    document.querySelectorAll('[data-qty-decrease]').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.preventDefault();
            const itemId = btn.dataset.qtyDecrease;
            const input = document.querySelector(`[data-qty-input="${itemId}"]`);
            const currentVal = parseInt(input.value) || 0;
            if (currentVal > 1) {
                input.value = currentVal - 1;
                Cart.updateQuantityLocal(itemId, currentVal - 1);
                await Cart.updateOnServer(itemId, currentVal - 1);
            }
        });
    });

    document.querySelectorAll('[data-qty-increase]').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.preventDefault();
            const itemId = btn.dataset.qtyIncrease;
            const input = document.querySelector(`[data-qty-input="${itemId}"]`);
            const currentVal = parseInt(input.value) || 0;
            input.value = currentVal + 1;
            Cart.updateQuantityLocal(itemId, currentVal + 1);
            await Cart.updateOnServer(itemId, currentVal + 1);
        });
    });
}

// Mobile nav toggle
function initMobileNav() {
    const toggle = document.getElementById('nav-mobile-toggle');
    const nav = document.getElementById('mainNav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', () => {
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        nav.classList.toggle('nav-open', !expanded);
    });
}

// Search overlay toggle (desktop + mobile)
function initSearchOverlay() {
    const toggleBtn = document.getElementById('search-toggle-btn');
    const overlay = document.getElementById('search-overlay');
    const closeBtn = document.getElementById('search-overlay-close');
    const mobileOverlay = document.getElementById('search-mobile-overlay');
    const mobileCloseBtn = document.getElementById('search-mobile-close');

    // Desktop search overlay
    if (toggleBtn && overlay && closeBtn) {
        toggleBtn.addEventListener('click', () => {
            const isMobile = window.innerWidth < 768;
            if (isMobile && mobileOverlay) {
                mobileOverlay.classList.add('active');
                const input = mobileOverlay.querySelector('.search-bar-input');
                if (input) input.focus();
            } else {
                overlay.classList.add('active');
                overlay.setAttribute('aria-hidden', 'false');
                toggleBtn.setAttribute('aria-expanded', 'true');
                const input = overlay.querySelector('.search-overlay-input');
                if (input) input.focus();
            }
        });

        closeBtn.addEventListener('click', () => {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.focus();
        });

        // Close on Escape key
        overlay.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                overlay.classList.remove('active');
                overlay.setAttribute('aria-hidden', 'true');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.focus();
            }
        });

        // Close on backdrop click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                overlay.setAttribute('aria-hidden', 'true');
                toggleBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Mobile search overlay
    if (mobileOverlay && mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', () => {
            mobileOverlay.classList.remove('active');
            if (toggleBtn) toggleBtn.focus();
        });
    }
}

// Password visibility toggle
function initPasswordToggle() {
    document.querySelectorAll('[data-toggle-pw]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.closest('[class*="password-wrap"]').querySelector('input');
            const showing = btn.classList.toggle('showing');
            input.type = showing ? 'text' : 'password';
            btn.setAttribute('aria-label', showing ? 'Ocultar senha' : 'Mostrar senha');
        });
    });
}

// Initialize everything
function init() {
    Cart.updateBadge();
    Cart.syncToServer();
    initKeyboardDropdown();
    initAddToCartButtons();
    initRemoveFromCart();
    initClearCart();
    initQuantityControls();
    processFlashMessages();
    initSearchOverlay();
    initMobileNav();
    initPasswordToggle();
}

document.addEventListener('DOMContentLoaded', init);
document.addEventListener('turbo:load', init);

// Export for use in inline scripts
window.SurpreendaCart = Cart;
window.showToast = showToast;
