<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

const META_APROBATORIA = 10.5;

function out(array $d, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit();
}

if (!isset($_SESSION['usuario_id'])) {
    out(['ok' => false, 'error' => 'Tu sesión expiró. Vuelve a iniciar sesión.'], 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

$uid = (int)$_SESSION['usuario_id'];
$rol = $_SESSION['rol'] ?? '';
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$accion = $in['accion'] ?? '';

// Registra cada movimiento: es el respaldo del saldo
function bolso_log(PDO $pdo, int $uid, int $mov, string $motivo): int
{
    $s = $pdo->prepare("SELECT bolso_puntos FROM usuarios WHERE id = ?");
    $s->execute([$uid]);
    $saldo = (int)$s->fetchColumn();
    $pdo->prepare("INSERT INTO bolso_historial (id_estudiante, movimiento, saldo_resultante, motivo) VALUES (?, ?, ?, ?)")
        ->execute([$uid, $mov, $saldo, $motivo]);
    return $saldo;
}

function get_saldo(PDO $pdo, int $uid): int
{
    $s = $pdo->prepare("SELECT bolso_puntos FROM usuarios WHERE id = ?");
    $s->execute([$uid]);
    return (int)$s->fetchColumn();
}

try {
    // ---------- Obtener saldo PrócerCoins ----------
    if ($accion === 'saldo') {
        out(['ok' => true, 'saldo' => get_saldo($pdo, $uid)]);
    }

    // ---------- Trivia diaria (1 vez por día) ----------
    if ($accion === 'trivia_diaria') {
        if ($rol !== 'estudiante') {
            out(['ok' => false, 'error' => 'Solo los estudiantes pueden participar en la trivia.'], 403);
        }
        $hoy = date('Y-m-d');
        // Verificar si ya participó hoy
        $chk = $pdo->prepare("SELECT COUNT(*) FROM bolso_historial WHERE id_estudiante = ? AND motivo LIKE ? AND DATE(fecha) = ?");
        $chk->execute([$uid, '%Trivia diaria%', $hoy]);
        if ((int)$chk->fetchColumn() > 0) {
            out(['ok' => false, 'completada' => true, 'error' => 'Trivia de hoy completada. Vuelve mañana para ganar más PrócerCoins.']);
        }
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE usuarios SET bolso_puntos = bolso_puntos + 2 WHERE id = ?")->execute([$uid]);
        $saldo = bolso_log($pdo, $uid, 2, 'Trivia diaria completada - +2 PrócerCoins (PC)');
        $pdo->commit();
        out(['ok' => true, 'mensaje' => '¡Correcto! Ganaste +2 PrócerCoins (PC).', 'saldo' => $saldo]);
    }

    // ---------- Verificar estado trivia diaria ----------
    if ($accion === 'trivia_estado') {
        if ($rol !== 'estudiante') {
            out(['ok' => true, 'completada' => false]);
        }
        $hoy = date('Y-m-d');
        $chk = $pdo->prepare("SELECT COUNT(*) FROM bolso_historial WHERE id_estudiante = ? AND motivo LIKE ? AND DATE(fecha) = ?");
        $chk->execute([$uid, '%Trivia diaria%', $hoy]);
        out(['ok' => true, 'completada' => ((int)$chk->fetchColumn() > 0)]);
    }

    // ---------- Canjear PrócerCoins por puntos de examen (5 PC = +1 punto) ----------
    if ($accion === 'canjear_pc') {
        if ($rol !== 'estudiante') {
            out(['ok' => false, 'error' => 'Solo los estudiantes pueden canjear PrócerCoins.'], 403);
        }
        $pc = (int)($in['pc'] ?? 0);
        if ($pc < 5 || ($pc % 5) !== 0) {
            out(['ok' => false, 'error' => 'Debes canjear en múltiplos de 5 PrócerCoins (5 PC = +1 punto en examen).'], 400);
        }
        $puntos_a_ganar = intdiv($pc, 5);
        $pdo->beginTransaction();
        $b = $pdo->prepare("SELECT bolso_puntos FROM usuarios WHERE id = ? FOR UPDATE");
        $b->execute([$uid]);
        $saldo_actual = (int)$b->fetchColumn();
        if ($saldo_actual < $pc) {
            $pdo->rollBack();
            out(['ok' => false, 'error' => 'No tienes suficientes PrócerCoins.'], 400);
        }
        $pdo->prepare("UPDATE usuarios SET bolso_puntos = bolso_puntos - ? WHERE id = ?")->execute([$pc, $uid]);
        $saldo = bolso_log($pdo, $uid, -$pc, "Canje de $pc PrócerCoins (PC) por $puntos_a_ganar punto(s) para examen");
        // Crear solicitud genérica para aplicar puntos (se puede aprobar desde panel)
        $pdo->prepare("INSERT INTO solicitudes_puntos (id_estudiante, id_curso, tipo_examen, nota_actual, puntos_aplicados, nota_resultante, estado)
            VALUES (?, 1, 'parcial', 0, ?, 0, 'pendiente')")->execute([$uid, $puntos_a_ganar]);
        $pdo->commit();
        out(['ok' => true, 'mensaje' => "Canje exitoso: $pc PC → $puntos_a_ganar punto(s) en examen. Solicitud enviada.", 'saldo' => $saldo]);
    }

    // ---------- Comprar recompensa en tienda ----------
    if ($accion === 'comprar_recompensa') {
        if ($rol !== 'estudiante') {
            out(['ok' => false, 'error' => 'Solo los estudiantes pueden comprar recompensas.'], 403);
        }
        $recompensa = $in['recompensa'] ?? '';
        $costos = [
            'insignia_maestra' => 12,
            'certificado_honor' => 20,
            'pase_extension' => 8
        ];
        if (!isset($costos[$recompensa])) {
            out(['ok' => false, 'error' => 'Recompensa inválida.'], 400);
        }
        $costo = $costos[$recompensa];
        $nombres = [
            'insignia_maestra' => 'Insignia Mente Maestra (Marco Dorado)',
            'certificado_honor' => 'Certificado Digital de Honor',
            'pase_extension' => 'Pase de Extensión de Entrega (+24h)'
        ];
        $pdo->beginTransaction();
        $b = $pdo->prepare("SELECT bolso_puntos FROM usuarios WHERE id = ? FOR UPDATE");
        $b->execute([$uid]);
        $saldo_actual = (int)$b->fetchColumn();
        if ($saldo_actual < $costo) {
            $pdo->rollBack();
            out(['ok' => false, 'error' => 'No tienes suficientes PrócerCoins.'], 400);
        }
        $pdo->prepare("UPDATE usuarios SET bolso_puntos = bolso_puntos - ? WHERE id = ?")->execute([$costo, $uid]);
        $saldo = bolso_log($pdo, $uid, -$costo, 'Compra: ' . $nombres[$recompensa]);
        $pdo->commit();
        out(['ok' => true, 'mensaje' => '¡Recompensa adquirida: ' . $nombres[$recompensa] . '!', 'saldo' => $saldo]);
    }

    // ---------- Estudiante pide aplicar puntos a un examen ----------
    if ($accion === 'solicitar') {
        if ($rol !== 'estudiante') {
            out(['ok' => false, 'error' => 'Solo los estudiantes pueden solicitar puntos.'], 403);
        }
        $id_curso = (int)($in['id_curso'] ?? 0);
        $tipo = $in['tipo'] ?? '';
        $nota = round((float)($in['nota'] ?? -1), 1);
        $puntos = (int)($in['puntos'] ?? 0);

        if (!in_array($tipo, ['parcial', 'final'], true)) {
            out(['ok' => false, 'error' => 'Tipo de examen inválido.'], 400);
        }
        if ($nota < 0 || $nota > 20) {
            out(['ok' => false, 'error' => 'La nota debe estar entre 0 y 20.'], 400);
        }
        if ($nota >= META_APROBATORIA) {
            out(['ok' => false, 'error' => 'Tu nota ya alcanza la meta de 10.5; no necesitas usar puntos.'], 400);
        }
        $necesarios = (int)ceil(round(META_APROBATORIA - $nota, 1));
        if ($puntos < 1 || $puntos > $necesarios) {
            out(['ok' => false, 'error' => "Puedes aplicar entre 1 y $necesarios punto(s) para llegar a 10.5."], 400);
        }
        $c = $pdo->prepare("SELECT id FROM cursos WHERE id = ?");
        $c->execute([$id_curso]);
        if (!$c->fetch()) {
            out(['ok' => false, 'error' => 'Curso no encontrado.'], 404);
        }

        $pdo->beginTransaction();
        $b = $pdo->prepare("SELECT bolso_puntos FROM usuarios WHERE id = ? FOR UPDATE");
        $b->execute([$uid]);
        if ((int)$b->fetchColumn() < $puntos) {
            $pdo->rollBack();
            out(['ok' => false, 'error' => 'No tienes suficientes PrócerCoins.'], 400);
        }
        $d = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_puntos WHERE id_estudiante = ? AND id_curso = ? AND tipo_examen = ? AND estado = 'pendiente'");
        $d->execute([$uid, $id_curso, $tipo]);
        if ((int)$d->fetchColumn() > 0) {
            $pdo->rollBack();
            out(['ok' => false, 'error' => 'Ya tienes una solicitud pendiente para este examen.'], 400);
        }
        // Los puntos se reservan (se descuentan) mientras la solicitud está pendiente
        $pdo->prepare("UPDATE usuarios SET bolso_puntos = bolso_puntos - ? WHERE id = ?")->execute([$puntos, $uid]);
        $resultante = min(20, $nota + $puntos);
        $pdo->prepare("INSERT INTO solicitudes_puntos (id_estudiante, id_curso, tipo_examen, nota_actual, puntos_aplicados, nota_resultante)
            VALUES (?, ?, ?, ?, ?, ?)")->execute([$uid, $id_curso, $tipo, $nota, $puntos, $resultante]);
        $saldo = bolso_log($pdo, $uid, -$puntos, "Solicitud de $puntos PrócerCoin(s) en examen $tipo");
        $pdo->commit();
        out(['ok' => true, 'mensaje' => 'Solicitud enviada. Tu docente la revisará.', 'bolso' => $saldo]);
    }

    // ---------- Profesor / Desarrollador resuelve ----------
    if ($accion === 'resolver') {
        if (!in_array($rol, ['profesor', 'desarrollador'], true)) {
            out(['ok' => false, 'error' => 'No tienes permiso para resolver solicitudes.'], 403);
        }
        $id = (int)($in['id'] ?? 0);
        $decision = $in['decision'] ?? '';
        if (!in_array($decision, ['aprobar', 'rechazar'], true)) {
            out(['ok' => false, 'error' => 'Decisión inválida.'], 400);
        }
        $pdo->beginTransaction();
        $s = $pdo->prepare("SELECT * FROM solicitudes_puntos WHERE id = ? FOR UPDATE");
        $s->execute([$id]);
        $sol = $s->fetch();
        if (!$sol || $sol['estado'] !== 'pendiente') {
            $pdo->rollBack();
            out(['ok' => false, 'error' => 'La solicitud no existe o ya fue resuelta.'], 400);
        }
        $estado = $decision === 'aprobar' ? 'aprobada' : 'rechazada';
        $pdo->prepare("UPDATE solicitudes_puntos SET estado = ?, fecha_resolucion = NOW(), resuelta_por = ? WHERE id = ?")
            ->execute([$estado, $uid, $id]);
        if ($estado === 'rechazada') { // devuelve los puntos reservados
            $pdo->prepare("UPDATE usuarios SET bolso_puntos = bolso_puntos + ? WHERE id = ?")
                ->execute([(int)$sol['puntos_aplicados'], (int)$sol['id_estudiante']]);
            bolso_log($pdo, (int)$sol['id_estudiante'], (int)$sol['puntos_aplicados'], 'Solicitud #' . $id . ' rechazada: puntos devueltos');
        }
        $pdo->commit();
        out(['ok' => true, 'mensaje' => "Solicitud $estado."]);
    }

    out(['ok' => false, 'error' => 'Acción desconocida.'], 400);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    out(['ok' => false, 'error' => 'Error de base de datos. ¿Ejecutaste migracion_skill_tree.sql?'], 500);
}
