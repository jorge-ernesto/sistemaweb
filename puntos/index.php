<?php
date_default_timezone_set('America/Lima');

include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");
include_once("/sistemaweb/puntos/m_targpromocion.php");
include_once("/sistemaweb/include/paginador_new.php");
include_once("/sistemaweb/puntos/m_canjeitem.php");
include_once("/sistemaweb/puntos/m_movpuntos.php");
include_once("/sistemaweb/puntos/m_movpuntosfideliza.php");
include_once("/sistemaweb/puntos/m_canjes.php");
include_once("/sistemaweb/puntos/m_rankingpuntosacumulados.php");
include_once("/sistemaweb/puntos/m_retenciones.php");
include_once("/sistemaweb/puntos/m_reporte_numtransfideliza.php");
include_once("/sistemaweb/puntos/m_puntosxproducto.php");
include_once("/sistemaweb/puntos/m_consultacanjes.php");
include_once("/sistemaweb/puntos/m_campaniafide.php");
include_once("/sistemaweb/puntos/m_horariomulti.php");
include_once("/sistemaweb/puntos/m_tiposcuenta.php");
include_once("/sistemaweb/puntos/m_puntosfidelizamanual.php");
include_once("/sistemaweb/puntos/m_acumulacion.php");
include_once("/sistemaweb/puntos/m_descuentos_fide.php");

global $db_host, $db_user, $db_password, $db_name;
$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

