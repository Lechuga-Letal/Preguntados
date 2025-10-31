
CREATE DATABASE IF NOT EXISTS tppreguntados;
USE TpPreguntados;

DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS reporte;
DROP TABLE IF EXISTS pregunta_sugerencia;
DROP TABLE IF EXISTS respuesta_sugerida;
DROP TABLE IF EXISTS respuesta;
DROP TABLE IF EXISTS pregunta;
DROP TABLE IF EXISTS partidas;
DROP TABLE IF EXISTS turno;

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
    coordenadas VARCHAR(100) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    rol ENUM('Administrador', 'Editor', 'Jugador') NOT NULL DEFAULT 'Jugador', -- pasarlo a una tabla Roles
    creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pregunta (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    cant_de_veces_respondidas INT DEFAULT 0,
    cant_de_veces_respondidas_correctamente INT DEFAULT 0,
    id_categoria INT NOT NULL -- pasarlo a una tabla categorias
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

-- Preguntas Sugeridas
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

CREATE TABLE partidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_oponente INT DEFAULT NULL,
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME DEFAULT NULL,
    puntaje INT DEFAULT 0,
    estado ENUM('en curso', 'finalizada', 'cancelada') DEFAULT 'en curso',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_oponente) REFERENCES usuarios(id)
);

CREATE TABLE turno (
                         id INT AUTO_INCREMENT,
                         id_partida INT NOT NULL,
                         id_usuario INT NOT NULL,
                         id_pregunta INT NOT NULL,
                         inicio_turno DATETIME DEFAULT CURRENT_TIMESTAMP, --toma el tiempo al iniciar el turno
                         fin_turno DATETIME DEFAULT NULL, --lo iniciamos en null y se setea al finalizar el turno
                         adivino BOOLEAN DEFAULT NULL,
                         PRIMARY KEY (id,id_partida,id_usuario,id_pregunta),
                         FOREIGN KEY (id_partida) REFERENCES partidas(id) ,
                         FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ,
                         FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta)
);

-- Las contrasenias son 123!
INSERT INTO usuarios (usuario, mail, password, nombre_completo, anio_nacimiento, sexo, pais, ciudad, coordenadas)
VALUES
('MiaG', 'juan@example.com', '$2y$10$gVthlUqs36PVJIYh3XNWyeIE71jyNjkUnVWs1l6PbRZbtU4tbTlz6', 'Juan Pérez', 2001, 'Femenino', 'Argentina', 'Rosario','-34.6037, -58.3816'),
('Jere', 'ana@example.com', '$2y$10$gVthlUqs36PVJIYh3XNWyeIE71jyNjkUnVWs1l6PbRZbtU4tbTlz6', 'Ana López', 1999, 'Masculino', 'Argentina', 'Córdoba','-34.6037, -58.3816');

INSERT INTO usuarios (usuario, mail, password, nombre_completo, anio_nacimiento, sexo, pais, ciudad, coordenadas, foto_perfil)
VALUES
('Joaco pro', 'Xeneixe2015@example.com', '$2y$10$gVthlUqs36PVJIYh3XNWyeIE71jyNjkUnVWs1l6PbRZbtU4tbTlz6', 'Joaquin', 1905, 'Masculino', 'Argentina', 'Buenos Aires','-34.6037, -58.3816', 'public/imagenes/carnet.jpg');
-- admin123
INSERT INTO usuarios (usuario, mail, password, anio_nacimiento, nombre_completo, pais, rol)
VALUES ('admin', 'admin@preguntados.com', '$2y$10$VUtlqJI6Ycv1f/LCecC1le2CcmHXnJHJalGOH12qhsIZMtC9FL3NK', 2025,'Administrador del Sistema', 'Brasil' , 'Administrador');
VALUES ('diego', 'diego@preguntados.com', '$2y$10$VUtlqJI6Ycv1f/LCecC1le2CcmHXnJHJalGOH12qhsIZMtC9FL3NK', 2025,'diego oliva', 'Argentina' , 'Editor');

-- Estos datos son temporales, con el objetivo de ver la funcionalidad de la pagina en todo su esplendor!

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
