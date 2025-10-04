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

     document.addEventListener('DOMContentLoaded', () => {
        const generateBtn = document.getElementById('generate-invitation-btn');
        const invitationsList = document.getElementById('invitations-list');

        if (generateBtn) {
            generateBtn.addEventListener('click', async () => {
                generateBtn.disabled = true;
                generateBtn.textContent = 'Generando...';

                try {
                    const response = await fetch('/invitations/create', { method: 'POST' });
                    const result = await response.json();

                    if (!response.ok) throw new Error(result.message);

                    const token = result.token;
                    const url = window.location.origin + '/register/' + token;
                    
                    // Eliminar el mensaje de "no hay invitaciones" si existe
                    const noInvitationsMsg = document.getElementById('no-invitations-msg');
                    if(noInvitationsMsg) noInvitationsMsg.remove();
                    
                    // Crear el nuevo elemento en la lista
                    const newInvitationEl = document.createElement('div');
                    newInvitationEl.className = 'flex items-center gap-2 p-2 rounded-md bg-slate-100 dark:bg-slate-700/50';
                    newInvitationEl.setAttribute('data-token', token);
                    newInvitationEl.innerHTML = `
                        <input type="text" readonly value="${url}" class="text-sm bg-transparent w-full focus:outline-none text-dynamic-secondary">
                        <button class="copy-link-btn p-2 rounded-md hover:bg-slate-200 dark:hover:bg-slate-600" title="Copiar enlace">
                            <svg class="w-4 h-4 text-dynamic-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </button>
                        <button class="delete-token-btn p-2 rounded-md hover:bg-red-500/10" title="Eliminar token">
                             <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    `;
                    invitationsList.appendChild(newInvitationEl);

                } catch (error) {
                    alert('Error al generar el enlace: ' + error.message);
                } finally {
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg> Generar Nuevo Enlace de Invitación';
                }
            });
        }

        // Delegación de eventos para los botones de copiar y borrar
        invitationsList.addEventListener('click', async (e) => {
            const copyBtn = e.target.closest('.copy-link-btn');
            const deleteBtn = e.target.closest('.delete-token-btn');

            if (copyBtn) {
                const input = copyBtn.previousElementSibling;
                navigator.clipboard.writeText(input.value).then(() => {
                    copyBtn.title = '¡Copiado!';
                    setTimeout(() => copyBtn.title = 'Copiar enlace', 2000);
                });
            }

            if (deleteBtn) {
                if (!confirm('¿Estás seguro de que quieres eliminar este enlace de invitación?')) return;

                const row = deleteBtn.closest('[data-token]');
                const token = row.dataset.token;

                try {
                    const response = await fetch(`/invitations/delete/${token}`, { method: 'POST' });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message);
                    row.remove(); // Eliminar el elemento del DOM
                } catch (error) {
                    alert('Error al eliminar el token: ' + error.message);
                }
            }
        });
    });
    
</script>
</body>
</html>

