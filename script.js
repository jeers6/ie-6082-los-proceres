// I.E. 6082 Los Próceres - Arquitectura por Roles
// Calculadora Vigésima Mejorada + Animaciones

document.addEventListener('DOMContentLoaded', function() {
    // Calculadora de Notas Vigésima
    const calcParcial = document.getElementById('notaParcial');
    const calcPracticas = document.getElementById('notaPracticas');
    const calcFinal = document.getElementById('notaFinalMinima');
    const calcResultado = document.getElementById('resultadoCalculadora');
    const calcBarra = document.getElementById('barraProgresoCalc');
    const calcTexto = document.getElementById('textoResultado');
    
    if (calcParcial && calcPracticas) {
        function calcularNotaMinima() {
            const parcial = parseFloat(calcParcial.value) || 0;
            const practicas = parseFloat(calcPracticas.value) || 0;
            
            if (parcial < 0) calcParcial.value = 0;
            if (parcial > 20) calcParcial.value = 20;
            if (practicas < 0) calcPracticas.value = 0;
            if (practicas > 20) calcPracticas.value = 20;
            
            const promedioActual = (parcial * 0.4 + practicas * 0.6);
            let notaMinimaFinal = (10.5 - promedioActual * 0.6) / 0.4;
            
            if (isNaN(notaMinimaFinal)) notaMinimaFinal = 0;
            if (notaMinimaFinal < 0) notaMinimaFinal = 0;
            if (notaMinimaFinal > 20) notaMinimaFinal = 20;
            
            notaMinimaFinal = Math.round(notaMinimaFinal * 10) / 10;
            
            if (calcFinal) {
                calcFinal.textContent = notaMinimaFinal.toFixed(1);
            }
            
            if (calcResultado && calcBarra && calcTexto) {
                if (notaMinimaFinal <= 10.5) {
                    calcResultado.classList.remove('fail');
                    calcBarra.classList.remove('fail');
                    calcFinal.classList.remove('fail');
                    calcTexto.textContent = '¡Aprobado! Con ' + notaMinimaFinal.toFixed(1) + ' en el Examen Final alcanzas la nota aprobatoria (10.5).';
                    calcBarra.style.width = Math.max(10, (notaMinimaFinal / 20) * 100) + '%';
                } else if (notaMinimaFinal <= 14) {
                    calcResultado.classList.remove('fail');
                    calcBarra.classList.remove('fail');
                    calcFinal.classList.remove('fail');
                    calcTexto.textContent = 'Requiere buen desempeño. Necesitas ' + notaMinimaFinal.toFixed(1) + ' en el Examen Final para aprobar.';
                    calcBarra.style.width = (notaMinimaFinal / 20) * 100 + '%';
                } else {
                    calcResultado.classList.add('fail');
                    calcBarra.classList.add('fail');
                    calcFinal.classList.add('fail');
                    calcTexto.textContent = 'Requiere mayor esfuerzo. Necesitas ' + notaMinimaFinal.toFixed(1) + ' en el Examen Final. Se recomienda reforzamiento académico.';
                    calcBarra.style.width = '100%';
                }
            }
        }
        
        calcParcial.addEventListener('input', calcularNotaMinima);
        calcPracticas.addEventListener('input', calcularNotaMinima);
        calcularNotaMinima();
    }
    
    // Efecto 3D en tarjetas de cursos
    const courseCards = document.querySelectorAll('.course-card-3d');
    courseCards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = (y - centerY) / 12;
            const rotateY = (centerX - x) / 12;
            card.style.transform = `perspective(1100px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(45px) scale(1.06)`;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1100px) rotateX(0) rotateY(0) translateZ(0) scale(1)';
        });
    });
    
    // Animación al scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    
    document.querySelectorAll('.stat-card, .course-card-3d, .dev-card, .dashboard-card, .profile-section, .calculator-section').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(35px)';
        el.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
        observer.observe(el);
    });
});


