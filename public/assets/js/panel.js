(() => {
    const sidebar = document.querySelector('#sidebar');
    const open = document.querySelector('[data-sidebar-open]');
    const close = document.querySelector('[data-sidebar-close]');
    const setSidebar = (visible) => {
        sidebar?.classList.toggle('is-open', visible);
        document.body.classList.toggle('sidebar-open', visible);
    };
    open?.addEventListener('click', () => setSidebar(true));
    close?.addEventListener('click', () => setSidebar(false));

    const accountSelect = document.querySelector('[data-account-select]');
    const newAccount = document.querySelector('[data-account-new]');
    const accountRole = document.querySelector('[data-account-role]');
    const syncAccountFields = () => {
        const value = accountSelect?.value ?? '';
        if (newAccount) newAccount.hidden = value !== 'new';
        if (accountRole) accountRole.hidden = value === '';
    };
    accountSelect?.addEventListener('change', syncAccountFields);
    syncAccountFields();

    const customerForm = document.querySelector('[data-duplicate-url]');
    const duplicateAlert = document.querySelector('[data-duplicate-alert]');
    let duplicateTimer;
    const checkDuplicate = () => {
        if (!customerForm || !duplicateAlert) return;
        const phone = customerForm.querySelector('[data-duplicate-phone]')?.value ?? '';
        const tax = customerForm.querySelector('[data-duplicate-tax]')?.value ?? '';
        if (phone.replace(/\D/g, '').length < 7 && tax.trim().length < 5) {
            duplicateAlert.hidden = true;
            return;
        }
        const url = new URL(customerForm.dataset.duplicateUrl, window.location.origin);
        url.searchParams.set('telefon', phone);
        url.searchParams.set('vergi', tax);
        if (customerForm.dataset.customerId) url.searchParams.set('haric', customerForm.dataset.customerId);
        fetch(url, {headers: {'Accept': 'application/json'}})
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(data => {
                duplicateAlert.hidden = !data.duplicate;
                duplicateAlert.textContent = data.message ?? '';
            })
            .catch(() => { duplicateAlert.hidden = true; });
    };
    customerForm?.querySelectorAll('[data-duplicate-phone], [data-duplicate-tax]').forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(duplicateTimer);
            duplicateTimer = setTimeout(checkDuplicate, 450);
        });
    });

    const checkAll = document.querySelector('[data-check-all]');
    const checkItems = [...document.querySelectorAll('[data-check-item]')];
    checkAll?.addEventListener('change', () => checkItems.forEach(item => { item.checked = checkAll.checked; }));
    checkItems.forEach(item => item.addEventListener('change', () => {
        if (!checkAll) return;
        checkAll.checked = checkItems.length > 0 && checkItems.every(box => box.checked);
        checkAll.indeterminate = !checkAll.checked && checkItems.some(box => box.checked);
    }));

    document.querySelectorAll('[data-table-per-page]').forEach(select => {
        select.addEventListener('change', () => {
            const url = new URL(window.location.href);
            url.searchParams.set(select.dataset.perPageParam, select.value);
            url.searchParams.delete(select.dataset.pageParam);
            window.location.assign(url.toString());
        });
    });

    const numericTableLabels = new Set([
        'Miktar', 'Mevcut', 'Ayrılmış', 'Kullanılabilir', 'Kalan', 'Tutar',
        'Toplam', 'Genel toplam', 'Net satış', 'Maliyet', 'Brüt kâr', 'Prim',
        'Matrah', 'Oran', 'İndirim', 'Birim fiyat', 'Vergi', 'Liste fiyatı',
        'Alış fiyatı', 'Fiyat', 'Özel fiyat', 'Kabul', 'Bu kabul',
    ]);

    document.querySelectorAll('.data-table').forEach(table => {
        const headers = [...table.querySelectorAll('thead th')];
        table.querySelectorAll('tbody td:not([colspan])').forEach(cell => {
            const header = headers[cell.cellIndex];
            const label = cell.dataset.label?.trim() ?? '';
            if (!header || !label) return;

            if (label === 'ID') {
                cell.textContent = cell.textContent.trim().replace(/^#/, '');
                cell.classList.add('table-column--id');
                header.classList.add('table-column--id');
            } else if (numericTableLabels.has(label)) {
                cell.classList.add('table-column--number');
                header.classList.add('table-column--number');
            } else if (label === 'İşlem') {
                cell.classList.add('table-column--action');
                header.classList.add('table-column--action');
            }
        });
    });

    document.querySelectorAll('form.filter-bar[method="get"], form.filter-bar:not([method])').forEach(form => {
        form.addEventListener('submit', () => {
            const current = new URL(window.location.href);
            current.searchParams.forEach((value, key) => {
                if (key !== 'per_page' && !key.endsWith('_per_page')) return;
                if (form.querySelector(`[name="${key}"]`)) return;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            });
        });
    });

    const movementType = document.querySelector('[data-movement-type]');
    const targetWarehouse = document.querySelector('[data-target-warehouse]');
    const syncMovement = () => { if (targetWarehouse) targetWarehouse.hidden = movementType?.value !== 'transfer'; };
    movementType?.addEventListener('change', syncMovement);
    syncMovement();
})();
