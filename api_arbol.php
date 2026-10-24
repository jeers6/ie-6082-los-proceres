<?php
session_start();
require_once 'db.php';
require_once 'banco_preguntas.php';
header('Content-Type: application/json; charset=utf-8');

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
$es_estudiante = ($_SESSION['rol'] ?? '') === 'estudiante';
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$accion = $in['accion'] ?? '';
$id_curso = (int)($in['id_curso'] ?? 0);

$stmt = $pdo->prepare("SELECT id, codigo, nombre FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();
if (!$curso) {
    out(['ok' => false, 'error' => 'Curso no encontrado.'], 404);
}
$banco = st_banco($curso['codigo']);
if (!$banco) {
    out(['ok' => false, 'error' => 'Este curso aún no tiene árbol de habilidades.'], 404);
}

// ---------- utilidades ----------
function st_progreso(PDO $pdo, int $uid, int $cid, string $rama): array
{
    $s = $pdo->prepare("SELECT * FROM progreso_arbol WHERE id_estudiante = ? AND id_curso = ? AND rama = ?");
    $s->execute([$uid, $cid, $rama]);
    $r = $s->fetch();
    if (!$r) {
        $total = $rama === 'desafio' ? null : 5;
        $pdo->prepare("INSERT IGNORE INTO progreso_arbol (id_estudiante, id_curso, rama, niveles_total) VALUES (?, ?, ?, ?)")
            ->execute([$uid, $cid, $rama, $total]);
        $s->execute([$uid, $cid, $rama]);
        $r = $s->fetch();
    }
    return $r;
}

function st_pendientes(PDO $pdo, int $uid): int
{
    $s = $pdo->prepare("SELECT COALESCE(SUM(puntos_obtenidos), 0) FROM progreso_arbol
        WHERE id_estudiante = ? AND consolidado = 0 AND completado = 1
        AND rama <> 'canje'");
    $s->execute([$uid]);
    return (int)$s->fetchColumn();
}

function st_bolso(PDO $pdo, int $uid): int
{
    $s = $pdo->prepare("SELECT bolso_puntos FROM usuarios WHERE id = ?");
    $s->execute([$uid]);
    return (int)$s->fetchColumn();
}

function st_estado(PDO $pdo, int $uid, array $curso, array $banco, bool $es_estudiante): array
{
    $ramas = [];
    foreach (st_todas($banco) as $k) {
        if ($es_estudiante) {
            $p = st_progreso($pdo, $uid, (int)$curso['id'], $k);
        } else { // vista previa para profesor/desarrollador: no se guarda nada
            $p = ['nivel_actual' => 1, 'niveles_total' => $k === 'desafio' ? null : 5, 'completado' => 0, 'puntos_obtenidos' => 0];
        }
        $ramas[$k] = [
            'nivel_actual' => (int)$p['nivel_actual'],
            'niveles_total' => $p['niveles_total'] !== null ? (int)$p['niveles_total'] : null,
            'completado' => (bool)$p['completado'],
            'puntos' => (int)$p['puntos_obtenidos'],
        ];
    }
    $etq = [];
    foreach (st_negras($banco) as $k) {
        $etq[$k] = $banco['ramas'][$k]['nombre'];
    }
    return [
        'ok' => true,
        'curso' => $curso['nombre'],
        'etiquetas' => $etq,
        'negras' => st_negras($banco),
        'ramas' => $ramas,
        'pendientes' => $es_estudiante ? st_pendientes($pdo, $uid) : 0,
        'bolso' => $es_estudiante ? st_bolso($pdo, $uid) : 0,
    ];
}

function st_preparar(array $qs): array
{
    $pub = [];
    $ok = [];
    foreach ($qs as $q) {
        $ops = $q['o'];
        $texto = $ops[$q['c']];
        shuffle($ops);
        $ok[] = array_search($texto, $ops, true);
        $pub[] = ['p' => $q['p'], 'o' => $ops];
    }
    return [$pub, $ok];
}

function st_payload(array $run): array
{
    return [
        'ok' => true,
        'titulo' => $run['titulo'],
        'rama' => $run['rama'],
        'nivel' => $run['nivel'],
        'total' => $run['total'],
        'dado' => $run['dado'],
        'consejo' => $run['consejo'],
        'requerido' => $run['req'],
        'preguntas' => $run['pub'],
    ];
}

// ---------- acciones ----------
if ($accion === 'estado') {
    out(st_estado($pdo, $uid, $curso, $banco, $es_estudiante));
}

if (!$es_estudiante) {
    out(['ok' => false, 'error' => 'Modo vista previa: solo los estudiantes pueden jugar y ganar puntos.'], 403);
}

$rama = $in['rama'] ?? '';
if (in_array($accion, ['tirar_dado', 'iniciar_nivel'], true) && !in_array($rama, st_todas($banco), true)) {
    out(['ok' => false, 'error' => 'Rama inválida.'], 400);
}

if ($accion === 'tirar_dado') {
    if ($rama !== 'desafio') {
        out(['ok' => false, 'error' => 'Solo el Desafío Supremo usa el dado de niveles.'], 400);
    }
    $p = st_progreso($pdo, $uid, $id_curso, 'desafio');
    if ($p['completado']) {
        out(['ok' => false, 'error' => 'Ya completaste el Desafío Supremo.'], 400);
    }
    if (!$p['niveles_total']) {
        $t = random_int(10, 15);
        $pdo->prepare("UPDATE progreso_arbol SET niveles_total = ? WHERE id = ?")->execute([$t, $p['id']]);
    } else {
        $t = (int)$p['niveles_total']; // el dado se tira una sola vez
    }
    out(['ok' => true, 'dado' => $t]);
}

if ($accion === 'iniciar_nivel') {
    $p = st_progreso($pdo, $uid, $id_curso, $rama);
    $nivel = (int)$p['nivel_actual'];
    $total = $p['niveles_total'] !== null ? (int)$p['niveles_total'] : 0;

    // Si ya había un nivel abierto igual, se reanuda (evita volver a tirar el dado o re-sortear)
    $run = $_SESSION['st_run'] ?? null;
    if ($run && $run['uid'] === $uid && $run['curso'] === $id_curso && $run['rama'] === $rama && $run['nivel'] === $nivel) {
        out(st_payload($run));
    }

    $consejo = null;
    $dado = null;
    if (in_array($rama, st_negras($banco), true)) {
        if ($p['completado']) {
            out(['ok' => false, 'error' => 'Ya completaste esta rama.'], 400);
        }
        $pool = $banco['ramas'][$rama]['preguntas'];
        if ($nivel < 5) {
            $sel = array_slice($pool, ($nivel - 1) * 3, 3);
            $consejo = $banco['ramas'][$rama]['consejos'][$nivel] ?? null;
        } else {
            shuffle($pool);
            $sel = array_slice($pool, 0, 10);
        }
        $titulo = $banco['ramas'][$rama]['nombre'] . ($nivel === 5 ? ' · Examen de Rama' : '');
    } elseif ($rama === 'desafio') {
        if ($p['completado']) {
            out(['ok' => false, 'error' => 'Ya completaste el Desafío Supremo.'], 400);
        }
        if (!$total) {
            out(['ok' => false, 'error' => 'Primero tira el dado para definir la ruta.'], 400);
        }
        $sel = st_sacar($banco, $nivel === $total ? 10 : 5);
        $titulo = 'Desafío Supremo' . ($nivel === $total ? ' · Nivel Final' : '');
    } else { // canje
        if ($nivel === 1 && st_pendientes($pdo, $uid) <= 0) {
            out(['ok' => false, 'error' => 'No tienes puntos pendientes. Gana puntos en las ramas o el desafío y luego canjéalos.'], 400);
        }
        $dado = random_int(5, 10);
        $sel = st_sacar($banco, $dado);
        $titulo = 'Canjear Puntos · Repaso';
    }

    [$pub, $ok] = st_preparar($sel);
    $run = [
        'uid' => $uid, 'curso' => $id_curso, 'rama' => $rama, 'nivel' => $nivel, 'total' => $total,
        'ok' => $ok, 'pub' => $pub, 'req' => intdiv(count($sel) * ST_APROBACION + 99, 100),
        'dado' => $dado, 'consejo' => $consejo, 'titulo' => $titulo,
    ];
    $_SESSION['st_run'] = $run;
    out(st_payload($run));
}

if ($accion === 'enviar_nivel') {
    $run = $_SESSION['st_run'] ?? null;
    if (!$run || $run['uid'] !== $uid || $run['curso'] !== $id_curso) {
        out(['ok' => false, 'error' => 'No hay un nivel en curso.'], 400);
    }
    unset($_SESSION['st_run']); // un solo intento por apertura de nivel

    $resp = is_array($in['respuestas'] ?? null) ? $in['respuestas'] : [];
    $correctas = 0;
    foreach ($run['ok'] as $i => $c) {
        if (isset($resp[$i]) && (int)$resp[$i] === $c) {
            $correctas++;
        }
    }
    $totalPreg = count($run['ok']);
    $aprobo = $correctas >= $run['req'];
    $rama = $run['rama'];
    $nivel = $run['nivel'];

    $res = [
        'ok' => true, 'correctas' => $correctas, 'total_preguntas' => $totalPreg, 'requerido' => $run['req'],
        'aprobo' => $aprobo, 'rama_completada' => false, 'puntos_ganados' => 0, 'consolidados' => 0,
    ];

    if ($aprobo) {
        $p = st_progreso($pdo, $uid, $id_curso, $rama);
        // El nivel jugado debe seguir siendo el actual (evita doble cobro)
        if ((int)$p['nivel_actual'] !== $nivel) {
            out(['ok' => false, 'error' => 'El progreso cambió. Recarga la página.'], 409);
        }
        $esFinal = $nivel >= (int)$p['niveles_total'];

        if (!$esFinal) {
            $pdo->prepare("UPDATE progreso_arbol SET nivel_actual = nivel_actual + 1 WHERE id = ?")->execute([$p['id']]);
        } elseif ($rama === 'canje') {
            $pdo->beginTransaction();
            $q = $pdo->prepare("SELECT COALESCE(SUM(puntos_obtenidos), 0) FROM progreso_arbol
                WHERE id_estudiante = ? AND consolidado = 0 AND completado = 1
                AND rama <> 'canje' FOR UPDATE");
            $q->execute([$uid]);
            $suma = (int)$q->fetchColumn();
            $pdo->prepare("UPDATE progreso_arbol SET consolidado = 1
                WHERE id_estudiante = ? AND consolidado = 0 AND completado = 1
                AND rama <> 'canje'")->execute([$uid]);
            $pdo->prepare("UPDATE usuarios SET bolso_puntos = bolso_puntos + ? WHERE id = ?")->execute([$suma, $uid]);
            $saldo = st_bolso($pdo, $uid);
            if ($suma > 0) {
                $pdo->prepare("INSERT INTO bolso_historial (id_estudiante, movimiento, saldo_resultante, motivo) VALUES (?, ?, ?, ?)")
                    ->execute([$uid, $suma, $saldo, 'Canje de puntos del árbol (' . $curso['nombre'] . ')']);
            }
            // La ruta de canje queda disponible de nuevo; guarda el total canjeado en puntos_obtenidos
            $pdo->prepare("UPDATE progreso_arbol SET nivel_actual = 1, puntos_obtenidos = puntos_obtenidos + ? WHERE id = ?")
                ->execute([$suma, $p['id']]);
            $pdo->commit();
            $res['consolidados'] = $suma;
            $res['rama_completada'] = true;
        } else {
            $pts = $rama === 'desafio' ? 3 : 1;
            $pdo->prepare("UPDATE progreso_arbol SET completado = 1, puntos_obtenidos = ?, consolidado = 0 WHERE id = ?")
                ->execute([$pts, $p['id']]);
            $res['puntos_ganados'] = $pts;
            $res['rama_completada'] = true;
        }
    }

    $res['bolso'] = st_bolso($pdo, $uid);
    $res['pendientes'] = st_pendientes($pdo, $uid);
    out($res);
}

out(['ok' => false, 'error' => 'Acción desconocida.'], 400);
