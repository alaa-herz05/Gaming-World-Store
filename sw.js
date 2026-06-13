const CACHE_NAME = "alaa-chat-v1";

const urlsToCache = [
    "/",
    "/index.php",
    "/login.php",
    "/register.php",
    "/friends.php",
    "/messages_room.php",
    "/group_room.php",
    "/style.css",
    "/offline.html"
];

self.addEventListener("install", event => {

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener("fetch", event => {

    event.respondWith(

        caches.match(event.request)
            .then(response => {

                if (response) {
                    return response;
                }

                return fetch(event.request)
                    .catch(() => caches.match("/offline.html"));
            })
    );
});