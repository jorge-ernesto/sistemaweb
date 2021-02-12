<?php

class liquidacion_ventas_diariasModel extends Model{
    
	function listado_almacen(){
		global $sqlca;
         
	         $sql = "
			 SELECT
				ch_almacen,
				TRIM(ch_nombre_almacen) AS ch_nombre_almacen
	                 FROM
				inv_ta_almacenes
                	 WHERE
				ch_clase_almacen = '1'
	                 ORDER BY
				ch_almacen;
			";

		$sqlca->query($sql);

		return $sqlca->fetchAll();

	}
    
	function venta_combustible(){
		global $sqlca;
        
		  $sql="
			SELECT
				SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as liquido,
				SUM(CASE WHEN ch_codigocombustible!='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as liquido_canti,
				SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventavalor ELSE 0 END) ELSE 0 END) as glp,
				SUM(CASE WHEN ch_codigocombustible='11620307' THEN (CASE WHEN nu_ventagalon!=0 THEN nu_ventagalon ELSE 0 END) ELSE 0 END) as glp_canti
			FROM
				comb_ta_contometros
			WHERE
		        	ch_sucursal = '" . pg_escape_string($_POST["almacen"]) . "' AND 
		        	dt_fechaparte BETWEEN '" . pg_escape_string($_POST["fecha_del"]) . "' AND '" . pg_escape_string($_POST["fecha_al"]) . "';
			";
        
		$sqlca->query($sql);

