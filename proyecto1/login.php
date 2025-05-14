<?php
session_start();
include 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $clave = $_POST['contraseña'];

    $stmt = $conn->prepare("SELECT idusuario, contraseña, rol FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($idusuario, $hash, $rol);
        $stmt->fetch();

        if (password_verify($clave, $hash)) {
            $_SESSION['idusuario'] = $idusuario;
            $_SESSION['correo'] = $correo;
            $_SESSION['rol'] = $rol;

            header("Location: index.php");
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Correo no registrado.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Retro</title>
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">

  
<a href="../index.html" style="position: absolute; top: 20px; left: 20px; z-index: 10;">
  <img 
    src="../img/logo.png" 
    alt="Kuimera Studios" 
    class="logo-animado"
    style="width: 200px; height: auto; background-color: rgba(231, 231, 231, 0.51); padding: 10px; border: 1px solid white; border-radius: 5px;">
</a>

<style>
  .logo-animado {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .logo-animado:hover {
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
  }
</style>






  <style>
    body {
      margin: 0;
      padding: 0;
      background: #0d0d0d url('img/fondo.png') repeat;
      font-family: 'Press Start 2P', cursive;
      color: #00ffcc;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-box {
      background-color: rgba(0, 0, 0, 0.85);
      border: 4px solid #00ffcc;
      padding: 40px;
      text-align: center;
      box-shadow: 0 0 20px #00ffcc;
      width: 400px;
    }

    .login-box h2 {
      margin-bottom: 30px;
      color: #00ffcc;
      text-shadow: 0 0 5px #00ffcc, 0 0 15px #00ffcc;
    }

    .form-group {
      margin-bottom: 25px;
      text-align: left;
    }

    label {
      display: block;
      margin-bottom: 10px;
    }

    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      background: #111;
      border: 2px solid #00ffcc;
      color: #00ffcc;
      font-family: 'Press Start 2P', cursive;
      font-size: 12px;
    }

    input[type="submit"] {
      margin-top: 20px;
      padding: 12px 20px;
      background: #00ffcc;
      color: black;
      border: none;
      cursor: pointer;
      font-family: 'Press Start 2P', cursive;
      font-size: 12px;
      transition: background 0.3s ease;
    }

    input[type="submit"]:hover {
      background: #00ffaa;
    }

    .pixel-border {
      border: 4px dashed #ff00cc;
      padding: 5px;
      margin-top: 20px;
    }
  </style>
</head>
<body>

  <div class="login-box">
    <h2>LOGIN</h2>
    <form action="login.php" method="POST">
      <div class="form-group">
        <label for="correo">Correo</label>
        <input type="text" id="correo" name="correo" required>
      </div>
      <div class="form-group">
        <label for="contraseña">Contraseña</label>
        <input type="password" id="contraseña" name="contraseña" required>
      </div>
      <input type="submit" value="INGRESAR">
    </form>
    <div class="pixel-border">💾 Inserta tu contraseña</div>
  </div>

</body>
</html>
