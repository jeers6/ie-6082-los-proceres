<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$id_curso = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) {
    header('Location: dashboard.php');
    exit();
}

$nombre_completo = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
$rol = $_SESSION['rol'];
$foto = isset($_SESSION['foto']) ? $_SESSION['foto'] : null;
$iniciales = strtoupper(substr($_SESSION['nombre'], 0, 1) . substr($_SESSION['apellido'], 0, 1));
$nivel_xp = isset($_SESSION['nivel_xp']) ? $_SESSION['nivel_xp'] : 0;
$racha_dias = isset($_SESSION['racha_dias']) ? $_SESSION['racha_dias'] : 1;

require_once 'banco_preguntas.php';

// Aseguramos que el árbol esté disponible para Matemática (o forzado a true para pruebas)
$arbol_disponible = true;
$arbol_disponible = st_banco($curso['codigo']) !== null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($curso['nombre']); ?> | I.E. 6082 Los Próceres</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body style="background: linear-gradient(135deg, var(--bg-gris-claro) 0%, #e9ecef 100%);">
    <header class="dashboard-header">
        <div class="container dashboard-header-inner">
            <div class="dashboard-brand">
                <div class="dashboard-brand-emblem">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3L3 7V17L12 21L21 17V7L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                        <path d="M12 12L21 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12 12V21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12 12L3 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="12" cy="12" r="1.4" fill="currentColor" />
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">Curso: <?php echo htmlspecialchars($curso['nombre']); ?></span>
                    <span class="brand-subtitle">Código: <?php echo htmlspecialchars($curso['codigo']); ?></span>
                </div>
            </div>
            <div class="dashboard-userbar">
                <?php if ($rol === 'desarrollador' && $foto === 'foto.jpg'): ?>
                    <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto" class="dashboard-avatar">
                <?php elseif ($rol !== 'desarrollador'): ?>
                    <div class="dashboard-avatar" style="display:grid;place-items:center;font-weight:800;"><?php echo $iniciales; ?></div>
                <?php endif; ?>
                <div class="dashboard-user-info">
                    <span class="dashboard-user-name"><?php echo htmlspecialchars($nombre_completo); ?></span>
                    <span class="dashboard-user-meta">🔥 <?php echo $racha_dias; ?> días | ⚡ <?php echo $nivel_xp; ?> XP</span>
                </div>
                <a class="btn-logout" href="dashboard.php">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.25 9H3.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8.25 13.5L3.75 9L8.25 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Volver al Dashboard</span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="container dashboard-main">
            <section class="welcome-banner">
                <div class="welcome-banner-content">
                    <span class="welcome-kicker"><?php echo $curso['icono']; ?> ÁREA CURRICULAR</span>
                    <h1 class="welcome-title"><?php echo htmlspecialchars($curso['nombre']); ?></h1>
                    <p class="welcome-text"><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                    <p style="font-weight: 700;">Docente a Cargo: <?php echo htmlspecialchars($curso['profesor']); ?></p>
                </div>
            </section>

            <?php if ($arbol_disponible): ?>
            <section id="skillTreeSection" class="skilltree-section">
                <header class="skilltree-header">
                    <span class="section-kicker">Árbol de Habilidades</span>
                    <h2 class="skilltree-title">Mapa de Conocimientos: <?php echo htmlspecialchars($curso['nombre']); ?></h2>
                    <p class="skilltree-sub">Avanza por las ramas negras (+1 punto por examen de rama) o enfrenta las rojas: Desafío Supremo (+3) y Canjear Puntos hacia tu Bolso.</p>
                </header>
                <div id="skillTreeRoot"
                     data-curso="<?php echo (int)$id_curso; ?>"
                     data-nombre="<?php echo htmlspecialchars($curso['nombre']); ?>"
                     data-readonly="<?php echo $rol === 'estudiante' ? '0' : '1'; ?>"></div>
                <ul class="skilltree-leyenda">
                    <li><span class="pt pt-negro"></span>Rama por tema (5 niveles)</li>
                    <li><span class="pt pt-rojo"></span>Desafío / Canje de puntos</li>
                    <li><span class="pt pt-gris"></span>Bloqueado</li>
                    <li>Pasa el cursor sobre un nivel para ver su detalle</li>
                </ul>
            </section>
            <?php endif; ?>

            <div class="dashboard-grid">
                <article class="dashboard-card dashboard-card-wide">
                    <header class="dashboard-card-header">
                        <div class="dashboard-card-title-group">
                            <div class="dashboard-card-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 19.5V4.5C4 3.67157 4.67157 3 5.5 3H18.5C19.3284 3 20 3.67157 20 4.5V19.5C20 20.3284 19.3284 21 18.5 21H5.5C4.67157 21 4 20.3284 4 19.5Z" stroke="currentColor" stroke-width="1.6" />
                                    <path d="M8 7H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                    <path d="M8 11H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                    <path d="M8 15H13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="dashboard-card-title">Temas / Unidades - Acordeón Interactivo</h2>
                                <p class="dashboard-card-subtitle">Desarrolla los contenidos por unidad</p>
                            </div>
                        </div>
                    </header>
                    <div class="dashboard-card-body" style="gap: 1rem;">
                        <details style="padding: 1.5rem; border-radius: var(--radius-lg); border: 2px solid var(--border-color); background: linear-gradient(90deg, rgba(11,60,93,0.05), transparent);" open>
                            <summary style="font-weight: 700; color: var(--azul-marino); cursor: pointer; font-size: 1.1rem;">Unidad 1: Fundamentos y Conceptos Básicos</summary>
                            <div style="margin-top: 1rem; padding-left: 1rem; display: flex; flex-direction: column; gap: 0.8rem;">
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">📄 Lectura 1.1 - Introducción al curso</a>
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">🎥 Video Tutorial - Explicación teórica</a>
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">📊 Presentación Interactiva</a>
                            </div>
                        </details>
                        <details style="padding: 1.5rem; border-radius: var(--radius-lg); border: 2px solid var(--border-color); background: linear-gradient(90deg, rgba(11,60,93,0.05), transparent);">
                            <summary style="font-weight: 700; color: var(--azul-marino); cursor: pointer; font-size: 1.1rem;">Unidad 2: Desarrollo de Competencias</summary>
                            <div style="margin-top: 1rem; padding-left: 1rem; display: flex; flex-direction: column; gap: 0.8rem;">
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">📄 Guía de Práctica N° 01</a>
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">🧩 Ejercicios Interactivos</a>
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">📚 Material Complementario</a>
                            </div>
                        </details>
                        <details style="padding: 1.5rem; border-radius: var(--radius-lg); border: 2px solid var(--border-color); background: linear-gradient(90deg, rgba(11,60,93,0.05), transparent);">
                            <summary style="font-weight: 700; color: var(--azul-marino); cursor: pointer; font-size: 1.1rem;">Unidad 3: Aplicación y Proyectos</summary>
                            <div style="margin-top: 1rem; padding-left: 1rem; display: flex; flex-direction: column; gap: 0.8rem;">
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">📝 Proyecto Integrador</a>
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">🔍 Casos Prácticos</a>
                                <a href="#" style="color: var(--azul-marino); font-weight: 600;">💡 Actividades de Refuerzo</a>
                            </div>
                        </details>
                    </div>
                </article>

                <article class="dashboard-card">
                    <header class="dashboard-card-header">
                        <div class="dashboard-card-title-group">
                            <div class="dashboard-card-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="dashboard-card-title">Zona de Entrega de Tareas</h2>
                                <p class="dashboard-card-subtitle">Simulación de subida</p>
                            </div>
                        </div>
                    </header>
                    <div class="dashboard-card-body">
                        <div style="padding: 1.5rem; border-radius: var(--radius-lg); background: linear-gradient(135deg, rgba(11,60,93,0.05), rgba(212,175,55,0.05)); border: 2px dashed var(--azul-marino); text-align: center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin: 0 auto 1rem;">
                                <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="var(--azul-marino)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M17 8L12 3L7 8" stroke="var(--azul-marino)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12 3V15" stroke="var(--azul-marino)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p style="font-weight: 700; color: var(--azul-marino); margin-bottom: 0.5rem;">Arrastra y suelta archivos aquí</p>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">o haz clic para seleccionar</p>
                            <button class="btn btn-institutional">Seleccionar Archivo</button>
                        </div>
                        <div style="padding: 1rem; border-radius: var(--radius-md); background: rgba(56,161,105,0.1); border: 1px solid rgba(56,161,105,0.4);">
                            <p style="color: var(--success-color); font-weight: 600;">✅ Tarea pendiente: Unidad 2 - Ejercicios</p>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">Fecha límite: 28/10/2026</p>
                        </div>
                    </div>
                </article>

                <article class="dashboard-card">
                    <header class="dashboard-card-header">
                        <div class="dashboard-card-title-group">
                            <div class="dashboard-card-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 6.253V19.253M12 6.253L19.5 12.253L12 6.253ZM12 6.253L4.5 12.253L12 6.253Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="dashboard-card-title">Recursos y Lecturas</h2>
                                <p class="dashboard-card-subtitle">Materiales digitales</p>
                            </div>
                        </div>
                    </header>
                    <div class="dashboard-card-body">
                        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                            <a href="#" style="padding: 1rem; border-radius: var(--radius-md); background: linear-gradient(90deg, rgba(11,60,93,0.08), transparent); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.8rem; color: var(--azul-marino); font-weight: 600;">
                                <span>📚</span>
                                <span>Biblioteca Digital del Curso</span>
                            </a>
                            <a href="#" style="padding: 1rem; border-radius: var(--radius-md); background: linear-gradient(90deg, rgba(11,60,93,0.08), transparent); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.8rem; color: var(--azul-marino); font-weight: 600;">
                                <span>📖</span>
                                <span>Glosario de Términos</span>
                            </a>
                            <a href="#" style="padding: 1rem; border-radius: var(--radius-md); background: linear-gradient(90deg, rgba(11,60,93,0.08), transparent); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.8rem; color: var(--azul-marino); font-weight: 600;">
                                <span>🎯</span>
                                <span>Banco de Preguntas</span>
                            </a>
                            <a href="#" style="padding: 1rem; border-radius: var(--radius-md); background: linear-gradient(90deg, rgba(11,60,93,0.08), transparent); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.8rem; color: var(--azul-marino); font-weight: 600;">
                                <span>📊</span>
                                <span>Repositorio de Exámenes</span>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </main>

    <footer class="dashboard-footer">
        <div class="container">
            <p>I.E. 6082 Los Próceres - Campus Virtual | Curso: <?php echo htmlspecialchars($curso['nombre']); ?></p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>

</html>
