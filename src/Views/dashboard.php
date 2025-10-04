<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="w-full px-4 sm:px-6 lg:px-8">
    <!-- Cabecera del Dashboard -->
    <header class="flex flex-col md:flex-row justify-between items-start mb-8 gap-6">
        <!-- Título y Selector de Campaña -->
        <div class="w-full md:w-auto">
             <div class="flex items-center gap-3 mb-4">
                <img src="/assets/images/logo.svg" alt="KYD Logo" class="rounded-lg h-[140px] w-[140px]"/>
                <div>
                    <h1 class="text-3xl font-bold text-dynamic-primary">Dashboard de Inteligencia</h1>
                    <p class="text-dynamic-secondary mt-1">
                        Mostrando datos para la campaña: <span class="font-semibold text-sky-500"><?= htmlspecialchars($selectedCampaign->nombre_campana ?? 'Ninguna campaña seleccionada') ?></span>
                    </p>
                </div>
            </div>
            <!-- Selector de Campañas -->
            <?php if (!empty($allCampaigns)): ?>
            <div class="w-full md:max-w-xs">
                <label for="campaignSelector" class="text-sm font-medium text-dynamic-secondary">Seleccionar otra campaña</label>
                <div class="relative mt-1">
                    <select id="campaignSelector" class="appearance-none block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-600 focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm rounded-md bg-white dark:bg-slate-700 text-dynamic-primary">
                        <?php foreach ($allCampaigns as $campaign): ?>
                            <option value="<?= $campaign->campaign_id ?>" <?= (isset($selectedCampaignId) && $campaign->campaign_id == $selectedCampaignId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($campaign->nombre_campana) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- Botones de Acción -->
        <div class="flex items-center gap-4 w-full md:w-auto self-start md:self-center">
             <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none rounded-lg text-sm p-2.5">
                <svg id="theme-toggle-moon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg id="theme-toggle-sun" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 5.05A1 1 0 003.636 6.464l.707.707a1 1 0 001.414-1.414l-.707-.707zM3 11a1 1 0 100-2H2a1 1 0 100 2h1zM6.464 16.364l-.707.707a1 1 0 001.414 1.414l.707-.707a1 1 0 00-1.414-1.414z"></path></svg>
            </button>
            <button id="openModalBtn" class="bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors duration-200 w-full sm:w-auto justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Crear Campaña
            </button>
            <!-- NUEVO: Botón de Logout -->
            <form action="/logout" method="POST">
                <button type="submit" title="Cerrar Sesión" class="text-red-500 hover:bg-red-500/10 rounded-lg p-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </header>

    <main>
        <?php if (!empty($kpis)): ?>
            <!-- KPIs Principales -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
                <div class="card"><p class="text-dynamic-secondary font-medium text-sm">Tasa de Apertura</p><p class="text-3xl font-bold text-sky-400 mt-2"><?= number_format($kpis['tasa_apertura'], 1) ?>%</p></div>
                <div class="card"><p class="text-dynamic-secondary font-medium text-sm">Tasa de Clics (CTR)</p><p class="text-3xl font-bold text-emerald-400 mt-2"><?= number_format($kpis['tasa_clics_ctr'], 1) ?>%</p></div>
                <div class="card"><p class="text-dynamic-secondary font-medium text-sm">Clics / Aperturas (CTOR)</p><p class="text-3xl font-bold text-violet-400 mt-2"><?= number_format($kpis['tasa_clics_ctor'], 1) ?>%</p></div>
                <div class="card"><p class="text-dynamic-secondary font-medium text-sm">Total Enviados</p><p class="text-3xl font-bold text-dynamic-primary mt-2"><?= number_format($kpis['total_enviados']) ?></p></div>
                <div class="card"><p class="text-dynamic-secondary font-medium text-sm">Tasa de Bajas</p><p class="text-3xl font-bold text-amber-400 mt-2"><?= number_format($kpis['tasa_bajas'], 1) ?>%</p></div>
                <div class="card"><p class="text-dynamic-secondary font-medium text-sm">Rebotes</p><p class="text-3xl font-bold text-red-400 mt-2">N/A</p></div>
            </div>

            <!-- Gráfico de Tiempo y Top Leads -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                <div class="card xl:col-span-2">
                    <div class="flex flex-col sm:flex-row justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-dynamic-primary">Interacciones en el Tiempo</h2>
                            <p class="text-sm text-dynamic-secondary">Aperturas y Clics durante el período seleccionado.</p>
                        </div>
                        <div class="flex items-center gap-2 mt-3 sm:mt-0 flex-wrap" id="period-selector">
                            <a href="?campaign_id=<?= $selectedCampaignId ?>&period=today" class="px-3 py-1 text-sm rounded-md <?= $selectedPeriod == 'today' ? 'active-period' : '' ?>">Hoy</a>
                            <a href="?campaign_id=<?= $selectedCampaignId ?>&period=7" class="px-3 py-1 text-sm rounded-md <?= $selectedPeriod == '7' ? 'active-period' : '' ?>">7d</a>
                            <a href="?campaign_id=<?= $selectedCampaignId ?>&period=30" class="px-3 py-1 text-sm rounded-md <?= $selectedPeriod == '30' ? 'active-period' : '' ?>">30d</a>
                            <a href="?campaign_id=<?= $selectedCampaignId ?>&period=90" class="px-3 py-1 text-sm rounded-md <?= $selectedPeriod == '90' ? 'active-period' : '' ?>">90d</a>
                        </div>
                    </div>
                    <div class="h-96"><canvas id="timeSeriesChart"></canvas></div>
                </div>
                <div class="card">
                    <h2 class="text-xl font-semibold text-dynamic-primary mb-2">Ranking (Top Leads)</h2>
                    <p class="text-sm text-dynamic-secondary mb-4">Contacto/Empresa con mayor interés.</p>
                    <div class="overflow-y-auto h-96 pr-2">
                        <table class="w-full text-left"><thead class="text-sm text-slate-400 dark:text-slate-300 sticky top-0"><tr class="table-header"><th class="p-3 font-semibold">Contacto/Empresa</th><th class="p-3 font-semibold">Puntos</th></tr></thead>
                            <tbody class="text-sm">
                                <?php if (!empty($topLeads)): foreach($topLeads as $lead): ?>
                                <tr class="border-b border-dynamic table-row">
                                    <td class="p-3 font-medium text-dynamic-primary">
                                        <a href="/contact/<?= htmlspecialchars($lead['id_contacto']) ?>" class="hover:text-sky-500">
                                            <?= htmlspecialchars($lead['contacto_empresa']) ?>
                                        </a>
                                    </td>
                                    <td class="p-3 text-center font-bold text-emerald-400"><?= $lead['puntuacion_total'] ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="2" class="text-center p-4 text-dynamic-secondary">No hay datos de leads.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gráficos de Interés -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="card">
                    <h2 class="text-xl font-semibold text-dynamic-primary mb-2">Interés por Rubro</h2>
                    <p class="text-sm text-dynamic-secondary mb-4">Sectores con mayor interacción.</p>
                    <div class="h-96"><canvas id="industryChart"></canvas></div>
                </div>
                <div class="card">
                    <h2 class="text-xl font-semibold text-dynamic-primary mb-2">Interés por Región</h2>
                    <p class="text-sm text-dynamic-secondary mb-4">Regiones geográficas con mayor interacción.</p>
                    <div class="h-96"><canvas id="regionChart"></canvas></div>
                </div>
            </div>

            <!-- Heatmap y Salud de la BD -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                 <div class="card xl:col-span-2">
                    <h2 class="text-xl font-semibold text-dynamic-primary mb-2">Mejores Horarios de Interacción</h2>
                    <p class="text-sm text-dynamic-secondary mb-4">Concentración de actividad por día y hora.</p>
                    <div class="h-80 w-full"><canvas id="heatmapChart"></canvas></div>
                </div>
                <div class="card">
                    <h2 class="text-xl font-semibold text-dynamic-primary mb-2">Salud de la Base de Datos</h2>
                    <p class="text-sm text-dynamic-secondary mb-4">Composición de la lista de contactos.</p>
                    <div class="h-80"><canvas id="contactStatusChart"></canvas></div>
                </div>
            </div>

            <!-- NUEVA SECCIÓN: Gestión de Invitaciones -->
            <div class="card mb-8">
                <h2 class="text-xl font-semibold text-dynamic-primary mb-4">Gestionar Invitaciones</h2>
                <div class="space-y-4">
                    <button id="generate-invitation-btn" class="w-full md:w-auto bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        Generar Nuevo Enlace de Invitación
                    </button>
                    <div id="invitations-list" class="space-y-2">
                        <p class="text-sm text-dynamic-secondary font-semibold mt-6 mb-2">Enlaces Activos:</p>
                        <?php if (!empty($activeInvitations)): ?>
                            <?php foreach ($activeInvitations as $token):
                                $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                                $link = $url . '/register/' . $token;
                            ?>
                                <div class="flex items-center gap-2 p-2 rounded-md bg-slate-100 dark:bg-slate-700/50" data-token="<?= $token ?>">
                                    <input type="text" readonly value="<?= $link ?>" class="text-sm bg-transparent w-full focus:outline-none text-dynamic-secondary">
                                    <button class="copy-link-btn p-2 rounded-md hover:bg-slate-200 dark:hover:bg-slate-600" title="Copiar enlace">
                                        <svg class="w-4 h-4 text-dynamic-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </button>
                                    <button class="delete-token-btn p-2 rounded-md hover:bg-red-500/10" title="Eliminar token">
                                         <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p id="no-invitations-msg" class="text-sm text-dynamic-secondary text-center py-4">No hay invitaciones activas.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Historiales en Tiempo Real -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Historial de Actividad Reciente -->
                <div class="card">
                    <h2 class="text-xl font-semibold text-dynamic-primary mb-4">Registro de Actividad Reciente</h2>
                    <div class="space-y-4 h-96 overflow-y-auto pr-2">
                        <?php if (!empty($latestInteractions)): ?>
                            <?php foreach ($latestInteractions as $interaction): ?>
                                <?php
                                    $date = new DateTime($interaction['timestamp'], new DateTimeZone('UTC'));
                                    $date->setTimezone(new DateTimeZone('America/Santiago'));
                                ?>
                                <div class="flex items-center gap-4 p-3 border-b border-dynamic">
                                    <?php if ($interaction['tipo_interaccion'] == 'clic'): ?>
                                        <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M3 12h18M12 3v18"/></svg></div>
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-full bg-sky-500/20 flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-sky-500"><path d="M21.75 9.812a9.42 9.42 0 0 1-8.625 11.188 9.42 9.42 0 0 1-10.313-7.313 9.42 9.42 0 0 1 6.25-11.438 9.42 9.42 0 0 1 11.188 6.25Z"/><path d="M12 12v-2"/></svg></div>
                                    <?php endif; ?>
                                    <div class="text-sm flex-grow">
                                        <p class="text-dynamic-primary">
                                            <a href="/contact/<?= htmlspecialchars($interaction['id_contacto']) ?>" class="font-bold hover:text-sky-500"><?= htmlspecialchars($interaction['interactor_name']) ?></a> 
                                            ha <?= $interaction['tipo_interaccion'] == 'clic' ? 'hecho <span class="font-semibold text-emerald-400">clic</span>' : ' <span class="font-semibold text-sky-400">abierto</span> el correo' ?>.
                                        </p>
                                    </div>
                                    <span class="text-xs text-dynamic-secondary flex-shrink-0"><?= $date->format('H:i') ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-dynamic-secondary text-sm text-center py-10">No hay interacciones recientes para mostrar.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Historial de Últimas Bajas -->
                <div class="card">
                    <h2 class="text-xl font-semibold text-dynamic-primary mb-4">Últimas Bajas Registradas</h2>
                    <div class="space-y-4 h-96 overflow-y-auto pr-2">
                        <?php if (!empty($latestUnsubscribes)): ?>
                            <?php foreach ($latestUnsubscribes as $unsub): ?>
                                <?php
                                    $date = new DateTime($unsub['timestamp'], new DateTimeZone('UTC'));
                                    $date->setTimezone(new DateTimeZone('America/Santiago'));
                                ?>
                                <div class="flex items-center gap-4 p-3 border-b border-dynamic">
                                     <div class="w-8 h-8 rounded-full bg-red-500/20 flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></div>
                                    <div class="text-sm flex-grow">
                                        <p class="text-dynamic-primary">
                                            <a href="/contact/<?= htmlspecialchars($unsub['id_contacto']) ?>" class="font-bold hover:text-sky-500"><?= htmlspecialchars($unsub['interactor_name']) ?></a> 
                                            se ha dado de baja.
                                        </p>
                                    </div>
                                    <span class="text-xs text-dynamic-secondary flex-shrink-0"><?= $date->format('d-m H:i') ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-dynamic-secondary text-sm text-center py-10">No se han registrado bajas en esta campaña.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="card text-center py-12">
                <h2 class="text-xl font-semibold text-dynamic-primary">No hay datos para mostrar</h2>
                <p class="text-dynamic-secondary mt-2">No se encontró información para la campaña seleccionada o no hay campañas creadas.</p>
                <button id="openModalBtn-alt" class="mt-6 bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg">Crear una Campaña</button>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Ventana Modal para Crear Campaña -->
<div id="campaignModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50 p-4">
    <div class="card p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-2xl font-bold text-dynamic-primary mb-4">Nueva Campaña</h2>
        <form id="createCampaignForm" novalidate>
            <div class="mb-6">
                <label for="campaign_name" class="block mb-2 text-sm font-medium text-dynamic-secondary">Nombre de la Campaña</label>
                <input type="text" id="campaign_name" name="nombre_campana" class="w-full p-2.5 text-sm rounded-lg bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-dynamic-primary focus:ring-sky-500 focus:border-sky-500" placeholder="Ej: Campaña de Onboarding Q4" required>
            </div>
            <div id="form-feedback" class="text-sm mb-4"></div>
            <div class="flex justify-end gap-4">
                <button type="button" id="closeModalBtn" class="bg-slate-500 hover:bg-slate-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">Cancelar</button>
                <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">Crear Campaña</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

