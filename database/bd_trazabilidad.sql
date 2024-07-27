-----BASE DE DATOS
create database trazabilidad;

-----TABLAS
create table area(
	id int unsigned AUTO_INCREMENT not null,
	nombre varchar(50) not null,
	unique(nombre),
	primary key(id)
);

create table proyecto(
	id int unsigned AUTO_INCREMENT not null,
	codigo_proyecto varchar(30) not null,
	empresa varchar(11) not null,
	estatus ENUM("activo", "cancelado", "entregado") default "activo",
	fecha_entrega date not null,
	imagen varchar(50),
	primary key(id),
 	UNIQUE (codigo_proyecto)
);

create table maquina(
	id int unsigned AUTO_INCREMENT not null,
	nombre varchar(30) not null,
	estatus ENUM("activa", "desactiva", "reparacion") default "activa",
	id_area int(11) unsigned not null,
	unique(nombre),
	primary key(id),
 	FOREIGN KEY (id_area) REFERENCES area(id)
);

create table estante(
	id int unsigned AUTO_INCREMENT not null,
	nombre varchar(30) not null,
	id_area int(11) unsigned not null,
	primary key(id),
	FOREIGN KEY(id_area) REFERENCES area(id)
);

create table operador(
	id int unsigned AUTO_INCREMENT not null,
	nombre varchar(30) not null,
	id_area int(11) unsigned not null,
	primary key(id),
 	FOREIGN KEY (id_area) REFERENCES area(id)
);

create table reportes_maquinado(
	id int unsigned AUTO_INCREMENT not null,
	codigo_proyecto varchar(30) not null,
	codigo_partida varchar(30) not null,
	fecha date not null,
	hora time not null,
	turno ENUM("primero", "segundo") not null,
	accion ENUM("entrada", "turno terminado", "pieza terminada") not null,
	estatus ENUM("proceso", "finalizado", "revisar") default "proceso",
	tiempo_total decimal(15,2),
	revision varchar(250),
	id_area int(11) unsigned not null,
	id_maquina int(11) unsigned not null,
	id_operador int(11) unsigned not null,
	primary key(id),
 	FOREIGN KEY (id_area) REFERENCES area(id),
 	FOREIGN KEY (id_maquina) REFERENCES maquina(id),
 	FOREIGN KEY (id_operador) REFERENCES operador(id)
);

create table reportes_estante(
	id int unsigned AUTO_INCREMENT not null,
	codigo_proyecto varchar(30) not null,
	codigo_partida varchar(30) not null,
	fecha date not null,
	hora time not null,
	accion ENUM("entrada", "salida") not null,
	tiempo_total decimal(15,2),
	revision varchar(250),
	estatus ENUM("conforme", "no conforme", "revisar") default "conforme",
	id_estante int(11) unsigned not null,
	primary key(id),
 	FOREIGN KEY (id_estante) REFERENCES estante(id)
);

-----DATOS DE PRUEBA
INSERT INTO proyectos (id, codigo_proyecto, empresa, fecha_entrega, estatus) VALUES
(1, '011-2024','11', '2008-11-11', 'activo'),
(2, '123-0804','12', '2008-11-12', 'activo'),
(3, '185-2380','15', '2008-11-13', 'activo'),
(4, '140-0021','10', '2008-11-14', 'cancelado'),
(5, '020-0136','02', '2008-11-15', 'activo');

INSERT INTO areas (id, nombre) VALUES
(1, 'TORNEADO'),
(2, 'FRESADO'),
(3, 'ACABADO'),
(4, 'RECTIFICADO'),
(5, 'METROLOGIA'),
(6, 'PAILERIA'),
(7, 'ALMACEN');

INSERT INTO operadores (id, nombre, id_area) VALUES
(1, '001-TONO', 2),
(2, '024-JORGE', 2),
(3, '064-BRENDA', 2),
(4, '082-JUAN ALBERTO', 2),
(5, '087-OSCAR M.', 2),
(6, '096-OCTAVIO', 2),
(7, '113-ALEXIS', 2),
(8, '127-YAZMIN', 2),
(9, '128-VICTOR HUGO', 2),
(10, '133 JONATHAN MARTINEZ', 2),
(11, '135-ERIK ZEPEDA', 2),
(12, '138-URIEL', 2),
(13, '139-ELIEL', 2),
(14, 'DON MEMO', 2),
(15, '005-GUILLERMO', 1),
(16, '015-ALFREDO ROMERO', 1),
(17, '023-FELIPE REYES', 1),
(18, '118-JOSUE', 1),
(19, '124-JONATHAN', 1),
(20, 'DON JESUS', 4),
(21, 'ABI', 5),
(22, '011-ALFREDO SERRANO', 6),
(23, 'AGUSTIN SALAMANCA', 6);

INSERT INTO maquinas (id, nombre, estatus, id_area) VALUES
(1, '01-MILTRONICS', 'activa', 1),
(2, '02-T-COLCHESTER', 'activa', 1),
(3, '03-NLX', 'activa', 1),
(4, '04-CTX', 'activa', 1),
(5, '05-T-TOS-SN50', 'activa', 1),
(6, '36-MINI-TORNO', 'activa', 1),
(7, '37-BX1000', 'activa', 1),
(8, '39-FEELER', 'activa', 1),
(9, '06-HILO', 'activa', 2),
(10, '07-PENETRACION', 'activa', 2),
(11, '09-MAZAK', 'activa', 2),
(12, '11-DMU', 'activa', 2),
(13, '17-F-TOS', 'activa', 2),
(14, '34-OKUMA', 'activa', 2),
(15, '35-MAZAK 2', 'activa', 2),
(16, '40-HERMLE', 'activa', 2),
(17, 'TOS kURIM', 'activa', 2),
(18, '10-MARCADOR LASER', 'activa', 3),
(19, '60-BANCO DE TRABAJO ACABADO', 'activa', 3),
(20, '12-PLANA', 'activa', 4),
(21, '13-CILINDRICA', 'activa', 4),
(22, '14-CMM', 'activa', 5),
(23, '50-CELDA-1-AJS', 'activa', 6),
(24, '51-CELDA-2-MAM', 'activa', 6),
(25, '52-CELDA-3-ASP', 'activa', 6),
(26, '53-CELDA-PAILERIA', 'activa', 6);

