<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="w-full px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <a href="/" class="inline-flex items-center gap-2 text-sky-500 hover:text-sky-600 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Volver al Dashboard
                </a>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sky-500 dark:text-sky-400 text-2xl font-bold">
                        <?= htmlspecialchars(strtoupper(substr($contactDetails['nombre_contacto'] ?? 'C', 0, 1) . substr($contactDetails['empresa_razon_social'] ?? 'E', 0, 1))) ?>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-dynamic-primary"><?= htmlspecialchars($contactDetails['nombre_contacto'] ?? 'N/A') ?></h1>
                        <p class="text-dynamic-secondary mt-1 text-lg"><?= htmlspecialchars($contactDetails['empresa_razon_social'] ?? 'Sin empresa asociada') ?></p>
                    </div>
                </div>
            </div>
            <!-- BOTÓN DE MODO OSCURO/CLARO -->
            <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none rounded-lg text-sm p-2.5">
                <svg id="theme-toggle-moon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg id="theme-toggle-sun" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 5.05A1 1 0 003.636 6.464l.707.707a1 1 0 001.414-1.414l-.707-.707zM3 11a1 1 0 100-2H2a1 1 0 100 2h1zM6.464 16.364l-.707.707a1 1 0 001.414 1.414l.707-.707a1 1 0 00-1.414-1.414z"></path></svg>
            </button>
        </div>
    </header>

    <main class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna de Información y Notas -->
        <div class="lg:col-span-1 space-y-8">
            <!-- Detalles de Contacto -->
            <div class="card">
                <h2 class="text-xl font-semibold text-dynamic-primary mb-4">Detalles de Contacto</h2>
                <div class="space-y-3 text-sm">
                    <p><strong class="text-dynamic-secondary">Email:</strong> <span class="text-dynamic-primary"><?= htmlspecialchars($contactDetails['email'] ?? 'N/A') ?></span></p>
                    <p><strong class="text-dynamic-secondary">Teléfono:</strong> <span class="text-dynamic-primary"><?= htmlspecialchars($contactDetails['telefono_1'] ?? 'N/A') ?></span></p>
                    <p><strong class="text-dynamic-secondary">Cargo:</strong> <span class="text-dynamic-primary"><?= htmlspecialchars($contactDetails['cargo'] ?? 'N/A') ?></span></p>
                    <p><strong class="text-dynamic-secondary">Rubro:</strong> <span class="text-dynamic-primary"><?= htmlspecialchars($contactDetails['rubro'] ?? 'N/A') ?></span></p>
                    <p><strong class="text-dynamic-secondary">Dirección:</strong> <span class="text-dynamic-primary"><?= htmlspecialchars($contactDetails['direccion'] ?? '') ?>, <?= htmlspecialchars($contactDetails['comuna'] ?? '') ?></span></p>
                </div>
            </div>

            <!-- Notas del Equipo -->
            <div class="card">
                <h2 class="text-xl font-semibold text-dynamic-primary mb-4">Notas del Equipo</h2>
                <!-- Formulario para añadir nota -->
                <form id="addNoteForm" class="mb-6">
                    <input type="hidden" name="id_contacto" value="<?= htmlspecialchars($contactDetails['id_contacto']) ?>">
                    <!-- CLASES CORREGIDAS AQUÍ -->
                    <textarea name="note_content" rows="3" class="w-full p-2 text-sm rounded-lg bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-dynamic-primary focus:ring-sky-500 focus:border-sky-500" placeholder="Añadir una nueva nota sobre este contacto..." required></textarea>
                    <div id="note-feedback" class="text-sm mt-2"></div>
                    <button type="submit" class="mt-2 w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg">Guardar Nota</button>
                </form>
                <!-- Historial de notas -->
                <div class="space-y-4 max-h-60 overflow-y-auto pr-2">
                    <?php if (!empty($contactNotes)): foreach ($contactNotes as $note): ?>
                    <div class="text-sm border-b border-dynamic pb-2">
                        <p class="text-dynamic-primary"><?= htmlspecialchars($note['note_content']) ?></p>
                        <p class="text-xs text-dynamic-secondary mt-1 text-right"><?= (new DateTime($note['created_at']))->format('d-m-Y H:i') ?></p>
                    </div>
                    <?php endforeach; else: ?>
                    <p class="text-sm text-dynamic-secondary text-center">No hay notas para este contacto.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna de Historial de Interacción -->
        <div class="lg:col-span-2 card">
            <h2 class="text-xl font-semibold text-dynamic-primary mb-4">Historial de Interacción</h2>
            <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                 <?php if (!empty($interactionHistory)): foreach ($interactionHistory as $item): ?>
                    <?php $date = new DateTime($item['timestamp'], new DateTimeZone('UTC')); $date->setTimezone(new DateTimeZone('America/Santiago')); ?>
                    <div class="flex items-center gap-4 p-3 border-b border-dynamic">
                        <?php if ($item['tipo_interaccion'] == 'clic'): ?>
                             <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M3 12h18M12 3v18"/></svg></div>
                        <?php else: ?>
                             <div class="w-8 h-8 rounded-full bg-sky-500/20 flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-sky-500"><path d="M21.75 9.812a9.42 9.42 0 0 1-8.625 11.188 9.42 9.42 0 0 1-10.313-7.313 9.42 9.42 0 0 1 6.25-11.438 9.42 9.42 0 0 1 11.188 6.25Z"/><path d="M12 12v-2"/></svg></div>
                        <?php endif; ?>
                        <div class="text-sm flex-grow">
                            <p class="text-dynamic-primary">
                                <?= $item['tipo_interaccion'] == 'clic' ? 'Hizo <span class="font-semibold text-emerald-400">clic</span>' : ' <span class="font-semibold text-sky-400">Abrió</span> el correo' ?>
                                en la campaña <span class="font-medium">"<?= htmlspecialchars($item['nombre_campana']) ?>"</span>.
                            </p>
                        </div>
                        <span class="text-xs text-dynamic-secondary flex-shrink-0"><?= $date->format('d-m-Y H:i') ?></span>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-dynamic-secondary text-sm text-center py-10">Este contacto no tiene interacciones registradas.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

