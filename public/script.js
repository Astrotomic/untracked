(() => {
    const script = document.currentScript;
    const website = script?.dataset.websiteId;

    if (! website) {
        return;
    }

    const endpoint = new URL(`/api/websites/${encodeURIComponent(website)}/collect/raw`, script.src);
    const body = new URLSearchParams({
        path: window.location.pathname,
        format: 'html',
    });

    fetch(endpoint, {
        method: 'POST',
        body,
        keepalive: true,
        credentials: 'omit',
    }).catch(() => {});
})();
