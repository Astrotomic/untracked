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

    const normalizeHost = (host) => host.toLowerCase().replace(/^www\./, '');

    if (document.referrer) {
        try {
            const referrer = new URL(document.referrer);

            if (normalizeHost(referrer.hostname) !== normalizeHost(window.location.hostname)) {
                body.set('referrer', referrer.hostname);
            }
        } catch {
            // Ignore malformed referrers.
        }
    }

    const query = new URLSearchParams(window.location.search);

    for (const key of ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content']) {
        const value = query.get(key);

        if (value) {
            body.set(key, value);
        }
    }

    fetch(endpoint, {
        method: 'POST',
        body,
        keepalive: true,
        credentials: 'omit',
    }).catch(() => {});
})();
