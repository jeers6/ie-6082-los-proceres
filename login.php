<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    
    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ?");
            $stmt->execute([$usuario, $password]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellido'] = $user['apellido'];
                $_SESSION['dni'] = $user['dni'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['nivel'] = $user['nivel'];
                $_SESSION['grado_seccion'] = $user['grado_seccion'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos. Verifique sus credenciales.';
            }
        } catch(PDOException $e) {
            $error = 'Error al intentar iniciar sesión. Intente nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar al Campus Virtual - I.E. 6082 Los Próceres</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">🎓</div>
            <h1>I.E. 6082 Los Próceres</h1>
            <p>Campus Virtual / Aula Virtual</p>
        </div>
        
        <div class="login-card">
            <h2>Iniciar Sesión</h2>
            
            <div class="info-acceso">
                <p><strong>💡 Indicador de Acceso:</strong></p>
                <p>• <strong>Usuario:</strong> Inicial del nombre + apellido (ej. Juan Pérez → jperez)</p>
                <p>• <strong>Contraseña:</strong> Número de DNI (ej. 60469739)</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert-error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" id="loginForm">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña (DNI)</label>
                    <input type="password" id="password" name="password" placeholder="Ingrese su DNI" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-login">🚀 Ingresar al Campus</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">← Volver al Portal Institucional</a>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
