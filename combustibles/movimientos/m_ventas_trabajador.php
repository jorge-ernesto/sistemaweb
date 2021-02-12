<?php
class VentasTrabajadorModel extends Model {
	function obtenerAperturaCierre($dia,$turno,$trabajador) {
		global $sqlca;
		$reporte	= Array();
		$cierre		= "";
		
		//Hallamos la fecha de Cierre
		$sql = "SELECT DISTINCT 
				fecha,
				to_char(fecha,'DD/MM/YYYY HH24:MI') as cierre
			FROM	pos_contometros
			WHERE	num_lado IN 
					(SELECT DISTINCT cast(hl.ch_lado as numeric(4,0)) 
					FROM	pos_historia_ladosxtrabajador hl
						LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
					WHERE	hl.dt_dia = '".$dia."'
						AND hl.ch_posturno = ".$turno."
						AND hl.ch_codigo_trabajador='".$trabajador."')
				AND dia = '".$dia."'
				AND turno = '".$turno."'
			GROUP BY fecha ;";

		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$cierre	= $a[0];
			$reporte[0]['cierre'] = $a[1];
		}
		
		//A partir de la fecha de cierre hallamos la fecha de Apertura
		$sql = "SELECT to_char(min(fecha),'DD/MM/YYYY HH24:MI')
			FROM	pos_contometros
			WHERE	num_lado IN 
					(SELECT DISTINCT cast(hl.ch_lado as numeric(4,0)) 
					FROM 	pos_historia_ladosxtrabajador hl
						LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
					WHERE	hl.dt_dia = '".$dia."'
						AND hl.ch_posturno = ".$turno."
						AND hl.ch_codigo_trabajador='".$trabajador."' )
				AND fecha < '".$cierre."'
			GROUP BY dia, turno
			ORDER BY dia DESC, turno DESC
			LIMIT 1;";

		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[0]['apertura'] = $a[0];
		}		
		return $reporte;
	}
	
	function obtenerTrabajadores($dia,$turno) {
		global $sqlca;
		$reporte = Array();
		$sql = "SELECT DISTINCT trim(p.ch_codigo_trabajador) as codigo
			FROM 	pos_historia_ladosxtrabajador p
			WHERE	p.dt_dia = '".$dia."'
				AND p.ch_posturno = ".$turno."
			ORDER BY trim(p.ch_codigo_trabajador);";

		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i]['codigo']= $a[0];
		}		
		return $reporte;
	}

	function obtenerReporteTurno($dia,$turno,$trabajador) {
		global $sqlca;
		$reporte	= Array();
		$dia_vector	= explode("/",$dia);
		$postrans	= "pos_trans{$dia_vector[2]}{$dia_vector[1]}";
		$dia_sql	= "{$dia_vector[2]}-{$dia_vector[1]}-{$dia_vector[0]}";
		
		$sql =	"SELECT DISTINCT
				ch_sucursal as sucursal
				,hl.ch_tipo as tipo
				,trim(hl.ch_codigo_trabajador) as cod_trab
				,trim(t.ch_nombre1) || ' ' || trim(Case WHEN t.ch_nombre2='-' OR t.ch_nombre2='.' THEN '' Else t.ch_nombre2 End) || ' ' || trim(t.ch_apellido_paterno) || ' ' || trim(Case WHEN t.ch_apellido_materno='-' OR t.ch_apellido_materno='.' THEN '' Else t.ch_apellido_materno End) || ' ' as trabajador
				,hl.ch_lado as lado
				,c.manguera
				,CAST(c.cnt_vol as numeric(10,2))
				,CAST(c.cnt_val as numeric(10,2))
				,c.cnt
				,c.precio
				,c.fecha
			FROM
				pos_historia_ladosxtrabajador hl
				LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
				LEFT JOIN pos_contometros c ON c.num_lado = CAST(hl.ch_lado as numeric) and c.dia = hl.dt_dia and c.turno = hl.ch_posturno and hl.ch_tipo='C'
			WHERE
				hl.dt_dia = '".$dia_sql."'
				AND hl.ch_posturno = ".$turno." 
				AND hl.ch_codigo_trabajador='".$trabajador."'
			ORDER BY
				hl.ch_tipo
				,hl.ch_lado
				,c.manguera ;";
		
		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i]['postrans']= $postrans;
			$reporte[$i]['dia']		= $dia_sql;
			$reporte[$i]['turno']	= $turno;
			$reporte[$i]['sucursal']= $a[0];
			$reporte[$i]['tipo']	= $a[1];
			$reporte[$i]['codigo']	= $a[2];
			$reporte[$i]['nombre']	= $a[3];
			$reporte[$i]['lado']	= $a[4];
			$reporte[$i]['manguera']= $a[5];
			$reporte[$i]['contometro_volumen'] = $a[6];
			$reporte[$i]['contometro_valor'] = $a[7];
			$reporte[$i]['contometro'] = $a[8];
			$reporte[$i]['precio'] = $a[9];
			$reporte[$i]['fecha_contometro'] = $a[10];
		}		
		return $reporte;
	}
	
	function obtenerProductos($lado) {
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT	ctc1.ch_codigocombustible
				,ctc1.ch_nombrebreve
				,ctc1.ch_codigocombex
				,ctc2.ch_codigocombustible
				,ctc2.ch_nombrebreve
				,ctc2.ch_codigocombex
				,ctc3.ch_codigocombustible
				,ctc3.ch_nombrebreve
				,ctc3.ch_codigocombex
				,ctc4.ch_codigocombustible
				,ctc4.ch_nombrebreve
				,ctc4.ch_codigocombex 
			FROM
				pos_cmblados pcl
				LEFT JOIN comb_ta_combustibles ctc1 ON pcl.prod1 = ctc1.ch_codigocombex
				LEFT JOIN comb_ta_combustibles ctc2 ON pcl.prod2 = ctc2.ch_codigocombex
				LEFT JOIN comb_ta_combustibles ctc3 ON pcl.prod3 = ctc3.ch_codigocombex
				LEFT JOIN comb_ta_combustibles ctc4 ON pcl.prod4 = ctc4.ch_codigocombex
			WHERE
				pcl.lado = '".$lado."'
			ORDER BY
				pcl.lado;";
					
		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i][$i]	= $a[0];
			$reporte[$i][$i+1]	= $a[1];
			$reporte[$i][$i+2]	= $a[2];
			$reporte[$i][$i+3]	= $a[3];
			$reporte[$i][$i+4]	= $a[4];
			$reporte[$i][$i+5]	= $a[5];
			$reporte[$i][$i+6]	= $a[6];
			$reporte[$i][$i+7]	= $a[7];
			$reporte[$i][$i+8]	= $a[8];
			$reporte[$i][$i+9]	= $a[9];
			$reporte[$i][$i+10]	= $a[10];
			$reporte[$i][$i+11]	= $a[11];
		}		
		return $reporte;
	}
	
	function obtenerContometros_cantidad($lado,$manguera,$fecha,$contometro) {
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT	min(cnt_vol)
				,min(cnt_val)
			FROM
				pos_contometros
			WHERE
				num_lado = '".$lado."'
				AND manguera = ".$manguera."
				AND fecha < '".$fecha."'
				AND cnt < ".$contometro."
			GROUP BY
				dia,
				turno
			ORDER BY
				dia DESC,
				turno DESC
			LIMIT 1	;";

		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i][$i]	= $a[0];
			$reporte[$i][$i+1]	= $a[1];
		}
		
		return $reporte;
	}
	
	function obtenerTickets_cantidad($postrans,$dia,$turno,$lado,$manguera,$tipo) {
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT	sum(cantidad),
				sum(importe)
			FROM
				".$postrans."
			WHERE
				date(dia) = '".$dia."'
				AND turno = '".$turno."'
				AND tipo = '".$tipo."' ";
		
		if($tipo == "C")
			$sql.= "AND pump = trim('".$lado."')
				AND codigo = '".$manguera."'
				AND importe > 0;";
		else
			$sql.= "AND caja = trim('".$lado."');";
		
		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i][$i]	= $a[0];
			$reporte[$i][$i+1]	= $a[1];
		}
		
		return $reporte;
	}
	
	function obtenerNotasDespacho($postrans,$dia,$turno,$trabajador,$tipo) {
		global $sqlca;
		$reporte	= Array();

		$sql = "SELECT DISTINCT ch_documento
				,c.cli_codigo
				,c.cli_razsocial
				,nu_importe
			FROM		
				val_ta_cabecera v
				LEFT JOIN int_clientes c ON v.ch_cliente = c.cli_codigo
			WHERE		
				ch_turno = '".$turno."' 
				AND dt_fecha='".$dia."'
				AND c.cli_ndespacho_efectivo != ".$tipo."
				AND (v.ch_lado IN 
						(SELECT DISTINCT 
							hl.ch_lado 
						FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."'
							AND hl.ch_tipo = 'C')
						 
					OR 	
						(trim(v.ch_lado)='' and  v.ch_caja IN 
							(SELECT DISTINCT 
								hl.ch_lado
							FROM
								pos_historia_ladosxtrabajador hl
								LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
							WHERE
								hl.dt_dia = '".$dia."'
								AND hl.ch_posturno = ".$turno."
								AND hl.ch_codigo_trabajador='".$trabajador."'
								AND hl.ch_tipo = 'M')
						)
				)
				ORDER BY ch_documento;";
		
		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i]['trans']	= $a[0];
			$reporte[$i]['codigo']	= $a[1];
			$reporte[$i]['cliente']	= $a[2];
			$reporte[$i]['importe']	= $a[3];
		}
		
		return $reporte;
	}
	
	function obtenerTarjetas_Credito($postrans,$dia,$turno,$trabajador) {
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT
				t.trans
				,max(t.text1) as numero
				,max(g.tab_desc_breve) as tarjeta
				,CAST(sum(t.importe) as numeric(10,2))
			FROM
				".$postrans." t
				LEFT JOIN int_tabla_general g ON (g.tab_tabla = '95' AND g.tab_elemento = '00000'||t.at)
			WHERE
				t.td IN ('B','F')
				AND fpago = '2'
				AND date(t.dia) = '".$dia."'
				AND t.turno = '".$turno."'
				AND ((t.pump IN 
						(SELECT DISTINCT 
							hl.ch_lado 
						  FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						  WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."')
							AND t.tipo='C') 
					OR 
						(t.caja IN 
							(SELECT DISTINCT 
								hl.ch_lado 
							FROM
								pos_historia_ladosxtrabajador hl
								LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
							WHERE
								hl.dt_dia = '".$dia."'
								AND hl.ch_posturno = ".$turno."
								AND hl.ch_codigo_trabajador='".$trabajador."'
								AND hl.ch_tipo = 'M')
						AND t.tipo='M')
					)
				GROUP BY t.trans
				ORDER BY t.trans;";

		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i]['trans']	= $a[0];
			$reporte[$i]['numero']	= $a[1];
			$reporte[$i]['tarjeta']	= $a[2];
			$reporte[$i]['importe']	= $a[3];
		}
		
		return $reporte;
	}
	
	function obtenerDescuentos($postrans,$dia,$turno,$trabajador) {
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT
				t.trans,
				a.art_descbreve,
				t.precio,
				r.razsocial,
				t.fpago,
				t.importe
			FROM
				".$postrans." t
				LEFT JOIN int_articulos a ON t.codigo = a.art_codigo
				LEFT JOIN ruc r ON r.ruc = t.ruc

			WHERE
				tm = 'V'
				and ((t.importe<0 and t.td!='A') or (t.td='N' and t.grupo='D' and t.importe>0))
				AND date(t.dia) = '".$dia."'
				AND t.turno = '".$turno."'
				AND ((t.pump IN (SELECT DISTINCT 
							hl.ch_lado 
						FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."')
							AND t.tipo='C'
					) 
				OR (t.caja IN (SELECT DISTINCT 
							hl.ch_lado 
						FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."'
							AND hl.ch_tipo = 'M')
							AND t.tipo='M')
					)
				ORDER BY t.trans;";
		
		if ($sqlca->query($sql)<0)
			return FALSE;		

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			if(trim($a[3])=="") 
				$desc = $a[1]." / ".$a[2]; 
			else 
				$desc = $a[3];

			$reporte[$i]['trans']	= $a[0];
			$reporte[$i]['descripcion']	= $desc;
			$reporte[$i]['forma_pago']	= $a[4];
			$reporte[$i]['importe']	= $a[5];
		}
		
		return $reporte;
	}
	
	function obtenerDevoluciones($postrans,$dia,$turno,$trabajador)	{
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT
				t.trans,
				t.fpago,
				t.importe
			FROM
				".$postrans." t
			WHERE
				tm = 'D'
				AND t.td IN ('B','F')
				AND date(t.dia) = '".$dia."'
				AND t.turno = '".$turno."'
				AND ((t.pump IN (SELECT DISTINCT 
							hl.ch_lado 
						  FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						  WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."')
							AND t.tipo='C'
						) 
				OR (t.caja IN (SELECT DISTINCT 
							hl.ch_lado 
						  FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						  WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."'
							AND hl.ch_tipo = 'M')
							AND t.tipo='M')
						)
				ORDER BY t.trans;";
		
		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i]['trans']	= $a[0];
			$reporte[$i]['fpago']	= $a[1];
			$reporte[$i]['importe']	= $a[3];
		}
		
		return $reporte;
	}
	
	/*function obtenerAfericiones5($postrans,$dia,$turno,$trabajador) { //function obtenerAfericiones($postrans,$dia,$turno,$trabajador) (original)
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT DISTINCT 
				t.trans
				,c.ch_nombrebreve
				,t.tanque
				,t.balance
				,t.importe
			FROM
				".$postrans." t
				LEFT JOIN comb_ta_combustibles c ON t.codigo = c.ch_codigocombustible
			WHERE
				tm = 'V'
				AND t.td = 'A'
				AND date(t.dia) = '".$dia."'
				AND t.turno = '".$turno."'
				AND t.tipo='C' 
				AND ((t.pump IN (SELECT DISTINCT 
							hl.ch_lado 
						  FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						  WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."')
							AND t.tipo='C'
						) 
				OR (t.caja IN (SELECT DISTINCT 
							hl.ch_lado 
						  FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						  WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."'
							AND hl.ch_tipo = 'M')
							AND t.tipo='M')
						)
				ORDER BY t.trans;";

		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i]['trans']	= $a[0];
			$reporte[$i]['producto']= $a[1];
			$reporte[$i]['detalle']	= $a[2]." / ".$a[3];
			$reporte[$i]['importe']	= $a[4];
		}
		
		return $reporte;
	}*/


	function obtenerAfericiones($postrans,$dia,$turno,$trabajador) { // reporte de pos_ta_afericiones nuevo
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT DISTINCT 
				t.trans
				,c.ch_nombrebreve
				,t.veloc
				,t.lineas
				,t.importe
			FROM
				pos_ta_afericiones t
				LEFT JOIN comb_ta_combustibles c ON t.codigo = c.ch_codigocombustible
			WHERE
				date(t.dia) = '".$dia."'
				AND t.turno = '".$turno."'
				AND ((t.pump IN (SELECT DISTINCT 
							hl.ch_lado 
						  FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						  WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."')
						) 
				OR (t.caja IN (SELECT DISTINCT 
							hl.ch_lado 
						  FROM
							pos_historia_ladosxtrabajador hl
							LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
						  WHERE
							hl.dt_dia = '".$dia."'
							AND hl.ch_posturno = ".$turno."
							AND hl.ch_codigo_trabajador='".$trabajador."')
						))
				ORDER BY t.trans;";
		echo "+++ AFERICION: ".$sql." +++";
		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i]['trans']	= $a[0];
			$reporte[$i]['producto']= $a[1];
			$reporte[$i]['detalle']	= $a[2]." / ".$a[3];
			$reporte[$i]['importe']	= $a[4];
		}
		
		return $reporte;
	}


	
	function obtenerDepositos($trabajador,$dia,$turno) {
		global $sqlca;
		$reporte	= Array();
		
		$sql = "SELECT
				ch_numero_correl,
				CASE
					WHEN ch_moneda = '01' THEN 'Nuevos Soles'
					WHEN ch_moneda = '02' THEN 'Dolares / ' || nu_tipo_cambio
				ELSE 
					'Otra'
				END,
				nu_tipo_cambio,
				nu_importe,
				CASE
					WHEN ch_moneda = '01' THEN nu_importe
					ELSE nu_importe * nu_tipo_cambio
				END
			FROM
				pos_depositos_diarios
			WHERE
				dt_dia = '".$dia."'
				AND ch_posturno = ".$turno."
				AND ch_codigo_trabajador = '".$trabajador."'
				AND ch_valida = 'S' ;";
		
		if ($sqlca->query($sql)<0)
			return FALSE;
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$reporte[$i]['correlativo']		= $a[0];
			$reporte[$i]['moneda']			= $a[1];
			$reporte[$i]['tipo_cambio']		= $a[2];
			$reporte[$i]['importe']			= $a[3];
			$reporte[$i]['importe_soles']	= $a[4];
		}
		
		return $reporte;
	}
	
	function obtenerDatosEESS() {
		global $sqlca;
		
		$sql =	"	SELECT
					trim(p1.par_valor) as nom_empresa,
					trim(p2.par_valor) as descripcion,
					trim(p3.par_valor) as direccion,
					trim(p4.par_valor) as market
				FROM
					int_parametros p1
					LEFT JOIN int_parametros p2 ON p2.par_nombre = 'desces'
					LEFT JOIN int_parametros p3 ON p3.par_nombre = 'dires'
					LEFT JOIN int_parametros p4 ON p4.par_nombre = 'razsocial_market'
				WHERE
					p1.par_nombre = 'razsocial';";
		if ($sqlca->query($sql)<0)
			return FALSE;
		return $sqlca->fetchRow();
	}
	
	function validarConsolidacion($dia,$turno) {
		global $sqlca;
		
		$fecha	= explode("/",$dia);
		$newday = $fecha[2].'-'.$fecha[1].'-'.$fecha[0];
		
		$sql =	"SELECT 1 FROM pos_consolidacion WHERE dia = '$newday' AND turno = $turno;"; 
		if ($sqlca->query($sql)<0 || $sqlca->numrows()==0)
			return FALSE;
		return TRUE;
	}
	
	function obtenerComandoImprimir($file) {
		global $sqlca;
		
		$sql =	"SELECT
				trim(pc_samba),
				trim(prn_samba),
				trim(ip) 
			FROM 	pos_cfg 
			WHERE	impcierre = true and pos = (SELECT par_valor from int_parametros where par_nombre='pos_consolida')
			ORDER BY tipo DESC, pos ASC";
		
		$rs = $sqlca->query($sql);
		if ($rs < 0) {
			echo "Error consultando POS\n";
			return false;
		}
		if ($sqlca->numrows()<1)
			return true;

		$row = $sqlca->fetchRow();
		$smbc="lpr -H {$row[2]} -P {$row[1]} {$file}";

		$fp = fopen("COMANDO.txt","a");
		fwrite($fp, "-".$smbc."-".PHP_EOL);
		fclose($fp);  
		return $smbc;
	}
	
	function obtenerDiferenciaIgnorada() {
		global $sqlca;

		$sql =	"SELECT par_valor FROM int_parametros WHERE par_nombre = 'diferencia_ignorada';";
		if ($sqlca->query($sql) < 0)
			return 0;
		$r = $sqlca->fetchRow();
		settype($r[0],"float");
		return $r[0];
	}
	
	function insertarConsolidacion($fecha,$turno) {
		global $sqlca;
		$di = VentasTrabajadorModel::obtenerDiferenciaIgnorada();
		$datos_trabajador = Array();
		$sql_general= "";
		
		$fec	= explode("/",$fecha);
		$newday = $fec[2].'-'.$fec[1].'-'.$fec[0];

		$lines = file('/sistemaweb/combustibles/movimientos/query_consolidacion.txt');
		$j = 0;
		foreach ($lines as $line_num => $line) {
			$datos = explode(";", $line);
			$datos_trabajador[$j]['sucursal'] = $datos[0];
			$datos_trabajador[$j]['codigo'] = $datos[1];
			$datos_trabajador[$j]['tipo'] = $datos[2];
			$datos_trabajador[$j]['diferencia'] = $datos[3];
			$j += 1;
		}
		
		for ($i = 0; $i < $j; $i++) {
			if (abs($datos_trabajador[$i]['diferencia'])<$di)
				continue;
			
			$sql_general .=	"INSERT INTO comb_diferencia_trabajador(
								es,
								ch_codigo_trabajador,
								dia,
								turno,
								flag,
								importe,
								observacion,
								tipo
							) VALUES (
								'".$datos_trabajador[$i]['sucursal']."',
								'".$datos_trabajador[$i]['codigo']."',
								'".$newday."',
								".$turno.",
								0,
								".$datos_trabajador[$i]['diferencia'].",
								'',
								'".$datos_trabajador[$i]['tipo']."'
							);";
		}
		
		$sql_general .=	"INSERT INTO pos_consolidacion
								(dia,turno) 
						VALUES 
								('".$newday."',".$turno.");";
		
		$sqlca->query("BEGIN;");
		if ($sqlca->query($sql_general)<0) 
		{
			$sqlca->query("ROLLBACK;");
			return $sql_general;
		}
		$sqlca->query("COMMIT;");
		
		return "1";
	}
}
