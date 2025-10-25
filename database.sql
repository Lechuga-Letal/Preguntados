/*No se olviden de importar todo correctamente!*/

CREATE DATABASE IF NOT EXISTS tppreguntados;
USE TpPreguntados;

DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    mail VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    anio_nacimiento INT NOT NULL,
    sexo ENUM('Masculino', 'Femenino', 'Prefiero no cargarlo') DEFAULT 'Prefiero no cargarlo',
    pais VARCHAR(100) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    rol ENUM('Administrador', 'Editor', 'Jugador') NOT NULL DEFAULT 'Jugador',
    creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios (usuario, mail, password, anio_nacimiento, nombre_completo, pais, rol)
VALUES ('admin', 'admin@preguntados.com', '$2y$10$VUtlqJI6Ycv1f/LCecC1le2CcmHXnJHJalGOH12qhsIZMtC9FL3NK', 2025,'Administrador del Sistema', 'Brasil' , 'Administrador');
