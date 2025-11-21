
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
    rol ENUM('Administrador', 'Editor', 'Jugador') NOT NULL DEFAULT 'Jugador', -- pasarlo a una tabla Roles
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
--                                 acierto BOOLEAN DEFAULT null,
                                PRIMARY KEY (id_turno, id_pregunta),
                                FOREIGN KEY (id_turno) REFERENCES turno(id),
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
nombre VARCHAR(50) NOT NULL
);

INSERT INTO categoria (nombre) VALUES
('Deportes'),
('Entretenimiento'),
('Informática'),
('Matematicas'),
('Historia');

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


INSERT INTO usuarios (usuario, mail, password, nombre_completo, anio_nacimiento, sexo, pais, ciudad, coordenadas, rol)
VALUES
('BotRex','botrex@example.com','123','Bot Rex',1992,'Prefiero no cargarlo','Argentina','CABA','-34.60,-58.38','Jugador'),
('BotLuna','botluna@example.com','123','Bot Luna',1996,'Prefiero no cargarlo','México','CDMX','19.43,-99.13','Jugador');

-- Obtener sus IDs automáticamente (en tu caso ya existen FK)
-- Suponiendo que son los últimos dos creados:
SET @idRex = (SELECT id FROM usuarios WHERE usuario='BotRex');
SET @idLuna = (SELECT id FROM usuarios WHERE usuario='BotLuna');


-- 2) NIVEL JUGADOR GENERAL
INSERT INTO nivelJugadorGeneral (id_usuario, nivel)
VALUES
(@idRex, 0.8),  -- rango PRO
(@idLuna, 0.45); -- rango MEDIO


-- 3) NIVEL POR CATEGORÍA (5 categorías: 1–5)
INSERT INTO nivelJugadorPorCategoria (id_usuario, id_categoria, nivel)
VALUES
(@idRex, 1, 0.7), (@idRex, 2, 0.6), (@idRex, 3, 0.8), (@idRex, 4, 0.9), (@idRex, 5, 0.75),
(@idLuna, 1, 0.3), (@idLuna, 2, 0.4), (@idLuna, 3, 0.5), (@idLuna, 4, 0.45), (@idLuna, 5, 0.4);


-- 4) PARTIDAS FINALIZADAS (ranking usa MAX(puntaje))
INSERT INTO partidas (id_usuario, id_oponente, fecha_inicio, fecha_fin, puntaje, estado)
VALUES
(@idRex, NULL, NOW(), NOW(), 6, 'finalizada'),
(@idRex, NULL, NOW(), NOW(), 11, 'finalizada'), -- mejor puntaje = 11

(@idLuna, NULL, NOW(), NOW(), 6, 'finalizada'),
(@idLuna, NULL, NOW(), NOW(), 7, 'finalizada');