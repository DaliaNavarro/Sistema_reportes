const CACHE_NAME =
    'reportes-ti-v1';


const STATIC_FILES = [

    '/',

    '/login.php',

    '/assets/css/style.css',

    '/assets/js/app.js',

    '/assets/js/reportes.js',

    '/manifest.json'

];


self.addEventListener(
    'install',
    event => {

        event.waitUntil(

            caches
                .open(
                    CACHE_NAME
                )
                .then(
                    cache =>
                        cache.addAll(
                            STATIC_FILES
                        )
                )

        );

    }
);


self.addEventListener(
    'fetch',
    event => {

        if (
            event.request.method
            !== 'GET'
        ) {

            return;
        }


        event.respondWith(

            fetch(
                event.request
            )
            .catch(
                () =>
                    caches.match(
                        event.request
                    )
            )

        );

    }
);