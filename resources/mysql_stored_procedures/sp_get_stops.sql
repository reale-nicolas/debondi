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
END