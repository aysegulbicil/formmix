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
})();
