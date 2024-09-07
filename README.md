# Sistemas-distribuidos

# Implementacion de un sistema de registro con metodo SOAP y manejo de tokens

Este proyecto implementa un sistema de registro distribuido utilizando el metodo SOAP en php, generando tokens unicos para cada transaccion y manejando errores en tres niveles (conexion, server, base de datos). A continuacion se describe la instalacion, la arquitectura del proyecto y funcionamiento de cada componente.

## Instalacion 

### Requisitos previos
1. **PHP**: Asegurate de tener instalado php. Asegurate que el servicio Apache este corriendo
2. **XAMPP**: Necesario para ejecutar MySQL. Asegurate que el servicio MySQL este corriendo

## Arquitectura del proyecto
El proyecto esta dividido en tres componentes principales:
# 1. index.php styles.css login.php dashboard.php logout.php
Este archivo actua como ntermediario e interfaz html (el formulario que llena el usiario) que conecta con el servidor SOAP.

Generacion de tokens:

Un token unico es generado utilizando los datos del formulario mediante un hash MD5. Esto asegura que cada transaccion sea unica y evita duplicados.

Envio de Datos:

Los datos del formulario y el token generados se envian al servidor mediante una solicitud POST.

Maneja los errores de conexion y un timeout para volver a hacer intento de conexion.

# 2. server.php
Este archivo es el servidor principal que maneja las solicitudes provenientes del cliente.

Valida que todos los campos requeridos esten presentes en la solicitud.
Se conecta al server soap BD donde se manejaran la introduccion y recuperacion de datos.

Si la persona ya existe en la base de datos, devuelve un mensaje informando que el usuario ya esta registrado.

Si el carnet ya fue utilizado, devuelve un mensaje informando que el carnet ya esta usado.

Si la conexion con la base de datos no existe devuelve un mensaje de error de conexion a la base de datos.

Se implementa el manejo de errores en nivel 2

# 3. bd.php
Este archivo es el servidor de base de datos que maneja la insercion y obtencion de datos de la bd.

Maneja errores de nivel 3

Conexion a la base de datos:

Utiliza MySQL para conectarse a la base de datos. los credenciales deben editarse en bd.php

## Creacion de la Base de datos 
Ejecuta las siguientes sentencias AQL para crear la base de datos y la tabla:

CREATE DATABASE person_db;

USE person_db;

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    numero_carnet VARCHAR(15) UNIQUE NOT NULL, -- Campo obligatorio y único
    fecha_nacimiento DATE NOT NULL,
    sexo ENUM('M', 'F') NOT NULL,
    lugar_nacimiento VARCHAR(50) NOT NULL,
    estado_civil ENUM('S', 'C', 'D', 'V') NOT NULL,
    profesion VARCHAR(50) NOT NULL,
    domicilio VARCHAR(100) NOT NULL,
    login VARCHAR(50) UNIQUE NOT NULL, -- Campo obligatorio y único
    password VARCHAR(255) NOT NULL, -- Contraseña encriptada
    token VARCHAR(64) UNIQUE NOT NULL, -- Token único y obligatorio
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Fecha de creación
);

CREATE TABLE cuentas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL,         -- Relacionado al cliente
    tipo_cuenta ENUM('bolivianos', 'dolares') NOT NULL,  -- Tipo de cuenta
    token VARCHAR(255) NOT NULL,        -- Token único para la cuenta
    saldo DECIMAL(10, 2) DEFAULT 0,     -- Saldo de la cuenta, puede empezar en 0
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Fecha de creación
);

CREATE TABLE transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cuenta_id INT NOT NULL,
    tipo_transaccion ENUM('deposito', 'retiro') NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cuenta_id) REFERENCES cuentas(id)
);


### En caso de equivocarse y querer eliminar alguna tabla
USE person_db;

-- Desactivar las restricciones de claves foráneas temporalmente para eliminar las tablas
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar tablas
DROP TABLE IF EXISTS transacciones;
DROP TABLE IF EXISTS cuentas;
DROP TABLE IF EXISTS clientes;

-- Activar nuevamente las restricciones de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;


# Modo de ejecucion:
PC1, PC2, PC3: Si se implementa en diferentes PCs, se debe cambiar la IP del servidor y puerto al cual se conectara cada cliente.php

# Ejecucion del Proyecto

Se debe ejecutar el XAMPP y asegurarse que tengamos activa la extension Soap en nuestro apache. esto se activa en config>php.ini> aqui se debe buscar la linea ;extension=soap  y quitar el ";"

despues debemos asignar la ip a nuestro APACHE que sera donde se dirigira el trafico

config>http.conf  aqui se debe buscar la linea listen e introducimos nuestra ip asignada en la red.
