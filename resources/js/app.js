const sidebar = document.getElementById('sidebar-mobile');

if (sidebar) {
    const openers = document.querySelectorAll('[data-buka-sidebar]');
    const closers = document.querySelectorAll('[data-tutup-sidebar]');

    const setExpanded = (value) => {
        openers.forEach((button) => button.setAttribute('aria-expanded', String(value)));
    };

    openers.forEach((button) => {
        button.addEventListener('click', () => {
            if (!sidebar.open) {
                sidebar.showModal();
            }

            setExpanded(true);
        });
    });

    closers.forEach((button) => {
        button.addEventListener('click', () => sidebar.close());
    });

    sidebar.addEventListener('click', (event) => {
        if (event.target === sidebar) {
            sidebar.close();
        }
    });

    sidebar.addEventListener('close', () => setExpanded(false));

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => sidebar.close());
    });
}
