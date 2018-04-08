<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetNearlyStopsOriginDestiny extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
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

                DROP TABLE IF EXISTS temporary_final_table;
                CREATE TABLE IF NOT EXISTS temporary_final_table (
                    id_stop_origin  int, id_line_origin  int,          stop_order_origin  int,          distance_origin  decimal(6,2),
                    id_stop_destiny int, id_line_destiny varchar(200), stop_order_destiny varchar(200), distance_destiny varchar(400)
				);
                CREATE INDEX idx_temporary_table 
                ON temporary_final_table (id_line_origin, id_line_destiny);
				


				-- Obtenemos todas las paradas cercanas al origen del recorrido
                CALL sp_get_nearly_stops_point(latFrom, lngFrom, distance, 'tabla_origen');

                SET b = 0;

                OPEN stopsNearOrigin;
                    WHILE b = 0 DO
                        FETCH stopsNearOrigin INTO v_id_stop, v_id_line, v_order, v_distance;
                        IF b = 0 THEN 

                            CALL sp_get_nearly_stops_point(latTo, lngTo, distance-(v_distance), 'tabla_destino');
                            OPEN stopsNearDestiny;
                                read_loop: LOOP
									-- Obtenemos todas las paradas cercanas al destino para el punto de 
                                    -- origen actual. Para que una parada destino sea considerada una
                                    -- parada cercana, la suma de la distancias entre la posicion inicial
                                    -- del usuario y la parada de inicio mas la distancia entre la parada
                                    -- destino y el punto final debe ser menor a la distancia maxima 
                                    -- que el usuario está dispuesto a caminar.
                                    FETCH stopsNearDestiny INTO v_id_stop_destiny, v_id_line_destiny, v_order_destiny, v_distance_destiny;

                                    IF b THEN
                                        SET b = 0;
                                        LEAVE read_loop;
                                    END IF;

									-- Chequeamos si la parada actual ya existe en la tabla
                                    -- temporal en donde vamos guardando las distintas paradas
                                    -- que arrojo el resultado.
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

				
	SELECT option_lines.id_stop_origin,  GROUP_CONCAT(option_lines.id_line_origin  SEPARATOR ';') as id_lines_origin, option_lines.distance_origin,
		  option_lines.id_stop_destiny,  GROUP_CONCAT(option_lines.id_line_destiny SEPARATOR ';') as id_lines_destiny, option_lines.distance_destiny, 
		  bs.line, bs.ramal, GROUP_CONCAT(bs.zone SEPARATOR ';') as txt_zones
	FROM (
                SELECT * FROM (
					SELECT *, (distance_origin + distance_destiny) as distance
					FROM temporary_final_table
					WHERE id_line_origin = id_line_destiny
					ORDER BY distance ASC
				) AS lineas_unicas
                UNION
                SELECT * FROM (
					SELECT *, (distance_origin + distance_destiny) as distance
					FROM temporary_final_table
					WHERE id_line_origin != id_line_destiny
						AND id_line_origin NOT IN (
								SELECT id_line_origin
								FROM temporary_final_table
								WHERE id_line_origin = id_line_destiny
								ORDER BY distance ASC
						) 
                        AND id_line_destiny NOT IN (
							select * from ((
                                SELECT id_line_destiny as asd
								FROM temporary_final_table
								WHERE id_line_origin != id_line_destiny
								ORDER BY distance ASC
							) 
							union
							(
								SELECT id_line_origin as asd
								FROM temporary_final_table
								WHERE id_line_origin != id_line_destiny
								ORDER BY distance ASC
							)) as qwert
                        )
                        AND id_line_origin NOT IN (
							select * from ((
                                SELECT id_line_destiny as asd
								FROM temporary_final_table
								WHERE id_line_origin != id_line_destiny
								ORDER BY distance ASC
							)
							union
							(
								SELECT id_line_origin as asd
								FROM temporary_final_table
								WHERE id_line_origin != id_line_destiny
								ORDER BY distance ASC
							)) as qwerty
                        )
					ORDER BY distance ASC
				) AS lineas_repetidas
	) option_lines
    JOIN 		bus_lines bs on option_lines.id_line_origin =  bs.id
    WHERE 		option_lines.id_line_origin = option_lines.id_line_destiny
    GROUP BY	option_lines.id_stop_origin, option_lines.distance_origin,
			  option_lines.id_stop_destiny,  option_lines.distance_destiny, 
			  bs.line, bs.ramal;
                
                DROP TABLE IF EXISTS temporary_final_table;
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
        DB::unprepared("DROP PROCEDURE sp_get_nearly_stops_origin_destiny");
    }
}
