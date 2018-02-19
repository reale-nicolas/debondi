<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetNearlyStopsPoint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
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

            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE sp_get_nearly_stops_point");
    }
}
