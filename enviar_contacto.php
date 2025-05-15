<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Evitar acceso directo por GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: contacto.php');
  exit;
}

// Recibir datos del formulario
$nombre   = $_POST['nombre'] ?? '';
$correo   = $_POST['correo'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$empresa  = $_POST['empresa'] ?? '';
$asunto   = $_POST['asunto'] ?? '';
$mensaje  = $_POST['mensaje'] ?? '';

// Validar correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
  header('Location: contacto.php?error=' . urlencode('Correo electrónico no válido.'));
  exit;
}

// Insertar en base de datos
$stmt = $conn->prepare("INSERT INTO contacto (nombre, email, telefono, empresa, asunto, mensaje) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $nombre, $correo, $telefono, $empresa, $asunto, $mensaje);

if (!$stmt->execute()) {
  $stmt->close();
  header('Location: contacto.php?error=' . urlencode("Error al guardar el mensaje: " . $conn->error));
  exit;
}
$stmt->close();

// Enviar correo con PHPMailer
$mail = new PHPMailer(true);

try {
  $mail->isSMTP();
  $mail->Host       = 'mail.kuimera.cl';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'estudiokuimera@kuimera.cl';
  $mail->Password   = 'kuikui2024!';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port       = 465;
  $mail->CharSet    = 'UTF-8';

  $mail->setFrom('estudiokuimera@kuimera.cl', 'Kuimera Web');
  $mail->addAddress('estudiokuimera@gmail.com'); // Receptor

  $mail->Subject = "Nuevo mensaje desde Kuimera.cl";
  $mail->Body    = "
Nombre: $nombre
Correo: $correo
Teléfono: $telefono
Empresa: $empresa
Asunto: $asunto
Mensaje:
$mensaje
  ";

  $mail->send();

  header('Location: contacto.php?exito=1');
  exit;

} catch (Exception $e) {
  $mensajeError = "No se pudo enviar el correo: " . $mail->ErrorInfo;
  header('Location: contacto.php?error=' . urlencode($mensajeError));
  exit;
}
?>