switch ($_REQUEST['action']) {
    case 'canjes':
		switch ($_REQUEST['proc']) {

	    		case 'obtenerDatos':
				$res = CanjesModel::obtenerDatos($_REQUEST['tarjeta']);
				echo serialize($res);
				break;

	    		case 'obtenerArticulosCanje':
				$res = CanjesModel::obtenerArticulosCanje($_REQUEST['tarjeta']);
				echo serialize($res);
				break;

	    		case 'realizarCanje':
				$res = CanjesModel::realizarCanje($_REQUEST['tarjeta'], 
								  $_REQUEST['id_item'], 
							 	  $_REQUEST['observacion'], 
								  $_REQUEST['sucursal'], 
								  $_REQUEST['usuario'], 
								  $_REQUEST['razsocial1'], 
								  $_REQUEST['razsocial2']);
				echo serialize($res);
				break;

	    		case 'obtenerTipoCuenta':
				$res = CanjesModel::realizarCanje($_REQUEST['tarjeta'], 
								  $_REQUEST['id_item'], 
								  $_REQUEST['observacion'], 
								  $_REQUEST['sucursal'], 
							          $_REQUEST['usuario'], 
								  $_REQUEST['razsocial1'], 
							          $_REQUEST['razsocial2']);
				echo serialize($res);
				break;
		}
		break;

	case 'consultacanjes':
		switch ($_REQUEST['proc']) {

	    		case 'tmListado':
				$res = ConsultaCanjesModel::tmListado(	$_REQUEST['almacen'],
									$_REQUEST['numerotarjeta'],
									$_REQUEST['descitem'],
									$_REQUEST['fechaini'],
									$_REQUEST['fechafin'],
									$_REQUEST['pp'],
									$_REQUEST['pagina']);
				echo serialize($res);
				break;

	    		default:
				echo "error";
				break;
		}
		break;
	
	case 'movpuntos':
		switch ($_REQUEST['proc']) {

	    		case 'obtenerCuentaxTarjeta':
				$res = MovpuntosModel::obtenerCuentaxTarjeta($_REQUEST['campovalor'],$_REQUEST['tipocampo']);
				echo serialize($res);
				break;

	   		case 'obtenerTarjeta':
				$res = MovpuntosModel::obtenerTarjeta($_REQUEST['campovalor'],$_REQUEST['tipocampo']);
				echo serialize($res);
				break;

	    		case 'tmListado':
				$res = MovpuntosModel::tmListado($_REQUEST['numerotarjeta'],
								 $_REQUEST['fechaini'],
								 $_REQUEST['fechafin'],
								 $_REQUEST['pp'],
								 $_REQUEST['pagina']);
				echo serialize($res);
				break;

	    		case 'tmResumen':
				$res = MovpuntosModel::tmResumen($_REQUEST['numerotarjeta'],
								 $_REQUEST['fechaini'],
								 $_REQUEST['fechafin']);
				echo serialize($res);
				break;

	    		default:
				echo "error";
				break;
		}
		break;

	case 'movpuntosfideliza':
		switch ($_REQUEST['proc']) {
			case 'tmListado':
				$res = MovPuntosFidelizaModel::tmListado($_REQUEST['almacen'],
									$_REQUEST['numveces'],
									$_REQUEST['fechainicio'],
									$_REQUEST['fechafin'],
									$_REQUEST['ruc'],
									$_REQUEST['pp'],
									$_REQUEST['pagina']);
			echo serialize($res);
			break;

		default:
			echo "error";
			break;
		}
	break;

	case 'reporte_numtransfideliza':
		switch ($_REQUEST['proc']) {
			case 'tmListado':
				$res = Reporte_NumTransFidelizaModel::tmListado($_REQUEST['almacen'],
										$_REQUEST['turno'],
										$_REQUEST['fechainicio'],
										$_REQUEST['fechafin'],
										$_REQUEST['pp'],
										$_REQUEST['pagina']);
			echo serialize($res);
			break;

		default:
			echo "error";
			break;
	}
	break;

	case 'retenciones':
		switch ($_REQUEST['proc']) {
			case 'tmListado':
				$res = RetencionesModel::tmListado($_REQUEST['fechainicio'],
								$_REQUEST['fechafin'],
								$_REQUEST['pp'],
								$_REQUEST['pagina']);
			echo serialize($res);
			break;

		case 'liberaRetencion':
			$res = RetencionesModel::liberaRetencion($_REQUEST['id']);
			echo serialize($res);
			break;

		default:
			echo "error";
			break;
	}
	break;

	case 'rankingpuntosacumulados':
		switch ($_REQUEST['proc']) {
			case 'tmListado':
				$res = RankingPuntosAcumuladosModel::tmListado(
									$_REQUEST['fechainicio'],
									$_REQUEST['fechafin'],
									$_REQUEST['sucursal'],
									$_REQUEST['pp'],
									$_REQUEST['pagina'],
									$_REQUEST['estado']);

			echo serialize($res);
			break;
			case 'listarDetalleMovimientos':
				$res = RankingPuntosAcumuladosModel::listarDetalleMovimientos(
									$_REQUEST['iAlmacen'],
									$_REQUEST['dIni'],
									$_REQUEST['dFin'],
									$_REQUEST['iCuenta'],
									$_REQUEST['iTarjeta']);

			echo serialize($res);
			break;

		default:
			echo "error";
			break;
	}
	break;

    	case 'itemscanje':
		switch ($_REQUEST['proc']) {
	    		case 'ingresarItem':
				$res = CanjeitemModel::ingresarItem($_REQUEST['campana'],
								$_REQUEST['codarticulo'],
								$_REQUEST['descripcion'],
								$_REQUEST['fechaven'],
								$_REQUEST['puntos'],
								$_REQUEST['observacion'],
								$_REQUEST['usuario'],
								$_REQUEST['sucursal']);
				echo serialize($res);
				break;

			case 'actualizarItem':
				$res = CanjeitemModel::actualizarItem($_REQUEST['campana'],
								$_REQUEST['iditem'],
								$_REQUEST['codarticulo'],
								$_REQUEST['descripcion'],
								$_REQUEST['fechaven'],
								$_REQUEST['puntos'],
								$_REQUEST['observacion'],
								$_REQUEST['usuario'],
								$_REQUEST['sucursal']);
				echo serialize($res);
				break;

	    		case 'eliminarItem':
				$res = CanjeitemModel::eliminarItem($_REQUEST['iditem']);
				echo serialize($res);
				break;

			case 'listarItems':
				$res = CanjeitemModel::listarItems($_REQUEST['filtro']);
				echo serialize($res);
				break;

	    		case 'listarArticulos':
				$res = CanjeitemModel::listarArticulos($_REQUEST['filtro']);
				echo serialize($res);
				break;

	    		case 'obtenerArticulo':
				$res = CanjeitemModel::obtenerArticulo($_REQUEST['campovalor'],$_REQUEST['tipocampo']);
				echo serialize($res);
				break;

	    		case 'tmListado':
				$res = CanjeitemModel::tmListado($_REQUEST['filtro'],$_REQUEST['tipo'],$_REQUEST['pp'],$_REQUEST['pagina']);
				echo serialize($res);
				break;

	    		default:
				echo "error";
				break;
		}
		break;

    	case 'tarjetascuentas':
		switch ($_REQUEST['proc']) {

	    		case 'ingresarCuenta':
				$existe = TargpromocionModel::validarDNI($_REQUEST['dni']);
				if($existe == 0){
					$res = TargpromocionModel::ingresarCuenta($_REQUEST['numero'],$_REQUEST['nombres'],$_REQUEST['apellidos'],$_REQUEST['vip'],$_REQUEST['fechanacimiento'],$_REQUEST['dni'],$_REQUEST['ruc'],$_REQUEST['direccion'],$_REQUEST['telefono'],$_REQUEST['telefono2'],$_REQUEST['email'],$_REQUEST['tipocuenta'],$_REQUEST['puntos'],$_REQUEST['usuario'],$_REQUEST['almacen']);
				} else {
					$res = false;
				}
				echo serialize($res);
				break;

	    		case 'actualizarCuenta':
				$res = TargpromocionModel::actualizarCuenta($_REQUEST['idcuenta'],$_REQUEST['numero'],$_REQUEST['nombres'],$_REQUEST['apellidos'],$_REQUEST['vip'],$_REQUEST['fechanacimiento'],$_REQUEST['dni'],$_REQUEST['ruc'],$_REQUEST['direccion'],$_REQUEST['telefono'],$_REQUEST['telefono2'],$_REQUEST['email'],$_REQUEST['tipocuenta'],$_REQUEST['estado'],$_REQUEST['usuario']);
				echo serialize($res);
				break;

	    		case 'obtenerCuenta':
				$res = TargpromocionModel::obtenerCuenta($_REQUEST['campovalor'],$_REQUEST['tipocampo']);
				echo serialize($res);
				break;

	    		case 'modificarTarjeta':
				$res = TargpromocionModel::modificarTarjeta($_REQUEST['idtarjeta'],$_REQUEST['numero'],$_REQUEST['descripcion'],$_REQUEST['placa'],$_REQUEST['fechaven'],
				$_REQUEST['flatcuenta'],$_REQUEST['flattitular'],$_REQUEST['usuario'],$_REQUEST['motivocambio'],$_REQUEST['estacion'],$_REQUEST['id_motivo_duplicada']);
				echo serialize($res);
				break;

	    		case 'insertarTarjeta':
				$res = TargpromocionModel::insertarTarjeta($_REQUEST['idcuenta'],$_REQUEST['numero'],$_REQUEST['descripcion'],$_REQUEST['placa'],
				$_REQUEST['fechaven'],$_REQUEST['puntos'],$_REQUEST['flatcuenta'],$_REQUEST['flattitular'],$_REQUEST['usuario']);
				echo serialize($res);
				break;

	    		case 'eliminarTarjeta':
				$res = TargpromocionModel::eliminarTarjeta($_REQUEST['idcuenta'],$_REQUEST['idtarjeta']);
				echo serialize($res);
				break;

	    		case 'eliminarCuenta':
				$res = TargpromocionModel::eliminarCuenta($_REQUEST['idcuenta']);
				echo serialize($res);
				break;

	    		case 'listarTarjetas':
				$res = TargpromocionModel::listarTarjetas($_REQUEST['filtro'],$_REQUEST['tipo']);
				echo serialize($res);
				break;

	    		case 'listarTiposCuenta':
				$res = TargpromocionModel::listarTiposCuenta($_REQUEST['filtro'],$_REQUEST['tipo']);
				echo serialize($res);
				break;

	    		case 'listarMotivoDuplicada':
				$res = TargpromocionModel::listarMotivoDuplicada();
				echo serialize($res);
				break;

	    		case 'tmListado':
				$res = TargpromocionModel::tmListado($_REQUEST['filtro'],$_REQUEST['tipo'],$_REQUEST['pp'],$_REQUEST['pagina'],$_REQUEST['almacen']);
				echo serialize($res);
				break;

	    		case 'listarCambiosTarjetas':
				$res = TargpromocionModel::listarCambiosTarjetas($_REQUEST['numerotarjeta'],$_REQUEST['fecha1'],$_REQUEST['fecha2']);
				echo serialize($res);
				break;

	    		default:
				echo "error";
				exit;
		}
		break;
	
	case 'campaniafide':
		switch ($_REQUEST['proc']) {

			case 'nuevoIdCampania':
				$res = CampaniaFideModel::nuevoIdCampania();
				echo serialize($res);
				break;

	    		case 'ingresarCampania': 
				$res = CampaniaFideModel::ingresarCampania(
						$_REQUEST['idcampania'],
						$_REQUEST['campaniadescripcion'],
						$_REQUEST['campaniafechaini'],
						$_REQUEST['campaniafechafin'],
						$_REQUEST['campaniadiasven'],
						$_REQUEST['campaniaobjetivo'],
						$_REQUEST['usuario'],
						$_REQUEST['sucursal'],
						$_REQUEST['repeticiones'],
						$_REQUEST['slogan'],
						$_REQUEST['saludacumple']);
				echo serialize($res);
				break;

	    		case 'actualizarCampania':
				$res = CampaniaFideModel::actualizarCampania(
						$_REQUEST['idcampania'],
						$_REQUEST['campaniadescripcion'],
						$_REQUEST['campaniafechafin'],
						$_REQUEST['campaniadiasven'],
						$_REQUEST['campaniaobjetivo'],
						$_REQUEST['usuario'],
						$_REQUEST['sucursal'],
						$_REQUEST['repeticiones'],
						$_REQUEST['slogan'],
						$_REQUEST['saludacumple']);
				echo serialize($res);
				break;

			case 'obtenerCampania':
				$res = CampaniaFideModel::obtenerCampania($_REQUEST['idcampania']);
				echo serialize($res);
				break;

			case 'listarCampanias':
				$res = CampaniaFideModel::listarCampanias($_REQUEST['filtro']);
				echo serialize($res);
				break;

			case 'listarCampaniasTipo':
				$res = CampaniaFideModel::listarCampaniasTipo($_REQUEST['tipo'],$_REQUEST['filtro']);
				echo serialize($res);
				break;

	    		case 'listarTipoCuentas':
				$res = CampaniaFideModel::listarTipoCuentas($_REQUEST['filtro']);
				echo serialize($res);
				break;

	   		case 'tmListado':
				$res = CampaniaFideModel::tmListado($_REQUEST['filtro'],$_REQUEST['pp'],$_REQUEST['pagina']);
				echo serialize($res);
				break;

			case 'ingresarTipoCuenta':
				$res = CampaniaFideModel::ingresarTipoCuenta($_REQUEST['idcampania'],$_REQUEST['idtipocuenta']);
				echo serialize($res);
				break;		

	    		default:
				echo "error";
				break;
		}
		break;
		
	case 'horariomulti':
		switch ($_REQUEST['proc']) {

			case 'ingresarHorario':
				$res = HorarioMultiModel::ingresarHorario(
						$_REQUEST['idcampania'],
						$_REQUEST['descripcion'],
						$_REQUEST['diamulti'],
						$_REQUEST['horaini'],
						$_REQUEST['minutoini'],
						$_REQUEST['horafin'],
						$_REQUEST['minutofin'],
						$_REQUEST['factormulti'],
						$_REQUEST['usuario'],
						$_REQUEST['sucursal']);
				echo serialize($res);
				break;

			case 'actualizarHorario':
				$res = HorarioMultiModel::actualizarHorario(
						$_REQUEST['idhorariomulti'],
						$_REQUEST['idcampania'],
						$_REQUEST['descripcion'],
						$_REQUEST['diamulti'],
						$_REQUEST['horaini'],
						$_REQUEST['minutoini'],
						$_REQUEST['horafin'],
						$_REQUEST['minutofin'],
						$_REQUEST['factormulti'],
						$_REQUEST['usuario'],
						$_REQUEST['sucursal']);
				echo serialize($res);
				break;

			case 'eliminarHorario':
				$res = HorarioMultiModel::eliminarHorario($_REQUEST['idhorario']);
				echo serialize($res);
				break;

			case 'tmListado':
				$res = HorarioMultiModel::tmListado($_REQUEST['filtro'],$_REQUEST['pp'],$_REQUEST['pagina']);
				echo serialize($res);
				break;

			default:
				echo "error";
				break;
		}
		break;
	
	case 'puntosxproducto':
		switch ($_REQUEST['proc']) {

			case 'ingresarPuntosxProducto':
				$res = PuntosxProductoModel::ingresarPuntosxProducto(	
						$_REQUEST['idcampania'],
						$_REQUEST['idarticulo'],
						$_REQUEST['puntossol'],
						$_REQUEST['puntosunidad']);
				echo serialize($res);
				break;

			case 'actualizarPuntosxProducto':
				$res = PuntosxProductoModel::actualizarPuntosxProducto($_REQUEST['idcampania'],
						$_REQUEST['idarticulo'],
						$_REQUEST['puntossol'],
						$_REQUEST['puntosunidad']);
				echo serialize($res);
				break;

			case 'eliminarPuntosxProducto':
				$res = PuntosxProductoModel::eliminarPuntosxProducto($_REQUEST['idcampania'],
						$_REQUEST['idarticulo']);
				echo serialize($res);
				break;

			case 'tmListado':
				$res = PuntosxProductoModel::tmListado($_REQUEST['filtro'],$_REQUEST['tipo'],$_REQUEST['pp'],$_REQUEST['pagina']);
				echo serialize($res);
				break;

			default:
				echo "error";
				break;
		}
		break;

	case 'tiposcuenta':
		switch ($_REQUEST['proc']) {

			case 'ingresartiposcuenta':
				$res = TiposCuentaModel::ingresartiposCuenta(	
						$_REQUEST['idtipocuenta'],
						$_REQUEST['descripcion'],
						$_REQUEST['sucursal'], 
						$_REQUEST['usuario']);
				echo serialize($res);
				break;

			case 'actualizartiposcuenta':
				$res = TiposCuentaModel::actualizartiposCuenta(
						$_REQUEST['idtipocuenta'],
						$_REQUEST['descripcion']);
				echo serialize($res);
				break;

			case 'eliminartiposcuenta':
				$res = TiposCuentaModel::eliminartiposcuenta($_REQUEST['idtipocuenta']);
				echo serialize($res);
				break;

			case 'tmListado':
				$res = TiposCuentaModel::tmListado($_REQUEST['filtro'],$_REQUEST['tipo']);
				echo serialize($res);
				break;

			default:
				echo "error";
				break;
		}
		break;

	case 'puntosfidelizamanual':
		switch ($_REQUEST['proc']) {
			case 'ingresarpuntosfidelizamanual':
				$res = PuntosFidelizaManualModel::ingresarpuntosfidelizamanual($_REQUEST['tarjeta'],
						$_REQUEST['puntos'],
						$_REQUEST['sucursal'], 
						$_REQUEST['usuario']);
				echo serialize($res);
				break;

			case 'actualizarpuntosfidelizamanual':
				$res = PuntosFidelizaManualModel::actualizarpuntosfidelizamanual(
						$_REQUEST['idpunto'],
						$_REQUEST['puntos']);
				echo serialize($res);
				break;

			case 'eliminarpuntosfidelizamanual':
				$res = PuntosFidelizaManualModel::eliminarpuntosfidelizamanual($_REQUEST['idpunto']);
				echo serialize($res);
				break;

			case 'tmListado':
				$res = PuntosFidelizaManualModel::tmListado(
					$_REQUEST['almacen'],
					$_REQUEST['filtro1'],
					$_REQUEST['filtro2'],
					$_REQUEST['tipo'],
					$_REQUEST['pp'],
					$_REQUEST['pagina']
					);
				echo serialize($res);
				break;

			default:
				echo "error";
				break;
		}
		break;

    	case 'realizarCanje':
		$sqlca->query("BEGIN");
		break;

    	case 'obtenerTarjeta':
		$tarjeta = $_REQUEST['tarjeta'];
		$sql = "SELECT * FROM prom_ta_tarjetas WHERE nu_tarjeta_numero='" . pg_escape_string($tarjeta) . "';";
		if ($sqlca->query($sql) < 0) { 
			echo "error"; 
			exit; 
		}
		if ($sqlca->numrows() != 1) { 
			echo "error"; 
			exit; 
		}
		$row = $sqlca->fetchRow();
		echo serialize($row);
		break;

    	case 'obtenerCuenta':
		$cuenta = $_REQUEST['cuenta'];
		$sql = "SELECT * FROM prom_ta_cuentas WHERE nu_cuenta_numero='" . pg_escape_string($cuenta) . "';";
		if ($sqlca->query($sql) < 0) { 
			echo "error"; 
			exit; 
		}
		if ($sqlca->numrows() != 1) { 
			echo "error"; 
			exit; 
		}
		$row = $sqlca->fetchRow();
		echo serialize($row);
		break;

    	case 'insertarCuenta':
		break;

    	case 'insertarTarjeta':
		break;

    	case 'obtenerSaldoTarjeta':					// diferente a ultracom !!!!!
		$tarjeta = $_REQUEST['tarjeta'];
		settype($tarjeta,"integer");
		die(serialize(AcumulacionModel::obtenerMensajeSaldo($tarjeta)));
		break;

    	case 'obtenerSaldoCuenta':
		$cuenta = $_REQUEST['cuenta'];
		$sql = "SELECT nu_cuenta_puntos FROM prom_ta_cuentas WHERE nu_cuenta_numero='" . pg_escape_string($cuenta) . "';";
		if ($sqlca->query($sql) < 0) { 
			echo "error"; 
			exit; 
		}
		if ($sqlca->numrows() != 1) {
			echo "error"; 
			exit; 
		}
		$row = $sqlca->fetchRow();
		echo serialize($row['nu_cuenta_puntos']);
		break;

    	case 'obtenerPromocion':
		$codigo = $_REQUEST['codigo'];
		$sql = "SELECT * FROM prom_ta_items_canje WHERE art_codigo='" . pg_escape_string($codigo) . "';";
		if ($sqlca->query($sql) < 0) { 
			echo "error"; 
			exit; 
		}
		if ($sqlca->numrows() < 1) { 
			echo serialize(array()); 
			exit; 
		}
		$row = $sqlca->fetchRow();
		echo serialize($row);
		break;

    	case 'obtenerPromocionesPorCuenta':
		$puntajeMaximo = $_REQUEST['puntajemaximo'];
		$sql = "SELECT
		    		art_codigo,
		    		ch_item_descripcion,
		    		nu_item_puntos
			FROM
		    		prom_ta_items_canje
			WHERE
		    		nu_item_puntos<='" . pg_escape_string($puntajeMaximo) . "'
		    		AND dt_item_fecha_vencimiento>=now()
			ORDER BY
		    		nu_item_puntos;";

		$sqlca->query($sql);
		$i   = 0;
        	$res = array();
        	while ($registro = $sqlca->fetchRow()) {
            		$res[$i++] = $registro;
        	}
		echo serialize($res);
		break;

    	case 'registrarConsumo':
		$tarjeta = $_REQUEST['tarjeta'];
		$codigo = $_REQUEST['codigo'];
		$fecha = $_REQUEST['fecha'];
		$td = $_REQUEST['td'];
		$caja = $_REQUEST['caja'];
		$numero = $_REQUEST['numero'];
		$cantidad = $_REQUEST['cantidad'];
		$importe = $_REQUEST['importe'];
		$sucursal = $_REQUEST['sucursal'];

		settype($tarjeta,"integer");
		settype($cantidad,"float");
		settype($importe,"float");

		die(AcumulacionModel::acumulaPuntos($tarjeta,$codigo,$fecha,$td,$caja,$numero,$cantidad,$importe,$sucursal));
		break;

	case 'descuentos_fide':
		switch ($_REQUEST['proc']) {

			case 'ingresarDescuento':
				$res = DescuentosFideModel::ingresarDescuento(	
						$_REQUEST['ruc'],
						$_REQUEST['cod_articulo'],
						$_REQUEST['descuento'],
						$_REQUEST['inicio'], 
						$_REQUEST['fin']);
				echo serialize($res);
				break;

			case 'editarDescuento':
				$res = DescuentosFideModel::editarDescuento(
						$_REQUEST['id'],
						$_REQUEST['descuento'],
						$_REQUEST['fin']);
				echo serialize($res);
				break;

			case 'eliminarDescuento':
				$res = DescuentosFideModel::eliminarCodigo($_REQUEST['id']);
				echo serialize($res);
				break;

			case 'obtenerDescuentos':
				$res = DescuentosFideModel::obtenerDatos($_REQUEST['ruc']);
				echo serialize($res);
				break;

			default:
				echo "error";
				break;
		}
		break;

    	default:
		echo "error";
		exit;
}
