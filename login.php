<?php
session_start(); // Iniciar sesión para guardar datos de login
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = htmlspecialchars($_POST['login']);
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $message = "Error: Login y contraseña son obligatorios.";
        $message_type = 'error';
    } else {
        try {
            // Crear cliente SOAP para conectar con el servidor
            $client = new SoapClient(null, [
                'location' => "http://localhost:8000/soap4/server.php", // Cambia según tu servidor SOAP
                'uri' => "urn:PersonService",
                'trace' => 1
            ]);

            // Enviar login y contraseña al servidor para autenticación
            $response = $client->loginPerson($login, $password);

            if ($response == "success") {
                $_SESSION['login'] = $login; // Guardar login en la sesión
                header("Location: dashboard.php"); // Redirigir a la página principal
                exit();
            } else {
                $message = $response; // Mostrar mensaje de error
                $message_type = 'error';
            }

        } catch (SoapFault $e) {
            // Nivel 1: Error de conexión al servidor SOAP
            if (strpos($e->getMessage(), 'Could not connect to host') !== false) {
                $message = "Nivel 1: Error - No se pudo conectar al servidor.";
            } else {
                // Nivel 2: Error en la validación del login
                $message = "Nivel 2: Error - No se pudo realizar la validación. Detalle: " . $e->getMessage();
            }
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <ul>
            <li><a href="index.php">Registrar Usuario</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
    </nav>

    <div class="container" id="container">
        <div class="left-side expanded" id="left-side">
            <h2>Bienvenido</h2>
            <p>Accede a los servicios disponibles para gestionar tus cuentas.</p>
        </div>

        <div class="right-side visible" id="right-side">
            <div class="form-container visible">            
                <h1>Login</h1>

                <!-- Mostrar mensaje de error o éxito -->
                <?php if ($message): ?>
                    <div class="message <?= $message_type ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <label for="login">Login:</label>
                    <input type="text" id="login" name="login" required>

                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
