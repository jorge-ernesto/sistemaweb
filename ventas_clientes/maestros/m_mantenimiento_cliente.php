<?php

class MantenimientoClienteModel extends Model { 

	function obtenerDatos($cliente) {
		global $sqlca;
	    
		$query =	"	SELECT
						pc.ch_codigo_cliente_grupo,
						cli.cli_razsocial,
						pc.art_codigo,
						art.art_descripcion,
						pc.dt_fecha_inicio,
						pc.dt_fecha_fin,
						pc.nu_preciopactado,
						pc.ch_usuario,
						pc.dt_fecha_actualizacion,
						pc.habilitado,
						pc.ch_cartaref,
						pc.ch_tipo_cliente
					FROM
						fac_precios_clientes pc
						LEFT JOIN int_articulos art ON (pc.art_codigo = art.art_codigo)
						LEFT JOIN int_clientes cli ON (cli.cli_codigo = pc.ch_codigo_cliente_grupo)
					WHERE
						1=1 ";
		if($cliente != '')
			$query .= "	AND pc.ch_codigo_cliente_grupo = '".pg_escape_string($cliente)."' ";
		
		$query .= "		ORDER BY pc.ch_codigo_cliente_grupo, pc.dt_fecha_inicio desc
						; ";

		if ($sqlca->query($query) <= 0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['cod_cliente']	= $a[0];
			$resultado[$i]['nom_cliente'] 	= $a[1];
			$resultado[$i]['cod_articulo'] 	= $a[2];
			$resultado[$i]['nom_articulo'] 	= $a[3];
			$resultado[$i]['fec_inicio'] 	= substr($a[4],8,2)."/".substr($a[4],5,2)."/".substr($a[4],0,4);
			$resultado[$i]['fec_fin']	= substr($a[5],8,2)."/".substr($a[5],5,2)."/".substr($a[5],0,4);
			$resultado[$i]['precio'] 	= $a[6];
			$resultado[$i]['usuario']	= $a[7];
			$resultado[$i]['actualiza']	= substr($a[8],8,2)."/".substr($a[8],5,2)."/".substr($a[8],0,4)." ".substr($a[8],11,8);
			$resultado[$i]['habilitado']	= $a[9];
			$resultado[$i]['carta_ref']	= $a[10];
			$resultado[$i]['tipo_cli']	= $a[11];
		}

		return $resultado;
  	}

	function ingresarDescuento($cliente, $articulo, $inicio, $fin, $precio, $habilitado, $cartaref, $tipocli, $usuario) {
		global $sqlca;
		
		$sql = "INSERT INTO fac_precios_clientes 
				       (
					ch_tipo_precio,
					ch_codigo_cliente_grupo,
					art_codigo,
					dt_fecha_inicio,
					dt_fecha_fin,
					nu_preciopactado,
					ch_usuario,
					dt_fecha_actualizacion,
					habilitado,
					ch_cartaref,
					ch_tipo_cliente) 
				VALUES (
					'C',
					'".trim($cliente)."',
					'".trim($articulo)."',
					to_date('".trim($inicio)."', 'DD/MM/YYYY'),
					to_date('".trim($fin)."', 'DD/MM/YYYY'),
					".trim($precio).", 
					'".trim($usuario)."',
					now(),
					'".trim($habilitado)."',
					'".trim($cartaref)."',
					'".trim($tipocli)."'
				       )";

		$sqlca->query($sql);

		return 1;		
	} 

	function editarDescuento($cliente, $articulo, $precio, $inicio, $fin, $habilitado, $cartaref, $tipocli, $usuario) {
		global $sqlca;

		$sql = "UPDATE 
				fac_precios_clientes
			SET	
				nu_preciopactado = ".$precio.",
				habilitado = ".$habilitado.",
				ch_cartaref = '".$cartaref."',
				ch_tipo_cliente = '".$tipocli."',
				ch_usuario = '".$usuario."',
				dt_fecha_actualizacion = now(),
				dt_fecha_fin = to_date('".$fin."', 'DD/MM/YYYY')
			WHERE
				ch_codigo_cliente_grupo = '".$cliente."'
				AND art_codigo = '".$articulo."' 
				AND dt_fecha_inicio=to_date('".$inicio."', 'DD/MM/YYYY');";

		$sqlca->query($sql);

		return 1;
	} 

	function eliminarCodigo($cliente, $articulo, $inicio, $fin) {
		global $sqlca;

		$query = "	DELETE FROM fac_precios_clientes 
				WHERE 
					ch_codigo_cliente_grupo='".$cliente."' 
					AND art_codigo='".$articulo."'
					AND dt_fecha_inicio=to_date('".$inicio."', 'DD/MM/YYYY')
					AND dt_fecha_fin=to_date('".$fin."', 'DD/MM/YYYY');";
		$sqlca->query($query);

		return 1;
 	}		
}
