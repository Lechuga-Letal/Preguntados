
CREATE DATABASE IF NOT EXISTS tppreguntados;
USE tppreguntados;

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
    baneado TINYINT(1) NOT NULL DEFAULT 0,
    baneado_definitivo TINYINT(1) NOT NULL DEFAULT 0,
    rol ENUM('Administrador', 'Editor', 'Jugador') NOT NULL DEFAULT 'Jugador',
    creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pregunta (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    cant_de_veces_respondidas INT DEFAULT 0,
    cant_de_veces_respondidas_correctamente INT DEFAULT 0,
    id_categoria INT NOT NULL, -- pasarlo a una tabla categorias
    dificultad DECIMAL(2,1) NOT NULL DEFAULT 0.5);

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

CREATE TABLE reporteUsuario (
                         id_reporte INT AUTO_INCREMENT PRIMARY KEY,
                         id_usuario INT NOT NULL,
                         id_usuarioReportado INT NOT NULL,
                         descripcion TEXT NOT NULL,
                         FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
                         FOREIGN KEY (id_usuarioReportado) REFERENCES usuarios(id) ON DELETE CASCADE

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
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       id_partida INT NOT NULL,
                       id_usuario INT NOT NULL,
                       id_categoria INT NOT NULL,
                       activo INT DEFAULT 0,
                       aciertos INT DEFAULT 0,
--                        aciertos INT DEFAULT null,
                       FOREIGN KEY (id_partida) REFERENCES partidas(id),
                       FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

CREATE TABLE turno_pregunta (
    id_turno INT NOT NULL,
    id_pregunta INT NOT NULL,
    respondida BOOLEAN DEFAULT FALSE,
    acierto BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id_turno, id_pregunta),

    FOREIGN KEY (id_turno) REFERENCES turno(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta)
        ON DELETE CASCADE
        ON UPDATE CASCADE
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

INSERT INTO pregunta (descripcion, id_categoria) VALUES
('¿Cuántos jugadores tiene un equipo de fútbol en el campo?', 1),
('¿En qué deporte se utiliza una raqueta y una pelota amarilla?', 1),
('¿Qué país ganó el Mundial de Fútbol 2018?', 1),
('¿Cuántos puntos vale un triple en baloncesto?', 1),
('¿Cómo se llama el torneo de tenis jugado en Londres sobre césped?', 1),
('¿Qué selección ganó la Copa América 2021?', 1),
('¿Cuántos anillos olímpicos hay en el logotipo de los Juegos Olímpicos?', 1),
('¿En qué deporte se destaca Michael Phelps?', 1),
('¿Qué país organiza el Tour de Francia?', 1),
('¿Qué deporte practica Lionel Messi?', 1),
('¿En qué deporte se utiliza un bate?', 1),
('¿Cuál es el deporte nacional de Japón?', 1),
('¿Cuánto dura un partido de fútbol (sin contar tiempo extra)?', 1),
('¿Qué deporte se juega en la NBA?', 1),
('¿Qué país ganó más mundiales de fútbol hasta 2022?', 1);

INSERT INTO respuesta (descripcion, es_correcta, id_pregunta) VALUES
('11', 1, 1), ('10', 0, 1), ('9', 0, 1), ('12', 0, 1),
('Tenis', 1, 2), ('Golf', 0, 2), ('Bádminton', 0, 2), ('Críquet', 0, 2),
('Francia', 1, 3), ('Brasil', 0, 3), ('Alemania', 0, 3), ('Argentina', 0, 3),
('3', 1, 4), ('2', 0, 4), ('1', 0, 4), ('4', 0, 4),
('Wimbledon', 1, 5), ('Roland Garros', 0, 5), ('US Open', 0, 5), ('Australian Open', 0, 5),
('Argentina', 1, 6), ('Brasil', 0, 6), ('Chile', 0, 6), ('Uruguay', 0, 6),
('5', 1, 7), ('6', 0, 7), ('4', 0, 7), ('7', 0, 7),
('Natación', 1, 8), ('Atletismo', 0, 8), ('Ciclismo', 0, 8), ('Boxeo', 0, 8),
('Francia', 1, 9), ('Italia', 0, 9), ('España', 0, 9), ('Bélgica', 0, 9),
('Fútbol', 1, 10), ('Baloncesto', 0, 10), ('Tenis', 0, 10), ('Críquet', 0, 10),
('Béisbol', 1, 11), ('Fútbol', 0, 11), ('Tenis', 0, 11), ('Balonmano', 0, 11),
('Sumo', 1, 12), ('Judo', 0, 12), ('Karate', 0, 12), ('Kendo', 0, 12),
('90 minutos', 1, 13), ('60 minutos', 0, 13), ('80 minutos', 0, 13), ('100 minutos', 0, 13),
('Baloncesto', 1, 14), ('Hockey', 0, 14), ('Tenis', 0, 14), ('Rugby', 0, 14),
('Brasil', 1, 15), ('Alemania', 0, 15), ('Italia', 0, 15), ('Argentina', 0, 15);

INSERT INTO pregunta (descripcion, id_categoria) VALUES
('¿Quién interpretó a Harry Potter en las películas?', 2),
('¿Qué superhéroe pertenece a Gotham City?', 2),
('¿En qué serie aparece el personaje Walter White?', 2),
('¿Qué película ganó el Óscar a mejor película en 2020?', 2),
('¿Quién canta la canción "Shape of You"?', 2),
('¿Qué película animada tiene un personaje llamado Buzz Lightyear?', 2),
('¿En qué año se estrenó Titanic?', 2),
('¿Quién es el villano principal de Los Vengadores?', 2),
('¿Qué instrumento toca Lisa Simpson?', 2),
('¿Qué saga tiene como protagonista a Frodo Bolsón?', 2),
('¿Qué película popularizó la frase “Yo soy tu padre”?', 2),
('¿Quién es el director de la película "Inception"?', 2),
('¿Qué princesa de Disney tiene un tigre llamado Rajah?', 2),
('¿Qué cantante es conocido como "El Rey del Pop"?', 2),
('¿Qué serie se desarrolla en la ciudad ficticia de Hawkins?', 2);

INSERT INTO respuesta (descripcion, es_correcta, id_pregunta) VALUES
('Daniel Radcliffe', 1, 16), ('Elijah Wood', 0, 16), ('Rupert Grint', 0, 16), ('Tom Holland', 0, 16),
('Batman', 1, 17), ('Superman', 0, 17), ('Iron Man', 0, 17), ('Spiderman', 0, 17),
('Breaking Bad', 1, 18), ('Better Call Saul', 0, 18), ('Dexter', 0, 18), ('Sons of Anarchy', 0, 18),
('Parásitos', 1, 19), ('Joker', 0, 19), ('1917', 0, 19), ('Green Book', 0, 19),
('Ed Sheeran', 1, 20), ('Shawn Mendes', 0, 20), ('Justin Bieber', 0, 20), ('Bruno Mars', 0, 20),
('Toy Story', 1, 21), ('Shrek', 0, 21), ('Frozen', 0, 21), ('Coco', 0, 21),
('1997', 1, 22), ('1995', 0, 22), ('2000', 0, 22), ('1999', 0, 22),
('Thanos', 1, 23), ('Loki', 0, 23), ('Ultron', 0, 23), ('Red Skull', 0, 23),
('Saxofón', 1, 24), ('Guitarra', 0, 24), ('Violín', 0, 24), ('Piano', 0, 24),
('El Señor de los Anillos', 1, 25), ('Harry Potter', 0, 25), ('Narnia', 0, 25), ('Star Wars', 0, 25),
('Star Wars', 1, 26), ('Matrix', 0, 26), ('Avatar', 0, 26), ('Transformers', 0, 26),
('Christopher Nolan', 1, 27), ('Steven Spielberg', 0, 27), ('James Cameron', 0, 27), ('Ridley Scott', 0, 27),
('Jasmín', 1, 28), ('Ariel', 0, 28), ('Bella', 0, 28), ('Mulan', 0, 28),
('Michael Jackson', 1, 29), ('Prince', 0, 29), ('Elvis Presley', 0, 29), ('Freddie Mercury', 0, 29),
('Stranger Things', 1, 30), ('The Witcher', 0, 30), ('Dark', 0, 30), ('Game of Thrones', 0, 30);

INSERT INTO pregunta (descripcion, id_categoria) VALUES
('¿Qué significa "HTML"?', 3),
('¿Cuál es el sistema operativo de código abierto más popular?', 3),
('¿Qué empresa desarrolló el sistema operativo Windows?', 3),
('¿Qué lenguaje se usa principalmente para el desarrollo web en el lado del cliente?', 3),
('¿Qué significa "CPU"?', 3),
('¿Cuál es la unidad básica de información en informática?', 3),
('¿Qué empresa creó el navegador Chrome?', 3),
('¿Qué extensión tienen los archivos ejecutables en Windows?', 3),
('¿Qué lenguaje se usa en inteligencia artificial y ciencia de datos?', 3),
('¿Qué significa "URL"?', 3),
('¿Cuál es la red social creada por Mark Zuckerberg?', 3),
('¿Qué significa "AI"?', 3),
('¿Cuál de los siguientes es un lenguaje de programación orientado a objetos?', 3),
('¿Qué es un virus informático?', 3),
('¿Qué comando se usa para listar archivos en Linux?', 3);

INSERT INTO respuesta (descripcion, es_correcta, id_pregunta) VALUES
('HyperText Markup Language', 1, 31), ('HighText Machine Language', 0, 31), ('Hyperlink and Text Markup Language', 0, 31), ('Hyper Tool Multi Language', 0, 31),
('Linux', 1, 32), ('Windows', 0, 32), ('macOS', 0, 32), ('Android', 0, 32),
('Microsoft', 1, 33), ('Apple', 0, 33), ('IBM', 0, 33), ('Google', 0, 33),
('JavaScript', 1, 34), ('Python', 0, 34), ('C#', 0, 34), ('PHP', 0, 34),
('Central Processing Unit', 1, 35), ('Computer Personal Unit', 0, 35), ('Central Peripheral Unit', 0, 35), ('Control Processing Unit', 0, 35),
('Bit', 1, 36), ('Byte', 0, 36), ('Pixel', 0, 36), ('Frame', 0, 36),
('Google', 1, 37), ('Apple', 0, 37), ('Microsoft', 0, 37), ('Mozilla', 0, 37),
('.exe', 1, 38), ('.bat', 0, 38), ('.zip', 0, 38), ('.dll', 0, 38),
('Python', 1, 39), ('C++', 0, 39), ('HTML', 0, 39), ('Swift', 0, 39),
('Uniform Resource Locator', 1, 40), ('Universal Resource Link', 0, 40), ('Uniform Reference Link', 0, 40), ('Universal Remote Locator', 0, 40),
('Facebook', 1, 41), ('Twitter', 0, 41), ('Instagram', 0, 41), ('LinkedIn', 0, 41),
('Artificial Intelligence', 1, 42), ('Advanced Integration', 0, 42), ('Auto Input', 0, 42), ('Analog Interface', 0, 42),
('Java', 1, 43), ('HTML', 0, 43), ('CSS', 0, 43), ('SQL', 0, 43),
('Un programa malicioso', 1, 44), ('Un hardware defectuoso', 0, 44), ('Un sistema operativo', 0, 44), ('Un antivirus', 0, 44),
('ls', 1, 45), ('dir', 0, 45), ('show', 0, 45), ('list', 0, 45);

INSERT INTO pregunta (descripcion, id_categoria) VALUES
('¿Cuánto es 9 x 9?', 4),
('¿Cuál es la raíz cuadrada de 64?', 4),
('¿Qué número es el resultado de 15 ÷ 3?', 4),
('¿Cuántos grados tiene un triángulo?', 4),
('¿Qué es el número π (pi)?', 4),
('¿Cuánto es 7²?', 4),
('¿Qué número sigue después del 99?', 4),
('¿Cuántos lados tiene un pentágono?', 4),
('¿Cuál es el doble de 25?', 4),
('¿Qué número romano representa el 10?', 4),
('¿Cuánto es 8 + 5?', 4),
('¿Cuánto es 100 - 75?', 4),
('¿Cuál es la mitad de 60?', 4),
('¿Qué figura tiene 4 lados iguales y 4 ángulos rectos?', 4),
('¿Qué número representa la centena?', 4);

INSERT INTO respuesta (descripcion, es_correcta, id_pregunta) VALUES
('81', 1, 46), ('72', 0, 46), ('99', 0, 46), ('90', 0, 46),
('8', 1, 47), ('6', 0, 47), ('10', 0, 47), ('9', 0, 47),
('5', 1, 48), ('4', 0, 48), ('3', 0, 48), ('6', 0, 48),
('180', 1, 49), ('360', 0, 49), ('90', 0, 49), ('120', 0, 49),
('Una constante matemática', 1, 50), ('Una letra griega sin valor', 0, 50), ('Un número primo', 0, 50), ('Una ecuación', 0, 50),
('49', 1, 51), ('14', 0, 51), ('56', 0, 51), ('64', 0, 51),
('100', 1, 52), ('101', 0, 52), ('98', 0, 52), ('99', 0, 52),
('5', 1, 53), ('6', 0, 53), ('4', 0, 53), ('7', 0, 53),
('50', 1, 54), ('40', 0, 54), ('60', 0, 54), ('45', 0, 54),
('X', 1, 55), ('V', 0, 55), ('L', 0, 55), ('C', 0, 55),
('13', 1, 56), ('12', 0, 56), ('14', 0, 56), ('11', 0, 56),
('25', 1, 57), ('30', 0, 57), ('20', 0, 57), ('15', 0, 57),
('30', 1, 58), ('20', 0, 58), ('40', 0, 58), ('25', 0, 58),
('Cuadrado', 1, 59), ('Rectángulo', 0, 59), ('Triángulo', 0, 59), ('Romboide', 0, 59),
('100', 1, 60), ('10', 0, 60), ('1000', 0, 60), ('1', 0, 60);

INSERT INTO pregunta (descripcion, id_categoria) VALUES
('¿En qué año comenzó la Segunda Guerra Mundial?', 5),
('¿Quién fue el primer presidente de los Estados Unidos?', 5),
('¿Qué civilización construyó las pirámides de Egipto?', 5),
('¿En qué año llegó Cristóbal Colón a América?', 5),
('¿Quién fue el libertador de Argentina, Chile y Perú?', 5),
('¿Qué muro cayó en 1989?', 5),
('¿Dónde se originaron los Juegos Olímpicos?', 5),
('¿Qué país fue el primero en enviar un hombre al espacio?', 5),
('¿Quién pintó la Capilla Sixtina?', 5),
('¿Qué imperio fue gobernado por Julio César?', 5),
('¿Qué acontecimiento inició la Edad Media?', 5),
('¿En qué año terminó la Primera Guerra Mundial?', 5),
('¿Cómo se llamaba el barco de Cristóbal Colón?', 5),
('¿Quién fue el primer hombre en pisar la Luna?', 5),
('¿En qué país se originó la Revolución Industrial?', 5);

INSERT INTO respuesta (descripcion, es_correcta, id_pregunta) VALUES
('1939', 1, 61), ('1941', 0, 61), ('1935', 0, 61), ('1945', 0, 61),
('George Washington', 1, 62), ('Abraham Lincoln', 0, 62), ('Thomas Jefferson', 0, 62), ('John Adams', 0, 62),
('Egipcia', 1, 63), ('Romana', 0, 63), ('Griega', 0, 63), ('Persa', 0, 63),
('1492', 1, 64), ('1500', 0, 64), ('1480', 0, 64), ('1498', 0, 64),
('José de San Martín', 1, 65), ('Simón Bolívar', 0, 65), ('Bernardo O’Higgins', 0, 65), ('Manuel Belgrano', 0, 65),
('Muro de Berlín', 1, 66), ('Muro de China', 0, 66), ('Muro Romano', 0, 66), ('Muro de Jerusalén', 0, 66),
('Grecia', 1, 67), ('Roma', 0, 67), ('Egipto', 0, 67), ('China', 0, 67),
('URSS', 1, 68), ('EE.UU.', 0, 68), ('China', 0, 68), ('Japón', 0, 68),
('Miguel Ángel', 1, 69), ('Leonardo da Vinci', 0, 69), ('Rafael', 0, 69), ('Donatello', 0, 69),
('Imperio Romano', 1, 70), ('Imperio Griego', 0, 70), ('Imperio Persa', 0, 70), ('Imperio Egipcio', 0, 70),
('Caída del Imperio Romano', 1, 71), ('Descubrimiento de América', 0, 71), ('Revolución Francesa', 0, 71), ('Invención de la imprenta', 0, 71),
('1918', 1, 72), ('1914', 0, 72), ('1920', 0, 72), ('1916', 0, 72),
('La Santa María', 1, 73), ('La Pinta', 0, 73), ('La Niña', 0, 73), ('La Victoria', 0, 73),
('Neil Armstrong', 1, 74), ('Buzz Aldrin', 0, 74), ('Yuri Gagarin', 0, 74), ('Michael Collins', 0, 74),
('Inglaterra', 1, 75), ('Francia', 0, 75), ('Alemania', 0, 75), ('Italia', 0, 75);

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

CREATE TABLE categoria (
id_categoria INT AUTO_INCREMENT PRIMARY KEY,
nombre VARCHAR(50) NOT NULL,
foto_categoria VARCHAR(50) NOT NULL,
estado INT NOT NULL
);

INSERT INTO categoria (nombre, foto_categoria, estado) VALUES
('Deportes', 'public/imagenes/DeportesColor.png', 1),
('Entretenimiento', 'public/imagenes/EntretenimientoColor.png', 1),
('Informática', 'public/imagenes/InformaticaColor.png', 1),
('Matematicas', 'public/imagenes/MatematicasColor.png', 1),
('Historia', 'public/imagenes/HistoriaColor.png', 1);

CREATE TABLE preguntasVistas (
id INT AUTO_INCREMENT PRIMARY KEY,
id_usuario INT NOT NULL,
id_pregunta INT NOT NULL,
fecha_vista DATETIME DEFAULT CURRENT_TIMESTAMP,
UNIQUE KEY unique_vista (id_usuario, id_pregunta),
FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE
);

ALTER TABLE pregunta
ADD FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE;

CREATE TABLE nivelJugadorPorCategoria (
                                 id INT AUTO_INCREMENT PRIMARY KEY,
                                 id_usuario INT NOT NULL,
                                 id_categoria INT NOT NULL,
                                 nivel DECIMAL(2,1) NOT NULL DEFAULT 0.5,
                                 UNIQUE KEY unique_vista (id_usuario, id_categoria),
                                 FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
                                 FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE);

CREATE TABLE nivelJugadorGeneral (
                                  id INT AUTO_INCREMENT PRIMARY KEY,
                                  id_usuario INT NOT NULL,
                                  nivel DECIMAL(2,1) NOT NULL DEFAULT 0.5,
                                  UNIQUE KEY unique_vista (id_usuario),
                                  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE);

INSERT INTO nivelJugadorGeneral (id_usuario)
VALUES
    (1),
    (2),
    (3);

INSERT INTO nivelJugadorPorCategoria (id_usuario, id_categoria)
VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5);

