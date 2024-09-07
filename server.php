<?php
class PersonService {
    private $dbServerUrl;

    public function __construct() {
        $this->dbServerUrl = "http://localhost:8000/soap4/bd.php"; // Cambia según tu configuración
    }

    // Registrar persona (incluyendo login y password)
    public function registerPerson($nombre, $apellido_paterno, $apellido_materno, $numero_carnet, $fecha_nacimiento, $sexo, $lugar_nacimiento, $estado_civil, $profesion, $domicilio, $login, $password, $token) {
        try {
            // Verificar si el token ya está registrado (persona con estos mismos datos)
            $tokenExists = $this->remoteCall('checkIfPersonExists', [$token]);
            if ($tokenExists) {
                $personInfo = $this->remoteCall('getPersonInfo', [$token]);
                throw new SoapFault("Client", "Nivel 2: Error - Este registro ya existe. Nombre: " . $personInfo['nombre'] . ", Apellido Paterno: " . $personInfo['apellido_paterno'] . ", Apellido Materno: " . $personInfo['apellido_materno'] . ", Carnet: " . $personInfo['numero_carnet'] . ".");
            }

            // Verificar si el carnet ya está registrado
            $carnetExists = $this->remoteCall('checkIfCarnetExists', [$numero_carnet]);
            if ($carnetExists) {
                throw new SoapFault("Client", "Nivel 2: Error - El número de carnet ya está registrado.");
            }

            // Verificar si el login ya está registrado
            $loginExists = $this->remoteCall('checkIfLoginExists', [$login]);
            if ($loginExists) {
                throw new SoapFault("Client", "Nivel 2: Error - El login ya está en uso.");
            }

            // Insertar persona en la base de datos
            $result = $this->remoteCall('insertPerson', [
                $nombre, $apellido_paterno, $apellido_materno, $numero_carnet, $fecha_nacimiento, $sexo,
                $lugar_nacimiento, $estado_civil, $profesion, $domicilio, $login, $password, $token
            ]);

            if ($result !== true) {
                throw new Exception("Error al insertar la persona.");
            }

            return "Registro exitoso de: $nombre $apellido_paterno $apellido_materno - $numero_carnet";
        } catch (SoapFault $e) {
            throw $e;  // Propagar errores de nivel 2 o 3
        } catch (Exception $e) {
            throw new SoapFault("Server", "Nivel 2: Error - " . $e->getMessage());
        }
    }

    // Método para autenticar login y contraseña
    public function loginPerson($login, $password) {
        try {
            // Llamar a la base de datos para verificar el login
            $result = $this->remoteCall('authenticateLogin', [$login, $password]);

            if ($result === true) {
                return "success"; // Autenticación exitosa
            } else {
                return "Nivel 2: Error - Login o contraseña incorrectos.";
            }
        } catch (SoapFault $e) {
            throw $e;
        } catch (Exception $e) {
            throw new SoapFault("Server", "Nivel 2: Error - Ocurrió un problema durante la autenticación.");
        }
    }

    // Método para crear una cuenta
    public function crearCuenta($login, $tipo_cuenta, $token) {
        try {
            // Verificar si ya existe una cuenta del mismo tipo para este cliente
            $cuentaExiste = $this->remoteCall('checkIfAccountExists', [$login, $tipo_cuenta]);
            if ($cuentaExiste) {
                throw new SoapFault("Client", "Nivel 2: Error - Ya tienes una cuenta en $tipo_cuenta.");
            }

            // Crear la cuenta si no existe
            $result = $this->remoteCall('insertAccount', [$login, $tipo_cuenta, $token]);

            if ($result !== true) {
                throw new Exception("Error al crear la cuenta.");
            }

            return "success";
        } catch (SoapFault $e) {
            throw $e;  // Propagar errores de nivel 2 o 3
        } catch (Exception $e) {
            throw new SoapFault("Server", "Nivel 2: Error - " . $e->getMessage());
        }
    }

    // Obtener las cuentas de un cliente
    public function getCuentasCliente($login) {
        try {
            return $this->remoteCall('getCuentasCliente', [$login]);
        } catch (SoapFault $e) {
            throw new SoapFault("Server", "Nivel 2: Error - " . $e->getMessage());  // Manejo de errores de Nivel 2 o 3
        }
    }

    // Realizar un depósito en una cuenta
    public function depositar($cuenta_id, $monto, $token) {
        try {
            return $this->remoteCall('depositar', [$cuenta_id, $monto, $token]);
        } catch (SoapFault $e) {
            throw new SoapFault("Server", "Nivel 2: Error - " . $e->getMessage());  // Manejo de errores de Nivel 2 o 3
        }
    }

    // Realizar un retiro en una cuenta
    public function retirar($cuenta_id, $monto, $token) {
        try {
            return $this->remoteCall('retirar', [$cuenta_id, $monto, $token]);
        } catch (SoapFault $e) {
            throw new SoapFault("Server", "Nivel 2: Error - " . $e->getMessage());
        }
    }

    private function remoteCall($method, $params) {
        try {
            $client = new SoapClient(null, [
                'location' => $this->dbServerUrl,
                'uri' => "urn:DatabaseService"
            ]);
            return $client->__soapCall($method, $params);
        } catch (SoapFault $e) {
            throw new SoapFault("Server", "Nivel 3: Error - Problema al acceder a la base de datos.");
        }
    }
}

// Configuración del servidor SOAP
$server = new SoapServer(null, ['uri' => "urn:PersonService"]);
$server->setClass('PersonService');
$server->handle();
?>
