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



    DECLARE stopsOrigin CURSOR FOR
        SELECT * FROM debondi.temporary_table;

    DECLARE stopsNearDestiny CURSOR FOR 
        SELECT  bs.id, 
                GROUP_CONCAT(bl.id SEPARATOR ',') as id_line,  
                GROUP_CONCAT(blbs.order SEPARATOR ',') as stop_order,
                GROUP_CONCAT(
                    distanceBetweenPoints(bs.latitude, bs.longitude, latTo, lngTo)
                    SEPARATOR ',')  as distance

        FROM debondi.bus_stops bs 
                INNER JOIN bus_lines_bus_stops blbs ON bs.id = blbs.id_bus_stop AND blbs.enabled = 1
                INNER JOIN bus_lines bl ON bl.id = blbs.id_bus_line
        WHERE bs.enabled = 1 
            AND distanceBetweenPoints(bs.latitude, bs.longitude, latTo, lngTo) <= distance-v_distance
           -- AND blbs.order > v_order
        GROUP BY bs.id
        ORDER BY distanceBetweenPoints(bs.latitude, bs.longitude, latTo, lngTo) ASC;



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

                -- Si no existe lo isertamos en la tabla temporal
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




    -- Iteramos sobre cada uno de los registros anteriores y buscamos las paradas destino para esa parada origen
    DROP TEMPORARY TABLE IF EXISTS temporary_final_table;
    CREATE TEMPORARY TABLE IF NOT EXISTS temporary_final_table (
        id_stop_origin  int, id_line_origin  int,          stop_order_origin  int,          distance_origin  decimal(6,2),
        id_stop_destiny int, id_line_destiny varchar(200), stop_order_destiny varchar(200), distance_destiny varchar(400));


    OPEN stopsOrigin;
        FETCH stopsOrigin INTO v_id_stop, v_id_line, v_order, v_distance;
        SET b = 0;

        WHILE b = 0 DO

            OPEN stopsNearDestiny;            
                
--                 SET c = 0;

--                 WHILE c = 0 DO
                read_loop: LOOP
                    FETCH stopsNearDestiny INTO v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny;
                    IF b THEN
                        SET b = 0;
                        LEAVE read_loop;
                    END IF;
                    -- Iteramos sobre la columna 'v_id_line_destiny' y 'v_order_destiny' mientras exista mas de un registro por columna
                    -- separados por comma ','
                    WHILE (LOCATE(',', v_id_line_destiny) > 0) DO

                        -- Tomamos el primer 'id_line' y 'order' que aparecen en la columnas
                        SET v_id_line_destiny_aux = CONVERT(SUBSTRING(v_id_line_destiny FROM 1 FOR LOCATE(',',v_id_line_destiny)-1), SIGNED INT);
                        SET v_order_destiny_aux   = CONVERT(SUBSTRING(v_order_destiny FROM 1 FOR LOCATE(',',v_order_destiny)-1), SIGNED INT);
                        SET v_distance_destiny_aux= CAST(SUBSTRING(v_distance_destiny FROM 1 FOR LOCATE(',',v_distance_destiny)-1) as decimal(6,2));

                        -- Modificamos las columnas eliminando las varibales que ya tomamos
                        SET v_id_line_destiny   = SUBSTRING(v_id_line_destiny, LOCATE(',',v_id_line_destiny) + 1);
                        SET v_order_destiny     = SUBSTRING(v_order_destiny, LOCATE(',',v_order_destiny) + 1);
                        SET v_distance_destiny  = SUBSTRING(v_distance_destiny, LOCATE(',',v_distance_destiny) + 1);

                        -- chequeamos si existe un registro en la tabla temporal con el id_line seleccionado.
                        SET v_exist = (SELECT EXISTS(
                                                    SELECT 1 FROM temporary_final_table 
                                                    WHERE 
                                                            id_line_origin = v_id_line
                                                        AND id_line_destiny = v_id_line_destiny_aux
                                                    )
                                      );

                        -- Si no existe lo isertamos en la tabla temporal
                        IF v_exist = 0 THEN
                            INSERT INTO temporary_final_table(
                                id_stop_origin, id_line_origin, stop_order_origin, distance_origin,
                                id_stop_destiny, id_line_destiny, stop_order_destiny, distance_destiny
                            )VALUES(
                                v_id_stop, v_id_line, v_order, v_distance,
                                v_id_stop_destiny, v_id_line_destiny_aux, v_order_destiny_aux, v_distance_destiny_aux);

                        -- Si existe tenemos que verificar que el 'order' del registro sea menor al 'order' actual,
                        -- caso contrario debemos elimianr el registro e insertar el actual.
                        ELSE
                            SET v_orderaaaa = (
                                                SELECT  stop_order_destiny 
                                                FROM    temporary_final_table 
                                                WHERE   id_line_origin  = v_id_line
                                                    AND id_line_destiny = v_id_line_destiny_aux
                                            );
                            IF (v_order_destiny_aux + 500) < v_orderaaaa THEN
                                DELETE FROM temporary_final_table 
                                WHERE   id_line_origin  = v_id_line
                                    AND id_line_destiny = v_id_line_destiny_aux;

                                INSERT INTO temporary_final_table(
                                    id_stop_origin, id_line_origin, stop_order_origin, distance_origin,
                                    id_stop_destiny, id_line_destiny, stop_order_destiny, distance_destiny
                                )VALUES(
                                    v_id_stop, v_id_line, v_order, v_distance,
                                    v_id_stop_destiny, v_id_line_destiny_aux, v_order_destiny_aux, v_distance_destiny_aux);
                            END IF;
                        END IF;

                    END WHILE;
--                 END WHILE;
                END LOOP;   
            CLOSE stopsNearDestiny;
        END WHILE;
            
    CLOSE stopsOrigin;

    SELECT * FROM temporary_final_table;
END


--                     INSERT INTO temporary_final_table(
--                         id_stop_origin, id_line_origin, stop_order_origin, distance_origin,
--                         id_stop_destiny, id_line_destiny, stop_order_destiny, distance_destiny
--                     )VALUES(
--                         v_id_stop, v_id_line, v_order, v_distance,
--                         v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny);
--
-- Si existe tenemos que verificar que el 'order' del registro sea menor al 'order' actual,
-- caso contrario debemos elimianr el registro e insertar el actual.