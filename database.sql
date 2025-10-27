CREATE DATABASE IF NOT EXISTS tppreguntados;
USE tppreguntados;

DROP TABLE IF EXISTS reporte;
DROP TABLE IF EXISTS pregunta_sugerencia; 
DROP TABLE IF EXISTS respuesta_sugerida; 
DROP TABLE IF EXISTS respuesta;
DROP TABLE IF EXISTS pregunta;
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

CREATE TABLE pregunta (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    cant_de_veces_respondidas INT DEFAULT 0,
    cant_de_veces_respondidas_correctamente INT DEFAULT 0,
    id_categoria INT NOT NULL
);

CREATE TABLE respuesta (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    es_correcta BOOLEAN NOT NULL,
    id_pregunta INT NOT NULL,
    FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE
);

CREATE TABLE reporte (
    id_reporte INT AUTO_INCREMENT PRIMARY KEY,
    id_pregunta INT NOT NULL,
    id_usuario INT NOT NULL,
    descripcion TEXT NOT NULL,
    FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE sugerencia (
    id_sugerencia INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL, 
    id_categoria INT NOT NULL,
    id_usuario INT NOT NULL,            
    estado ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente',  -- Vemos si esto es realmente necesario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE respuesta_sugerida (
    id_respuesta_sugerida INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    es_correcta BOOLEAN NOT NULL,
    id_sugerencia INT NOT NULL,
    FOREIGN KEY (id_sugerencia) REFERENCES sugerencia(id_sugerencia) ON DELETE CASCADE
);

-- Aca dejo un par de datos para hacer pruebas!!!

-- Las contrasenias son 123!
INSERT INTO usuarios (usuario, mail, password, nombre_completo, anio_nacimiento, sexo, pais, ciudad)
VALUES
('Joaco pro', 'Xeneixe2015@example.com', '$2y$10$gVthlUqs36PVJIYh3XNWyeIE71jyNjkUnVWs1l6PbRZbtU4tbTlz6', 'Joaquin', 1990, 'Masculino', 'Argentina', 'Buenos Aires'),
('Mia', 'juan@example.com', '$2y$10$gVthlUqs36PVJIYh3XNWyeIE71jyNjkUnVWs1l6PbRZbtU4tbTlz6', 'Juan Pérez', 2001, 'Femenino', 'Argentina', 'Rosario'),
('Jere', 'ana@example.com', '$2y$10$gVthlUqs36PVJIYh3XNWyeIE71jyNjkUnVWs1l6PbRZbtU4tbTlz6', 'Ana López', 1999, 'Masculino', 'Argentina', 'Córdoba');

-- admin123
INSERT INTO usuarios (usuario, mail, password, anio_nacimiento, nombre_completo, pais, rol)
VALUES 
('admin', 'admin@preguntados.com', '$2y$10$VUtlqJI6Ycv1f/LCecC1le2CcmHXnJHJalGOH12qhsIZMtC9FL3NK', 2025,'Administrador del Sistema', 'Brasil' , 'Administrador');

INSERT INTO pregunta (descripcion, id_categoria) VALUES
('¿Cuál es la capital de Francia?', 1),
('¿Cuánto es 5 + 7?', 2),
('¿Quién pintó la Mona Lisa?', 3);

INSERT INTO respuesta (descripcion, es_correcta, id_pregunta) VALUES
('París', 1, 1),
('Londres', 0, 1),
('Madrid', 0, 1),
('Berlín', 0, 1),
('12', 1, 2),
('10', 0, 2),
('11', 0, 2),
('13', 0, 2),
('Leonardo da Vinci', 1, 3),
('Pablo Picasso', 0, 3),
('Vincent van Gogh', 0, 3),
('Claude Monet', 0, 3);

INSERT INTO reporte (id_pregunta, id_usuario, descripcion) VALUES
(2, 2, 'La pregunta es demasiado fácil'),
(2, 2, 'Debería tener más opciones incorrectas');

INSERT INTO sugerencia (descripcion, id_categoria, id_usuario, estado) VALUES
('¿Cuál es el planeta más grande del sistema solar?', 4, 2, 'pendiente'),
('¿En qué año comenzó la Segunda Guerra Mundial?', 5, 3, 'pendiente');

INSERT INTO respuesta_sugerida (descripcion, es_correcta, id_sugerencia) VALUES
('Júpiter', 1, 1),
('Saturno', 0, 1),
('Urano', 0, 1),
('Neptuno', 0, 1),
('1939', 1, 2),
('1945', 0, 2),
('1914', 0, 2),
('1940', 0, 2);
