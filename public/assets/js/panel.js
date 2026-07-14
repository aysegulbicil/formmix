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
})();
