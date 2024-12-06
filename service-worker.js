self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            const fetchRequest = event.request.clone();
            return fetch(fetchRequest)
                .then((networkResponse) => {
                    if (!networkResponse || networkResponse.status !== 200) {
                        return cachedResponse;
                    }
                    caches.open('pwa-cache').then((cache) => {
                        cache.put(event.request, networkResponse.clone());
                    });
                    return networkResponse;
                })
                .catch(() => {
                    return cachedResponse;
                });
        })
    );
});
