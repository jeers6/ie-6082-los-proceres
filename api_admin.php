<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

function out(array $d, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit();
}

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario'] ?? '') !== 'jjauyajesus' || ($_SESSION['rol'] ?? '') !== 'desarrollador') {
    out(['ok' => false, 'error' => 'Acceso denegado.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$accion = $in['accion'] ?? '';

try {
    // Registrar nuevo estudiante
    if ($accion === 'registrar_estudiante') {
        $usuario = trim($in['usuario'] ?? '');
        $password = trim($in['password'] ?? '123456');
        $nombre = trim($in['nombre'] ?? '');
        $apellido = trim($in['apellido'] ?? '');
        $dni = trim($in['dni'] ?? '');
        $foto = trim($in['foto'] ?? '') ?: null;

        if ($usuario === '' || $nombre === '' || $apellido === '' || $dni === '') {
            out(['ok' => false, 'error' => 'Completa todos los campos obligatorios.'], 400);
        }

        // Verificar unicidad
        $chk = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? OR dni = ?");
        $chk->execute([$usuario, $dni]);
        if ($chk->fetch()) {
            out(['ok' => false, 'error' => 'El usuario o DNI ya existe.'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, nombre, apellido, dni, rol, foto, nivel_xp, racha_dias, bolso_puntos) VALUES (?, ?, ?, ?, ?, 'estudiante', ?, 0, 0, 0)");
        $stmt->execute([$usuario, $password, $nombre, $apellido, $dni, $foto]);
        out(['ok' => true, 'mensaje' => 'Estudiante registrado correctamente.']);
    }

    // Editar XP / Racha
    if ($accion === 'editar_xp_racha') {
        $id = (int)($in['id'] ?? 0);
        $nivel_xp = (int)($in['nivel_xp'] ?? 0);
        $racha_dias = (int)($in['racha_dias'] ?? 0);
        if ($id <= 0) {
            out(['ok' => false, 'error' => 'ID inválido.'], 400);
        }
        $stmt = $pdo->prepare("UPDATE usuarios SET nivel_xp = ?, racha_dias = ? WHERE id = ? AND rol = 'estudiante'");
        $stmt->execute([$nivel_xp, $racha_dias, $id]);
        out(['ok' => true, 'mensaje' => 'XP y racha actualizados.']);
    }

    // Reiniciar puntos
    if ($accion === 'reiniciar_puntos') {
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) {
            out(['ok' => false, 'error' => 'ID inválido.'], 400);
        }
        $stmt = $pdo->prepare("UPDATE usuarios SET bolso_puntos = 0 WHERE id = ? AND rol = 'estudiante'");
        $stmt->execute([$id]);
        out(['ok' => true, 'mensaje' => 'Puntos reiniciados a 0.']);
    }

    // Eliminar estudiante
    if ($accion === 'eliminar_estudiante') {
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) {
            out(['ok' => false, 'error' => 'ID inválido.'], 400);
        }
        // No eliminar al desarrollador
        $chk = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $chk->execute([$id]);
        $r = $chk->fetch();
        if (!$r || $r['rol'] === 'desarrollador') {
            out(['ok' => false, 'error' => 'No se puede eliminar este usuario.'], 400);
        }
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'estudiante'");
        $stmt->execute([$id]);
        out(['ok' => true, 'mensaje' => 'Estudiante eliminado correctamente.']);
    }

    // Publicar comunicado
    if ($accion === 'publicar_comunicado') {
        $titulo = trim($in['titulo'] ?? '');
        $contenido = trim($in['contenido'] ?? '');
        $tipo = $in['tipo'] ?? 'general';
        $autor = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
        if ($titulo === '' || $contenido === '') {
            out(['ok' => false, 'error' => 'Completa título y contenido.'], 400);
        }
        if (!in_array($tipo, ['general', 'urgente', 'evento'], true)) {
            $tipo = 'general';
        }
        $stmt = $pdo->prepare("INSERT INTO avisos (titulo, contenido, autor, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titulo, $contenido, $autor, $tipo]);
        out(['ok' => true, 'mensaje' => 'Comunicado publicado correctamente.']);
    }

    // Estadísticas para analítica
    if ($accion === 'estadisticas') {
        // Promedio XP
        $prom = $pdo->query("SELECT AVG(nivel_xp) as promedio FROM usuarios WHERE rol = 'estudiante'")->fetch();
        $promedio_xp = round($prom['promedio'] ?? 0, 2);

        // Top por racha
        $topRacha = $pdo->query("SELECT usuario, nombre, apellido, racha_dias FROM usuarios WHERE rol = 'estudiante' ORDER BY racha_dias DESC, nivel_xp DESC LIMIT 1")->fetch();

        // Solicitudes pendientes
        $pend = $pdo->query("SELECT COUNT(*) as c FROM solicitudes_puntos WHERE estado = 'pendiente'")->fetch();
        $solicitudes_pendientes = (int)($pend['c'] ?? 0);

        // Solicitudes aceptadas vs pendientes
        $acep = $pdo->query("SELECT COUNT(*) as c FROM solicitudes_puntos WHERE estado = 'aprobada'")->fetch();
        $rech = $pdo->query("SELECT COUNT(*) as c FROM solicitudes_puntos WHERE estado = 'rechazada'")->fetch();
        $aceptadas = (int)($acep['c'] ?? 0);
        $rechazadas = (int)($rech['c'] ?? 0);

        // Distribución por estudiante
        $dist = $pdo->query("SELECT usuario, nivel_xp, racha_dias FROM usuarios WHERE rol = 'estudiante' ORDER BY nivel_xp DESC")->fetchAll();

        out([
            'ok' => true,
            'promedio_xp' => $promedio_xp,
            'top_racha' => $topRacha,
            'solicitudes_pendientes' => $solicitudes_pendientes,
            'solicitudes_aceptadas' => $aceptadas,
            'solicitudes_rechazadas' => $rechazadas,
            'distribucion' => $dist
        ]);
    }

    out(['ok' => false, 'error' => 'Acción desconocida.'], 400);
} catch (PDOException $e) {
    out(['ok' => false, 'error' => 'Error de base de datos.'], 500);
}