-- ============================
-- 1) INSERTAR 30 USUARIOS
-- ============================
INSERT INTO usuarios (usuario, mail, password, nombre_completo, anio_nacimiento, sexo, pais, ciudad, coordenadas, rol)
VALUES
('andres.perez','andres.perez@example.com','123','Andrés Pérez',1990,'Prefiero no cargarlo','Argentina','Rosario','-32.9,-60.6','Jugador'),
('maria.suarez','maria.suarez@example.com','123','María Suárez',1993,'Prefiero no cargarlo','México','CDMX','19.4,-99.1','Jugador'),
('jorge.ibarra','jorge.ibarra@example.com','123','Jorge Ibarra',1988,'Prefiero no cargarlo','Chile','Santiago','-33.4,-70.6','Jugador'),
('valentina.mesa','valentina.mesa@example.com','123','Valentina Mesa',1996,'Prefiero no cargarlo','Colombia','Medellín','6.2,-75.5','Jugador'),
('sebastian.lopez','sebastian.lopez@example.com','123','Sebastián López',1999,'Prefiero no cargarlo','Perú','Lima','-12.0,-77.0','Jugador'),
('claudia.vargas','claudia.vargas@example.com','123','Claudia Vargas',1992,'Prefiero no cargarlo','Uruguay','Montevideo','-34.9,-56.2','Jugador'),
('tomas.garcia','tomas.garcia@example.com','123','Tomás García',1991,'Prefiero no cargarlo','Argentina','Córdoba','-31.4,-64.2','Jugador'),
('romina.cortez','romina.cortez@example.com','123','Romina Cortez',1998,'Prefiero no cargarlo','Chile','Valparaíso','-33.0,-71.6','Jugador'),
('martin.guzman','martin.guzman@example.com','123','Martín Guzmán',1987,'Prefiero no cargarlo','México','Guadalajara','20.7,-103.3','Jugador'),
('carla.mendez','carla.mendez@example.com','123','Carla Méndez',1994,'Prefiero no cargarlo','Colombia','Bogotá','4.6,-74.1','Jugador'),
('fabian.roldan','fabian.roldan@example.com','123','Fabián Roldán',1989,'Prefiero no cargarlo','Argentina','Mendoza','-32.9,-68.8','Jugador'),
('kiara.palma','kiara.palma@example.com','123','Kiara Palma',1997,'Prefiero no cargarlo','Perú','Cusco','-13.5,-71.9','Jugador'),
('pablo.campos','pablo.campos@example.com','123','Pablo Campos',1995,'Prefiero no cargarlo','México','Monterrey','25.7,-100.3','Jugador'),
('luciana.rios','luciana.rios@example.com','123','Luciana Ríos',1991,'Prefiero no cargarlo','Chile','Coquimbo','-29.9,-71.3','Jugador'),
('diego.vera','diego.vera@example.com','123','Diego Vera',1993,'Prefiero no cargarlo','Argentina','La Plata','-34.9,-57.9','Jugador'),
('rocio.alvarez','rocio.alvarez@example.com','123','Rocío Álvarez',1998,'Prefiero no cargarlo','Colombia','Cali','3.4,-76.5','Jugador'),
('damian.flores','damian.flores@example.com','123','Damián Flores',1990,'Prefiero no cargarlo','Uruguay','Punta del Este','-34.9,-54.9','Jugador'),
('sabrina.navarro','sabrina.navarro@example.com','123','Sabrina Navarro',1996,'Prefiero no cargarlo','Argentina','Bahía Blanca','-38.7,-62.3','Jugador'),
('walter.molina','walter.molina@example.com','123','Walter Molina',1986,'Prefiero no cargarlo','México','Puebla','19.0,-98.2','Jugador'),
('veronica.soto','veronica.soto@example.com','123','Verónica Soto',1994,'Prefiero no cargarlo','Chile','Temuco','-38.7,-72.6','Jugador'),
('alan.rojas','alan.rojas@example.com','123','Alan Rojas',1995,'Prefiero no cargarlo','Perú','Arequipa','-16.4,-71.5','Jugador'),
('mariela.cano','mariela.cano@example.com','123','Mariela Cano',1997,'Prefiero no cargarlo','Argentina','Tucumán','-26.8,-65.2','Jugador'),
('ramiro.santos','ramiro.santos@example.com','123','Ramiro Santos',1992,'Prefiero no cargarlo','Uruguay','Salto','-31.4,-57.9','Jugador'),
('julieta.duran','julieta.duran@example.com','123','Julieta Durán',1998,'Prefiero no cargarlo','Colombia','Barranquilla','11.0,-74.8','Jugador'),
('franco.yanez','franco.yanez@example.com','123','Franco Yáñez',1993,'Prefiero no cargarlo','Argentina','San Juan','-31.5,-68.5','Jugador'),
('celeste.martinez','celeste.martinez@example.com','123','Celeste Martínez',1994,'Prefiero no cargarlo','México','Cancún','21.1,-86.8','Jugador'),
('rodrigo.aguirre','rodrigo.aguirre@example.com','123','Rodrigo Aguirre',1990,'Prefiero no cargarlo','Chile','Antofagasta','-23.6,-70.4','Jugador'),
('agustin.barrios','agustin.barrios@example.com','123','Agustín Barrios',1999,'Prefiero no cargarlo','Argentina','Mar del Plata','-38.0,-57.6','Jugador'),
('isabela.reyes','isabela.reyes@example.com','123','Isabela Reyes',1996,'Prefiero no cargarlo','Perú','Trujillo','-8.1,-79.0','Jugador'),
('ricardo.vera','ricardo.vera@example.com','123','Ricardo Vera',1988,'Prefiero no cargarlo','Colombia','Cartagena','10.4,-75.5','Jugador');

