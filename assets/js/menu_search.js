/**
 * Menu Live Search & Auto-Expand for Berry Admin Template (Sidebar Placement)
 * Pure client-side filtering without modifying permissions
 */
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('sidebar-menu-search') || document.getElementById('header-menu-search');
  const searchClear = document.getElementById('sidebar-menu-search-clear') || document.getElementById('header-menu-search-clear');
  const navbar = document.querySelector('.pc-sidebar .pc-navbar');

  if (!searchInput || !navbar) return;

  let isInitialized = false;

  // Initialize and cache original menu states
  function initMenuCache() {
    if (isInitialized) return;
    const allItems = navbar.querySelectorAll('li.pc-item');
    allItems.forEach(li => {
      // Cache original trigger class
      li.dataset.origTrigger = li.classList.contains('pc-trigger') ? '1' : '0';

      const submenu = li.querySelector(':scope > .pc-submenu');
      if (submenu) {
        submenu.dataset.origDisplay = submenu.style.display || '';
      }

      const mtext = li.querySelector(':scope > a.pc-link > .pc-mtext');
      if (mtext) {
        mtext.dataset.origHtml = mtext.innerHTML;
        mtext.dataset.origText = mtext.textContent.trim();
      }
    });
    isInitialized = true;
  }

  // Highlight helper
  function highlightText(span, query) {
    if (!span) return;
    const originalText = span.dataset.origText || span.textContent.trim();
    if (!query) {
      span.innerHTML = span.dataset.origHtml || originalText;
      return;
    }
    const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp('(' + escaped + ')', 'gi');
    span.innerHTML = originalText.replace(regex, '<mark class="pc-search-highlight">$1</mark>');
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function (m) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      })[m];
    });
  }

  // Reset menu search to original state
  function resetMenuSearch() {
    if (!isInitialized) return;

    const allItems = navbar.querySelectorAll('li.pc-item');
    allItems.forEach(li => {
      li.style.display = '';

      const submenu = li.querySelector(':scope > .pc-submenu');
      if (submenu) {
        submenu.style.display = submenu.dataset.origDisplay || '';
      }

      if (li.dataset.origTrigger === '1') {
        li.classList.add('pc-trigger');
        if (submenu) submenu.style.display = 'block';
      } else if (li.classList.contains('pc-hasmenu')) {
        li.classList.remove('pc-trigger');
      }

      const mtext = li.querySelector(':scope > a.pc-link > .pc-mtext');
      if (mtext) {
        mtext.innerHTML = mtext.dataset.origHtml || mtext.dataset.origText || '';
      }
    });

    const noResultLi = navbar.querySelector('.pc-menu-no-results');
    if (noResultLi) {
      noResultLi.style.display = 'none';
    }

    if (searchClear) searchClear.style.display = 'none';
  }

  // Filter sidebar
  function performSearch(query) {
    initMenuCache();

    const q = query.trim().toLowerCase();
    if (!q) {
      resetMenuSearch();
      return;
    }

    if (searchClear) searchClear.style.display = 'inline-flex';

    const allItems = navbar.querySelectorAll('li.pc-item');
    const directMatches = new Set();
    const itemsToKeepVisible = new Set();
    const parentsToExpand = new Set();

    // Step 1: Detect direct matches
    allItems.forEach(li => {
      const mtext = li.querySelector(':scope > a.pc-link > .pc-mtext');
      if (!mtext) return;

      const text = (mtext.dataset.origText || mtext.textContent).trim().toLowerCase();
      if (text.includes(q)) {
        directMatches.add(li);
        itemsToKeepVisible.add(li);
      }
    });

    // Step 2: Handle ancestors and descendants
    directMatches.forEach(li => {
      // Expand all ancestors
      let curr = li.parentElement ? li.parentElement.closest('li.pc-item') : null;
      while (curr) {
        itemsToKeepVisible.add(curr);
        parentsToExpand.add(curr);
        curr = curr.parentElement ? curr.parentElement.closest('li.pc-item') : null;
      }

      // If matching item is a parent container with submenus:
      // Expand it and make its descendant items visible
      if (li.classList.contains('pc-hasmenu')) {
        parentsToExpand.add(li);
        li.querySelectorAll('li.pc-item').forEach(child => {
          itemsToKeepVisible.add(child);
        });
      }
    });

    // Step 3: Apply DOM changes to sidebar
    allItems.forEach(li => {
      const mtext = li.querySelector(':scope > a.pc-link > .pc-mtext');
      const submenu = li.querySelector(':scope > .pc-submenu');

      if (itemsToKeepVisible.has(li)) {
        li.style.display = '';

        if (parentsToExpand.has(li)) {
          li.classList.add('pc-trigger');
          if (submenu) submenu.style.display = 'block';
        }

        if (mtext) {
          if (directMatches.has(li)) {
            highlightText(mtext, q);
          } else {
            highlightText(mtext, '');
          }
        }
      } else {
        li.style.display = 'none';
        if (mtext) highlightText(mtext, '');
        if (submenu) submenu.style.display = 'none';
      }
    });

    // Step 4: Show / Hide No-results indicator in sidebar
    let noResultLi = navbar.querySelector('.pc-menu-no-results');
    if (itemsToKeepVisible.size === 0) {
      if (!noResultLi) {
        noResultLi = document.createElement('li');
        noResultLi.className = 'pc-item pc-menu-no-results text-center py-4 px-3';
        noResultLi.innerHTML = `
          <div class="text-muted" style="font-size: 13px;">
            <i class="ti ti-search-off d-block fs-3 mb-2 text-secondary"></i>
            <span>Menu "<strong>${escapeHtml(q)}</strong>" tidak ditemukan</span>
          </div>
        `;
        navbar.appendChild(noResultLi);
      } else {
        noResultLi.style.display = '';
        const strongEl = noResultLi.querySelector('strong');
        if (strongEl) strongEl.textContent = q;
      }
    } else if (noResultLi) {
      noResultLi.style.display = 'none';
    }
  }

  // Event Listeners
  let debounceTimeout = null;
  searchInput.addEventListener('input', function () {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
      performSearch(this.value);
    }, 60);
  });

  // Clear button click
  if (searchClear) {
    searchClear.addEventListener('click', function () {
      searchInput.value = '';
      resetMenuSearch();
      searchInput.focus();
    });
  }

  // Keyboard navigation: Escape clears search
  searchInput.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      this.value = '';
      resetMenuSearch();
      this.blur();
    }
  });

  // Global shortcut: Ctrl+K or "/" to focus search
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      searchInput.focus();
      searchInput.select();
    } else if (e.key === '/' && document.activeElement !== searchInput && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
      e.preventDefault();
      searchInput.focus();
      searchInput.select();
    }
  });
});