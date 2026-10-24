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

try {
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
            out(['ok' => false, 'error' => 'No tienes suficientes puntos en el Bolso.'], 400);
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
        $saldo = bolso_log($pdo, $uid, -$puntos, "Solicitud de $puntos punto(s) en examen $tipo");
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
