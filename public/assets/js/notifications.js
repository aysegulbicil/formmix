(() => {
    if (typeof window.Swal === 'undefined') return;

    const normalize = (value) => value.replace(/\s+/g, ' ').trim();
    const unique = (values) => [...new Set(values.filter(Boolean))];
    const alertMessage = (alert) => {
        const detailParts = [...alert.querySelectorAll('span')]
            .map((item) => normalize(item.textContent ?? ''))
            .filter(Boolean);
        return detailParts.length > 0 ? detailParts.join(' ') : normalize(alert.textContent ?? '');
    };
    const successMessages = [];
    const errorMessages = [];

    document.querySelectorAll('.alert--success, .auth-alert--success').forEach((alert) => {
        successMessages.push(alertMessage(alert));
        alert.remove();
    });

    document.querySelectorAll('.alert--error, .auth-alert--error').forEach((alert) => {
        errorMessages.push(alertMessage(alert));
        alert.remove();
    });

    const fireSuccess = (message, options = {}) => window.Swal.fire({
        icon: 'success',
        title: options.title ?? 'İşlem başarılı',
        text: message,
        toast: options.toast ?? true,
        position: options.position ?? 'top-end',
        showConfirmButton: false,
        timer: options.timer ?? 3600,
        timerProgressBar: true,
        iconColor: '#16835f',
        customClass: {
            popup: 'formmix-swal formmix-swal--toast',
            timerProgressBar: 'formmix-swal__timer',
        },
    });

    const fireError = (message, options = {}) => window.Swal.fire({
        icon: 'error',
        title: options.title ?? 'İşlem tamamlanamadı',
        text: message,
        confirmButtonText: 'Tamam',
        confirmButtonColor: '#102a43',
        iconColor: '#c33d47',
        customClass: {
            popup: 'formmix-swal',
            confirmButton: 'formmix-swal__confirm',
        },
    });

    const fireWarning = (message, options = {}) => window.Swal.fire({
        icon: 'warning',
        title: options.title ?? 'Dikkat',
        text: message,
        confirmButtonText: 'Tamam',
        confirmButtonColor: '#102a43',
        iconColor: '#f26a21',
        customClass: {
            popup: 'formmix-swal',
            confirmButton: 'formmix-swal__confirm',
        },
    });

    window.FormmixAlert = {
        success: fireSuccess,
        error: fireError,
        warning: fireWarning,
    };

    const errors = unique(errorMessages);
    const successes = unique(successMessages);

    if (errors.length > 0) {
        fireError(errors.join('\n'));
    } else if (successes.length > 0) {
        fireSuccess(successes.join(' '));
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.swalConfirm) return;
        if (form.dataset.swalConfirmed === '1') {
            delete form.dataset.swalConfirmed;
            return;
        }

        event.preventDefault();
        const submitter = event.submitter;
        const result = await window.Swal.fire({
            icon: 'question',
            title: form.dataset.swalConfirmTitle ?? 'İşlemi onaylayın',
            text: form.dataset.swalConfirm,
            showCancelButton: true,
            confirmButtonText: 'Evet, devam et',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
            focusCancel: true,
            confirmButtonColor: '#102a43',
            cancelButtonColor: '#e8eef3',
            iconColor: '#f26a21',
            customClass: {
                popup: 'formmix-swal',
                confirmButton: 'formmix-swal__confirm',
                cancelButton: 'formmix-swal__cancel',
            },
        });

        if (!result.isConfirmed) return;
        form.dataset.swalConfirmed = '1';
        if (typeof form.requestSubmit === 'function') form.requestSubmit(submitter ?? undefined);
        else form.submit();
    });
})();
