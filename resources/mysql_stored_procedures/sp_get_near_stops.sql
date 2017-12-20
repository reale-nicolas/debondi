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
END