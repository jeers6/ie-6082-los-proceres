// Animaciones y funcionalidad interactiva para el Campus Virtual

document.addEventListener('DOMContentLoaded', function() {
    // Animación de entrada para las tarjetas del dashboard
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Efecto hover suave para elementos interactivos
    const interactiveElements = document.querySelectorAll('.historia-card, .valor-card, .aviso-item');
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
    
    // Validación en tiempo real para el formulario de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const usuarioInput = document.getElementById('usuario');
        const passwordInput = document.getElementById('password');
        
        usuarioInput.addEventListener('input', function() {
            this.value = this.value.toLowerCase();
        });
        
        loginForm.addEventListener('submit', function(e) {
            const usuario = usuarioInput.value.trim();
            const password = passwordInput.value.trim();
            
            if (usuario === '' || password === '') {
                e.preventDefault();
                alert('Por favor, complete todos los campos.');
                return;
            }
            
            // Animación de carga en el botón
            const btnLogin = document.querySelector('.btn-login');
            if (btnLogin) {
                btnLogin.innerHTML = '⏳ Ingresando...';
                btnLogin.disabled = true;
            }
        });
    }
    
    // Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Efecto parallax ligero para el hero
    const hero = document.querySelector('.hero');
    if (hero) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallax = scrolled * 0.5;
            hero.style.backgroundPositionY = parallax + 'px';
        });
    }
    
    console.log('Campus Virtual I.E. 6082 Los Próceres - Sistema cargado correctamente');
});