INSERT INTO estantes (id, nombre, id_area) VALUES
(1, 'Estante TORNEADO', 1),
(2, 'Estante FRESADO', 2),
(3, 'Estante METROLOGIA', 5),
(4, 'Estante ALMACEN', 7);

-----PROCEDIMIENTOS ALMACENADOS
-----ESTANTE
DELIMITER $$

CREATE PROCEDURE `insertarRegistroEstante`(
    IN `p_codigo_proyecto` VARCHAR(30), 
    IN `p_codigo_partida` VARCHAR(30), 
    IN `p_accion` ENUM('entrada','salida'), 
    IN `p_estatus` ENUM('conforme','revisar'), 
    IN `p_id_estante` INT
)
BEGIN
    DECLARE v_fecha DATE;
    DECLARE v_hora TIME;
    DECLARE v_tiempo_total DECIMAL(5,2);
    DECLARE v_entrada_fecha DATE;
    DECLARE v_entrada_hora TIME;
    DECLARE v_entrada_datetime DATETIME;
    DECLARE v_salida_datetime DATETIME;

    -- Configurar la zona horaria de la sesión
    SET @@session.time_zone = '-06:00';

    -- Obtener la fecha y hora actual en la zona horaria configurada
    SET v_fecha = CURDATE();
    SET v_hora = CURTIME();

    IF p_accion = 'salida' THEN
        -- Buscar el registro de entrada correspondiente
        SELECT fecha, hora INTO v_entrada_fecha, v_entrada_hora
        FROM reportes_estante
        WHERE codigo_proyecto = p_codigo_proyecto
          AND codigo_partida = p_codigo_partida
          AND id_estante = p_id_estante
          AND accion = 'entrada'
        ORDER BY fecha DESC, hora DESC
        LIMIT 1;

        -- Calcular el tiempo total en minutos
        SET v_entrada_datetime = CONCAT(v_entrada_fecha, ' ', v_entrada_hora);
        SET v_salida_datetime = CONCAT(v_fecha, ' ', v_hora);
        SET v_tiempo_total = TIMESTAMPDIFF(MINUTE, v_entrada_datetime, v_salida_datetime);
    ELSE
        SET v_tiempo_total = NULL;
    END IF;

    -- Insertar el nuevo registro
    INSERT INTO reportes_estante (codigo_proyecto, codigo_partida, fecha, hora, accion, id_estante, tiempo_total, estatus)
    VALUES (p_codigo_proyecto, p_codigo_partida, v_fecha, v_hora, p_accion, p_id_estante, v_tiempo_total, p_estatus);
END$$

DELIMITER ;

-----MAQUINADO
DELIMITER $$
CREATE PROCEDURE `insertarRegistroMaquinado`(
	IN `p_codigo_proyecto` VARCHAR(30),
	IN `p_codigo_partida` VARCHAR(30), 
	IN `p_turno` ENUM('primero','segundo'), 
	IN `p_accion` ENUM('entrada','turno terminado','pieza terminada'), 
	IN `p_estatus` ENUM('proceso','finalizado','revisar'), 
	IN `p_id_area` INT, 
	IN `p_id_maquina` INT, 
	IN `p_id_operador` INT)
BEGIN
    DECLARE v_fecha DATE;
    DECLARE v_hora TIME;
    DECLARE v_tiempo_total DECIMAL(10,2);
    DECLARE v_entrada_fecha DATE;
    DECLARE v_entrada_hora TIME;
    DECLARE v_entrada_datetime DATETIME;
    DECLARE v_salida_datetime DATETIME;
	
	-- Configurar la zona horaria de la sesión
    SET @@session.time_zone = '-06:00';
    
    -- Obtener la fecha y hora actual en la zona horaria configurada
    SET v_fecha = CURDATE();
    SET v_hora = CURTIME();

    IF p_accion IN ('turno terminado', 'pieza terminada') THEN
        -- Buscar el registro de entrada correspondiente
        SELECT fecha, hora INTO v_entrada_fecha, v_entrada_hora
        FROM reportes_maquinado
        WHERE codigo_proyecto = p_codigo_proyecto
          AND codigo_partida = p_codigo_partida
          AND id_maquina = p_id_maquina
          AND id_operador = p_id_operador
          AND accion = 'entrada'
        ORDER BY fecha DESC, hora DESC
        LIMIT 1;

        -- Calcular el tiempo total en minutos
        SET v_entrada_datetime = CONCAT(v_entrada_fecha, ' ', v_entrada_hora);
        SET v_salida_datetime = CONCAT(v_fecha, ' ', v_hora);
        SET v_tiempo_total = TIMESTAMPDIFF(MINUTE, v_entrada_datetime, v_salida_datetime);
    ELSE
        SET v_tiempo_total = NULL;
    END IF;

    -- Insertar el nuevo registro
    INSERT INTO reportes_maquinado (codigo_proyecto, codigo_partida, fecha, hora, turno, accion, estatus, id_area, id_maquina, id_operador, tiempo_total)
    VALUES (p_codigo_proyecto, p_codigo_partida, v_fecha, v_hora, p_turno, p_accion, p_estatus, p_id_area, p_id_maquina, p_id_operador, v_tiempo_total);
END$$
DELIMITER ;