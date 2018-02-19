-- MySQL dump 10.13  Distrib 5.7.21, for Linux (x86_64)
--
-- Host: 192.168.10.10    Database: debondi
-- ------------------------------------------------------
-- Server version	5.7.20-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping routines for database 'debondi'
--
/*!50003 DROP FUNCTION IF EXISTS `distanceBetweenPoints` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`homestead`@`%` FUNCTION `distanceBetweenPoints`(`@oLat` DECIMAL(10,7), `@oLon` DECIMAL(10,7), `@dLat` DECIMAL(10,7), `@dLon` DECIMAL(10,7)) RETURNS decimal(10,7)
    NO SQL
BEGIN
	RETURN (
		(
			ACOS(
				SIN(`@dLat` * PI() / 180) *
				SIN(`@oLat` * PI() / 180) +
				COS(`@dLat` * PI() / 180) *
				COS(`@oLat` * PI() / 180) *
				COS((`@dLon` - `@oLon`) * PI() / 180)
			) *
			180 / PI()
		) *
		60 *
		1.1515 *
		1.609344
	);
    END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_near_stops_sp` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`homestead`@`%` PROCEDURE `get_near_stops_sp`(
    IN p_latitude     FLOAT, 
    IN p_longitude    FLOAT,
    IN p_radio        FLOAT)
BEGIN
    DECLARE v_continue INT;

    DECLARE v_exist INT;

    DECLARE v_id_stop   INT;
    DECLARE v_id_line   VARCHAR(100);
    DECLARE v_order     VARCHAR(100);
    DECLARE v_distance  VARCHAR(400);

    DECLARE v_id_line_aux INT;
    DECLARE v_order_aux   INT;
    DECLARE v_distance_aux DECIMAL(6,2);


    DECLARE stopsNearly CURSOR FOR 
            SELECT  bs.id, 
                    GROUP_CONCAT(bl.id SEPARATOR ',') as id_line,  
                    GROUP_CONCAT(blbs.order SEPARATOR ',') as stop_order,
                    GROUP_CONCAT(
                        distanceBetweenPoints(bs.latitude, bs.longitude, p_latitude, p_longitude)
                        SEPARATOR ',')  as distance

            FROM debondi.bus_stops bs 
                    INNER JOIN bus_lines_bus_stops blbs ON bs.id = blbs.id_bus_stop AND blbs.enabled = 1
                    INNER JOIN bus_lines bl ON bl.id = blbs.id_bus_line
            WHERE bs.enabled = 1 AND distanceBetweenPoints(bs.latitude, bs.longitude, p_latitude, p_longitude) <= p_radio
            GROUP BY bs.id
            ORDER BY distanceBetweenPoints(bs.latitude, bs.longitude, p_latitude, p_longitude) ASC;


    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_continue = 1;
    
    DROP  TABLE IF EXISTS temporary_table;
    CREATE  TABLE IF NOT EXISTS temporary_table (id_stop int, id_line int, stop_order int, distance decimal(6,2));

    
    SET v_continue = 0;

    OPEN stopsNearly;
    WHILE v_continue = 0 DO
        FETCH stopsNearly INTO v_id_stop, v_id_line, v_order, v_distance;
        IF v_continue = 0 THEN

            -- Iteramos sobre la columna 'id_line' y 'order' mientras exista mas de un registro por columna
            -- separados por comma ','
            WHILE (LOCATE(',', v_id_line) > 0) DO

                -- Tomamos el primer 'id_line' y 'order' que aparecen en la columnas
                SET v_id_line_aux = CONVERT(SUBSTRING(v_id_line FROM 1 FOR LOCATE(',',v_id_line)-1), SIGNED INT);
                SET v_order_aux   = CONVERT(SUBSTRING(v_order FROM 1 FOR LOCATE(',',v_order)-1), SIGNED INT);
                SET v_distance_aux= CAST(SUBSTRING(v_distance FROM 1 FOR LOCATE(',',v_distance)-1) as decimal(6,2));

                -- Modificamos las columnas eliminando las varibales que ya tomamos
                SET v_id_line   = SUBSTRING(v_id_line, LOCATE(',',v_id_line) + 1);
                SET v_order     = SUBSTRING(v_order, LOCATE(',',v_order) + 1);
                SET v_distance  = SUBSTRING(v_distance, LOCATE(',',v_distance) + 1);

                -- Lo isertamos en la tabla temporal
                INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                       VALUES(v_id_stop, v_id_line_aux, v_order_aux, v_distance_aux);
            END WHILE;

            -- Ya solo queda el ultimo registro en la columna 'id_line' y 'order' por lo que debemos hacer
            -- las operaciones de comparacion para determinar si se deben insertar en la tabla auxiliar o no.            
            SET v_id_line_aux = CONVERT(v_id_line, SIGNED INT);
            SET v_order_aux   = CONVERT(v_order, SIGNED INT);
            SET v_distance_aux= CAST(v_distance as decimal(6,2));

            INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                VALUES(v_id_stop, v_id_line_aux, v_order_aux, v_distance_aux);

        END IF;  
    END WHILE;
    CLOSE stopsNearly;
  


--     SELECT * FROM temporary_table;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_route_sp` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`homestead`@`%` PROCEDURE `get_route_sp`(
    IN latFrom FLOAT, 
    IN lngFrom FLOAT, 
    IN latTo FLOAT, 
    IN lngTo FLOAT, 
    IN distance VARCHAR(20))
BEGIN
    DECLARE b INT;
    DECLARE c INT;

    DECLARE v_exist INT;

    DECLARE v_id_stop   INT;   
    DECLARE v_id_line   VARCHAR(100);
    DECLARE v_order     VARCHAR(100);
    DECLARE v_distance  VARCHAR(400);

    DECLARE v_id_line_aux INT;
    DECLARE v_order_aux   INT;
    DECLARE v_distance_aux DECIMAL(6,2);
    DECLARE v_orderaaaa   INT;


    DECLARE v_id_stop_destiny   INT;
    DECLARE v_id_line_destiny   VARCHAR(200);
    DECLARE v_order_destiny     VARCHAR(200);
    DECLARE v_distance_destiny  VARCHAR(400);

    DECLARE v_id_line_destiny_aux INT;
    DECLARE v_order_destiny_aux   INT;
    DECLARE v_distance_destiny_aux DECIMAL(6,2);


    DECLARE stopsNearOrigin CURSOR FOR 
            SELECT  bs.id, 
                    GROUP_CONCAT(bl.id SEPARATOR ',') as id_line,  
                    GROUP_CONCAT(blbs.order SEPARATOR ',') as stop_order,
                    GROUP_CONCAT(
                        distanceBetweenPoints(bs.latitude, bs.longitude, latFrom, lngFrom)
                        SEPARATOR ',')  as distance

            FROM debondi.bus_stops bs 
                    INNER JOIN bus_lines_bus_stops blbs ON bs.id = blbs.id_bus_stop AND blbs.enabled = 1
                    INNER JOIN bus_lines bl ON bl.id = blbs.id_bus_line
            WHERE bs.enabled = 1 AND distanceBetweenPoints(bs.latitude, bs.longitude, latFrom, lngFrom) <= distance
            GROUP BY bs.id
            ORDER BY distanceBetweenPoints(bs.latitude, bs.longitude, latFrom, lngFrom) ASC;


    DECLARE CONTINUE HANDLER FOR NOT FOUND SET b = 1;
--     DECLARE CONTINUE HANDLER FOR NOT FOUND SET c = 1;
    
    DROP TEMPORARY TABLE IF EXISTS temporary_table;
    CREATE TEMPORARY TABLE IF NOT EXISTS temporary_table (id_stop int, id_line int, stop_order int, distance decimal(6,2));

    
    SET b = 0;

    OPEN stopsNearOrigin;
    WHILE b = 0 DO
        FETCH stopsNearOrigin INTO v_id_stop, v_id_line, v_order, v_distance;
        IF b = 0 THEN


            -- Iteramos sobre la columna 'id_line' y 'order' mientras exista mas de un registro por columna
            -- separados por comma ','
            WHILE (LOCATE(',', v_id_line) > 0) DO

                -- Tomamos el primer 'id_line' y 'order' que aparecen en la columnas
                SET v_id_line_aux = CONVERT(SUBSTRING(v_id_line FROM 1 FOR LOCATE(',',v_id_line)-1), SIGNED INT);
                SET v_order_aux   = CONVERT(SUBSTRING(v_order FROM 1 FOR LOCATE(',',v_order)-1), SIGNED INT);
                SET v_distance_aux= CAST(SUBSTRING(v_distance FROM 1 FOR LOCATE(',',v_distance)-1) as decimal(6,2));

                -- Modificamos las columnas eliminando las varibales que ya tomamos
                SET v_id_line   = SUBSTRING(v_id_line, LOCATE(',',v_id_line) + 1);
                SET v_order     = SUBSTRING(v_order, LOCATE(',',v_order) + 1);
                SET v_distance  = SUBSTRING(v_distance, LOCATE(',',v_distance) + 1);
                
                -- chequeamos si existe un registro en la tabla temporal con el id_line seleccionado.
                SET v_exist = (SELECT EXISTS(SELECT 1 FROM temporary_table WHERE id_line = v_id_line_aux));

                -- Si no existe lo insertamos en la tabla temporal
                IF v_exist = 0 THEN
                    INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                       VALUES(v_id_stop, v_id_line_aux, v_order_aux, v_distance_aux);

                -- Si existe tenemos que verificar que el 'order' del registro sea menor al 'order' actual,
                -- caso contrario debemos elimianr el registro e insertar el actual.
                ELSE
                    SET v_orderaaaa = (SELECT stop_order FROM temporary_table WHERE id_line = v_id_line_aux);
                    IF (v_order_aux + 500) < v_orderaaaa THEN
                        DELETE FROM temporary_table WHERE id_line = v_id_line_aux;
                        INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                        VALUES(v_id_stop, v_id_line_aux, v_order_aux, v_distance_aux);
                    END IF;
                END IF;

            END WHILE;

            -- Ya solo queda el ultimo registro en la columna 'id_line' y 'order' por lo que debemos hacer
            -- las operaciones de comparacion para determinar si se deben insertar en la tabla auxiliar o no.            
            SET v_exist = (SELECT EXISTS(SELECT 1 FROM temporary_table WHERE id_line = v_id_line));
            -- SET v_order  = SUBSTRING(v_order, LOCATE(',',v_order) + 1);

            SET v_id_line_aux = CONVERT(v_id_line, SIGNED INT);
            SET v_order_aux   = CONVERT(v_order, SIGNED INT);
            SET v_distance_aux= CAST(v_distance as decimal(6,2));

            IF v_exist = 0 THEN
                INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                    VALUES(v_id_stop, v_id_line, v_order_aux, v_distance_aux);
            ELSE
                    SET v_orderaaaa = (SELECT stop_order FROM temporary_table WHERE id_line = v_id_line_aux);
                    IF (v_order_aux + 500) < v_orderaaaa THEN
                        DELETE FROM temporary_table WHERE  id_line = v_id_line_aux;
                        INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                       VALUES(v_id_stop, v_id_line_aux, v_order_aux, v_distance_aux);
                    END IF;
            END IF;
        END IF;  
    END WHILE;
    CLOSE stopsNearOrigin;
    -- Hasta acá obtuvimos una tabla temporal con todas las paradas que estan cercanas al origen
    -- y tienen menor 'order', la tabla es tal como la siguiente:    
    -- id_stop,  id_line,    stop_order,     distance
    -- '281',     '2',        '2400',         '94.08'
    -- '281',     '3',        '2500',         '94.08'
    -- '281',     '4',        '3500',         '94.08'
    -- '504',     '9',        '1900',         '163.12'



    SELECT * FROM temporary_table;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_stops_sp` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`homestead`@`%` PROCEDURE `get_stops_sp`(
    IN p_latFrom    FLOAT, 
    IN p_lngFrom    FLOAT, 
    IN p_latTo      FLOAT, 
    IN p_lngTo      FLOAT, 
    IN p_distance   DECIMAL(6,2))
BEGIN
    -- Variables de control
    DECLARE v_exist     INT;
    DECLARE v_continue  INT;
    DECLARE v_replace   INT;
    DECLARE v_continue_destiny  INT;

    --
    DECLARE v_id_stop_origin   INT;
    DECLARE v_id_line_origin   INT;
    DECLARE v_order_origin     FLOAT;
    DECLARE v_distance_origin  DECIMAL(6,2);

    --
    DECLARE v_id_stop_destiny   INT;
    DECLARE v_id_line_destiny   INT;
    DECLARE v_order_destiny     FLOAT;
    DECLARE v_distance_destiny  DECIMAL(6,2);

    -- Variable auxiliares necesarias
    DECLARE v_distance_remaining DECIMAL(6,2);
    DECLARE v_order_aux          DECIMAL(6,2);


    -- Cursores necesarios
    DECLARE stopsNearOrigin CURSOR FOR 
        SELECT * FROM temporary_table;

    DECLARE stopsNearDestiny CURSOR FOR 
--         SELECT * FROM temporary_table where id_line = v_id_line_destiny and stop_order > v_order_destiny;
        SELECT * FROM temporary_table;

    DECLARE stopsNearlyOrigin CURSOR FOR
        SELECT * FROM temporary_parcial_table;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_continue = 1;
--     DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_continue_destiny = 1;


    DROP TEMPORARY TABLE IF EXISTS temporary_parcial_table;
    CREATE TEMPORARY TABLE IF NOT EXISTS temporary_parcial_table (id_stop int, id_line int, stop_order int, distance decimal(6,2));

    DROP  TABLE IF EXISTS temporary_final_table;
    CREATE  TABLE IF NOT EXISTS temporary_final_table (
        id_stop_origin int, id_line_origin int, stop_order_origin int, distance_origin decimal(6,2),
        id_stop_destiny int, id_line_destiny int, stop_order_destiny int, distance_destiny decimal(6,2)
    );


    CALL get_near_stops_sp(p_latFrom, p_lngFrom, p_distance);

    SET v_continue = 0;
    OPEN stopsNearOrigin;
    WHILE v_continue = 0 DO
        FETCH stopsNearOrigin INTO v_id_stop_origin, v_id_line_origin, v_order_origin, v_distance_origin;

        SET v_replace = 0;
        -- chequeamos si existe un registro en la tabla temporal con el id_line seleccionado.
        SET v_exist = (SELECT EXISTS(SELECT 1 FROM temporary_parcial_table WHERE id_line = v_id_line_origin));

        -- Si no existe lo marcamos para insertar en la tabla temporal
        IF v_exist = 0 THEN
            SET v_replace = 1;

        -- Si existe tenemos que verificar que el 'order' del registro sea menor al 'order' actual,
        -- caso contrario debemos marcarlo para insertar el actual.
        ELSE
            SET v_order_aux = (SELECT stop_order FROM temporary_parcial_table WHERE id_line = v_id_line_origin);
            IF (v_order_origin + 500)  < v_order_aux THEN
                SET v_replace = 1;
            END IF;
        END IF;

        IF v_replace = 1 THEN
            DELETE FROM temporary_parcial_table WHERE id_line = v_id_line_origin;
            INSERT INTO temporary_parcial_table(id_stop, id_line, stop_order, distance)
                VALUES(v_id_stop_origin, v_id_line_origin, v_order_origin, v_distance_origin);
        END IF;
            

    END WHILE;
    CLOSE stopsNearOrigin;
    -- Hasta acá obtuvimos una tabla temporal con todas las paradas que estan cercanas al origen
    -- y tienen menor 'order', la tabla es tal como la siguiente:    
    -- id_stop,  id_line,    stop_order,     distance
    -- '281',     '2',        '2400',         '94.08'
    -- '281',     '3',        '2500',         '94.08'
    -- '281',     '4',        '3500',         '94.08'
    -- '504',     '9',        '1900',         '163.12'

    

    SET v_continue = 0;
    OPEN stopsNearlyOrigin;
    get_origin: LOOP
        FETCH stopsNearlyOrigin INTO v_id_stop_origin, v_id_line_origin, v_order_origin, v_distance_origin;
        
        IF v_continue = 1 THEN
            CLOSE stopsNearlyOrigin;
            LEAVE get_origin;
        END IF; 
     

        SET v_distance_remaining = p_distance - v_distance_origin;
        CALL get_near_stops_sp(p_latTo, p_lngTo, v_distance_remaining);

        OPEN stopsNearDestiny;
        get_destiny: LOOP
            FETCH stopsNearDestiny INTO v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny;

            IF v_continue = 1 THEN
                SET v_continue = 0;
                CLOSE stopsNearDestiny;
                LEAVE get_destiny;
            END IF; 

            SET v_exist = (SELECT EXISTS(
                                SELECT 1 FROM temporary_final_table 
                                WHERE 
                                        id_line_origin  = v_id_line_origin
                                    AND id_line_destiny = v_id_line_destiny
                                )
                );

            -- Si no existe lo isertamos en la tabla temporal
            IF v_exist = 0 THEN
                INSERT INTO temporary_final_table(
                    id_stop_origin,  id_line_origin,  stop_order_origin,  distance_origin,
                    id_stop_destiny, id_line_destiny, stop_order_destiny, distance_destiny
                )VALUES(
                    v_id_stop_origin,  v_id_line_origin,  v_order_origin,  v_distance_origin,
                    v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny);
            -- Si existe tenemos que verificar que el 'order' del registro sea menor al 'order' actual,
            -- caso contrario debemos elimianr el registro e insertar el actual.
            ELSE
                SET v_order_aux = (
                                    SELECT  stop_order_destiny 
                                    FROM    temporary_final_table 
                                    WHERE   id_line_origin  = v_id_line_origin
                                        AND id_line_destiny = v_id_line_destiny
                                );
                IF (v_order_destiny + 500) < v_order_aux THEN
                    DELETE FROM temporary_final_table 
                    WHERE   id_line_origin  = v_id_line_origin
                        AND id_line_destiny = v_id_line_destiny;

                    INSERT INTO temporary_final_table(
                        id_stop_origin,  id_line_origin,  stop_order_origin,  distance_origin,
                        id_stop_destiny, id_line_destiny, stop_order_destiny, distance_destiny
                    )VALUES(
                        v_id_stop_origin,  v_id_line_origin,  v_order_origin,  v_distance_origin,
                        v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny);
                END IF;
            END IF;
        END LOOP get_destiny;

    END LOOP get_origin;
    
    SELECT * FROM temporary_final_table;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_get_nearly_stops_origin_destiny` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`homestead`@`%` PROCEDURE `sp_get_nearly_stops_origin_destiny`(
                IN latFrom FLOAT, 
                IN lngFrom FLOAT, 
                IN latTo FLOAT, 
                IN lngTo FLOAT, 
                IN distance VARCHAR(20)
            )
BEGIN

                DECLARE v_id_stop   INT;   
                DECLARE v_id_line   INT;
                DECLARE v_order     INT;
                DECLARE v_distance  DECIMAL(8,2);

                DECLARE v_id_stop_destiny   INT;
                DECLARE v_id_line_destiny   INT;
                DECLARE v_order_destiny     INT;
                DECLARE v_distance_destiny  DECIMAL(8,2);

                DECLARE b INT;
                DECLARE v_orderaaaa   INT;
                DECLARE v_exist INT;

                DECLARE stopsNearDestiny CURSOR FOR SELECT * FROM tabla_destino;
                DECLARE stopsNearOrigin CURSOR FOR SELECT * FROM tabla_origen;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET b = 1;

                DROP TEMPORARY TABLE IF EXISTS temporary_final_table;
                CREATE TEMPORARY TABLE IF NOT EXISTS temporary_final_table (
                    id_stop_origin  int, id_line_origin  int,          stop_order_origin  int,          distance_origin  decimal(6,2),
                    id_stop_destiny int, id_line_destiny varchar(200), stop_order_destiny varchar(200), distance_destiny varchar(400));



                CALL sp_get_nearly_stops_point(latFrom, lngFrom, distance, 'tabla_origen');

                SET b = 0;

                OPEN stopsNearOrigin;
                    WHILE b = 0 DO
                        FETCH stopsNearOrigin INTO v_id_stop, v_id_line, v_order, v_distance;
                        IF b = 0 THEN 

                            CALL sp_get_nearly_stops_point(latTo, lngTo, distance-(v_distance/100), 'tabla_destino');
                            OPEN stopsNearDestiny;
                                read_loop: LOOP

                                    FETCH stopsNearDestiny INTO v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny;

                                    IF b THEN
                                        SET b = 0;
                                        LEAVE read_loop;
                                    END IF;

                                    SET v_exist = (
                                        SELECT EXISTS (
                                            SELECT 1 FROM temporary_final_table 
                                            WHERE 
                                                id_line_origin = v_id_line
                                                AND id_line_destiny = v_id_line_destiny
                                        )
                                    );

                                    -- Si no existe lo isertamos en la tabla temporal
                                    IF v_exist = 0 THEN
                                        INSERT INTO temporary_final_table(
                                                id_stop_origin, id_line_origin, stop_order_origin, distance_origin,
                                                id_stop_destiny, id_line_destiny, stop_order_destiny, distance_destiny
                                        )VALUES(
                                                v_id_stop, v_id_line, v_order, v_distance,
                                                v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny);

                                    -- Si existe tenemos que verificar que el 'order' del registro sea menor al 'order' actual,
                                    -- caso contrario debemos elimianr el registro e insertar el actual.
                                    ELSE
                                        SET v_orderaaaa = (
                                                            SELECT  stop_order_destiny 
                                                            FROM    temporary_final_table 
                                                            WHERE   id_line_origin  = v_id_line
                                                                AND id_line_destiny = v_id_line_destiny
                                                        );
                                        IF (v_order_destiny + 500) < v_orderaaaa THEN
                                            DELETE FROM temporary_final_table 
                                            WHERE   id_line_origin  = v_id_line
                                                AND id_line_destiny = v_id_line_destiny;

                                            INSERT INTO temporary_final_table(
                                                id_stop_origin, id_line_origin, stop_order_origin, distance_origin,
                                                id_stop_destiny, id_line_destiny, stop_order_destiny, distance_destiny
                                            )VALUES(
                                                v_id_stop, v_id_line, v_order, v_distance,
                                                v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny);
                                        END IF;
                                    END IF;
                                END LOOP;   
                            CLOSE stopsNearDestiny;
                        END IF;
                    END WHILE;
                CLOSE stopsNearOrigin;

                SELECT * FROM temporary_final_table;
            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_get_nearly_stops_point` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`homestead`@`%` PROCEDURE `sp_get_nearly_stops_point`(
                IN latFrom FLOAT, 
                IN lngFrom FLOAT,
                IN distance VARCHAR(20),
                IN tableName VARCHAR(20))
BEGIN
                DECLARE b INT;

                DECLARE v_exist INT;

                DECLARE v_id_stop   INT;   
                DECLARE v_id_line   VARCHAR(100);
                DECLARE v_order     VARCHAR(100);
                DECLARE v_distance  VARCHAR(400);

                DECLARE v_id_line_aux INT;
                DECLARE v_order_aux   INT;
                DECLARE v_distance_aux DECIMAL(6,2);
                DECLARE v_orderaaaa   INT;


                DECLARE stopsNear CURSOR FOR 
                        SELECT  bs.id, 
                                GROUP_CONCAT(bl.id SEPARATOR ',') as id_line,  
                                GROUP_CONCAT(blbs.order SEPARATOR ',') as stop_order,
                                GROUP_CONCAT(
                                    distanceBetweenPoints(bs.latitude, bs.longitude, latFrom, lngFrom)
                                    SEPARATOR ',')  as distance

                        FROM debondi.bus_stops bs 
                                INNER JOIN bus_lines_bus_stops blbs ON bs.id = blbs.id_bus_stop AND blbs.enabled = 1
                                INNER JOIN bus_lines bl ON bl.id = blbs.id_bus_line
                        WHERE bs.enabled = 1 AND distanceBetweenPoints(bs.latitude, bs.longitude, latFrom, lngFrom) <= distance
                        GROUP BY bs.id
                        ORDER BY distanceBetweenPoints(bs.latitude, bs.longitude, latFrom, lngFrom) ASC;


                DECLARE CONTINUE HANDLER FOR NOT FOUND SET b = 1;


                -- DROP TEMPORARY TABLE IF EXISTS temporary_table;
                SET @sql = CONCAT('DROP TEMPORARY TABLE IF EXISTS ', tableName);
                PREPARE s1 from @sql;
                EXECUTE s1 ;

                DROP TEMPORARY TABLE IF EXISTS temporary_table;
                CREATE TEMPORARY TABLE IF NOT EXISTS temporary_table (id_stop int, id_line int, stop_order int, distance decimal(6,2));


                SET b = 0;

                OPEN stopsNear;
                WHILE b = 0 DO
                    FETCH stopsNear INTO v_id_stop, v_id_line, v_order, v_distance;
                    IF b = 0 THEN


                        -- Iteramos sobre la columna 'id_line' y 'order' mientras exista mas de un registro por columna
                        -- separados por comma ','
                        WHILE (LOCATE(',', v_id_line) > 0) DO

                            -- Tomamos el primer 'id_line' y 'order' que aparecen en la columnas
                            SET v_id_line_aux = CONVERT(SUBSTRING(v_id_line FROM 1 FOR LOCATE(',',v_id_line)-1), SIGNED INT);
                            SET v_order_aux   = CONVERT(SUBSTRING(v_order FROM 1 FOR LOCATE(',',v_order)-1), SIGNED INT);
                            SET v_distance_aux= CAST(SUBSTRING(v_distance FROM 1 FOR LOCATE(',',v_distance)-1) as decimal(6,2));

                            -- Modificamos las columnas eliminando las varibales que ya tomamos
                            SET v_id_line   = SUBSTRING(v_id_line, LOCATE(',',v_id_line) + 1);
                            SET v_order     = SUBSTRING(v_order, LOCATE(',',v_order) + 1);
                            SET v_distance  = SUBSTRING(v_distance, LOCATE(',',v_distance) + 1);

                            -- chequeamos si existe un registro en la tabla temporal con el id_line seleccionado.
                            SET v_exist = (SELECT EXISTS(SELECT 1 FROM temporary_table WHERE id_line = v_id_line_aux));

                            -- Si no existe lo insertamos en la tabla temporal
                            IF v_exist = 0 THEN
                                INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                                   VALUES(v_id_stop, v_id_line_aux, v_order_aux, v_distance_aux);

                            -- Si existe tenemos que verificar que el 'order' del registro sea menor al 'order' actual,
                            -- caso contrario debemos elimianr el registro e insertar el actual.
                            ELSE
                                SET v_orderaaaa = (SELECT stop_order FROM temporary_table WHERE id_line = v_id_line_aux);
                                IF (v_order_aux + 500) < v_orderaaaa THEN
                                    DELETE FROM temporary_table WHERE id_line = v_id_line_aux;
                                    INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                                    VALUES(v_id_stop, v_id_line_aux, v_order_aux, v_distance_aux);
                                END IF;
                            END IF;

                        END WHILE;

                        -- Ya solo queda el ultimo registro en la columna 'id_line' y 'order' por lo que debemos hacer
                        -- las operaciones de comparacion para determinar si se deben insertar en la tabla auxiliar o no.            
                        SET v_exist = (SELECT EXISTS(SELECT 1 FROM temporary_table WHERE id_line = v_id_line));
                        -- SET v_order  = SUBSTRING(v_order, LOCATE(',',v_order) + 1);

                        SET v_id_line_aux = CONVERT(v_id_line, SIGNED INT);
                        SET v_order_aux   = CONVERT(v_order, SIGNED INT);
                        SET v_distance_aux= CAST(v_distance as decimal(6,2));

                        IF v_exist = 0 THEN
                            INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                                VALUES(v_id_stop, v_id_line, v_order_aux, v_distance_aux);
                        ELSE
                                SET v_orderaaaa = (SELECT stop_order FROM temporary_table WHERE id_line = v_id_line_aux);
                                IF (v_order_aux + 500) < v_orderaaaa THEN
                                    DELETE FROM temporary_table WHERE  id_line = v_id_line_aux;
                                    INSERT INTO temporary_table(id_stop, id_line, stop_order, distance)
                                   VALUES(v_id_stop, v_id_line_aux, v_order_aux, v_distance_aux);
                                END IF;
                        END IF;
                    END IF;  
                END WHILE;
                CLOSE stopsNear;
                -- Hasta acá obtuvimos una tabla temporal con todas las paradas que estan cercanas al origen
                -- y tienen menor 'order', la tabla es tal como la siguiente:    
                -- id_stop,  id_line,    stop_order,     distance
                -- '281',     '2',        '2400',         '94.08'
                -- '281',     '3',        '2500',         '94.08'
                -- '281',     '4',        '3500',         '94.08'
                -- '504',     '9',        '1900',         '163.12'

                    SET @sql = CONCAT('CREATE TEMPORARY TABLE ', tableName, ' SELECT * FROM temporary_table');
                PREPARE s1 from @sql;
                EXECUTE s1 ;
                    -- return stopsNear;

            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-02-18 15:29:51
