<?php
session_start();
require_once 'db.php';

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$nombre_completo = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
$grado_seccion = $_SESSION['grado_seccion'];
$nivel = $_SESSION['nivel'];
$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Virtual - I.E. 6082 Los Próceres</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header del Dashboard -->
    <header class="dashboard-header">
        <nav class="dashboard-nav">
            <div class="dashboard-brand">
                <div class="logo-icon">🎓</div>
                <h1>Campus Virtual - I.E. 6082 Los Próceres</h1>
            </div>
            <div class="user-info">
                <div class="user-details">
                    <strong>👤 <?php echo htmlspecialchars($nombre_completo); ?></strong>
                    <span>Estudiante | <?php echo htmlspecialchars($grado_seccion); ?> - <?php echo ucfirst(htmlspecialchars($nivel)); ?></span>
                </div>
                <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
            </div>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="dashboard-main">
        <!-- Sección de Bienvenida -->
        <section class="welcome-section">
            <h2>¡Bienvenido(a), <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h2>
            <p>Has ingresado exitosamente al Aula Virtual de la I.E. 6082 Los Próceres. Desde aquí podrás acceder a todos tus recursos académicos.</p>
        </section>

        <!-- Grid de Paneles Funcionales -->
        <div class="dashboard-grid">
            <!-- Horario de Clases -->
            <article class="dashboard-card">
                <div class="card-header">
                    <span class="card-icon">📅</span>
                    <h3>Horario de Clases</h3>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Lunes:</strong> Matemática (8:00 - 9:30) | Comunicación (9:40 - 11:10)</li>
                        <li><strong>Martes:</strong> Ciencia y Tecnología (8:00 - 9:30) | Inglés (9:40 - 11:10)</li>
                        <li><strong>Miércoles:</strong> Historia, Geografía y Economía (8:00 - 9:30) | Arte (9:40 - 11:10)</li>
                        <li><strong>Jueves:</strong> Educación Física (8:00 - 9:30) | Matemática (9:40 - 11:10)</li>
                        <li><strong>Viernes:</strong> Comunicación (8:00 - 9:30) | Formación Ciudadana (9:40 - 11:10)</li>
                    </ul>
                </div>
            </article>

            <!-- Cursos Inscritos -->
            <article class="dashboard-card">
                <div class="card-header">
                    <span class="card-icon">📚</span>
                    <h3>Cursos Inscritos</h3>
                </div>
                <div class="card-body">
                    <ul>
                        <li>✅ Matemática</li>
                        <li>✅ Comunicación</li>
                        <li>✅ Ciencia y Tecnología</li>
                        <li>✅ Inglés</li>
                        <li>✅ Historia, Geografía y Economía</li>
                        <li>✅ Arte y Cultura</li>
                        <li>✅ Educación Física</li>
                        <li>✅ Formación Ciudadana y Cívica</li>
                        <li>✅ Desarrollo Personal, Ciudadanía y Cívica</li>
                    </ul>
                </div>
            </article>

            <!-- Descarga de Materiales/Tareas -->
            <article class="dashboard-card">
                <div class="card-header">
                    <span class="card-icon">📥</span>
                    <h3>Descarga de Materiales / Tareas</h3>
                </div>
                <div class="card-body">
                    <p><strong>Materiales Recientes:</strong></p>
                    <ul>
                        <li><a href="#" class="download-link">📄 Unidad 4 - Ecuaciones Lineales.pdf</a></li>
                        <li><a href="#" class="download-link">📄 Guía de Lectura - El Caballero Carmelo.pdf</a></li>
                        <li><a href="#" class="download-link">📄 Laboratorio - Mezclas y Combinaciones.pdf</a></li>
                    </ul>
                    <p style="margin-top: 1rem;"><strong>Tareas Pendientes:</strong></p>
                    <ul>
                        <li>📝 Ejercicios de Matemática - Vence: 25/10/2026</li>
                        <li>📝 Ensayo sobre la Independencia - Vence: 26/10/2026</li>
                        <li>📝 Informe de Experimento - Vence: 28/10/2026</li>
                    </ul>
                </div>
            </article>

            <!-- Reporte de Notas -->
            <article class="dashboard-card">
                <div class="card-header">
                    <span class="card-icon">📊</span>
                    <h3>Reporte de Notas</h3>
                </div>
                <div class="card-body">
                    <p><strong>Trimestre I - Promedio General</strong></p>
                    <div class="nota-item">
                        <span>Matemática</span>
                        <span class="nota-valor">18</span>
                    </div>
                    <div class="nota-item">
                        <span>Comunicación</span>
                        <span class="nota-valor">17</span>
                    </div>
                    <div class="nota-item">
                        <span>Ciencia y Tecnología</span>
                        <span class="nota-valor">19</span>
                    </div>
                    <div class="nota-item">
                        <span>Inglés</span>
                        <span class="nota-valor">16</span>
                    </div>
                    <div class="nota-item">
                        <span>Historia, Geografía y Economía</span>
                        <span class="nota-valor">18</span>
                    </div>
                    <p style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid var(--border-color); font-weight: 600;">
                        <strong>Promedio Trimestral: 17.6</strong>
                    </p>
                </div>
            </article>

            <!-- Anuncios del Colegio -->
            <article class="dashboard-card" style="grid-column: 1 / -1;">
                <div class="card-header">
                    <span class="card-icon">📢</span>
                    <h3>Anuncios del Colegio</h3>
                </div>
                <div class="card-body">
                    <ul>
                        <li>🔔 <strong>23/10/2026:</strong> Plataforma virtual actualizada - Nuevas funcionalidades disponibles</li>
                        <li>🔔 <strong>22/10/2026:</strong> Recordatorio: Entregar proyecto de Ciencias hasta el 29/10/2026</li>
                        <li>🔔 <strong>21/10/2026:</strong> Horario de tutorías personalizado disponible en secretaría virtual</li>
                        <li>🔔 <strong>20/10/2026:</strong> Invitación a participar en el Concurso de Declamación Escolar</li>
                        <li>🔔 <strong>19/10/2026:</strong> Campaña de reciclaje - Trae tus botellas plásticas este viernes</li>
                    </ul>
                </div>
            </article>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <p><strong>I.E. 6082 Los Próceres - Campus Virtual</strong></p>
            <p>&copy; 2026 Todos los derechos reservados</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
