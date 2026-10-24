<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I.E. 6082 Los Próceres | Portal Institucional</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Header Institucional -->
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand">
                <div class="brand-emblem">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3L3 7V17L12 21L21 17V7L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                        <path d="M12 12L21 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12 12V21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12 12L3 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="12" cy="12" r="1.4" fill="currentColor" />
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">I.E. 6082 Los Próceres</span>
                    <span class="brand-subtitle">Santiago de Surco - Lima, Perú</span>
                </div>
            </div>
            <nav class="nav-menu">
                <a class="nav-link" href="#inicio">Inicio</a>
                <a class="nav-link" href="#logros">Logros</a>
                <a class="nav-link" href="#cursos">Áreas Curriculares</a>
                <a class="nav-link" href="#contacto">Contacto</a>
                <a class="btn btn-ghost" href="login.php">Acceso Institucional</a>
                <a class="btn btn-primary" href="login.php">Ingresar al Campus Virtual</a>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section id="inicio" class="hero">
            <div class="container hero-inner">
                <div class="hero-content">
                    <span class="hero-kicker">Excelencia • Honor • Servicio</span>
                    <h1 class="hero-title">I.E. 6082 Los Próceres</h1>
                    <p class="hero-subtitle">"Formando Líderes para el Futuro"</p>
                    <p class="hero-text">
                        Institución Educativa con sólida trayectoria al servicio de la comunidad de Santiago de Surco.
                        Brindamos una educación integral, inclusiva e innovadora, fundamentada en valores, disciplina
                        y compromiso con la excelencia académica.
                    </p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="login.php">
                            <span>Ingresar al Campus Virtual</span>
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.75 9H14.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.75 4.5L14.25 9L9.75 13.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                        <a class="btn btn-institutional" href="#cursos">Conocer Áreas Curriculares</a>
                    </div>
                </div>
                <div class="hero-visual">
                    <article class="hero-card">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=2070&auto=format&fit=crop" alt="Estudiantes I.E. 6082 Los Próceres">
                        <div class="hero-card-overlay"></div>
                        <div class="hero-card-content">
                            <span class="hero-badge">Identidad Institucional</span>
                            <h2 class="hero-card-title">Tradición, Disciplina y Excelencia</h2>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Estadísticas -->
        <section id="logros" class="stats">
            <div class="container">
                <div class="stats-grid">
                    <article class="stat-card">
                        <span class="stat-value">+40</span>
                        <span class="stat-label">Años de Excelencia Educativa</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-value">+850</span>
                        <span class="stat-label">Alumnos Formados</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-value">+60</span>
                        <span class="stat-label">Docentes Especializados</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-value">100%</span>
                        <span class="stat-label">Formación Integral en Valores</span>
                    </article>
                </div>
            </div>
        </section>

        <!-- Áreas Curriculares con Tarjetas Flotantes 3D -->
        <section id="cursos" class="section">
            <div class="container">
                <div class="section-header">
                    <span class="section-kicker">Oferta Académica</span>
                    <h2 class="section-title">Áreas Curriculares</h2>
                    <p class="section-subtitle">
                        Desarrollo integral por competencias con énfasis en el pensamiento crítico, científico y humanista
                    </p>
                </div>
                <div class="courses-grid">
                    <article class="course-card-3d">
                        <div class="course-card-3d-bg course-matematica"></div>
                        <div class="course-card-content">
                            <h3 class="course-card-title">Matemática</h3>
                            <p class="course-card-subtitle">Razonamiento Lógico y Resolución de Problemas</p>
                        </div>
                    </article>
                    <article class="course-card-3d">
                        <div class="course-card-3d-bg course-comunicacion"></div>
                        <div class="course-card-content">
                            <h3 class="course-card-title">Comunicación</h3>
                            <p class="course-card-subtitle">Comprensión Lectora y Expresión Oral y Escrita</p>
                        </div>
                    </article>
                    <article class="course-card-3d">
                        <div class="course-card-3d-bg course-cyt"></div>
                        <div class="course-card-content">
                            <h3 class="course-card-title">Ciencia y Tecnología</h3>
                            <p class="course-card-subtitle">Investigación Científica e Innovación</p>
                        </div>
                    </article>
                    <article class="course-card-3d">
                        <div class="course-card-3d-bg course-sociales"></div>
                        <div class="course-card-content">
                            <h3 class="course-card-title">Ciencias Sociales</h3>
                            <p class="course-card-subtitle">Historia, Geografía y Formación Ciudadana</p>
                        </div>
                    </article>
                    <article class="course-card-3d">
                        <div class="course-card-3d-bg course-ingles"></div>
                        <div class="course-card-content">
                            <h3 class="course-card-title">Inglés</h3>
                            <p class="course-card-subtitle">Comunicación Internacional y Competencias Globales</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="contacto" class="site-footer">
        <div class="container footer-top">
            <div class="footer-brand">
                <div class="footer-brand-header">
                    <div class="brand-emblem">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 3L3 7V17L12 21L21 17V7L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                            <path d="M12 12L21 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M12 12V21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M12 12L3 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="12" cy="12" r="1.4" fill="currentColor" />
                        </svg>
                    </div>
                    <div class="brand-text">
                        <span class="brand-title">I.E. 6082 Los Próceres</span>
                        <span class="brand-subtitle">Excelencia • Honor • Servicio</span>
                    </div>
                </div>
                <p class="footer-text">
                    Institución Educativa comprometida con la formación integral, el desarrollo de valores
                    y la excelencia académica al servicio de la comunidad surquina.
                </p>
            </div>
            <div class="footer-column">
                <h4 class="footer-title">Institución</h4>
                <ul class="footer-list">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#logros">Logros Institucionales</a></li>
                    <li><a href="#cursos">Áreas Curriculares</a></li>
                    <li><a href="login.php">Campus Virtual</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4 class="footer-title">Acceso</h4>
                <ul class="footer-list">
                    <li><a href="login.php">Estudiantes</a></li>
                    <li><a href="login.php">Docentes</a></li>
                    <li><a href="login.php">Administración</a></li>
                </ul>
            </div>
            <div class="footer-column-wide">
                <h4 class="footer-title">Contacto y Ubicación</h4>
                <div class="footer-contact">
                    <div class="footer-contact-item">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 10.8333C11.3807 10.8333 12.5 9.71405 12.5 8.33334C12.5 6.95263 11.3807 5.83334 10 5.83334C8.61929 5.83334 7.5 6.95263 7.5 8.33334C7.5 9.71405 8.61929 10.8333 10 10.8333Z" stroke="currentColor" stroke-width="1.6" />
                            <path d="M10 17.5C12.5 15 16.6667 11.8056 16.6667 8.33333C16.6667 4.65143 13.6819 1.66667 10 1.66667C6.3181 1.66667 3.33334 4.65143 3.33334 8.33333C3.33334 11.8056 7.5 15 10 17.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                        </svg>
                        <div>
                            <p>Av. Los Próceres s/n</p>
                            <p>Santiago de Surco - Lima, Perú</p>
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.33334 5C3.33334 4.07953 4.07953 3.33334 5 3.33334H6.54762C6.81647 3.33334 7.06293 3.48079 7.19803 3.71541L8.57005 6.09099C8.72678 6.36276 8.69656 6.70282 8.4943 6.94278L7.41667 8.22222C7.41667 8.22222 8.05556 10.6944 10.2778 12.5833C12.5 14.4722 14.7778 15.0833 14.7778 15.0833L16.0572 14.0057C16.2972 13.8034 16.6372 13.7732 16.909 13.93L19.2846 15.302C19.5192 15.4371 19.6667 15.6835 19.6667 15.9524V17.5C19.6667 18.4205 18.9205 19.1667 18 19.1667H17.5C9.41667 19.1667 2.83334 12.5833 2.83334 4.5V4C2.83334 3.07953 3.57953 2.33334 4.5 2.33334H5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                        </svg>
                        <div>
                            <p>(01) 271-6082</p>
                            <p>Central Telefónica Institucional</p>
                        </div>
                    </div>
                </div>
                <div class="footer-map">
                    <iframe src="https://maps.google.com/maps?q=Santiago%20de%20Surco%2C%20Lima%2C%20Per%C3%BA&t=&z=14&ie=UTF8&iwloc=&output=embed" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; 2026 I.E. 6082 Los Próceres. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>

</html>
