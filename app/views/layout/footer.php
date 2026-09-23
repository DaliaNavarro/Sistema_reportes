</main>

<footer class="footer">

    Sistema de Reportes TI

</footer>

<script src="assets/js/app.js"></script>

<script>

if ('serviceWorker' in navigator) {

    navigator.serviceWorker.register('sw.js')
        .catch(error => {
            console.error(
                'Error registrando Service Worker:',
                error
            );
        });

}

</script>

</body>
</html>