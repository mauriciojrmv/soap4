# Sistemas-distribuidos

# Implementacion de un sistema de registro con metodo SOAP y manejo de tokens

Este proyecto implementa un sistema de registro distribuido utilizando el metodo SOAP en php, generando tokens unicos para cada transaccion y manejando errores en tres niveles (conexion, server, base de datos). A continuacion se describe la instalacion, la arquitectura del proyecto y funcionamiento de cada componente.

## Instalacion 

### Requisitos previos
1. **PHP**: Asegurate de tener instalado php. Asegurate que el servicio Apache este corriendo
2. **XAMPP**: Necesario para ejecutar MySQL. Asegurate que el servicio MySQL este corriendo

## Arquitectura del proyecto
El proyecto esta dividido en tres componentes principales:

# 1. index.php styles.css
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

CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellido_paterno VARCHAR(255) NOT NULL,
    apellido_materno VARCHAR(255) NOT NULL,
    numero_carnet VARCHAR(255) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    sexo CHAR(1) NOT NULL,
    lugar_nacimiento VARCHAR(255) NOT NULL,
    estado_civil CHAR(1) NOT NULL,
    profesion VARCHAR(255) NOT NULL,
    domicilio VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


# Modo de ejecucion:
PC1, PC2, PC3: Si se implementa en diferentes PCs, se debe cambiar la IP del servidor y puerto al cual se conectara cada cliente.php

# Ejecucion del Proyecto

Se debe ejecutar el XAMPP y asegurarse que tengamos activa la extension Soap en nuestro apache. esto se activa en config>php.ini> aqui se debe buscar la linea ;extension=soap  y quitar el ";"

despues debemos asignar la ip a nuestro APACHE que sera donde se dirigira el trafico

config>http.conf  aqui se debe buscar la linea listen e introducimos nuestra ip asignada en la red.