-- =====================================
-- 2) INSERTAR NIVELES (A MANO)
-- Pro: 0.70–0.99 / Medio: 0.40–0.69 / Novato: 0.10–0.39
-- =====================================
INSERT INTO nivelJugadorGeneral (id_usuario, nivel) VALUES
(4,0.78),(5,0.44),
(6,0.63),(7,0.29),(8,0.81),(9,0.59),(10,0.37),
(11,0.91),(12,0.48),(13,0.55),(14,0.26),(15,0.73),
(16,0.34),(17,0.67),(18,0.23),(19,0.77),(20,0.42),
(21,0.33),(22,0.85),(23,0.58),(24,0.39),(25,0.72),
(26,0.53),(27,0.44),(28,0.36),(29,0.79),(30,0.62);

-- =====================================
-- 3) PARTIDAS FINALIZADAS (puntajes 0–15)
-- =====================================
INSERT INTO partidas (id_usuario, id_oponente, fecha_inicio, fecha_fin, puntaje, estado) VALUES
(4,NULL,NOW(),NOW(),14,'finalizada'),
(5,NULL,NOW(),NOW(),10,'finalizada'),
(6,NULL,NOW(),NOW(), 8,'finalizada'),
(7,NULL,NOW(),NOW(), 6,'finalizada'),
(8,NULL,NOW(),NOW(),15,'finalizada'),
(9,NULL,NOW(),NOW(), 7,'finalizada'),
(10,NULL,NOW(),NOW(), 5,'finalizada'),
(11,NULL,NOW(),NOW(),13,'finalizada'),
(12,NULL,NOW(),NOW(), 9,'finalizada'),
(13,NULL,NOW(),NOW(), 6,'finalizada'),
(14,NULL,NOW(),NOW(), 4,'finalizada'),
(15,NULL,NOW(),NOW(),15,'finalizada'),
(16,NULL,NOW(),NOW(), 8,'finalizada'),
(17,NULL,NOW(),NOW(),10,'finalizada'),
(18,NULL,NOW(),NOW(), 3,'finalizada'),
(19,NULL,NOW(),NOW(),14,'finalizada'),
(20,NULL,NOW(),NOW(), 7,'finalizada'),
(21,NULL,NOW(),NOW(), 5,'finalizada'),
(22,NULL,NOW(),NOW(),13,'finalizada'),
(23,NULL,NOW(),NOW(), 8,'finalizada'),
(24,NULL,NOW(),NOW(), 4,'finalizada'),
(25,NULL,NOW(),NOW(),12,'finalizada'),
(26,NULL,NOW(),NOW(), 9,'finalizada'),
(27,NULL,NOW(),NOW(), 7,'finalizada'),
(28,NULL,NOW(),NOW(), 3,'finalizada'),
(29,NULL,NOW(),NOW(),15,'finalizada'),
(30,NULL,NOW(),NOW(),10,'finalizada');

