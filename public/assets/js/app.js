(() => {
  'use strict';

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const $ = (selector, scope = document) => scope.querySelector(selector);
  const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

  function toast(message, type = 'success') {
    const region = $('[data-toast-region]');
    if (!region) return;
    const node = document.createElement('div');
    node.className = 'toast';
    node.dataset.type = type;
    node.setAttribute('role', type === 'error' ? 'alert' : 'status');
    node.textContent = message;
    region.appendChild(node);
    const remove = () => node.remove();
    let timer = window.setTimeout(remove, 5000);
    node.addEventListener('mouseenter', () => window.clearTimeout(timer));
    node.addEventListener('mouseleave', () => { timer = window.setTimeout(remove, 2500); });
    node.addEventListener('click', remove);
  }

  async function api(url, options = {}) {
    const headers = { Accept: 'application/json', 'X-CSRF-Token': csrf, ...(options.headers || {}) };
    if (options.body && !(options.body instanceof FormData)) headers['Content-Type'] = 'application/json';
    const response = await fetch(url, { ...options, headers });
    const payload = await response.json().catch(() => ({ ok: false, error: 'The server returned an unreadable response.' }));
    if (!response.ok || !payload.ok) {
      const error = new Error(payload.error || 'The request could not be completed.');
      error.errors = payload.errors || {};
      throw error;
    }
    return payload.data;
  }

  function updateCounts(selection) {
    const count = (selection?.bouquet ? 1 : 0) + Object.keys(selection?.cafe || {}).length;
    $$('[data-selection-count]').forEach((node) => { node.textContent = String(count); });
  }

  const menuToggle = $('[data-menu-toggle]');
  const mobileMenu = $('[data-mobile-menu]');
  menuToggle?.addEventListener('click', () => {
    const open = menuToggle.getAttribute('aria-expanded') === 'true';
    menuToggle.setAttribute('aria-expanded', String(!open));
    mobileMenu.hidden = open;
    document.body.style.overflow = open ? '' : 'hidden';
  });

  $$('[name="fulfilment_method"]').forEach((input) => input.addEventListener('change', () => {
    $$('[data-delivery-field]').forEach((field) => {
      const delivery = $('[name="fulfilment_method"]:checked')?.value === 'delivery';
      field.hidden = !delivery;
      const textarea = $('textarea', field);
      if (textarea) textarea.required = delivery;
    });
  }));

  function initCafe() {
    const sheet = $('[data-product-sheet]');
    if (!sheet) return;
    const form = $('[data-product-form]', sheet);
    $$('[data-open-product]').forEach((button) => button.addEventListener('click', () => {
      const product = JSON.parse(button.dataset.openProduct);
      form.reset();
      form.elements.product_id.value = product.id;
      $('[data-product-name]', sheet).textContent = product.name;
      $('[data-product-description]', sheet).textContent = product.description;
      const image = $('[data-product-image]', sheet);
      image.src = product.cover_image || '/public/assets/images/cafe-900.webp';
      image.alt = product.name;
      const options = $('[data-product-options]', sheet);
      options.replaceChildren();
      const groups = Object.groupBy ? Object.groupBy(product.options || [], (option) => option.option_group) : (product.options || []).reduce((result, option) => { (result[option.option_group] ||= []).push(option); return result; }, {});
      Object.entries(groups).forEach(([group, items]) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'option-group';
        const title = document.createElement('strong');
        title.textContent = group.charAt(0).toUpperCase() + group.slice(1);
        wrapper.appendChild(title);
        const grid = document.createElement('div');
        grid.className = 'option-group-grid';
        items.forEach((option, index) => {
          const label = document.createElement('label');
          const input = document.createElement('input');
          input.type = group === 'addon' ? 'checkbox' : 'radio';
          input.name = group === 'addon' ? 'option_ids[]' : `option_${group}`;
          input.value = option.id;
          if (group !== 'addon' && index === 0) input.checked = true;
          const text = document.createElement('span');
          text.textContent = option.name + (Number(option.price_adjustment) > 0 ? ` +BND ${Number(option.price_adjustment).toFixed(2)}` : '');
          label.append(input, text);
          grid.appendChild(label);
        });
        wrapper.appendChild(grid);
        options.appendChild(wrapper);
      });
      sheet.showModal();
    }));
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const data = new FormData(form);
      const optionIds = $$('input[type="radio"]:checked,input[type="checkbox"]:checked', form).filter((input) => input.closest('.option-group')).map((input) => Number(input.value));
      try {
        const selection = await api('/api/selection/cafe', { method: 'POST', body: JSON.stringify({ product_id: Number(data.get('product_id')), option_ids: optionIds, quantity: Number(data.get('quantity')), notes: data.get('notes') }) });
        updateCounts(selection);
        sheet.close();
        toast('Added to your selection.');
      } catch (error) { toast(error.message, 'error'); }
    });
  }

  function initBuilder() {
    const root = $('[data-builder]');
    if (!root) return;
    const form = $('[data-builder-form]', root);
    const steps = $$('[data-step]', root);
    const progress = $('[data-progress-bar]', root);
    const progressLabel = $('[data-progress-label]', root);
    const previous = $('[data-prev]', root);
    const next = $('[data-next]', root);
    const submit = $('[data-submit]', root);
    const labels = new Map($$('input', form).map((input) => [`${input.name}:${input.value}`, input.closest('label')?.innerText.trim().split('\n')[0] || input.value]));
    let current = 0;

    function selectedNames(name) {
      return $$(`[name="${name}"]:checked`, form).map((input) => labels.get(`${input.name}:${input.value}`));
    }

    function renderSummary() {
      const entries = [
        ['Occasion', selectedNames('occasion_id')[0]],
        ['Flowers', selectedNames('flower_ids[]').join(', ')],
        ['Inspiration', selectedNames('sample_id')[0]],
        ['Colours', selectedNames('colour_ids[]').join(', ')],
        ['Size', selectedNames('size_id')[0]],
        ['Wrapping', selectedNames('wrapping_id')[0]],
        ['Decorations', selectedNames('decoration_ids[]').join(', ') || 'None'],
        ['Budget', `BND ${form.elements.budget_min.value || '—'}–${form.elements.budget_max.value || '—'}`],
      ];
      const html = `<dl>${entries.map(([term, value]) => `<div><dt>${term}</dt><dd>${escapeHtml(value || 'Not chosen')}</dd></div>`).join('')}</dl>`;
      const summary = $('[data-live-summary]', root);
      const review = $('[data-review]', root);
      if (summary) summary.innerHTML = html;
      if (review) review.innerHTML = html;
    }

    function escapeHtml(value) {
      const node = document.createElement('span'); node.textContent = String(value); return node.innerHTML;
    }

    function validateStep(index) {
      const step = steps[index];
      if (index === 1 && $$('[name="flower_ids[]"]:checked', step).length === 0) { toast('Choose at least one flower.', 'error'); return false; }
      if (index === 3 && $$('[name="colour_ids[]"]:checked', step).length === 0) { toast('Choose at least one colour direction.', 'error'); return false; }
      for (const input of $$('input,textarea,select', step)) {
        if (!input.checkValidity()) { input.reportValidity(); return false; }
      }
      if (index === 7 && Number(form.elements.budget_max.value) < Number(form.elements.budget_min.value)) { toast('Maximum budget must be at least the minimum.', 'error'); return false; }
      return true;
    }

    async function saveBouquet() {
      const data = new FormData(form);
      return api('/api/selection/bouquet', { method: 'POST', body: JSON.stringify({
        occasion_id: Number(data.get('occasion_id')), flower_ids: data.getAll('flower_ids[]').map(Number), sample_id: Number(data.get('sample_id') || 0),
        colour_ids: data.getAll('colour_ids[]').map(Number), size_id: Number(data.get('size_id')), wrapping_id: Number(data.get('wrapping_id')),
        decoration_ids: data.getAll('decoration_ids[]').map(Number), budget_min: Number(data.get('budget_min')), budget_max: Number(data.get('budget_max')), instructions: data.get('instructions'),
      }) });
    }

    async function loadMatches() {
      const ids = $$('[name="flower_ids[]"]:checked', form).map((input) => input.value);
      const status = $('[data-match-status]', root);
      const grid = $('[data-match-grid]', root);
      if (!ids.length) { status.textContent = 'Choose flowers to see ranked inspiration.'; grid.replaceChildren(); return; }
      status.textContent = 'Ranking inspiration…';
      try {
        const results = await api(`/api/florist/matches?flowers=${encodeURIComponent(ids.join(','))}&occasion=${encodeURIComponent(form.elements.occasion_id.value || '')}`);
        grid.replaceChildren();
        results.forEach((sample, index) => {
          const label = document.createElement('label'); label.className = 'match-card';
          const input = document.createElement('input'); input.type = 'radio'; input.name = 'sample_id'; input.value = sample.id;
          const wrap = document.createElement('span');
          const image = document.createElement('img'); image.src = sample.thumbnail || sample.cover_image; image.alt = `${sample.name} bouquet inspiration`; image.loading = 'lazy';
          const name = document.createElement('b'); name.textContent = sample.name;
          const score = document.createElement('small'); score.textContent = sample.match_exact ? 'Exact flower combination' : sample.match_complete ? 'Contains every selected flower' : `${sample.matched_flower_count} selected flower match`;
          wrap.append(image, name, score); label.append(input, wrap); grid.appendChild(label);
          labels.set(`sample_id:${sample.id}`, sample.name);
          if (index === 0 && !form.elements.sample_id.value) input.checked = true;
        });
        status.textContent = results.length ? `${results.length} relevant ${results.length === 1 ? 'study' : 'studies'}, ranked for you.` : 'No current study contains those flowers. Our florist can still work from your brief.';
      } catch (error) { status.textContent = error.message; }
    }

    function show(index) {
      current = Math.max(0, Math.min(steps.length - 1, index));
      steps.forEach((step, i) => step.classList.toggle('is-active', i === current));
      progress.style.width = `${((current + 1) / steps.length) * 100}%`;
      progressLabel.textContent = `${current + 1} of ${steps.length}`;
      previous.disabled = current === 0;
      next.hidden = current === steps.length - 1;
      submit.hidden = current !== steps.length - 1;
      if (current === 2) loadMatches();
      if (current === 10) renderSummary();
      window.scrollTo({ top: 0, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    }

    next.addEventListener('click', async () => {
      if (!validateStep(current)) return;
      if (current >= 1 && current <= 8) {
        try { const selection = await saveBouquet(); updateCounts(selection); } catch (error) { toast(error.message, 'error'); return; }
      }
      renderSummary(); show(current + 1);
    });
    previous.addEventListener('click', () => show(current - 1));
    form.addEventListener('change', renderSummary);
    form.elements.instructions?.addEventListener('input', (event) => { $('[data-character-count]', root).textContent = String(event.target.value.length); });
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (!validateStep(current)) return;
      const popup = window.open('', '_blank');
      const errors = $('[data-form-errors]', root); errors.textContent = '';
      submit.disabled = true; submit.textContent = 'Saving enquiry…';
      try {
        const selection = await saveBouquet(); updateCounts(selection);
        const data = new FormData(form);
        const enquiry = await api('/enquiries', { method: 'POST', body: JSON.stringify({ customer_name: data.get('customer_name'), customer_phone: data.get('customer_phone'), customer_email: data.get('customer_email'), fulfilment_method: data.get('fulfilment_method'), requested_date: data.get('requested_date'), requested_time: data.get('requested_time'), delivery_address: data.get('delivery_address'), customer_notes: data.get('customer_notes'), consent: data.get('consent') }) });
        if (popup) popup.location.href = enquiry.whatsapp_url;
        window.location.href = `/enquiry/${encodeURIComponent(enquiry.reference)}`;
      } catch (error) {
        if (popup) popup.close();
        const messages = Object.values(error.errors || {});
        errors.textContent = messages.length ? messages.join(' ') : error.message;
        toast(error.message, 'error');
        submit.disabled = false; submit.textContent = 'Save and open WhatsApp';
      }
    });
    renderSummary(); show(0);
  }

  function initSelection() {
    $$('[data-remove-cafe]').forEach((button) => button.addEventListener('click', async () => {
      button.disabled = true;
      try {
        const selection = await api(`/api/selection/cafe/${encodeURIComponent(button.dataset.removeCafe)}`, { method: 'DELETE' });
        updateCounts(selection); button.closest('[data-selection-line]')?.remove(); toast('Café item removed.');
      } catch (error) { button.disabled = false; toast(error.message, 'error'); }
    }));
    const form = $('.enquiry-form');
    form?.addEventListener('submit', async (event) => {
      if (!form.checkValidity()) return;
      event.preventDefault();
      const popup = window.open('', '_blank');
      const button = $('button[type="submit"]', form); button.disabled = true; button.textContent = 'Saving enquiry…';
      const data = Object.fromEntries(new FormData(form));
      try {
        const enquiry = await api('/enquiries', { method: 'POST', body: JSON.stringify(data) });
        if (popup) popup.location.href = enquiry.whatsapp_url;
        window.location.href = `/enquiry/${encodeURIComponent(enquiry.reference)}`;
      } catch (error) {
        if (popup) popup.close(); toast(error.message, 'error'); button.disabled = false; button.textContent = 'Save enquiry and open WhatsApp';
      }
    });
  }

  function initMotion() {
    if (!window.gsap || !window.ScrollTrigger || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    window.gsap.registerPlugin(window.ScrollTrigger);
    const cards = $$('.story-cards article');
    cards.forEach((card, index) => {
      window.gsap.fromTo(card, { y: 80, scale: .94, opacity: .35 }, { y: 0, scale: 1, opacity: 1, ease: 'none', scrollTrigger: { trigger: card, start: 'top 88%', end: 'top 42%', scrub: true } });
      if (index < cards.length - 1) window.gsap.to(card, { scale: .96, opacity: .5, ease: 'none', scrollTrigger: { trigger: cards[index + 1], start: 'top 70%', end: 'top 20%', scrub: true } });
    });
    const text = $('[data-reveal-text]');
    if (text) {
      const words = text.textContent.trim().split(/\s+/); text.textContent = '';
      words.forEach((word) => { const span = document.createElement('span'); span.textContent = `${word} `; span.style.opacity = '.12'; text.appendChild(span); });
      window.gsap.to($$('span', text), { opacity: 1, stagger: .08, ease: 'none', scrollTrigger: { trigger: text, start: 'top 82%', end: 'bottom 42%', scrub: true } });
    }
  }

  $$('form[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  }));

  initCafe();
  initBuilder();
  initSelection();
  initMotion();
  if (window.ckfFlash) toast(window.ckfFlash.message, window.ckfFlash.type);
})();
