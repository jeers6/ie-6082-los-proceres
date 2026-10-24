<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario'] !== 'jjauyajesus' || $_SESSION['rol'] !== 'desarrollador') {
    header('Location: login.php');
    exit();
}

$nombre_completo = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
$foto = isset($_SESSION['foto']) ? $_SESSION['foto'] : 'foto.jpg';
$iniciales = strtoupper(substr($_SESSION['nombre'], 0, 1) . substr($_SESSION['apellido'], 0, 1));

// Estadísticas
$totalUsuarios = $pdo->query("SELECT COUNT(*) as total FROM usuarios")->fetch()['total'];
$totalCursos = $pdo->query("SELECT COUNT(*) as total FROM cursos")->fetch()['total'];
$totalAvisos = $pdo->query("SELECT COUNT(*) as total FROM avisos")->fetch()['total'];
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id ASC")->fetchAll();
$avisos = $pdo->query("SELECT * FROM avisos ORDER BY fecha_publicacion DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Desarrollador Web | I.E. 6082 Los Próceres</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body style="background: linear-gradient(135deg, var(--bg-gris-claro) 0%, #e9ecef 100%);">
    <header class="dashboard-header">
        <div class="container dashboard-header-inner">
            <div class="dashboard-brand">
                <div class="dashboard-brand-emblem">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 15L8.5 16.5L9.5 12.5L6.5 9.5L10.5 9L12 5L13.5 9L17.5 9.5L14.5 12.5L15.5 16.5L12 15Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6" />
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">Panel Exclusivo - Desarrollador Web Institucional</span>
                    <span class="brand-subtitle">I.E. 6082 Los Próceres | Acceso Restringido</span>
                </div>
            </div>
            <div class="dashboard-userbar">
                <div class="dev-badge">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 10.6667L8 12.6667L10 10.6667" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10 5.33333L8 3.33333L6 5.33333" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8 3.33333V12.6667" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>ROOT DEV ACCESS</span>
                </div>
                <div class="dashboard-user">
                    <?php if ($foto && $foto === 'foto.jpg'): ?>
                        <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto desarrollador" class="dashboard-avatar">
                    <?php else: ?>
                        <div class="dashboard-avatar" style="display:grid;place-items:center;font-weight:800;"><?php echo $iniciales; ?></div>
                    <?php endif; ?>
                    <div class="dashboard-user-info">
                        <span class="dashboard-user-name"><?php echo htmlspecialchars($nombre_completo); ?></span>
                        <span class="dashboard-user-meta">Desarrollador Web Institucional</span>
                    </div>
                </div>
                <a class="btn-logout" href="logout.php">
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container dashboard-main">
            <section class="welcome-banner">
                <div class="welcome-banner-content">
                    <span class="welcome-kicker">Panel de Control Administrativo</span>
                    <h1 class="welcome-title">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
                    <p class="welcome-text">
                        Acceso exclusivo para el Desarrollador Web Institucional. Monitoreo en vivo del servidor,
                        gestión de usuarios, publicación de comunicados y herramientas de depuración.
                    </p>
                </div>
            </section>

            <div class="stats-grid" style="margin-top: 0;">
                <article class="stat-card">
                    <span class="stat-value"><?php echo $totalUsuarios; ?></span>
                    <span class="stat-label">Usuarios Registrados</span>
                </article>
                <article class="stat-card">
                    <span class="stat-value"><?php echo $totalCursos; ?></span>
                    <span class="stat-label">Cursos Activos</span>
                </article>
                <article class="stat-card">
                    <span class="stat-value"><?php echo $totalAvisos; ?></span>
                    <span class="stat-label">Comunicados Publicados</span>
                </article>
                <article class="stat-card">
                    <span class="stat-value">ONLINE</span>
                    <span class="stat-label">Sistema Operativo</span>
                </article>
            </div>

            <section class="dev-panel">
                <header class="dev-panel-header">
                    <div class="dev-panel-title-group">
                        <div class="dev-panel-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.6" />
                                <path d="M8 21H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <path d="M12 17V21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="dev-panel-title">Métricas en Vivo del Servidor</h2>
                            <p class="dev-panel-subtitle">PHP / MySQL / XAMPP Environment</p>
                        </div>
                    </div>
                </header>
                <div class="dev-grid">
                    <article class="dev-card">
                        <header class="dev-card-header">
                            <h3 class="dev-card-title">PHP Info</h3>
                        </header>
                        <ul class="dev-card-list">
                            <li class="dev-card-item"><span>PHP Version</span><span class="dev-status-ok"><?php echo phpversion(); ?></span></li>
                            <li class="dev-card-item"><span>Memory Limit</span><span><?php echo ini_get('memory_limit'); ?></span></li>
                            <li class="dev-card-item"><span>Max Execution Time</span><span><?php echo ini_get('max_execution_time'); ?>s</span></li>
                            <li class="dev-card-item"><span>Upload Max Filesize</span><span><?php echo ini_get('upload_max_filesize'); ?></span></li>
                        </ul>
                    </article>
                    <article class="dev-card">
                        <header class="dev-card-header">
                            <h3 class="dev-card-title">MySQL / PDO</h3>
                        </header>
                        <ul class="dev-card-list">
                            <li class="dev-card-item"><span>Connection</span><span class="dev-status-ok">ACTIVE</span></li>
                            <li class="dev-card-item"><span>Database</span><span>ie_6082_los_proceres</span></li>
                            <li class="dev-card-item"><span>Charset</span><span>utf8mb4</span></li>
                            <li class="dev-card-item"><span>Driver</span><span>PDO MySQL</span></li>
                        </ul>
                    </article>
                    <article class="dev-card">
                        <header class="dev-card-header">
                            <h3 class="dev-card-title">Acceso Rápido</h3>
                        </header>
                        <ul class="dev-card-list">
                            <li class="dev-card-item"><span>Dashboard Estudiante</span><a href="dashboard.php" style="color:var(--azul-marino);font-weight:700;">Ir →</a></li>
                            <li class="dev-card-item"><span>Portal Público</span><a href="index.php" style="color:var(--azul-marino);font-weight:700;">Ir →</a></li>
                            <li class="dev-card-item"><span>Login</span><a href="login.php" style="color:var(--azul-marino);font-weight:700;">Ir →</a></li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="profile-section">
                <header class="profile-header">
                    <div>
                        <h2 class="profile-name">Inspector de Tablas - Base de Datos</h2>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">Visor de estructura: usuarios, cursos, entregas_tareas, avisos</p>
                    </div>
                </header>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: linear-gradient(90deg, var(--azul-marino), var(--azul-rey)); color: white;">
                                <th style="padding: 1rem; text-align: left;">ID</th>
                                <th style="padding: 1rem; text-align: left;">Usuario</th>
                                <th style="padding: 1rem; text-align: left;">Nombre Completo</th>
                                <th style="padding: 1rem; text-align: left;">DNI</th>
                                <th style="padding: 1rem; text-align: left;">Rol</th>
                                <th style="padding: 1rem; text-align: left;">Foto</th>
                                <th style="padding: 1rem; text-align: left;">XP</th>
                                <th style="padding: 1rem; text-align: left;">Racha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.9rem;"><?php echo $u['id']; ?></td>
                                <td style="padding: 0.9rem; font-weight: 600;"><?php echo htmlspecialchars($u['usuario']); ?></td>
                                <td style="padding: 0.9rem;"><?php echo htmlspecialchars($u['nombre'].' '.$u['apellido']); ?></td>
                                <td style="padding: 0.9rem;"><?php echo htmlspecialchars($u['dni']); ?></td>
                                <td style="padding: 0.9rem;"><span style="padding: 0.4rem 0.8rem; border-radius: 999px; background: <?php echo $u['rol']==='desarrollador'?'rgba(192,57,43,0.15)':($u['rol']==='profesor'?'rgba(11,60,93,0.15)':'rgba(56,161,105,0.15)'); ?>; color: <?php echo $u['rol']==='desarrollador'?'var(--rojo-institucional)':($u['rol']==='profesor'?'var(--azul-marino)':'var(--success-color)'); ?>; font-weight: 700;"><?php echo ucfirst($u['rol']); ?></span></td>
                                <td style="padding: 0.9rem;"><?php echo $u['foto'] ? htmlspecialchars($u['foto']) : 'NULL (Iniciales)'; ?></td>
                                <td style="padding: 0.9rem;"><?php echo $u['nivel_xp']; ?></td>
                                <td style="padding: 0.9rem;">🔥 <?php echo $u['racha_dias']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="calculator-section">
                <header class="calculator-header">
                    <div class="calculator-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 21H5C3.89543 21 3 20.1046 3 19V5C3 4.89543 3.89543 4 5 4H19C20.1046 4 21 4.89543 21 5V19C21 20.1046 20.1046 21 19 21Z" stroke="currentColor" stroke-width="1.6" />
                            <path d="M7 7H17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            <path d="M7 12H9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            <path d="M15 12H17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            <path d="M7 17H9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            <path d="M15 17H17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="calculator-title">Publicador de Comunicados Generales</h2>
                        <p style="color: var(--text-secondary);">Gestión en vivo de avisos para toda la comunidad</p>
                    </div>
                </header>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($avisos as $av): ?>
                        <article style="padding: 1.5rem; border-radius: var(--radius-lg); background: linear-gradient(90deg, rgba(11,60,93,0.05), transparent); border: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                                <h3 style="font-weight: 700; color: var(--azul-marino);"><?php echo htmlspecialchars($av['titulo']); ?></h3>
                                <span style="padding: 0.4rem 0.8rem; border-radius: 999px; background: <?php echo $av['tipo']==='urgente'?'rgba(192,57,43,0.15)':'rgba(11,60,93,0.15)'; ?>; color: <?php echo $av['tipo']==='urgente'?'var(--rojo-institucional)':'var(--azul-marino)'; ?>; font-weight: 600;"><?php echo strtoupper($av['tipo']); ?></span>
                            </div>
                            <p style="color: var(--text-secondary); margin-bottom: 0.8rem;"><?php echo nl2br(htmlspecialchars($av['contenido'])); ?></p>
                            <div style="display: flex; justify-content: space-between; color: var(--text-light); font-size: 0.9rem;">
                                <span>Autor: <?php echo htmlspecialchars($av['autor']); ?></span>
                                <span><?php echo date('d/m/Y H:i', strtotime($av['fecha_publicacion'])); ?></span>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <div style="padding: 2rem; border-radius: var(--radius-lg); background: var(--bg-surface); border: 1px solid var(--border-color); height: fit-content;">
                        <h3 style="font-weight: 700; color: var(--azul-marino); margin-bottom: 1.5rem;">Nuevo Comunicado</h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <input type="text" placeholder="Título del comunicado" style="padding: 0.9rem; border-radius: var(--radius-md); border: 2px solid var(--border-color); font-family: inherit;">
                            <select style="padding: 0.9rem; border-radius: var(--radius-md); border: 2px solid var(--border-color); font-family: inherit;">
                                <option value="general">General</option>
                                <option value="urgente">Urgente</option>
                                <option value="evento">Evento</option>
                            </select>
                            <textarea rows="6" placeholder="Contenido del comunicado..." style="padding: 0.9rem; border-radius: var(--radius-md); border: 2px solid var(--border-color); font-family: inherit; resize: vertical;"></textarea>
                            <button class="btn btn-institutional" style="justify-content: center;">Publicar Comunicado</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="dashboard-footer">
        <div class="container">
            <p>Panel Exclusivo Desarrollador Web Institucional - I.E. 6082 Los Próceres | &copy; 2026</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>

</html>