INSERT INTO nivelJugadorPorCategoria (id_usuario, id_categoria, nivel) VALUES
(4,1,0.70),(4,2,0.75),(4,3,0.83),(4,4,0.78),(4,5,0.81),
(5,1,0.36),(5,2,0.41),(5,3,0.49),(5,4,0.44),(5,5,0.47),
(6,1,0.55),(6,2,0.60),(6,3,0.68),(6,4,0.63),(6,5,0.66),
(7,1,0.22),(7,2,0.27),(7,3,0.35),(7,4,0.30),(7,5,0.33),
(8,1,0.73),(8,2,0.78),(8,3,0.86),(8,4,0.81),(8,5,0.84),
(9,1,0.51),(9,2,0.56),(9,3,0.64),(9,4,0.59),(9,5,0.62),
(10,1,0.29),(10,2,0.34),(10,3,0.42),(10,4,0.37),(10,5,0.40),
(11,1,0.83),(11,2,0.88),(11,3,0.96),(11,4,0.91),(11,5,0.94),
(12,1,0.40),(12,2,0.45),(12,3,0.53),(12,4,0.48),(12,5,0.51),
(13,1,0.47),(13,2,0.52),(13,3,0.60),(13,4,0.55),(13,5,0.58),
(14,1,0.18),(14,2,0.21),(14,3,0.29),(14,4,0.26),(14,5,0.29),
(15,1,0.65),(15,2,0.70),(15,3,0.78),(15,4,0.73),(15,5,0.76),
(16,1,0.26),(16,2,0.31),(16,3,0.39),(16,4,0.34),(16,5,0.37),
(17,1,0.59),(17,2,0.64),(17,3,0.72),(17,4,0.67),(17,5,0.70),
(18,1,0.15),(18,2,0.18),(18,3,0.26),(18,4,0.23),(18,5,0.26),
(19,1,0.69),(19,2,0.74),(19,3,0.82),(19,4,0.77),(19,5,0.80),
(20,1,0.34),(20,2,0.39),(20,3,0.47),(20,4,0.42),(20,5,0.45),
(21,1,0.25),(21,2,0.30),(21,3,0.38),(21,4,0.33),(21,5,0.36),
(22,1,0.77),(22,2,0.82),(22,3,0.90),(22,4,0.85),(22,5,0.88),
(23,1,0.50),(23,2,0.55),(23,3,0.63),(23,4,0.58),(23,5,0.61),
(24,1,0.31),(24,2,0.36),(24,3,0.44),(24,4,0.39),(24,5,0.42),
(25,1,0.64),(25,2,0.69),(25,3,0.77),(25,4,0.72),(25,5,0.75),
(26,1,0.45),(26,2,0.50),(26,3,0.58),(26,4,0.53),(26,5,0.56),
(27,1,0.36),(27,2,0.41),(27,3,0.49),(27,4,0.44),(27,5,0.47),
(28,1,0.28),(28,2,0.31),(28,3,0.39),(28,4,0.36),(28,5,0.39),
(29,1,0.71),(29,2,0.76),(29,3,0.84),(29,4,0.79),(29,5,0.82),
(30,1,0.54),(30,2,0.59),(30,3,0.67),(30,4,0.62),(30,5,0.65)
ON DUPLICATE KEY UPDATE nivel = VALUES(nivel);