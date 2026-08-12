</main>
</div>

<script>
(function () {
    const layout = document.querySelector('[data-admin-layout]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const toggle = document.querySelector('[data-admin-menu-toggle]');
    const overlay = document.querySelector('[data-admin-menu-overlay]');

    if (!layout || !sidebar || !toggle) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 900px)');

    function updateAria() {
        const open = mobileQuery.matches
            ? layout.classList.contains('admin-menu-open')
            : !layout.classList.contains('admin-menu-collapsed');

        toggle.setAttribute(
            'aria-expanded',
            open ? 'true' : 'false'
        );
    }

    function closeMobile() {
        layout.classList.remove('admin-menu-open');
        document.body.classList.remove('admin-menu-lock');
        updateAria();
    }

    function openMobile() {
        layout.classList.add('admin-menu-open');
        document.body.classList.add('admin-menu-lock');
        updateAria();
    }

    function toggleDesktop() {
        const collapsed = !layout.classList.contains(
            'admin-menu-collapsed'
        );

        layout.classList.toggle(
            'admin-menu-collapsed',
            collapsed
        );

        try {
            localStorage.setItem(
                'checkout_admin_menu_collapsed',
                collapsed ? '1' : '0'
            );
        } catch (_) {}

        updateAria();
    }

    function restoreState() {
        if (mobileQuery.matches) {
            layout.classList.remove('admin-menu-collapsed');
            closeMobile();
            return;
        }

        layout.classList.remove('admin-menu-open');
        document.body.classList.remove('admin-menu-lock');

        let collapsed = false;

        try {
            collapsed = localStorage.getItem(
                'checkout_admin_menu_collapsed'
            ) === '1';
        } catch (_) {}

        layout.classList.toggle(
            'admin-menu-collapsed',
            collapsed
        );

        updateAria();
    }

    toggle.addEventListener('click', function () {
        if (mobileQuery.matches) {
            if (layout.classList.contains('admin-menu-open')) {
                closeMobile();
            } else {
                openMobile();
            }

            return;
        }

        toggleDesktop();
    });

    overlay?.addEventListener('click', closeMobile);

    sidebar.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', function () {
            if (mobileQuery.matches) {
                closeMobile();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape'
            && layout.classList.contains('admin-menu-open')
        ) {
            closeMobile();
            toggle.focus();
        }
    });

    if (typeof mobileQuery.addEventListener === 'function') {
        mobileQuery.addEventListener(
            'change',
            restoreState
        );
    } else if (typeof mobileQuery.addListener === 'function') {
        mobileQuery.addListener(
            restoreState
        );
    }

    restoreState();
})();

document.addEventListener('click', async function (event) {
    const button = event.target.closest('[data-copy-url]');

    if (!button) {
        return;
    }

    const value = button.getAttribute('data-copy-url') || '';

    if (!value) {
        return;
    }

    const original = button.textContent;

    try {
        if (
            navigator.clipboard
            && window.isSecureContext
        ) {
            await navigator.clipboard.writeText(value);
        } else {
            const input = document.createElement('textarea');
            input.value = value;
            input.setAttribute('readonly', '');
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            input.remove();
        }

        button.textContent = 'Copiado!';
    } catch (_) {
        button.textContent = 'Não foi possível copiar';
    }

    window.setTimeout(function () {
        button.textContent = original;
    }, 1600);
});
</script>
</body>
</html>
