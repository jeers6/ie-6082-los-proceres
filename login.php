<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, complete todos los campos para acceder al sistema.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ?");
            $stmt->execute([$usuario, $password]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellido'] = $user['apellido'];
                $_SESSION['dni'] = $user['dni'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['foto'] = $user['foto'];
                $_SESSION['nivel_xp'] = $user['nivel_xp'];
                $_SESSION['racha_dias'] = $user['racha_dias'];

                if ($user['rol'] === 'desarrollador') {
                    header('Location: admin.php');
                    exit();
                } elseif ($user['rol'] === 'estudiante') {
                    header('Location: dashboard.php');
                    exit();
                } elseif ($user['rol'] === 'profesor') {
                    header('Location: dashboard.php');
                    exit();
                } else {
                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                $error = 'Usuario o contraseña incorrectos. Verifique sus credenciales.';
            }
        } catch (PDOException $e) {
            $error = 'Error al intentar acceder al sistema. Intente nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema | I.E. 6082 Los Próceres</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="login-body">
    <section class="login-visual">
        <div class="login-visual-content">
            <div class="login-visual-header">
                <div class="brand-emblem">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3L3 7V17L12 21L21 17V7L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                        <path d="M12 12L21 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12 12V21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12 12L3 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="12" cy="12" r="1.4" fill="currentColor" />
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">I.E. 6082 Los Próceres</span>
                    <span class="brand-subtitle">Sistema de Gestión Académica</span>
                </div>
            </div>
            <div>
                <h1 class="login-visual-title">Campus Virtual Institucional</h1>
                <p class="login-visual-text">
                    Acceso unificado con separación por rol. Desarrollador con panel administrativo exclusivo.
                    Estudiantes acceden al Campus Gamificado con XP, Racha de Estudio y módulos interactivos.
                </p>
                <ul class="login-visual-list">
                    <li>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.6667 5L7.5 14.1667L3.33334 10" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>Navegación Multipágina Interactiva</span>
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.6667 5L7.5 14.1667L3.33334 10" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>Gamificación: XP, Rachas y Trivia</span>
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.6667 5L7.5 14.1667L3.33334 10" stroke="#d4af37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>Panel Exclusivo para Desarrollador Web</span>
                    </li>
                </ul>
            </div>
        </div>
        <div style="position: relative; color: rgba(255,255,255,0.7);">
            <p>&copy; 2026 I.E. 6082 Los Próceres - Arquitectura por Roles</p>
        </div>
    </section>

    <section class="login-panel">
        <div class="login-card">
            <header class="login-card-header">
                <span class="section-kicker" style="background: rgba(11, 60, 93, 0.1); color: var(--azul-marino); border-color: rgba(11, 60, 93, 0.3);">Acceso al Sistema</span>
                <h2 class="login-card-title">Iniciar Sesión</h2>
                <p class="login-card-subtitle">Seleccione su perfil para ingresar correctamente</p>
            </header>

            <?php if ($error): ?>
                <div class="alert-error">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="9" cy="9" r="7.5" stroke="currentColor" stroke-width="1.6" />
                        <path d="M9 5.25V9.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        <circle cx="9" cy="12.75" r="0.75" fill="currentColor" />
                    </svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label class="form-label" for="usuario">Usuario</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="9" cy="6.75" r="3.75" stroke="currentColor" stroke-width="1.6" />
                                <path d="M2.25 15C2.25 12.1005 5.10051 9.75 9 9.75C12.8995 9.75 15.75 12.1005 15.75 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                        </span>
                        <input class="form-input" type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="8.25" width="12" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.6" />
                                <path d="M6 8.25V6C6 4.34315 7.34315 3 9 3C10.6569 3 12 4.34315 12 6V8.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <circle cx="9" cy="12" r="1" fill="currentColor" />
                            </svg>
                        </span>
                        <input class="form-input" type="password" id="password" name="password" placeholder="Ingrese su contraseña" required autocomplete="current-password">
                    </div>
                </div>

                <button class="btn btn-login" type="submit">
                    <span>Ingresar al Sistema</span>
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.75 9H14.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M9.75 4.5L14.25 9L9.75 13.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </form>

            <a class="back-link" href="index.php">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.25 9H3.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M8.25 13.5L3.75 9L8.25 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Volver al Portal Institucional</span>
            </a>
        </div>
    </section>

    <script src="script.js"></script>
</body>

</html>
