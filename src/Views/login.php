<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="es" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - KYD Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 dark:bg-slate-900 flex items-center justify-center min-h-screen p-4 transition-colors duration-300">
    
    <!-- Contenedor Principal del Login -->
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="/assets/images/logo.svg" alt="KYD Logo" class="mx-auto h-24 w-24 mb-4"/>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Bienvenido</h1>
            <p class="text-slate-500 dark:text-slate-400">Inicia sesión para acceder al dashboard.</p>
        </div>

        <div class="p-8 space-y-8 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700">
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-500/10 text-red-600 dark:text-red-400 text-sm p-3 rounded-md border border-red-500/20"><?= $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-500/10 text-green-600 dark:text-green-400 text-sm p-3 rounded-md border border-green-500/20"><?= $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form class="space-y-6" action="/login" method="POST">
                <div>
                    <label for="username" class="block mb-2 text-sm font-medium text-slate-600 dark:text-slate-300">Usuario</label>
                    <input type="text" name="username" id="username" class="w-full p-2.5 bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white rounded-lg focus:ring-sky-500 focus:border-sky-500" required>
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-slate-600 dark:text-slate-300">Contraseña</label>
                    <input type="password" name="password" id="password" class="w-full p-2.5 bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white rounded-lg focus:ring-sky-500 focus:border-sky-500" required>
                </div>
                <button type="submit" class="w-full py-2.5 px-5 bg-sky-600 text-white font-bold rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-4 focus:ring-sky-800 transition-colors">
                    Acceder
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                ¿No tienes una cuenta? 
                <button id="openRegisterModal" class="font-semibold text-sky-500 hover:underline">Crear una con código de invitación</button>
            </p>
        </div>
    </div>

    <!-- Modal de Registro -->
    <div id="registerModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50 p-4">
        <div class="w-full max-w-md p-8 space-y-6 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 relative">
            <button id="closeRegisterModal" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div id="feedback" class="text-sm p-3 rounded-md hidden"></div>
            
            <!-- Paso 1: Verificar Token -->
            <div id="step1">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Registro</h2>
                <p class="text-slate-500 dark:text-slate-400 mb-6">Ingresa tu código de invitación para continuar.</p>
                <div class="space-y-4">
                    <div>
                        <label for="invitation_token" class="block mb-2 text-sm font-medium text-slate-600 dark:text-slate-300">Código de Invitación</label>
                        <input type="text" id="invitation_token" class="w-full p-2.5 bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white rounded-lg focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <button id="verifyTokenBtn" class="w-full py-2.5 px-5 bg-sky-600 text-white font-bold rounded-lg hover:bg-sky-700 transition-colors">Verificar Código</button>
                </div>
            </div>

            <!-- Paso 2: Crear Cuenta -->
            <form id="registerForm" class="hidden">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Crear Cuenta</h2>
                <p class="text-slate-500 dark:text-slate-400 mb-6">Completa tus datos para finalizar.</p>
                <div class="space-y-4">
                    <input type="hidden" id="verified_token" name="token">
                    <div>
                        <label for="reg_username" class="block mb-2 text-sm font-medium text-slate-600 dark:text-slate-300">Nombre de Usuario</label>
                        <input type="text" name="username" id="reg_username" class="w-full p-2.5 bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white rounded-lg focus:ring-sky-500 focus:border-sky-500" required>
                    </div>
                    <div>
                        <label for="reg_password" class="block mb-2 text-sm font-medium text-slate-600 dark:text-slate-300">Contraseña</label>
                        <input type="password" name="password" id="reg_password" class="w-full p-2.5 bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white rounded-lg focus:ring-sky-500 focus:border-sky-500" required>
                    </div>
                    <div>
                        <label for="reg_password_confirm" class="block mb-2 text-sm font-medium text-slate-600 dark:text-slate-300">Confirmar Contraseña</label>
                        <input type="password" name="password_confirm" id="reg_password_confirm" class="w-full p-2.5 bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white rounded-lg focus:ring-sky-500 focus:border-sky-500" required>
                    </div>
                    <button type="submit" class="w-full py-2.5 px-5 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-colors">Registrarse</button>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Lógica del Modal de Registro ---
    const openBtn = document.getElementById('openRegisterModal');
    const closeBtn = document.getElementById('closeRegisterModal');
    const modal = document.getElementById('registerModal');
    
    const step1 = document.getElementById('step1');
    const registerForm = document.getElementById('registerForm');
    const verifyTokenBtn = document.getElementById('verifyTokenBtn');
    const feedbackDiv = document.getElementById('feedback');
    
    const showFeedback = (message, isError) => {
        feedbackDiv.textContent = message;
        feedbackDiv.className = `text-sm p-3 rounded-md ${isError ? 'bg-red-500/10 text-red-600 dark:text-red-400' : 'bg-green-500/10 text-green-600 dark:text-green-400'}`;
        feedbackDiv.classList.remove('hidden');
    };

    const resetModal = () => {
        step1.classList.remove('hidden');
        registerForm.classList.add('hidden');
        feedbackDiv.classList.add('hidden');
        document.getElementById('invitation_token').value = '';
        registerForm.reset();
    };

    openBtn.addEventListener('click', () => {
        resetModal();
        modal.classList.remove('hidden');
    });

    closeBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // --- Paso 1: Verificar el token ---
    verifyTokenBtn.addEventListener('click', async () => {
        const tokenInput = document.getElementById('invitation_token');
        const token = tokenInput.value.trim();
        if (!token) {
            showFeedback('Por favor, ingresa un código de invitación.', true);
            return;
        }

        verifyTokenBtn.disabled = true;
        verifyTokenBtn.textContent = 'Verificando...';

        try {
            const response = await fetch('/api/verify-token', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: token })
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Error del servidor');
            
            // Transición al paso 2
            document.getElementById('verified_token').value = token;
            step1.classList.add('hidden');
            registerForm.classList.remove('hidden');
            feedbackDiv.classList.add('hidden');

        } catch (error) {
            showFeedback(error.message, true);
        } finally {
            verifyTokenBtn.disabled = false;
            verifyTokenBtn.textContent = 'Verificar Código';
        }
    });
    
    // --- Paso 2: Enviar el formulario de registro ---
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        const formData = new FormData(registerForm);
        const data = Object.fromEntries(formData.entries());

        submitBtn.disabled = true;
        submitBtn.textContent = 'Registrando...';

        try {
            const response = await fetch('/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Error del servidor');

            // Redirigir al login con mensaje de éxito
            window.location.href = '/login?success=1';

        } catch (error) {
            showFeedback(error.message, true);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Registrarse';
        }
    });
});
</script>
</body>
</html>