// =========================================================
// ÁRBOL DE HABILIDADES (Skill Tree radial en SVG)
// Solo se activa en curso.php cuando existe #skillTreeRoot
// =========================================================
(function () {
    const root = document.getElementById('skillTreeRoot');
    if (!root) return;

    const API = 'api_arbol.php';
    const idCurso = parseInt(root.dataset.curso, 10);
    const soloLectura = root.dataset.readonly === '1';
    const NS = 'http://www.w3.org/2000/svg';
    const CX = 500, CY = 350;
    // Ángulos en pantalla (grados): coinciden con tu boceto
    const ANG = { desafio: -32, canje: 135 };
    const ANG_NEGRAS = [-90, 180, 0, 90]; // arriba, izquierda, derecha, abajo (según el orden del curso)
    let estado = null;

    root.innerHTML =
        '<div class="st-barra" id="stBarra"></div>' +
        '<div class="st-lienzo"><svg id="stSvg" viewBox="0 0 1000 700" role="img" aria-label="Árbol de habilidades"></svg></div>';

    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    async function api(accion, extra) {
        try {
            const r = await fetch('api_arbol.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.assign({ accion: accion, id_curso: idCurso }, extra || {}))
            });
            return await r.json();
        } catch (e) {
            return { ok: false, error: 'No se pudo conectar con el servidor.' };
        }
    }

    function nodo(tag, attrs, parent) {
        const el = document.createElementNS(NS, tag);
        Object.keys(attrs || {}).forEach((k) => el.setAttribute(k, attrs[k]));
        if (parent) parent.appendChild(el);
        return el;
    }

    // ---------- geometría ----------
    function punto(rama, i, n) {
        const a = ANG[rama] * Math.PI / 180;
        let r, wob;
        if (rama === 'desafio') { r = 100 + i * 28; wob = Math.sin(i * 1.3) * 14; }
        else { r = 110 + i * 48; wob = Math.sin(i * 1.4 + 1) * 8; }
        return {
            x: CX + Math.cos(a) * r - Math.sin(a) * wob,
            y: CY + Math.sin(a) * r + Math.cos(a) * wob
        };
    }

    function trazo(pts) {
        let d = 'M' + pts[0].x + ',' + pts[0].y;
        for (let i = 1; i < pts.length; i++) {
            const q = pts[i - 1], p = pts[i];
            d += ' Q' + q.x + ',' + q.y + ' ' + (q.x + p.x) / 2 + ',' + (q.y + p.y) / 2;
        }
        const l = pts[pts.length - 1];
        return d + ' L' + l.x + ',' + l.y;
    }

    // ---------- dibujo ----------
    function dibujar() {
        const svg = document.getElementById('stSvg');
        svg.innerHTML = '';
        [120, 220, 320].forEach((r) => nodo('circle', { cx: CX, cy: CY, r: r, class: 'st-anillo' }, svg));
        estado.negras.concat(['desafio', 'canje']).forEach(dibujarRama);

        const g = nodo('g', { class: 'st-centro' }, svg);
        nodo('circle', { cx: CX, cy: CY, r: 64 }, g);
        const nom = root.dataset.nombre;
        const pal = nom.split(' ');
        const mitad = Math.ceil(pal.length / 2);
        const lineas = (pal.length > 1 && nom.length > 11) ? [pal.slice(0, mitad).join(' '), pal.slice(mitad).join(' ')] : [nom];
        lineas.forEach((ln, i) => {
            const t = nodo('text', { x: CX, y: CY + 5 + (i - (lineas.length - 1) / 2) * 17, 'text-anchor': 'middle' }, g);
            t.textContent = ln;
        });

        const b = document.getElementById('stBarra');
        b.innerHTML =
            '<span>⭐ Puntos por canjear: <strong>' + estado.pendientes + '</strong></span>' +
            '<span>🎒 Bolso: <strong>' + estado.bolso + '</strong></span>' +
            (soloLectura ? '<span class="st-aviso">Vista previa: solo los estudiantes ganan puntos</span>' : '');
    }

    function dibujarRama(k) {
        const svg = document.getElementById('stSvg');
        const r = estado.ramas[k];
        const roja = (k === 'desafio' || k === 'canje');
        const clase = roja ? 'st-roja' : 'st-negra';
        const g = nodo('g', { class: 'st-rama' }, svg);

        // Desafío sin dado: un solo nodo-dado + trazo punteado
        if (k === 'desafio' && !r.niveles_total && !r.completado) {
            const fin = { x: CX + Math.cos(ANG[k] * Math.PI / 180) * 500, y: CY + Math.sin(ANG[k] * Math.PI / 180) * 500 };
            nodo('path', { d: 'M' + CX + ',' + CY + ' L' + fin.x + ',' + fin.y, class: 'st-linea ' + clase + ' st-punteada' }, g);
            const p = punto(k, 1, 1);
            const n = nodo('g', { class: 'st-node ' + clase + ' st-current' }, g);
            nodo('circle', { cx: p.x, cy: p.y, r: 22 }, n);
            const tx = nodo('text', { x: p.x, y: p.y + 6, 'text-anchor': 'middle' }, n);
            tx.textContent = '🎲';
            nodo('title', {}, n).textContent = soloLectura ? 'Tira el dado: define si tu ruta tendrá de 10 a 15 niveles' : 'Haz clic para tirar el dado (10 a 15 niveles)';
            if (!soloLectura) n.addEventListener('click', tirarDado);
            etiqueta(g, k, { x: fin.x, y: fin.y }, 'Desafío Supremo · +3 pts');
            return;
        }

        const n = r.niveles_total || 5;
        const pts = [];
        for (let i = 0; i < n; i++) pts.push(punto(k, i, n));
        nodo('path', { d: trazo([{ x: CX, y: CY }].concat(pts)), class: 'st-linea ' + clase }, g);

        const sinPendientes = (k === 'canje' && r.nivel_actual === 1 && estado.pendientes <= 0);

        pts.forEach((p, i) => {
            const nivel = i + 1;
            const final = nivel === n;
            let est = 'st-locked';
            if (r.completado || nivel < r.nivel_actual) est = 'st-done';
            else if (nivel === r.nivel_actual && !sinPendientes) est = 'st-current';

            const nd = nodo('g', { class: 'st-node ' + clase + ' ' + est }, g);
            nodo('circle', { cx: p.x, cy: p.y, r: final ? 19 : 15 }, nd);
            const tx = nodo('text', { x: p.x, y: p.y + 5, 'text-anchor': 'middle' }, nd);
            tx.textContent = est === 'st-done' ? '✓' : (final ? '★' : String(nivel));
            nodo('title', {}, nd).textContent = tooltip(k, nivel, final, est, sinPendientes);
            if (est === 'st-current' && !soloLectura) nd.addEventListener('click', () => abrirNivel(k));
        });

        const rotulo = k === 'desafio' ? 'Desafío Supremo · +3 pts · ' + n + ' niveles'
            : k === 'canje' ? 'Canjear Puntos 🎒'
            : estado.etiquetas[k] + ' · +1 pt';
        etiqueta(g, k, pts[pts.length - 1], rotulo);
    }

    function etiqueta(g, k, ult, texto) {
        const abajo = Math.sin(ANG[k] * Math.PI / 180) > 0.5;
        const t = nodo('text', {
            x: ult.x + (k === 'desafio' ? 18 : 0),
            y: abajo ? ult.y + 38 : ult.y - 28,
            'text-anchor': k === 'desafio' ? 'end' : 'middle',
            class: 'st-etiqueta ' + (k === 'desafio' || k === 'canje' ? 'st-etq-roja' : '')
        }, g);
        t.textContent = texto;
    }

    function tooltip(k, nivel, final, est, sinPend) {
        if (est === 'st-done') return 'Nivel ' + nivel + ' completado';
        if (k === 'canje' && sinPend) return 'Gana puntos en las ramas o el desafío para poder canjearlos';
        let s = 'Nivel ' + nivel + (final ? ' (final)' : '');
        if (k === 'canje') s += ' · repaso con 5 a 10 preguntas (según el dado)';
        else if (k === 'desafio') s += final ? ' · 10 preguntas' : ' · 5 preguntas';
        else s += final ? ' · Examen de rama: 10 preguntas · +1 punto' : ' · 3 preguntas + consejo';
        return s + (est === 'st-locked' ? ' · bloqueado' : ' · ¡haz clic para jugar!');
    }

    // ---------- modales ----------
    function cerrar() {
        const o = document.querySelector('.st-overlay');
        if (o) o.remove();
    }
    function modal(html) {
        cerrar();
        const o = document.createElement('div');
        o.className = 'st-overlay';
        o.innerHTML = '<div class="st-modal">' + html + '</div>';
        o.addEventListener('click', (e) => { if (e.target === o) cerrar(); });
        document.body.appendChild(o);
        return o;
    }
    function aviso(msg) {
        const o = modal('<p class="st-msg">' + esc(msg) + '</p><div class="st-acciones"><button class="btn btn-institutional" id="stOk">Entendido</button></div>');
        o.querySelector('#stOk').addEventListener('click', cerrar);
    }

    async function refrescar() {
        const r = await api('estado');
        if (!r.ok) { root.innerHTML = '<p class="st-msg">' + esc(r.error || 'No se pudo cargar el árbol.') + '</p>'; return; }
        estado = r;
        estado.negras.forEach((k, i) => { ANG[k] = ANG_NEGRAS[i]; });
        dibujar();
    }

    async function tirarDado() {
        const r = await api('tirar_dado', { rama: 'desafio' });
        if (!r.ok) { aviso(r.error); return; }
        const o = modal('<h3 class="st-titulo">🎲 Tirando el dado…</h3><div class="st-dado" id="stDado">?</div><p class="st-msg" id="stDadoTxt"></p>');
        let n = 0;
        const iv = setInterval(() => {
            document.getElementById('stDado').textContent = 10 + Math.floor(Math.random() * 6);
            if (++n > 14) {
                clearInterval(iv);
                document.getElementById('stDado').textContent = r.dado;
                document.getElementById('stDadoTxt').textContent = '¡Tu Desafío Supremo tendrá ' + r.dado + ' niveles!';
                o.querySelector('.st-modal').insertAdjacentHTML('beforeend', '<div class="st-acciones"><button class="btn btn-institutional" id="stSeg">¡A jugar!</button></div>');
                o.querySelector('#stSeg').addEventListener('click', () => { cerrar(); refrescar(); });
            }
        }, 70);
    }

    async function abrirNivel(rama) {
        const r = await api('iniciar_nivel', { rama: rama });
        if (!r.ok) { aviso(r.error); return; }
        let h = '<h3 class="st-titulo">' + esc(r.titulo) + ' — Nivel ' + r.nivel + '/' + r.total + '</h3>';
        if (r.dado) h += '<p class="st-dado-info">🎲 El dado marcó <strong>' + r.dado + '</strong> preguntas para este nivel</p>';
        if (r.consejo) h += '<div class="st-consejo">💡 ' + esc(r.consejo) + '</div>';
        h += '<p class="st-req">Necesitas ' + r.requerido + ' de ' + r.preguntas.length + ' aciertos para avanzar.</p><form id="stForm">';
        r.preguntas.forEach((q, i) => {
            h += '<fieldset class="st-pregunta"><legend>' + (i + 1) + '. ' + esc(q.p) + '</legend>';
            q.o.forEach((op, j) => {
                h += '<label><input type="radio" name="q' + i + '" value="' + j + '"> <span>' + esc(op) + '</span></label>';
            });
            h += '</fieldset>';
        });
        h += '<div class="st-acciones"><button type="button" class="btn btn-ghost" id="stSalir">Salir</button><button type="submit" class="btn btn-institutional">Enviar respuestas</button></div></form>';
        const o = modal(h);
        o.querySelector('#stSalir').addEventListener('click', cerrar);
        o.querySelector('#stForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const resp = [];
            for (let i = 0; i < r.preguntas.length; i++) {
                const sel = o.querySelector('input[name="q' + i + '"]:checked');
                if (!sel) { alert('Responde la pregunta ' + (i + 1) + ' antes de enviar.'); return; }
                resp.push(parseInt(sel.value, 10));
            }
            const res = await api('enviar_nivel', { respuestas: resp });
            if (!res.ok) { aviso(res.error); return; }
            resultado(res);
        });
    }

    function resultado(res) {
        let h = '<h3 class="st-titulo">' + (res.aprobo ? '🎉 ¡Nivel superado!' : '😅 Casi lo logras') + '</h3>';
        h += '<p class="st-msg">Acertaste <strong>' + res.correctas + ' de ' + res.total_preguntas + '</strong> (mínimo ' + res.requerido + ').</p>';
        if (!res.aprobo) h += '<p class="st-msg">Repasa y vuelve a intentarlo: el nivel te espera.</p>';
        if (res.puntos_ganados) h += '<p class="st-puntos">+' + res.puntos_ganados + ' Punto(s) Académico(s) ganado(s). Ve a Canjear Puntos para pasarlos a tu Bolso.</p>';
        if (res.rama_completada && res.consolidados !== undefined && res.puntos_ganados === 0 && res.aprobo) {
            h += '<p class="st-puntos">🎒 Se pasaron ' + res.consolidados + ' punto(s) a tu Bolso. Saldo: ' + res.bolso + '</p>';
        }
        h += '<div class="st-acciones"><button class="btn btn-institutional" id="stCont">Continuar</button></div>';
        const o = modal(h);
        o.querySelector('#stCont').addEventListener('click', () => { cerrar(); refrescar(); });
    }

    refrescar();
})();

