<footer class="text-center mt-12 mb-4">
    <p class="text-xs text-slate-400 dark:text-slate-500">
        Desarrollado por Jonathan Petersen Z. para KyD Consulting
    </p>
</footer>

<!-- Librerías de JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@2.0.0"></script> <!-- NUEVO: Adaptador para manejar fechas -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@1.1.1"></script>

<!-- Nuestro script de frontend -->
<script>
    // Pasamos los datos del backend (PHP) al frontend (JavaScript)
    const chartData = <?= isset($chartJSData) ? json_encode($chartJSData) : 'null' ?>;
</script>
<script src="/assets/js/charts.js"></script>
<script>
    // Lógica del modo oscuro/claro (debe estar aquí para funcionar en todas las páginas)
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeToggleDarkIcon = document.getElementById('theme-toggle-moon');
    const themeToggleLightIcon = document.getElementById('theme-toggle-sun');

    // Cambia los iconos y el tema basado en la configuración guardada o del sistema
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        themeToggleLightIcon.classList.remove('hidden');
        themeToggleDarkIcon.classList.add('hidden');
    } else {
        document.documentElement.classList.remove('dark');
        themeToggleLightIcon.classList.add('hidden');
        themeToggleDarkIcon.classList.remove('hidden');
    }

    themeToggleBtn.addEventListener('click', function() {
        // alterna los iconos
        themeToggleDarkIcon.classList.toggle('hidden');
        themeToggleLightIcon.classList.toggle('hidden');

        // si el tema estaba guardado, lo cambia
        if (localStorage.getItem('color-theme')) {
            if (localStorage.getItem('color-theme') === 'light') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            }
        // si no, lo crea y lo establece
        } else {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            }
        }
    });

    // Lógica para el formulario de añadir notas en la página de perfil
    const addNoteForm = document.getElementById('addNoteForm');
    if (addNoteForm) {
        addNoteForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const noteFeedback = document.getElementById('note-feedback');
            const formData = new FormData(addNoteForm);
            const data = Object.fromEntries(formData.entries());
            const submitButton = addNoteForm.querySelector('button[type="submit"]');

            if (!data.note_content) {
                noteFeedback.textContent = 'El contenido de la nota no puede estar vacío.';
                noteFeedback.className = 'text-sm mt-2 text-red-400';
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Guardando...';

            try {
                const response = await fetch('/contact/note/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Error en el servidor.');
                
                noteFeedback.textContent = result.message;
                noteFeedback.className = 'text-sm mt-2 text-green-400';
                setTimeout(() => window.location.reload(), 1500);

            } catch (error) {
                noteFeedback.textContent = `Error: ${error.message}`;
                noteFeedback.className = 'text-sm mt-2 text-red-400';
                submitButton.disabled = false;
                submitButton.textContent = 'Guardar Nota';
            }
        });
    }
</script>
</body>
</html>