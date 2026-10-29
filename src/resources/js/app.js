import './bootstrap';

/**
 * Arus – small, dependency-free UI interactions.
 */
const onReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
};

const formatRupiah = (digits) => {
    if (!digits) {
        return '';
    }

    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const initSidebar = () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openButtons = document.querySelectorAll('[data-sidebar-open]');
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');

    if (!sidebar) {
        return;
    }

    const setOpen = (open) => {
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop?.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
    };

    openButtons.forEach((button) => button.addEventListener('click', () => setOpen(true)));
    closeButtons.forEach((button) => button.addEventListener('click', () => setOpen(false)));
    backdrop?.addEventListener('click', () => setOpen(false));

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });
};

const initDropdowns = () => {
    const dropdowns = document.querySelectorAll('[data-dropdown]');

    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const panel = dropdown.querySelector('[data-dropdown-panel]');

        if (!trigger || !panel) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isHidden = panel.classList.contains('hidden');

            document.querySelectorAll('[data-dropdown-panel]').forEach((other) => other.classList.add('hidden'));

            panel.classList.toggle('hidden', !isHidden);
            trigger.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown-panel]').forEach((panel) => panel.classList.add('hidden'));
    });
};

const initMoneyInputs = () => {
    document.querySelectorAll('[data-money-input]').forEach((input) => {
        const initial = (input.value || '').replace(/[^\d]/g, '');
        input.value = formatRupiah(initial);

        input.addEventListener('input', () => {
            const digits = input.value.replace(/[^\d]/g, '');
            input.value = formatRupiah(digits);
        });
    });
};

const initPasswordToggles = () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const target = document.querySelector(button.dataset.passwordToggle);

        if (!target) {
            return;
        }

        button.addEventListener('click', () => {
            const isPassword = target.getAttribute('type') === 'password';
            target.setAttribute('type', isPassword ? 'text' : 'password');
            button.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            button.querySelector('[data-eye-open]')?.classList.toggle('hidden', isPassword);
            button.querySelector('[data-eye-off]')?.classList.toggle('hidden', !isPassword);
        });
    });
};

const initCategoryFilter = () => {
    const typeInputs = document.querySelectorAll('[data-transaction-type]');
    const categoryOptions = document.querySelectorAll('[data-category-type]');

    if (!typeInputs.length || !categoryOptions.length) {
        return;
    }

    const refresh = () => {
        const selected = document.querySelector('[data-transaction-type]:checked')?.value;

        categoryOptions.forEach((option) => {
            const matches = !selected || option.dataset.categoryType === selected;
            option.hidden = !matches;
            option.disabled = !matches;

            if (!matches && option.selected) {
                option.selected = false;
            }
        });
    };

    typeInputs.forEach((input) => input.addEventListener('change', refresh));
    refresh();
};

const initPeriodToggle = () => {
    const select = document.querySelector('[data-period-type]');
    const monthInput = document.querySelector('[data-period-month]');
    const yearInput = document.querySelector('[data-period-year]');

    if (!select || !monthInput || !yearInput) {
        return;
    }

    const refresh = () => {
        const monthly = select.value === 'monthly';
        monthInput.classList.toggle('hidden', !monthly);
        monthInput.disabled = !monthly;
        yearInput.classList.toggle('hidden', monthly);
        yearInput.disabled = monthly;
    };

    select.addEventListener('change', refresh);
    refresh();
};

const initConfirmations = () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
};

const initAlerts = () => {
    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('[data-alert]')?.remove();
        });
    });
};

const initTransferGuard = () => {
    const source = document.querySelector('[data-source-account]');
    const destination = document.querySelector('[data-destination-account]');

    if (!source || !destination) {
        return;
    }

    const refresh = () => {
        Array.from(destination.options).forEach((option) => {
            option.disabled = option.value !== '' && option.value === source.value;
        });

        if (destination.value === source.value) {
            destination.value = '';
        }
    };

    source.addEventListener('change', refresh);
    refresh();
};

onReady(() => {
    initSidebar();
    initDropdowns();
    initMoneyInputs();
    initPasswordToggles();
    initCategoryFilter();
    initPeriodToggle();
    initConfirmations();
    initAlerts();
    initTransferGuard();
});
