<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $correo = $_POST['correo'];
  $telefono = $_POST['telefono'];
  $empresa = $_POST['empresa'];
  $asunto = $_POST['asunto'];
  $mensaje = $_POST['mensaje'];

  // Guardar en base de datos
  $stmt = $conn->prepare("INSERT INTO contacto (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $nombre, $correo, $asunto, $mensaje);

  if ($stmt->execute()) {
    $exito = true;

    // Enviar correo
    $destinatario = "estudiokuimera@gmail.com";
    $titulo = "Nuevo mensaje desde Kuimera.cl";
    $contenido = "
      Nombre: $nombre\n
      Correo: $correo\n
      Teléfono: $telefono\n
      Empresa: $empresa\n
      Asunto: $asunto\n
      Mensaje:\n$mensaje
    ";
    $cabeceras = "From: $correo\r\n" .
                 "Reply-To: $correo\r\n" .
                 "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($destinatario, $titulo, $contenido, $cabeceras);

  } else {
    $error = "Error al guardar el mensaje: " . $conn->error;
  }

  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contacto | Kuimera Studios</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<div id="header-placeholder"></div>


<!-- Formulario de contacto moderno -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="contact-form">
        <h2 class="form-title mb-4 text-primary fw-bold">Contáctanos</h2>
        <form action="#" method="POST">

          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
          </div>

          <div class="mb-3">
            <label for="correo" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" required>
          </div>

          <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="+56 9 1234 5678" required>
          </div>



          <div class="mb-3">
            <label for="empresa" class="form-label">Empresa (opcional)</label>
            <input type="text" class="form-control" id="empresa" name="empresa" placeholder="Ej: Mi Empresa SA">
          </div>

          <div class="mb-3">
            <label for="asunto" class="form-label">Asunto</label>
            <input type="text" class="form-control" id="asunto" name="asunto" required>
          </div>

          <div class="mb-3">
            <label for="mensaje" class="form-label">Mensaje</label>
            <textarea class="form-control" id="mensaje" name="mensaje" rows="5" placeholder="Cuéntanos qué tipo de proyecto necesitas: sistema de ventas, plataforma para gimnasio, inventario empresarial, etc..." required></textarea>
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-kuimera px-5 py-2 mt-3">Enviar Mensaje</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($exito)): ?>
  <div class="alert alert-success text-center fw-semibold">✅ Tu mensaje ha sido enviado correctamente.</div>
<?php elseif (!empty($error)): ?>
  <div class="alert alert-danger text-center fw-semibold"><?= $error ?></div>
<?php endif; ?>


<div id="footer-container"></div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  fetch('header.html')
    .then(res => res.text())
    .then(data => {
      document.getElementById('header-placeholder').innerHTML = data;
    });
</script>
<script>
  fetch("footer.html")
    .then(res => res.text())
    .then(data => {
      document.getElementById("footer-container").innerHTML = data;
    });
</script>

<?php if (!empty($exito)): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Mensaje enviado',
      text: 'Tu mensaje ha sido enviado correctamente ✅',
      confirmButtonColor: '#0d6efd'
    });
  </script>
<?php elseif (!empty($error)): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: '¡Ups!',
      text: '<?= $error ?>',
      confirmButtonColor: '#dc3545'
    });
  </script>
<?php endif; ?>

<script>
// 🟦 Formateo en vivo mientras escribe
document.getElementById('telefono').addEventListener('input', function(e) {
  let valor = e.target.value.replace(/\D/g, ''); // Eliminar no numéricos
  if (valor.startsWith('56')) {
    valor = valor.slice(2); // Eliminar doble 56 si lo puso manual
  }
  if (valor.startsWith('9')) {
    valor = valor.slice(1); // Eliminar 9 si ya está después del 56
  }

  // Limitar máximo 8 dígitos después del 9
  valor = valor.substring(0, 8);

  // Reconstruir formato +56 9 XXXX XXXX
  e.target.value = `+56 9 ${valor.slice(0, 4)} ${valor.slice(4, 8)}`.trim();
});

// 🟦 Limpieza antes de enviar el formulario
document.querySelector("form").addEventListener("submit", function (e) {
  const telInput = document.getElementById("telefono");
  let numero = telInput.value.replace(/\D/g, ""); // solo dígitos

  // Asegurar prefijo +56
  if (!numero.startsWith("56")) {
    numero = "56" + numero;
  }

  // Resultado final: +569XXXXXXXX
  telInput.value = "+" + numero;
});
</script>


</body>
</html>
