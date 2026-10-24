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

// Estadísticas generales
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .modal-close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            cursor: pointer;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .form-group-admin {
            margin-bottom: 1rem;
        }
        .form-group-admin label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--azul-marino);
        }
        .form-group-admin input, .form-group-admin select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-family: inherit;
        }
        .action-btn {
            padding: 0.4rem 0.7rem;
            margin: 0.2rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .btn-edit { background: rgba(11,60,93,0.15); color: var(--azul-marino); }
        .btn-reset { background: rgba(212,175,55,0.2); color: #b8860b; }
        .btn-delete { background: rgba(192,57,43,0.15); color: var(--rojo-institucional); }
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .analytics-card {
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--bg-surface), #f8f9fa);
            border: 2px solid var(--border-color);
            text-align: center;
        }
        .analytics-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--azul-marino);
            display: block;
        }
        .analytics-label {
            color: var(--text-secondary);
            font-weight: 600;
            margin-top: 0.5rem;
            display: block;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .chart-container {
            background: var(--bg-surface);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
        }
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                    <span class="welcome-kicker">Panel de Control Administrativo - Data Analytics</span>
                    <h1 class="welcome-title">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
                    <p class="welcome-text">
                        Panel exclusivo para Jesús Jauya (Desarrollador / Data Analyst). 
                        Monitoreo en vivo, gestión completa de alumnos, analítica educativa y publicación de comunicados.
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

            <!-- Dashboard de Analítica de Datos Educativos en Vivo -->
            <section class="profile-section" style="margin-top: 2rem;">
                <header class="profile-header">
                    <div>
                        <h2 class="profile-name">📊 Dashboard de Analítica de Datos Educativos en Vivo</h2>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">Métricas calculadas dinámicamente desde la Base de Datos</p>
                    </div>
                </header>
                <div class="analytics-grid" id="analyticsGrid">
                    <article class="analytics-card">
                        <span class="analytics-value" id="avgXP">--</span>
                        <span class="analytics-label">Promedio de XP Global (Estudiantes)</span>
                    </article>
                    <article class="analytics-card">
                        <span class="analytics-value" id="topRacha">--</span>
                        <span class="analytics-label">Líder de Racha</span>
                    </article>
                    <article class="analytics-card">
                        <span class="analytics-value" id="solPend">--</span>
                        <span class="analytics-label">Solicitudes de Puntos Pendientes</span>
                    </article>
                    <article class="analytics-card">
                        <span class="analytics-value" id="tasaAct">--</span>
                        <span class="analytics-label">Tasa de Actividad</span>
                    </article>
                </div>
                <div class="charts-grid">
                    <div class="chart-container">
                        <h3 style="margin-bottom: 1rem; color: var(--azul-marino); font-weight: 700;">Distribución de XP y Racha por Estudiante</h3>
                        <canvas id="chartXP"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3 style="margin-bottom: 1rem; color: var(--azul-marino); font-weight: 700;">Solicitudes: Aceptadas vs Pendientes</h3>
                        <canvas id="chartSolicitudes"></canvas>
                    </div>
                </div>
            </section>

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
                        <h2 class="profile-name">👥 Gestión Completa de Alumnos (CRUD)</h2>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">Registrar, editar XP/Racha, reiniciar puntos, eliminar estudiantes</p>
                    </div>
                    <button class="btn btn-institutional" onclick="abrirModalRegistro()">+ Registrar Nuevo Estudiante</button>
                </header>
                <div style="overflow-x: auto; margin-top: 1.5rem;">
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
                                <th style="padding: 1rem; text-align: left;">Bolso Puntos</th>
                                <th style="padding: 1rem; text-align: left;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaUsuarios">
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
                                <td style="padding: 0.9rem;">🎒 <?php echo isset($u['bolso_puntos']) ? $u['bolso_puntos'] : 0; ?></td>
                                <td style="padding: 0.9rem;">
                                    <?php if ($u['rol'] === 'estudiante'): ?>
                                    <button class="action-btn btn-edit" onclick="editarXPRacha(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['usuario']); ?>', <?php echo $u['nivel_xp']; ?>, <?php echo $u['racha_dias']; ?>)">Editar XP/Racha</button>
                                    <button class="action-btn btn-reset" onclick="reiniciarPuntos(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['usuario']); ?>')">Reiniciar Puntos</button>
                                    <button class="action-btn btn-delete" onclick="eliminarEstudiante(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['usuario']); ?>')">Eliminar</button>
                                    <?php else: ?>
                                    <span style="color: var(--text-light); font-size: 0.85rem;">Sin acciones</span>
                                    <?php endif; ?>
                                </td>
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
                    <div style="display: flex; flex-direction: column; gap: 1rem; max-height: 500px; overflow-y: auto;" id="listaAvisos">
                        <?php foreach ($avisos as $av): ?>
                        <article style="padding: 1.5rem; border-radius: var(--radius-lg); background: linear-gradient(90deg, rgba(11,60,93,0.05), transparent); border: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem; flex-wrap: wrap; gap: 0.5rem;">
                                <h3 style="font-weight: 700; color: var(--azul-marino);"><?php echo htmlspecialchars($av['titulo']); ?></h3>
                                <span style="padding: 0.4rem 0.8rem; border-radius: 999px; background: <?php echo $av['tipo']==='urgente'?'rgba(192,57,43,0.15)':($av['tipo']==='evento'?'rgba(212,175,55,0.2)':'rgba(11,60,93,0.15)'); ?>; color: <?php echo $av['tipo']==='urgente'?'var(--rojo-institucional)':($av['tipo']==='evento'?'#b8860b':'var(--azul-marino)'); ?>; font-weight: 600;"><?php echo strtoupper($av['tipo']); ?></span>
                            </div>
                            <p style="color: var(--text-secondary); margin-bottom: 0.8rem;"><?php echo nl2br(htmlspecialchars($av['contenido'])); ?></p>
                            <div style="display: flex; justify-content: space-between; color: var(--text-light); font-size: 0.9rem; flex-wrap: wrap;">
                                <span>Autor: <?php echo htmlspecialchars($av['autor']); ?></span>
                                <span><?php echo date('d/m/Y H:i', strtotime($av['fecha_publicacion'])); ?></span>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <div style="padding: 2rem; border-radius: var(--radius-lg); background: var(--bg-surface); border: 1px solid var(--border-color); height: fit-content;">
                        <h3 style="font-weight: 700; color: var(--azul-marino); margin-bottom: 1.5rem;">Nuevo Comunicado</h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <input type="text" id="avisoTitulo" placeholder="Título del comunicado" style="padding: 0.9rem; border-radius: var(--radius-md); border: 2px solid var(--border-color); font-family: inherit;">
                            <select id="avisoTipo" style="padding: 0.9rem; border-radius: var(--radius-md); border: 2px solid var(--border-color); font-family: inherit;">
                                <option value="general">General</option>
                                <option value="urgente">Urgente</option>
                                <option value="evento">Evento</option>
                            </select>
                            <textarea id="avisoContenido" rows="6" placeholder="Contenido del comunicado..." style="padding: 0.9rem; border-radius: var(--radius-md); border: 2px solid var(--border-color); font-family: inherit; resize: vertical;"></textarea>
                            <button class="btn btn-institutional" style="justify-content: center;" onclick="publicarComunicado()">Publicar Comunicado</button>
                            <div id="avisoMsg" style="text-align: center; font-weight: 600;"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal Registro Estudiante -->
    <div id="modalRegistro" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="cerrarModalRegistro()">&times;</span>
            <h2 style="color: var(--azul-marino); margin-bottom: 1.5rem;">Registrar Nuevo Estudiante</h2>
            <div class="form-group-admin">
                <label>Usuario *</label>
                <input type="text" id="regUsuario" placeholder="Ej: alumno5">
            </div>
            <div class="form-group-admin">
                <label>Contraseña</label>
                <input type="text" id="regPassword" value="123456">
            </div>
            <div class="form-group-admin">
                <label>Nombre *</label>
                <input type="text" id="regNombre" placeholder="Nombre">
            </div>
            <div class="form-group-admin">
                <label>Apellido *</label>
                <input type="text" id="regApellido" placeholder="Apellido">
            </div>
            <div class="form-group-admin">
                <label>DNI *</label>
                <input type="text" id="regDNI" placeholder="DNI">
            </div>
            <div class="form-group-admin">
                <label>Foto (opcional)</label>
                <input type="text" id="regFoto" placeholder="foto.jpg o dejar vacío">
            </div>
            <button class="btn btn-institutional" style="width: 100%; justify-content: center; margin-top: 1rem;" onclick="guardarEstudiante()">Registrar</button>
            <div id="regMsg" style="text-align: center; margin-top: 0.8rem; font-weight: 600;"></div>
        </div>
    </div>

    <!-- Modal Editar XP/Racha -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="cerrarModalEditar()">&times;</span>
            <h2 style="color: var(--azul-marino); margin-bottom: 1.5rem;">Editar XP / Racha</h2>
            <p style="margin-bottom: 1rem;"><strong>Usuario:</strong> <span id="editUsuario"></span></p>
            <input type="hidden" id="editId">
            <div class="form-group-admin">
                <label>Nivel XP</label>
                <input type="number" id="editXP">
            </div>
            <div class="form-group-admin">
                <label>Racha Días</label>
                <input type="number" id="editRacha">
            </div>
            <button class="btn btn-institutional" style="width: 100%; justify-content: center; margin-top: 1rem;" onclick="guardarXPRacha()">Guardar Cambios</button>
            <div id="editMsg" style="text-align: center; margin-top: 0.8rem; font-weight: 600;"></div>
        </div>
    </div>

    <footer class="dashboard-footer">
        <div class="container">
            <p>Panel Exclusivo Desarrollador Web Institucional - I.E. 6082 Los Próceres | &copy; 2026</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        let chartXP, chartSol;

        async function cargarEstadisticas() {
            try {
                const res = await fetch('api_admin.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({accion: 'estadisticas'})
                });
                const data = await res.json();
                if (data.ok) {
                    document.getElementById('avgXP').textContent = data.promedio_xp;
                    document.getElementById('topRacha').textContent = data.top_racha ? data.top_racha.usuario + ' (' + data.top_racha.racha_dias + ' días)' : '--';
                    document.getElementById('solPend').textContent = data.solicitudes_pendientes;
                    document.getElementById('tasaAct').textContent = data.distribucion.length + ' Activos';

                    // Gráfico XP y Racha
                    const ctx1 = document.getElementById('chartXP');
                    if (chartXP) chartXP.destroy();
                    chartXP = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: data.distribucion.map(d => d.usuario),
                            datasets: [{
                                label: 'XP',
                                data: data.distribucion.map(d => d.nivel_xp),
                                backgroundColor: 'rgba(11, 60, 93, 0.7)'
                            }, {
                                label: 'Racha (días)',
                                data: data.distribucion.map(d => d.racha_dias),
                                backgroundColor: 'rgba(212, 175, 55, 0.7)'
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: { y: { beginAtZero: true } }
                        }
                    });

                    // Gráfico Solicitudes
                    const ctx2 = document.getElementById('chartSolicitudes');
                    if (chartSol) chartSol.destroy();
                    chartSol = new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: ['Aceptadas', 'Pendientes', 'Rechazadas'],
                            datasets: [{
                                data: [data.solicitudes_aceptadas, data.solicitudes_pendientes, data.solicitudes_rechazadas],
                                backgroundColor: ['rgba(56, 161, 105, 0.7)', 'rgba(212, 175, 55, 0.7)', 'rgba(192, 57, 43, 0.7)']
                            }]
                        },
                        options: { responsive: true }
                    });
                }
            } catch(e) {
                console.error(e);
            }
        }

        function abrirModalRegistro() {
            document.getElementById('modalRegistro').classList.add('active');
        }
        function cerrarModalRegistro() {
            document.getElementById('modalRegistro').classList.remove('active');
            document.getElementById('regMsg').textContent = '';
        }

        async function guardarEstudiante() {
            const data = {
                accion: 'registrar_estudiante',
                usuario: document.getElementById('regUsuario').value,
                password: document.getElementById('regPassword').value,
                nombre: document.getElementById('regNombre').value,
                apellido: document.getElementById('regApellido').value,
                dni: document.getElementById('regDNI').value,
                foto: document.getElementById('regFoto').value
            };
            const res = await fetch('api_admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const json = await res.json();
            const msg = document.getElementById('regMsg');
            if (json.ok) {
                msg.style.color = 'var(--success-color)';
                msg.textContent = json.mensaje;
                setTimeout(() => { location.reload(); }, 1500);
            } else {
                msg.style.color = 'var(--rojo-institucional)';
                msg.textContent = json.error;
            }
        }

        function editarXPRacha(id, usuario, xp, racha) {
            document.getElementById('modalEditar').classList.add('active');
            document.getElementById('editId').value = id;
            document.getElementById('editUsuario').textContent = usuario;
            document.getElementById('editXP').value = xp;
            document.getElementById('editRacha').value = racha;
        }
        function cerrarModalEditar() {
            document.getElementById('modalEditar').classList.remove('active');
            document.getElementById('editMsg').textContent = '';
        }

        async function guardarXPRacha() {
            const data = {
                accion: 'editar_xp_racha',
                id: document.getElementById('editId').value,
                nivel_xp: document.getElementById('editXP').value,
                racha_dias: document.getElementById('editRacha').value
            };
            const res = await fetch('api_admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const json = await res.json();
            const msg = document.getElementById('editMsg');
            if (json.ok) {
                msg.style.color = 'var(--success-color)';
                msg.textContent = json.mensaje;
                setTimeout(() => { location.reload(); }, 1500);
            } else {
                msg.style.color = 'var(--rojo-institucional)';
                msg.textContent = json.error;
            }
        }

        async function reiniciarPuntos(id, usuario) {
            if (!confirm('¿Reiniciar los puntos del bolso de ' + usuario + ' a 0?')) return;
            const res = await fetch('api_admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({accion: 'reiniciar_puntos', id: id})
            });
            const json = await res.json();
            if (json.ok) {
                alert(json.mensaje);
                location.reload();
            } else {
                alert(json.error);
            }
        }

        async function eliminarEstudiante(id, usuario) {
            if (!confirm('¿ELIMINAR al estudiante ' + usuario + '? Esta acción NO se puede deshacer.')) return;
            const res = await fetch('api_admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({accion: 'eliminar_estudiante', id: id})
            });
            const json = await res.json();
            if (json.ok) {
                alert(json.mensaje);
                location.reload();
            } else {
                alert(json.error);
            }
        }

        async function publicarComunicado() {
            const data = {
                accion: 'publicar_comunicado',
                titulo: document.getElementById('avisoTitulo').value,
                contenido: document.getElementById('avisoContenido').value,
                tipo: document.getElementById('avisoTipo').value
            };
            const res = await fetch('api_admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            const json = await res.json();
            const msg = document.getElementById('avisoMsg');
            if (json.ok) {
                msg.style.color = 'var(--success-color)';
                msg.textContent = json.mensaje;
                setTimeout(() => { location.reload(); }, 1500);
            } else {
                msg.style.color = 'var(--rojo-institucional)';
                msg.textContent = json.error;
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }

        document.addEventListener('DOMContentLoaded', cargarEstadisticas);
    </script>
</body>

</html>
