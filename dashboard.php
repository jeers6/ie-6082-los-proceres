<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$nombre_completo = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
$rol = $_SESSION['rol'];
$foto = isset($_SESSION['foto']) ? $_SESSION['foto'] : null;
$iniciales = strtoupper(substr($_SESSION['nombre'], 0, 1) . substr($_SESSION['apellido'], 0, 1));
$nivel_xp = isset($_SESSION['nivel_xp']) ? $_SESSION['nivel_xp'] : 0;
$racha_dias = isset($_SESSION['racha_dias']) ? $_SESSION['racha_dias'] : 1;

$cursos = $pdo->query("SELECT * FROM cursos ORDER BY id ASC")->fetchAll();
$avisos = $pdo->query("SELECT * FROM avisos ORDER BY fecha_publicacion DESC LIMIT 3")->fetchAll();

// --- Bolso de Puntos (el saldo siempre se lee de la BD) ---
$bolso_puntos = 0;
$mis_solicitudes = [];
$solicitudes_pendientes = [];
$aviso_bolso = '';
try {
    $stmtB = $pdo->prepare("SELECT bolso_puntos FROM usuarios WHERE id = ?");
    $stmtB->execute([$_SESSION['usuario_id']]);
    $bolso_puntos = (int)$stmtB->fetchColumn();

    if ($rol === 'estudiante') {
        $stmtS = $pdo->prepare("SELECT s.*, c.nombre AS curso FROM solicitudes_puntos s
            JOIN cursos c ON c.id = s.id_curso WHERE s.id_estudiante = ?
            ORDER BY s.fecha_solicitud DESC LIMIT 5");
        $stmtS->execute([$_SESSION['usuario_id']]);
        $mis_solicitudes = $stmtS->fetchAll();
    } elseif (in_array($rol, ['profesor', 'desarrollador'], true)) {
        $solicitudes_pendientes = $pdo->query("SELECT s.*, c.nombre AS curso, u.nombre AS est_nombre, u.apellido AS est_apellido
            FROM solicitudes_puntos s
            JOIN cursos c ON c.id = s.id_curso
            JOIN usuarios u ON u.id = s.id_estudiante
            WHERE s.estado = 'pendiente' ORDER BY s.fecha_solicitud ASC")->fetchAll();
    }
} catch (PDOException $e) {
    $aviso_bolso = 'Falta ejecutar migracion_skill_tree.sql en phpMyAdmin para activar el Bolso de Puntos.';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Gamificado | I.E. 6082 Los Próceres</title>
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
                    <span class="brand-title">Campus Gamificado - I.E. 6082 Los Próceres</span>
                    <span class="brand-subtitle">Dashboard Interactivo por Rol</span>
                </div>
            </div>
            <div class="dashboard-userbar">
                <?php if ($rol === 'desarrollador' && $foto === 'foto.jpg'): ?>
                    <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto desarrollador" class="dashboard-avatar">
                <?php else: ?>
                    <div class="dashboard-avatar" style="display:grid;place-items:center;font-weight:800;"><?php echo $iniciales; ?></div>
                <?php endif; ?>
                <div class="dashboard-user-info">
                    <span class="dashboard-user-name"><?php echo htmlspecialchars($nombre_completo); ?></span>
                    <span class="dashboard-user-meta"><?php echo ucfirst($rol); ?> | 🔥 <?php echo $racha_dias; ?> Días | ⚡ <?php echo $nivel_xp; ?> XP</span>
                </div>
                <a class="btn-logout" href="logout.php">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.75 15.75H3.75C3.35218 15.75 2.97064 15.592 2.68934 15.3107C2.40804 15.0294 2.25 14.6478 2.25 14.25V3.75C2.25 3.35218 2.40804 2.97064 2.68934 2.68934C2.97064 2.40804 3.35218 2.25 3.75 2.25H6.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12 12.75L15.75 9L12 5.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.75 9H6.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </header>

    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 0; min-height: calc(100vh - 90px);">
        <!-- Sidebar Interactivo -->
        <aside style="background: linear-gradient(180deg, var(--azul-marino) 0%, #071528 100%); color: white; padding: 2rem 1rem;">
            <div style="margin-bottom: 2rem;">
                <h3 style="font-weight: 800; margin-bottom: 1.5rem; text-align: center;">MENÚ LATERAL</h3>
                <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <a href="dashboard.php" style="padding: 0.9rem 1rem; border-radius: var(--radius-md); background: rgba(212,175,55,0.2); border: 1px solid var(--dorado-sutil); display: flex; align-items: center; gap: 0.8rem; font-weight: 600;">
                        <span>🏠</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="#cursos" style="padding: 0.9rem 1rem; border-radius: var(--radius-md); transition: var(--transition); display: flex; align-items: center; gap: 0.8rem; font-weight: 600;">
                        <span>📚</span>
                        <span>Mis Cursos</span>
                    </a>
                    <a href="#calculadora" style="padding: 0.9rem 1rem; border-radius: var(--radius-md); transition: var(--transition); display: flex; align-items: center; gap: 0.8rem; font-weight: 600;">
                        <span>🧮</span>
                        <span>Calculadora de Notas</span>
                    </a>
                    <a href="#bolso" style="padding: 0.9rem 1rem; border-radius: var(--radius-md); transition: var(--transition); display: flex; align-items: center; gap: 0.8rem; font-weight: 600;">
                        <span>🎒</span>
                        <span>Bolso de Puntos</span>
                    </a>
                    <a href="#trivia" style="padding: 0.9rem 1rem; border-radius: var(--radius-md); transition: var(--transition); display: flex; align-items: center; gap: 0.8rem; font-weight: 600;">
                        <span>🎮</span>
                        <span>Pausa Activa / Trivia</span>
                    </a>
                    <a href="#biblioteca" style="padding: 0.9rem 1rem; border-radius: var(--radius-md); transition: var(--transition); display: flex; align-items: center; gap: 0.8rem; font-weight: 600;">
                        <span>📖</span>
                        <span>Biblioteca</span>
                    </a>
                    <a href="#perfil" style="padding: 0.9rem 1rem; border-radius: var(--radius-md); transition: var(--transition); display: flex; align-items: center; gap: 0.8rem; font-weight: 600;">
                        <span>👤</span>
                        <span>Mi Perfil</span>
                    </a>
                </nav>
            </div>
            <div style="padding: 1.2rem; border-radius: var(--radius-lg); background: rgba(212,175,55,0.15); border: 1px solid var(--dorado-sutil); text-align: center;">
                <p style="font-weight: 700; color: var(--dorado-sutil); margin-bottom: 0.5rem;">🔥 Racha de Estudio</p>
                <p style="font-size: 2rem; font-weight: 800;"><?php echo $racha_dias; ?> Días</p>
                <p style="font-size: 0.9rem; opacity: 0.9;">¡Sigue así!</p>
            </div>
        </aside>

        <main>
            <div class="container" style="padding: 2rem 0;">
                <section class="welcome-banner">
                    <div class="welcome-banner-content">
                        <span class="welcome-kicker">Campus Gamificado Interactivo</span>
                        <h1 class="welcome-title">¡Bienvenido(a), <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h1>
                        <p class="welcome-text">
                            Explora tus cursos, gana XP respondiendo la Trivia Prócer, calcula tu nota mínima para aprobar
                            y acumula tu racha de estudio. ¡Aprende, juega y progresa!
                        </p>
                    </div>
                </section>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
                    <div style="padding: 1.5rem; border-radius: var(--radius-lg); background: linear-gradient(135deg, rgba(11,60,93,0.1), rgba(212,175,55,0.1)); border: 2px solid rgba(11,60,93,0.3);">
                        <p style="font-weight: 700; color: var(--azul-marino); margin-bottom: 0.5rem;">⚡ Puntos de Experiencia</p>
                        <p style="font-size: 2.5rem; font-weight: 800; color: var(--azul-marino);"><?php echo $nivel_xp; ?> XP</p>
                    </div>
                    <div style="padding: 1.5rem; border-radius: var(--radius-lg); background: linear-gradient(135deg, rgba(192,57,43,0.1), rgba(212,175,55,0.1)); border: 2px solid rgba(192,57,43,0.3);">
                        <p style="font-weight: 700; color: var(--rojo-institucional); margin-bottom: 0.5rem;">🎯 Nivel Actual</p>
                        <p style="font-size: 2.5rem; font-weight: 800; color: var(--rojo-institucional);">Nivel <?php echo floor($nivel_xp / 100) + 1; ?></p>
                    </div>
                </div>

                <?php if ($aviso_bolso): ?>
                <div class="bolso-aviso"><?php echo htmlspecialchars($aviso_bolso); ?></div>
                <?php elseif ($rol === 'estudiante'): ?>
                <section id="bolso" class="bolso-widget">
                    <div class="bolso-resumen">
                        <div class="bolso-icono">💰</div>
                        <div>
                            <span class="bolso-label">PrócerCoins (PC) Acumulados</span>
                            <span class="bolso-saldo" id="bolsoSaldo" data-saldo="<?php echo $bolso_puntos; ?>"><?php echo $bolso_puntos; ?> PC</span>
                            <span class="bolso-sub">Gana +2 PC con la Trivia Diaria o +1 PC por punto en el Árbol de Habilidades · 5 PC = +1 punto en examen</span>
                        </div>
                    </div>

                    <!-- Tienda de Recompensas -->
                    <div style="margin-top: 1.5rem; padding: 1.5rem; background: linear-gradient(135deg, rgba(212,175,55,0.1), rgba(11,60,93,0.05)); border-radius: var(--radius-lg); border: 2px solid rgba(212,175,55,0.4);">
                        <h3 style="color: var(--azul-marino); font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            🛍️ Tienda de Recompensas para Alumnos Destacados
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                            <div style="padding: 1rem; background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                <div style="font-weight: 700; color: var(--azul-marino);">Insignia Mente Maestra (Marco Dorado)</div>
                                <div style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.3rem 0;">12 PC</div>
                                <button class="btn btn-institutional" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="comprarRecompensa('insignia_maestra', 12)">Comprar</button>
                            </div>
                            <div style="padding: 1rem; background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                <div style="font-weight: 700; color: var(--azul-marino);">Certificado Digital de Honor</div>
                                <div style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.3rem 0;">20 PC</div>
                                <button class="btn btn-institutional" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="comprarRecompensa('certificado_honor', 20)">Comprar</button>
                            </div>
                            <div style="padding: 1rem; background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                <div style="font-weight: 700; color: var(--azul-marino);">Pase de Extensión de Entrega (+24h)</div>
                                <div style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.3rem 0;">8 PC</div>
                                <button class="btn btn-institutional" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="comprarRecompensa('pase_extension', 8)">Comprar</button>
                            </div>
                        </div>
                    </div>

                    <!-- Canjear PC por puntos -->
                    <div style="margin-top: 1rem; padding: 1.5rem; background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                        <h3 style="color: var(--azul-marino); font-weight: 700; margin-bottom: 0.8rem;">Canjear PrócerCoins por Puntos de Examen</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">5 PrócerCoins (PC) = +1 Punto en Examen</p>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                            <div>
                                <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Cantidad de PC a canjear</label>
                                <input type="number" id="canjearPC" min="5" step="5" placeholder="Múltiplo de 5" style="padding: 0.7rem; border-radius: var(--radius-md); border: 2px solid var(--border-color);">
                            </div>
                            <button class="btn btn-institutional" onclick="canjearPC()">Canjear PC → Puntos de Examen</button>
                        </div>
                        <div id="canjeMsg" class="bolso-msg" style="margin-top: 0.8rem;"></div>
                    </div>

                    <form id="formBolso" class="bolso-form" style="margin-top: 1rem;">
                        <div>
                            <label for="bolsoCurso">Curso</label>
                            <select id="bolsoCurso" required>
                                <?php foreach ($cursos as $c): ?>
                                <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="bolsoTipo">Examen</label>
                            <select id="bolsoTipo">
                                <option value="parcial">Examen Parcial</option>
                                <option value="final">Examen Final</option>
                            </select>
                        </div>
                        <div>
                            <label for="bolsoNota">Nota obtenida (0 - 20)</label>
                            <input type="number" id="bolsoNota" min="0" max="20" step="0.5" required>
                        </div>
                        <div>
                            <label for="bolsoPuntos">PrócerCoins a aplicar</label>
                            <input type="number" id="bolsoPuntos" min="1" step="1" required>
                        </div>
                        <div class="bolso-preview" id="bolsoPreview"></div>
                        <button type="submit" class="btn btn-institutional" <?php echo $bolso_puntos < 1 ? 'disabled' : ''; ?>>Solicitar aplicar PrócerCoins</button>
                    </form>
                    <div id="bolsoMsg" class="bolso-msg"></div>
                    <?php if ($mis_solicitudes): ?>
                    <table class="bolso-tabla">
                        <thead><tr><th>Curso</th><th>Examen</th><th>Nota</th><th>Puntos</th><th>Resultado</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($mis_solicitudes as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['curso']); ?></td>
                                <td><?php echo ucfirst($s['tipo_examen']); ?></td>
                                <td><?php echo $s['nota_actual']; ?></td>
                                <td>+<?php echo (int)$s['puntos_aplicados']; ?></td>
                                <td><?php echo $s['nota_resultante']; ?></td>
                                <td><span class="bolso-estado <?php echo $s['estado']; ?>"><?php echo ucfirst($s['estado']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </section>
                <?php elseif ($solicitudes_pendientes !== []): ?>
                <section id="bolso" class="bolso-widget">
                    <div class="bolso-resumen">
                        <div class="bolso-icono">📥</div>
                        <div><span class="bolso-label">Solicitudes de puntos por revisar</span></div>
                    </div>
                    <table class="bolso-tabla">
                        <thead><tr><th>Estudiante</th><th>Curso</th><th>Examen</th><th>Nota</th><th>Puntos</th><th>Resultado</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($solicitudes_pendientes as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['est_nombre'] . ' ' . $s['est_apellido']); ?></td>
                                <td><?php echo htmlspecialchars($s['curso']); ?></td>
                                <td><?php echo ucfirst($s['tipo_examen']); ?></td>
                                <td><?php echo $s['nota_actual']; ?></td>
                                <td>+<?php echo (int)$s['puntos_aplicados']; ?></td>
                                <td><?php echo $s['nota_resultante']; ?></td>
                                <td>
                                    <button class="btn btn-institutional" data-resolver="aprobar" data-id="<?php echo (int)$s['id']; ?>">Aprobar</button>
                                    <button class="btn btn-ghost" data-resolver="rechazar" data-id="<?php echo (int)$s['id']; ?>">Rechazar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <?php endif; ?>

                <section id="cursos" class="section" style="padding: 2rem 0;">
                    <div class="section-header" style="text-align: left; align-items: flex-start;">
                        <span class="section-kicker">Acceso Rápido</span>
                        <h2 class="section-title" style="text-align: left;">Mis Cursos Interactivos</h2>
                        <p style="color: var(--text-secondary);">Haz clic en cualquier curso para ingresar al aula virtual</p>
                    </div>
                    <div class="courses-grid" style="grid-template-columns: repeat(5, 1fr);">
                        <?php foreach ($cursos as $c): ?>
                        <a href="curso.php?id=<?php echo $c['id']; ?>" class="course-card-3d" style="text-decoration: none;">
                            <?php
                            $bgClass = 'course-matematica';
                            if (strpos($c['nombre'], 'Comunicación') !== false) $bgClass = 'course-comunicacion';
                            elseif (strpos($c['nombre'], 'Ciencia') !== false) $bgClass = 'course-cyt';
                            elseif (strpos($c['nombre'], 'Sociales') !== false) $bgClass = 'course-sociales';
                            elseif (strpos($c['nombre'], 'Inglés') !== false) $bgClass = 'course-ingles';
                            ?>
                            <div class="course-card-3d-bg <?php echo $bgClass; ?>"></div>
                            <div class="course-card-content">
                                <h3 class="course-card-title"><?php echo $c['icono']; ?> <?php echo htmlspecialchars($c['nombre']); ?></h3>
                                <p class="course-card-subtitle"><?php echo htmlspecialchars($c['codigo']); ?></p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section id="calculadora" class="calculator-section">
                    <header class="calculator-header">
                        <div class="calculator-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="4" y="2" width="16" height="20" rx="2" stroke="currentColor" stroke-width="1.6" />
                                <path d="M8 6H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <path d="M8 10H10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <path d="M14 10H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <path d="M8 14H10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <path d="M14 14H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <path d="M8 18H10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                <path d="M14 18H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="calculator-title">Calculadora Vigésimal Mejorada</h2>
                            <p style="color: var(--text-secondary);">Nota Aprobatoria Institucional: 10.5 | Simulación Interactiva</p>
                        </div>
                    </header>
                    <div class="calculator-grid">
                        <div class="calculator-inputs">
                            <div class="calc-input-group">
                                <label class="calc-label" for="notaParcial">Examen Parcial (40%)</label>
                                <input class="calc-input" type="number" id="notaParcial" min="0" max="20" step="0.5" placeholder="0 - 20">
                            </div>
                            <div class="calc-input-group">
                                <label class="calc-label" for="notaPracticas">Prácticas y Trabajos (60%)</label>
                                <input class="calc-input" type="number" id="notaPracticas" min="0" max="20" step="0.5" placeholder="0 - 20">
                            </div>
                        </div>
                        <div class="calculator-result" id="resultadoCalculadora">
                            <span class="result-label">Nota Mínima Requerida en Examen Final</span>
                            <span class="result-value" id="notaFinalMinima">0.0</span>
                            <p class="result-text" id="textoResultado">Ingresa tus notas para simular</p>
                            <div class="progress-bar-calc">
                                <div class="progress-bar-fill-calc" id="barraProgresoCalc" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="trivia" class="section" style="padding: 2rem 0;">
                    <div class="section-header" style="text-align: left; align-items: flex-start;">
                        <span class="section-kicker">Gamificación</span>
                        <h2 class="section-title" style="text-align: left;">Pausa Activa / Trivia Prócer</h2>
                        <p style="color: var(--text-secondary);">Responde correctamente y gana +10 XP</p>
                    </div>
                    <div class="profile-section">
                        <div id="triviaContainer">
                            <div style="padding: 2rem; text-align: center;">
                                <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--azul-marino); margin-bottom: 1rem;">¿Listo para ganar XP?</h3>
                                <p style="color: var(--text-secondary); margin-bottom: 2rem;">Pon a prueba tus conocimientos con preguntas rápidas</p>
                                <button class="btn btn-institutional" onclick="iniciarTrivia()">Iniciar Trivia</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="perfil" class="profile-section">
                    <header class="profile-header">
                        <?php if ($rol === 'desarrollador' && $foto === 'foto.jpg'): ?>
                            <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto" class="profile-avatar">
                        <?php else: ?>
                            <div class="profile-avatar" style="display:grid;place-items:center;font-weight:800;font-size:2.5rem;background:linear-gradient(135deg,var(--azul-marino),var(--rojo-institucional));color:white;"><?php echo $iniciales; ?></div>
                        <?php endif; ?>
                        <div class="profile-info">
                            <h2 class="profile-name"><?php echo htmlspecialchars($nombre_completo); ?></h2>
                            <div class="profile-meta">
                                <span>🎓 <?php echo ucfirst($rol); ?></span>
                                <span>⚡ <?php echo $nivel_xp; ?> XP | Nivel <?php echo floor($nivel_xp / 100) + 1; ?></span>
                                <span>🔥 <?php echo $racha_dias; ?> Días de Racha</span>
                                <span>🆔 DNI: <?php echo htmlspecialchars($_SESSION['dni']); ?></span>
                            </div>
                        </div>
                    </header>
                </section>
            </div>
        </main>
    </div>

    <footer class="dashboard-footer">
        <div class="container">
            <p>I.E. 6082 Los Próceres - Campus Gamificado | &copy; 2026</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        const preguntasTrivia = [
            { pregunta: "¿Capital del Perú?", opciones: ["Lima", "Cusco", "Arequipa"], correcta: 0 },
            { pregunta: "¿Símbolo químico del Oxígeno?", opciones: ["H", "O", "N"], correcta: 1 },
            { pregunta: "¿Autor de 'Los Comentarios Reales'?", opciones: ["Garcilaso de la Vega", "Cesar Vallejo", "Mario Vargas Llosa"], correcta: 0 },
            { pregunta: "¿Año de la Independencia del Perú?", opciones: ["1821", "1824", "1810"], correcta: 0 },
            { pregunta: "¿Planeta más grande del sistema solar?", opciones: ["Marte", "Júpiter", "Saturno"], correcta: 1 }
        ];
        let preguntaActual = 0;
        let triviaCompletadaHoy = false;
        
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const res = await fetch('api_bolso.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({accion: 'trivia_estado'})
                });
                const data = await res.json();
                if (data.ok && data.completada) {
                    triviaCompletadaHoy = true;
                    const cont = document.getElementById('triviaContainer');
                    if (cont) {
                        cont.innerHTML = '<div style="padding: 2rem; text-align: center;"><h3 style="font-size: 1.5rem; font-weight: 700; color: var(--azul-marino);">✅ Trivia de hoy completada</h3><p style="color: var(--text-secondary); margin: 1rem 0;">Ya participaste en la trivia diaria. Vuelve mañana para ganar +2 PrócerCoins (PC).</p></div>';
                    }
                }
            } catch(e) {}
        });
        
        function iniciarTrivia() {
            if (triviaCompletadaHoy) {
                alert('Ya completaste la trivia de hoy. Vuelve mañana.');
                return;
            }
            preguntaActual = 0;
            mostrarPregunta();
        }
        
        function mostrarPregunta() {
            const cont = document.getElementById('triviaContainer');
            if (preguntaActual >= preguntasTrivia.length) {
                finalizarTrivia();
                return;
            }
            const p = preguntasTrivia[preguntaActual];
            let html = '<div style="padding: 2rem;"><h3 style="font-size: 1.3rem; font-weight: 700; color: var(--azul-marino); margin-bottom: 1.5rem;">Pregunta ' + (preguntaActual+1) + ': ' + p.pregunta + '</h3><div style="display: flex; flex-direction: column; gap: 0.8rem;">';
            p.opciones.forEach((op, i) => {
                html += '<button onclick="responder(' + i + ')" style="padding: 1rem 1.5rem; border-radius: var(--radius-md); border: 2px solid var(--border-color); background: white; text-align: left; font-weight: 600; cursor: pointer; transition: var(--transition);" onmouseover="this.style.borderColor=\'var(--azul-marino)\';this.style.background=\'rgba(11,60,93,0.05)\'" onmouseout="this.style.borderColor=\'var(--border-color)\';this.style.background=\'white\'">' + String.fromCharCode(65+i) + '. ' + op + '</button>';
            });
            html += '</div></div>';
            cont.innerHTML = html;
        }
        
        async function finalizarTrivia() {
            const cont = document.getElementById('triviaContainer');
            try {
                const res = await fetch('api_bolso.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({accion: 'trivia_diaria'})
                });
                const data = await res.json();
                if (data.ok) {
                    triviaCompletadaHoy = true;
                    cont.innerHTML = '<div style="padding: 2rem; text-align: center;"><h3 style="font-size: 1.8rem; font-weight: 800; color: var(--success-color);">¡Felicitaciones!</h3><p style="color: var(--text-secondary); margin: 1rem 0;">Completaste la trivia diaria</p><p style="font-weight: 700; color: var(--azul-marino); font-size: 1.2rem;">+2 PrócerCoins (PC) Ganados</p><p style="margin-top: 0.5rem;">Saldo: ' + (data.saldo || '') + ' PC</p></div>';
                } else if (data.completada) {
                    triviaCompletadaHoy = true;
                    cont.innerHTML = '<div style="padding: 2rem; text-align: center;"><h3 style="font-size: 1.5rem; font-weight: 700; color: var(--azul-marino);">✅ Trivia de hoy completada</h3><p style="color: var(--text-secondary); margin: 1rem 0;">Vuelve mañana para ganar más PrócerCoins.</p></div>';
                } else {
                    cont.innerHTML = '<div style="padding: 2rem; text-align: center;"><p style="color: var(--rojo-institucional);">' + data.error + '</p></div>';
                }
            } catch(e) {
                cont.innerHTML = '<div style="padding: 2rem; text-align: center;"><p style="color: var(--rojo-institucional);">Error al procesar</p></div>';
            }
        }
        
        function responder(sel) {
            const p = preguntasTrivia[preguntaActual];
            if (sel === p.correcta) {
                // Correcto, continuar
            } else {
                alert('Incorrecto. La respuesta correcta era: ' + p.opciones[p.correcta]);
            }
            preguntaActual++;
            setTimeout(mostrarPregunta, 300);
        }

        async function comprarRecompensa(recompensa, costo) {
            const saldo = parseInt(document.getElementById('bolsoSaldo').textContent) || 0;
            if (saldo < costo) {
                alert('No tienes suficientes PrócerCoins. Necesitas ' + costo + ' PC.');
                return;
            }
            if (!confirm('¿Comprar esta recompensa por ' + costo + ' PrócerCoins?')) return;
            try {
                const res = await fetch('api_bolso.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({accion: 'comprar_recompensa', recompensa: recompensa})
                });
                const data = await res.json();
                if (data.ok) {
                    alert(data.mensaje);
                    document.getElementById('bolsoSaldo').textContent = data.saldo + ' PC';
                    document.getElementById('bolsoSaldo').dataset.saldo = data.saldo;
                } else {
                    alert(data.error);
                }
            } catch(e) {
                alert('Error al comprar');
            }
        }

        async function canjearPC() {
            const pc = parseInt(document.getElementById('canjearPC').value);
            const saldo = parseInt(document.getElementById('bolsoSaldo').dataset.saldo) || 0;
            const msg = document.getElementById('canjeMsg');
            if (!pc || pc < 5 || pc % 5 !== 0) {
                msg.textContent = 'Debes canjear en múltiplos de 5 PrócerCoins';
                msg.className = 'bolso-msg error';
                return;
            }
            if (saldo < pc) {
                msg.textContent = 'No tienes suficientes PrócerCoins';
                msg.className = 'bolso-msg error';
                return;
            }
            try {
                const res = await fetch('api_bolso.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({accion: 'canjear_pc', pc: pc})
                });
                const data = await res.json();
                if (data.ok) {
                    msg.textContent = data.mensaje;
                    msg.className = 'bolso-msg ok';
                    document.getElementById('bolsoSaldo').textContent = data.saldo + ' PC';
                    document.getElementById('bolsoSaldo').dataset.saldo = data.saldo;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    msg.textContent = data.error;
                    msg.className = 'bolso-msg error';
                }
            } catch(e) {
                msg.textContent = 'Error al canjear';
                msg.className = 'bolso-msg error';
            }
        }
    </script>
</body>

</html>