		return $sqlca->fetchRow();    
	}

	function diferencia_precio(){
		global $sqlca;

		$sql="
			SELECT
				sum(af.importe) as afericion,
			FROM 
				pos_ta_afericiones af
			WHERE
				af.es = '" . pg_escape_string($_POST["almacen"]) . "'
				AND af.dia BETWEEN '" . pg_escape_string($_POST["fecha_del"]) . "' AND '" . pg_escape_string($_POST["fecha_al"]) . "';
			";
        
		$sqlca->query($sql);

		return $sqlca->fetchRow(); 

	}

	function descuentos(){
		global $sqlca;

		$anio = $_POST["anio"];
		$mes  = $_POST["mes"];

		$sql="
			SELECT
				SUM(t.importe) importe
			FROM 
				pos_trans" . $anio . $mes . " t 
			WHERE 
				t.es = '" . pg_escape_string($_POST["almacen"]) . "'
				AND t.dia BETWEEN '" . pg_escape_string($_POST["fecha_del"]) . "' AND '" . pg_escape_string($_POST["fecha_al"]) . "'
				AND t.tipo = 'C'
				AND t.grupo = 'D'
				AND t.tm='V';
			";

		$sqlca->query($sql);
        
		return $sqlca->fetchAll(); 

	}

	function consumo_interno(){
		global $sqlca;

		$sql="
			SELECT
				sum(af.importe) as afericion,
			FROM 
				pos_ta_afericiones af
			WHERE
				af.es = '" . pg_escape_string($_POST["almacen"]) . "'
				AND af.dia BETWEEN '" . pg_escape_string($_POST["fecha_del"]) . "' AND '" . pg_escape_string($_POST["fecha_al"]) . "';
			";
        
		$sqlca->query($sql);

		return $sqlca->fetchRow(); 

	}

	function afericiones(){
		global $sqlca;

		$sql="
			SELECT
				sum(af.importe) importe
			FROM 
				pos_ta_afericiones af
			WHERE
				af.es = '" . pg_escape_string($_POST["almacen"]) . "'
				AND af.dia BETWEEN '" . pg_escape_string($_POST["fecha_del"]) . "' AND '" . pg_escape_string($_POST["fecha_al"]) . "';
			";

		$sqlca->query($sql);
        
		return $sqlca->fetchAll(); 

	}

	function venta_productos_promociones(){
		global $sqlca;
         
		$sql = "
			SELECT
				SUM(d.nu_fac_valortotal) AS ventatienda,
				SUM(d.nu_fac_cantidad) AS cantienda
			FROM
				fac_ta_factura_cabecera f 
				LEFT JOIN int_clientes c ON (f.cli_codigo = c.cli_codigo)
				LEFT JOIN fac_ta_factura_detalle d ON (f.ch_fac_tipodocumento = d.ch_fac_tipodocumento AND f.ch_fac_seriedocumento = d.ch_fac_seriedocumento AND f.ch_fac_numerodocumento = d.ch_fac_numerodocumento AND f.cli_codigo=d.cli_codigo)
			WHERE
				f.ch_fac_seriedocumento = '" . pg_escape_string($_POST["almacen"]) . "'
				AND f.ch_fac_tipodocumento = '45'
				AND f.dt_fac_fecha BETWEEN '" . pg_escape_string($_POST["fecha_del"]) . "' AND '" . pg_escape_string($_POST["fecha_al"]) . "'
				AND c.cli_ndespacho_efectivo != 1;
			";

		$sqlca->query($sql);

		return $sqlca->fetchRow();        

	}
    
	function vales_credito(){
	        global $sqlca;
         
		$sql="
			SELECT
				c.ch_cliente AS  codcliente,
		                cl.cli_ruc AS ruc,
                		cl.cli_razsocial AS cliente,
		                SUM(d.nu_cantidad)  AS cantidad,
        		        SUM(c.nu_importe) AS importe
        	        FROM
				val_ta_cabecera c
				JOIN val_ta_detalle d ON (c.ch_sucursal = d.ch_sucursal AND c.dt_fecha = d.dt_fecha AND c.ch_documento = d.ch_documento)
				JOIN int_clientes cl ON (c.ch_cliente = cl.cli_codigo)
	                WHERE
				c.dt_fecha BETWEEN '".pg_escape_string($_POST["fecha_del"])."' AND '".pg_escape_string($_POST["fecha_al"])."'
		                AND c.ch_sucursal='".pg_escape_string($_POST["almacen"])."'
		                AND c.ch_estado = '1'
                		AND cl.cli_ndespacho_efectivo != 1
			GROUP BY
		                c.ch_cliente,
                		cl.cli_ruc,
		                cl.cli_razsocial;
		";
                
		$sqlca->query($sql);

		return $sqlca->fetchAll(); 
	}

	function tarjetas_credito(){
	        global $sqlca;
         
		$anio = $_POST["anio"];
		$mes  = $_POST["mes"];

		$sql="
			SELECT 
				g.tab_descripcion as descripciontarjeta,
				SUM(t.importe) as importe
			FROM
				pos_trans" . $anio . $mes . " t
				JOIN int_tabla_general g ON (g.tab_tabla='95' AND g.tab_elemento='00000'||t.at)
				LEFT JOIN int_clientes c on c.cli_ruc = t.ruc AND c.cli_ndespacho_efectivo != 1
			WHERE
				t.es = '".pg_escape_string($_POST["almacen"])."'
				AND t.fpago = '2'
				AND t.dia BETWEEN '".pg_escape_string($_POST["fecha_del"])."' AND '".pg_escape_string($_POST["fecha_al"])."'
			GROUP BY
				1
			ORDER BY
				descripciontarjeta;
		";

		//echo "<pre>"; print_r($sql); echo "</pre>";

		$sqlca->query($sql);

		return $sqlca->fetchAll(); 
	}

	function depositos_pos(){
	        global $sqlca;
         
		$sql="
			SELECT 
				SUM(
					CASE 
						WHEN ch_moneda='01'THEN nu_importe 
						WHEN ch_moneda='02'THEN nu_importe * nu_tipo_cambio
					END) AS importe
			FROM 
				pos_depositos_diarios
			WHERE 
				ch_almacen =  '".pg_escape_string($_POST["almacen"])."'
				AND (ch_valida = 'S' OR ch_valida = 's')
				AND dt_dia BETWEEN '".pg_escape_string($_POST["fecha_del"])."' AND '".pg_escape_string($_POST["fecha_al"])."';

		";
                
		$sqlca->query($sql);

		return $sqlca->fetchAll(); 
	}

	function sobrantes_faltantes(){
	        global $sqlca;
         
		$sql="
			SELECT
				ROUND(SUM(importe),2) AS importe
			FROM
				comb_diferencia_trabajador
			WHERE
				dia BETWEEN '".pg_escape_string($_POST["fecha_del"])."' AND '".pg_escape_string($_POST["fecha_al"])."'
				AND flag = '0';

		";
                
		$sqlca->query($sql);

		return $sqlca->fetchAll(); 
	}

	function depositos_bancarios(){
	        global $sqlca;
         
		$sql="
			SELECT
				ROUND(SUM(importe),2) AS sfttotal
			FROM
				comb_diferencia_trabajador
			WHERE
				dia BETWEEN '".pg_escape_string($_POST["fecha_del"])."' AND '".pg_escape_string($_POST["fecha_al"])."';

		";
                
		$sqlca->query($sql);

		return $sqlca->fetchAll(); 
	}

	function sobrantes_faltantes_manuales(){
	        global $sqlca;
         
		$sql="
			SELECT
				ROUND(SUM(importe),2) AS importe
			FROM
				comb_diferencia_trabajador
			WHERE
				dia BETWEEN '".pg_escape_string($_POST["fecha_del"])."' AND '".pg_escape_string($_POST["fecha_al"])."'
				AND flag = '1'

		";

		$sqlca->query($sql);

		return $sqlca->fetchAll(); 
	}

}

