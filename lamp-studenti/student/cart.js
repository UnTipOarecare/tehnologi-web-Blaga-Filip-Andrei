(() => {
  const STORAGE_KEY = 'calorifere_cart';
  const NOTIF_DISPLAY_MS = 8000;
  const NOTIF_GAP_PX = 8;
  const PUSH_ANIM_MS = 300;
  const ENTER_TRANS_MS = 420;
  const EXIT_TRANS_MS = 900;

  function loadCart() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
    catch (e) { return []; }
  }
  function saveCart(cart) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    updateCartCount();
  }

  function formatPrice(n) {
    return Number(n).toLocaleString('ro-RO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' lei';
  }
  function parsePriceString(s) {
    if (typeof s === 'number') return s;
    if (!s) return 0;
    const cleaned = String(s).replace(/[^\d.]/g, '');
    const num = parseFloat(cleaned);
    return isNaN(num) ? 0 : num;
  }

  function addToCart(product) {
    const cart = loadCart();
    const existing = cart.find(i => i.id === product.id);
    if (existing) existing.qty = (existing.qty || 1) + 1;
    else cart.push(Object.assign({ qty: 1 }, product));
    saveCart(cart);
    addStackedNotification(`${product.title} a fost adăugat în coș.`);
    renderCartPage();
  }

  function changeQty(id, delta) {
    const cart = loadCart();
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.qty = (item.qty || 0) + delta;
    if (item.qty <= 0) {
      removeFromCart(id);
      return;
    }
    saveCart(cart);
    renderCartPage();
  }

  function removeFromCart(id) {
    let cart = loadCart();
    cart = cart.filter(i => i.id !== id);
    saveCart(cart);
    renderCartPage();
  }

  function getCartCount() {
    const cart = loadCart();
    return cart.reduce((s, i) => s + (i.qty || 0), 0);
  }
  function getCartTotal() {
    const cart = loadCart();
    return cart.reduce((s, i) => s + (i.qty || 0) * Number(i.price || 0), 0);
  }

  function normalizeImgPath(img) {
    if (!img) return '';
    if (img.startsWith('http') || img.startsWith('/') || img.startsWith('images/')) {
      return img;
    }
    return 'images/' + img;
  }
  

  function renderCartPage() {
    const container = document.getElementById('cart-items');
    const totalEl = document.getElementById('cart-total');
    if (!container) return;
    const cart = loadCart();
    container.innerHTML = '';
    if (!cart.length) {
      container.innerHTML = '<p>Coșul este gol.</p>';
      if (totalEl) totalEl.textContent = 'Total: 0.00 lei';
      return;
    }
    const list = document.createElement('div');
    list.className = 'cart-list';
    cart.forEach(item => {
      const row = document.createElement('div');
      row.className = 'cart-item';
      row.style.display = 'flex';
      row.style.justifyContent = 'space-between';
      row.style.alignItems = 'center';
      row.style.padding = '12px 0';
      row.style.borderBottom = '1px solid #eef3f6';

      const left = document.createElement('div');
      left.style.display = 'flex';
      left.style.gap = '12px';
      left.style.alignItems = 'center';
      left.innerHTML = `
        ${ item.img ? `<img src="${normalizeImgPath(item.img)}" alt="${escapeHtml(item.title)}" style="width:72px;height:72px;object-fit:cover;border-radius:6px">` : '' }
        <div>
          <div style="font-weight:700">${escapeHtml(item.title)}</div>
          <div style="color:#7d8a97">${formatPrice(item.price)}</div>
        </div>
      `;

      const right = document.createElement('div');
      right.style.display = 'flex';
      right.style.flexDirection = 'column';
      right.style.alignItems = 'flex-end';
      right.style.gap = '8px';

      const qtyWrap = document.createElement('div');
      qtyWrap.style.display = 'flex';
      qtyWrap.style.alignItems = 'center';
      qtyWrap.style.gap = '8px';

      const dec = document.createElement('button');
      dec.type = 'button';
      dec.className = 'btn qty-decrease';
      dec.dataset.id = item.id;
      dec.textContent = '−';
      dec.style.padding = '6px 10px';

      const qtyText = document.createElement('div');
      qtyText.textContent = `Cantitate: ${item.qty}`;
      qtyText.style.minWidth = '86px';
      qtyText.style.textAlign = 'center';

      const inc = document.createElement('button');
      inc.type = 'button';
      inc.className = 'btn qty-increase';
      inc.dataset.id = item.id;
      inc.textContent = '+';
      inc.style.padding = '6px 10px';

      qtyWrap.appendChild(dec);
      qtyWrap.appendChild(qtyText);
      qtyWrap.appendChild(inc);

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn remove-from-cart';
      removeBtn.dataset.removeId = item.id;
      removeBtn.textContent = 'Sterge';
      removeBtn.style.background = '#c0392b';

      right.appendChild(qtyWrap);
      right.appendChild(removeBtn);

      row.appendChild(left);
      row.appendChild(right);
      list.appendChild(row);
    });
    container.appendChild(list);
    if (totalEl) totalEl.textContent = 'Total: ' + formatPrice(getCartTotal());
  }

  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function updateCartCount() {
    const el = document.getElementById('cart-count');
    if (!el) return;
    const count = getCartCount();
    if (count > 0) {
      el.textContent = count;
      el.style.display = 'inline-flex';
    } else {
      el.textContent = '';
      el.style.display = 'none';
    }
  }

  function ensureFlashContainer() {
    let c = document.getElementById('cart-flash-container');
    if (!c) {
      c = document.createElement('div');
      c.id = 'cart-flash-container';
      c.style.pointerEvents = 'none';
      document.body.appendChild(c);
    }
    return c;
  }

  function getTranslateY(el) {
    const s = el.style.transform || getComputedStyle(el).transform;
    if (!s || s === 'none') return 0;
    const m3 = s.match(/matrix3d\(([^)]+)\)/);
    if (m3) {
      const parts = m3[1].split(',').map(p => parseFloat(p));
      return parts[13] || 0;
    }
    const m = s.match(/matrix\(([^)]+)\)/);
    if (m) {
      const parts = m[1].split(',').map(p => parseFloat(p));
      return parts[5] || 0;
    }
    const ty = s.match(/translateY\((-?\d+(\.\d+)?)px\)/);
    return ty ? parseFloat(ty[1]) : 0;
  }

  function flipAnimateAfterRemoval(oldMap, container) {
    const remaining = Array.from(container.children);
    const newPositions = remaining.map(el => ({ el, top: el.getBoundingClientRect().top }));
    newPositions.forEach(({ el, top }) => {
      const oldTop = oldMap.get(el) || top;
      const delta = oldTop - top;
      if (!delta) return;
      el.style.transform = `translateY(${delta}px)`;
      void el.offsetWidth;
      el.style.transition = `transform ${PUSH_ANIM_MS}ms ease`;
      el.style.transform = `translateY(0)`;
      const cleanup = (ev) => {
        if (ev.propertyName !== 'transform') return;
        el.style.transition = '';
        el.style.transform = '';
        el.removeEventListener('transitionend', cleanup);
      };
      el.addEventListener('transitionend', cleanup);
    });
  }

  function addStackedNotification(text, displayMs = NOTIF_DISPLAY_MS) {
    const container = ensureFlashContainer();
    const newEl = document.createElement('div');
    newEl.className = 'cart-flash-item offscreen-right';
    newEl.textContent = text;
    newEl.style.pointerEvents = 'auto';
    container.appendChild(newEl);
    void newEl.offsetWidth;
    newEl.style.transition = `transform ${ENTER_TRANS_MS}ms cubic-bezier(0.4,0,0.2,1), opacity ${ENTER_TRANS_MS}ms cubic-bezier(0.4,0,0.2,1)`;
    newEl.classList.remove('offscreen-right');
    newEl.classList.add('enter');

    const hideTimer = setTimeout(() => {
      newEl.classList.remove('enter');
      newEl.style.transition = `transform ${EXIT_TRANS_MS}ms linear, opacity ${EXIT_TRANS_MS}ms linear`;
      newEl.classList.add('exit');
      const onEnd = (ev) => {
        if (ev.propertyName !== 'transform' && ev.propertyName !== 'opacity') return;
        newEl.removeEventListener('transitionend', onEnd);
        const oldPositions = Array.from(container.children).map(el => ({ el, top: el.getBoundingClientRect().top }));
        const oldMap = new Map(oldPositions.map(o => [o.el, o.top]));
        if (newEl.parentNode) newEl.parentNode.removeChild(newEl);
        flipAnimateAfterRemoval(oldMap, container);
      };
      newEl.addEventListener('transitionend', onEnd);
    }, displayMs);

    newEl.addEventListener('click', () => {
      clearTimeout(hideTimer);
      if (!newEl.classList.contains('exit')) {
        newEl.classList.remove('enter');
        newEl.style.transition = `transform ${EXIT_TRANS_MS}ms linear, opacity ${EXIT_TRANS_MS}ms linear`;
        newEl.classList.add('exit');
      }
    });
  }

  function initAddButtons() {
    document.addEventListener('click', (e) => {
      const addBtn = e.target.closest('.add-to-cart-btn');
      if (!addBtn) return;
      e.preventDefault();
      const card = addBtn.closest('.product-card');
      if (!card) return;
      const product = {
        id: card.dataset.id || generateIdFromTitle(card.dataset.title || card.querySelector('.product-title')?.innerText || 'prod'),
        title: card.dataset.title || card.querySelector('.product-title')?.innerText || 'Produs',
        price: parsePriceString(card.dataset.price || (card.querySelector('.product-price')?.innerText || '0')),
        img: card.dataset.img || (card.querySelector('.product-thumb img')?.getAttribute('src') || '')
      };
      addToCart(product);
    });

    document.addEventListener('click', (e) => {
      const d = e.target.closest('.add-to-cart-detail');
      if (!d) return;
      e.preventDefault();
      const product = {
        id: d.dataset.id || generateIdFromTitle(d.dataset.title || 'prod'),
        title: d.dataset.title || 'Produs',
        price: parsePriceString(d.dataset.price || '0'),
        img: d.dataset.img || ''
      };
      addToCart(product);
    });
  }

  function initCartPageButtons() {
    document.addEventListener('click', (e) => {
      const rem = e.target.closest('.remove-from-cart');
      if (rem) {
        const id = rem.dataset.removeId;
        if (id) {
          removeFromCart(id);
          addStackedNotification('Produs șters din coș.');
        }
        return;
      }
      const inc = e.target.closest('.qty-increase');
      if (inc) {
        const id = inc.dataset.id;
        if (id) changeQty(id, +1);
        return;
      }
      const dec = e.target.closest('.qty-decrease');
      if (dec) {
        const id = dec.dataset.id;
        if (id) changeQty(id, -1);
        return;
      }
    });
  }

  function generateIdFromTitle(t) {
    return 'p_' + String(t).toLowerCase().replace(/\s+/g, '_').replace(/[^\w\-]/g, '').slice(0, 40);
  }

  document.addEventListener('DOMContentLoaded', () => {
    initAddButtons();
    initCartPageButtons();
    updateCartCount();
    renderCartPage();
    const checkout = document.getElementById('checkout-btn');
    if (checkout) {
      checkout.addEventListener('click', () => {
        const cart = loadCart();
        if (!cart.length) {
          alert('Coșul este gol. Adaugă produse înainte de a merge la plată.');
          return;
        }
        window.location.href = 'plata.php';
      });
    }
  });

  window.__calorifere_cart = { addToCart, removeFromCart, changeQty, loadCart, saveCart, getCartCount, getCartTotal };
})();