// =========================================================
// BOLSO DE PUNTOS (dashboard.php)
// =========================================================
(function () {
    const META = 10.5;
    const form = document.getElementById('formBolso');
    const msg = document.getElementById('bolsoMsg');

    function mostrar(texto, ok) {
        if (!msg) return;
        msg.textContent = texto;
        msg.className = 'bolso-msg ' + (ok ? 'ok' : 'error');
    }

    async function post(body) {
        try {
            const r = await fetch('api_bolso.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            return await r.json();
        } catch (e) {
            return { ok: false, error: 'No se pudo conectar con el servidor.' };
        }
    }

    if (form) {
        const saldoEl = document.getElementById('bolsoSaldo');
        const saldo = parseInt(saldoEl.dataset.saldo, 10) || 0;
        const notaIn = document.getElementById('bolsoNota');
        const ptsIn = document.getElementById('bolsoPuntos');
        const prev = document.getElementById('bolsoPreview');

        function actualizar() {
            const nota = parseFloat(notaIn.value);
            if (isNaN(nota)) { prev.textContent = 'Ingresa tu nota del examen para ver cuántos puntos necesitas.'; return; }
            if (nota >= META) { prev.textContent = 'Con ' + nota.toFixed(1) + ' ya alcanzas la meta de ' + META + '. No necesitas usar puntos.'; return; }
            const necesarios = Math.ceil(Math.round((META - nota) * 10) / 10);
            const usar = Math.min(necesarios, saldo);
            ptsIn.max = Math.max(usar, 0);
            if (!ptsIn.value) ptsIn.value = usar > 0 ? usar : '';
            const aplicados = parseInt(ptsIn.value, 10) || 0;
            const nuevo = Math.min(20, nota + aplicados);
            let t = 'Necesitas ' + necesarios + ' punto(s) para llegar a ' + META + '. Con ' + aplicados + ' tu nota quedaría en ' + nuevo.toFixed(1) +
                (nuevo >= META ? ' ✅ (aprobatoria)' : ' (aún por debajo de ' + META + ')') + '.';
            if (saldo < necesarios) t += ' Te faltan ' + (necesarios - saldo) + ' punto(s) en el Bolso.';
            prev.textContent = t;
        }
        notaIn.addEventListener('input', () => { ptsIn.value = ''; actualizar(); });
        ptsIn.addEventListener('input', actualizar);
        actualizar();

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const r = await post({
                accion: 'solicitar',
                id_curso: parseInt(document.getElementById('bolsoCurso').value, 10),
                tipo: document.getElementById('bolsoTipo').value,
                nota: parseFloat(notaIn.value),
                puntos: parseInt(ptsIn.value, 10)
            });
            if (r.ok) { mostrar(r.mensaje, true); setTimeout(() => location.reload(), 1200); }
            else mostrar(r.error, false);
        });
    }

    // Profesor / Desarrollador: aprobar o rechazar
    document.querySelectorAll('[data-resolver]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const r = await post({ accion: 'resolver', id: parseInt(btn.dataset.id, 10), decision: btn.dataset.resolver });
            if (r.ok) location.reload();
            else alert(r.error);
        });
    });
})();
