<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['administrador', 'vendedor']);

require_once __DIR__ . '/../../app/config/database.php';

$pdo = Database::getConnection();

$id = (int)($_POST['id'] ?? 0);
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$rol = trim($_POST['rol'] ?? 'cliente');
$estado = trim($_POST['estado'] ?? 'activo');
$verificado = isset($_POST['verificado']) ? 1 : 0;
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validaciones
$errores = [];
if ($nombre_completo === '') $errores[] = 'El nombre es obligatorio.';
if ($email === '') $errores[] = 'El correo es obligatorio.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
if ($telefono === '') $errores[] = 'El teléfono es obligatorio.';
if (!in_array($rol, ['cliente', 'vendedor', 'administrador'], true)) $errores[] = 'El rol no es válido.';
if (!in_array($estado, ['activo', 'inactivo'], true)) $errores[] = 'El estado no es válido.';

if ($id === 0) {
    if (strlen($password) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    if ($password !== $password_confirm) $errores[] = 'Las contraseñas no coinciden.';
}

if (!empty($errores)) {
    header('Location: ' . base_url('dashboard/usuario_form.php?id=' . $id . '&error=' . urlencode(implode(' ', $errores))));
    exit;
}

// Verificar correo único (excepto el del propio usuario)
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1");
$stmt->execute([':email' => $email, ':id' => $id]);
if ($stmt->fetch()) {
    header('Location: ' . base_url('dashboard/usuario_form.php?id=' . $id . '&error=El correo ya está registrado'));
    exit;
}

try {
    if ($id > 0) {
        // Actualizar
        $sql = "UPDATE usuarios 
                SET nombre_completo = :nombre, 
                    email = :email, 
                    telefono = :telefono, 
                    rol = :rol, 
                    estado = :estado, 
                    verificado = :verificado";
        
        $params = [
            ':nombre' => $nombre_completo,
            ':email' => $email,
            ':telefono' => $telefono,
            ':rol' => $rol,
            ':estado' => $estado,
            ':verificado' => $verificado,
            ':id' => $id,
        ];

        // Si se cambió la contraseña
        if ($password !== '') {
            if (strlen($password) < 8) {
                header('Location: ' . base_url('dashboard/usuario_form.php?id=' . $id . '&error=La contraseña debe tener al menos 8 caracteres'));
                exit;
            }
            $sql .= ", password = :password";
            $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header('Location: ' . base_url('dashboard/usuarios.php?msg=Usuario actualizado'));
    } else {
        // Crear
        $stmt = $pdo->prepare("
            INSERT INTO usuarios 
                (nombre_completo, email, telefono, password, rol, verificado, estado)
            VALUES 
                (:nombre, :email, :telefono, :password, :rol, :verificado, :estado)
        ");
        $stmt->execute([
            ':nombre' => $nombre_completo,
            ':email' => $email,
            ':telefono' => $telefono,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':rol' => $rol,
            ':verificado' => $verificado,
            ':estado' => $estado,
        ]);

        header('Location: ' . base_url('dashboard/usuarios.php?msg=Usuario creado'));
    }
    exit;

} catch (PDOException $e) {
    error_log("Error al guardar usuario: " . $e->getMessage());
    header('Location: ' . base_url('dashboard/usuario_form.php?id=' . $id . '&error=Error al guardar el usuario'));
    exit;
}