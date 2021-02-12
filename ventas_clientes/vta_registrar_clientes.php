<?php
include("../valida_sess.php");
include("store_procedures.php");
include("../functions.php");
include_once("/sistemaweb/start_connection.inc");
global $sqlca;

if ($campo_busqueda == "") {
    $like = "null";
}
if ($consulta == "") {
    $consulta = "clientes_vales_xd";
}
if ($accion == "") {
    $accion = "Buscar";
}

switch ($accion) {
    	case "Buscar":

        	if ($campo_busqueda == "") {
            		$q = "	SELECT 
            				cab.ch_cliente as ch_cliente, 
			             	cli.cli_razsocial as cli_razsocial, 
			             	sum(cab.nu_importe) as importe
			      	FROM 
			           	val_ta_cabecera cab, 
			           	int_clientes cli
			      	WHERE 
			           	cli.cli_codigo=cab.ch_cliente
			      		AND (cab.ch_liquidacion is null or cab.ch_liquidacion='')
			      		AND cab.dt_fecha>=to_date('$c_fec_desde','dd/mm/yyyy')
			      		AND cab.dt_fecha<=to_date('$c_fec_hasta','dd/mm/yyyy')
			      	GROUP BY 
			      		cab.ch_cliente, cli.cli_razsocial 
			      	ORDER BY 
			      		ch_cliente";
        	} else {
            		switch ($consulta) {
                		case "clientes_vales_xc":

                    			$q = "	SELECT 
			             			cab.ch_cliente as ch_cliente, 
			             			cli.cli_razsocial  as cli_razsocial, 
			             			sum(cab.nu_importe) as importe
			      			FROM 
			           			val_ta_cabecera cab, 
			           			int_clientes cli
			     			WHERE 
			           			cli.cli_codigo=cab.ch_cliente
			      				AND (cab.ch_liquidacion is null or cab.ch_liquidacion='')
			      				AND cli.cli_codigo like '%$campo_busqueda%'
			      				AND cab.dt_fecha>=to_date('$c_fec_desde','dd/mm/yyyy')
			      				AND cab.dt_fecha<=to_date('$c_fec_hasta','dd/mm/yyyy')
			      				GROUP BY cab.ch_cliente,cli.cli_razsocial
			      			ORDER BY 
			      				ch_cliente";

                    			break;

                		case "clientes_vales_xd":

                   			$q = "	SELECT 
			             			cab.ch_cliente as ch_cliente, 
			             			cli.cli_razsocial  as cli_razsocial, 
			             			sum(cab.nu_importe) as importe
			      			FROM 
			           			val_ta_cabecera cab, 
			           			int_clientes cli
			      			WHERE 
			          			cli.cli_codigo=cab.ch_cliente
			      				AND (cab.ch_liquidacion is null or cab.ch_liquidacion='')
			      				AND cli.cli_razsocial like '%$campo_busqueda%'
			      				AND cab.dt_fecha>=to_date('$c_fec_desde','dd/mm/yyyy')
			      				AND cab.dt_fecha<=to_date('$c_fec_hasta','dd/mm/yyyy')
			      			GROUP BY 
			      				cab.ch_cliente, 
			               			cli.cli_razsocial
			      			ORDER BY 
			      				ch_cliente";
                    			break;
            		}
        	}
        	//echo $q;
        	$sqlca->query($q, '_lista');

        	break;

	case "Agregar":

        	for ($i = 0; $i < count($clientes_registrados); $i++) {
            		$query = "DELETE FROM vales_temp WHERE ch_cliente='" . $clientes_registrados[$i] . "'; ";

            		$sqlca->query($query);

            		$query = "SELECT
					cab.ch_cliente,
					cab.ch_documento,
					det.nu_importe,
					cli.cli_anticipo,
					cli.cli_fpago_credito,
					cli.cli_ruc,
					cab.dt_fecha, 
					cab.ch_sucursal,
					cab.ch_liquidacion,
					coalesce(cli.cli_mantenimiento,0) as cli_mantenimiento,
					sum(det.nu_cantidad) as nu_cantidad,
					det.ch_articulo as art_codigo 
				FROM 
					val_ta_cabecera cab 
					LEFT JOIN int_clientes cli ON (cli.cli_codigo=cab.ch_cliente) 
					LEFT JOIN val_ta_detalle det ON (det.ch_sucursal=cab.ch_sucursal AND det.dt_fecha=cab.dt_fecha AND det.ch_documento=cab.ch_documento)
				WHERE 
					cab.ch_cliente = '" . $clientes_registrados[$i] . "' 
					AND cab.dt_fecha BETWEEN to_date('" . $c_fec_desde . "', 'DD/MM/YYYY') AND to_date('" . $c_fec_hasta . "', 'DD/MM/YYYY')
					AND cab.ch_liquidacion is null 
				GROUP BY 
					1,2,3,4,5,6,7,8,9,10,12  
				ORDER BY 
					cab.ch_documento;";

            		$sqlca->query($query, '_lcabecera');
       
            		for ($y = 0; $y < $sqlca->numrows('_lcabecera'); $y++) {          
                		$Val = $sqlca->fetchRow('_lcabecera');        

                		$ch_documento 		= $Val["ch_documento"];
                		$ch_cliente 		= $Val["ch_cliente"];
                		$cli_anticipo 		= $Val["cli_anticipo"];
                		$nu_importe 		= $Val["nu_importe"];
                		$nu_cantidad 		= $Val["nu_cantidad"];
                		$art_codigo 		= $Val["art_codigo"];
                		$nroliquidcion 		= "";
                		$cli_fpago_credito 	= $Val["cli_fpago_credito"];
                		$cli_ruc 		= $Val["cli_ruc"];
                		$dt_fecha 		= $Val["dt_fecha"];
                		$ch_sucursal 		= $Val["ch_sucursal"];
                		$cli_mantenimiento 	= $Val["cli_mantenimiento"];

                		$resultado_desde = explode("/", $c_fec_desde);
                		$desde = $resultado_desde[2] . "/" . $resultado_desde[1] . "/" . $resultado_desde[0];

                		$resultado_hasta = explode("/", $c_fec_hasta);
                		$hasta = $resultado_hasta[2] . "/" . $resultado_hasta[1] . "/" . $resultado_hasta[0];

                		$resultado_liqui = explode("/", $c_fec_liquidacion);

                		$fec_liquidacion = $resultado_liqui[2] . "/" . $resultado_liqui[1] . "/" . $resultado_liqui[0];

                		$query = "INSERT INTO 
		                          		vales_temp 
		              			VALUES 
		              			(
				              		'" . $ch_cliente . "', 
				              		'" . $ch_documento . "',
				              		" . $nu_importe . ",
				              		" . $nu_cantidad . ",
				              		'" . $art_codigo . "', 
	  		                      		'" . $cli_anticipo . "',
	   		                     		'" . $nroliquidcion . "',
	   		                      		'" . $fec_liquidacion . "',
							'" . $cli_fpago_credito . "',
							'" . $cli_ruc . "','" . $dt_fecha . "',
							'" . $ch_sucursal . "',
							'" . $desde . "',
							'" . $hasta . "',
							" . $cli_mantenimiento . ");";

                		$sqlca->query($query, '_insert');
            		}
		}
        	break;

    	case "Eliminar":
        	if ($_REQUEST["items"]) {
            		for ($i = 0; $i < count($items); $i++) {
                		$query = "DELETE FROM vales_temp WHERE ch_cliente='" . $items[$i] . "'; ";
                		$sqlca->query($query);                
            		}
       		} else {
            		for ($i = 0; $i < count($items2); $i++) {                
                		$items3 = explode('|', $items2[$i]);
                		$query = "DELETE FROM vales_temp WHERE ch_nro_vale='" . $items3[0] . "' AND nu_importe = " . $items3[1] . " ";
		                $sqlca->query($query);
            		}
       		}
        	break;

    	case "Terminar":
        	print "<script>window.close();</script>";
        	break;

    	case "Cancelar":
        	$query = "DELETE FROM vales_temp;";
        	$sqlca->query($query);
        	break;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript">
function mandarDatos(form,opt){
	form.accion.value=opt;
	form.submit();
}

function hallarSubTotal(fn,valor){
	var newvalor;
	var valval = document.getElementsByName('subtotal')[0];
	f = document.getElementById(fn);
	if (f.checked)
		newvalor = parseFloat(valval.value) - valor;
	else
		newvalor = parseFloat(valval.value) + valor;
	valval.value =Math.round(newvalor*100)/100;
}
</script>


<link type="text/css" href="../sistemaweb.css" rel="stylesheet" >
<title>REGISTRAR CLIENTES</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<form name="form1" method="post">
Buscar clientes 
<table width="75%" border="1">
	<tr> 
		<td width="23%"><div align="right"> 
			<input type="text" name="campo_busqueda">
                        </div></td>
                <td width="43%"><input type="button" name="btn_buscar" value="Buscar" onClick="javascript:mandarDatos(form1,'Buscar');">
                <input type="hidden" name="accion"> <label></label></td>
                <td width="34%">&nbsp;</td>
	</tr>
	<tr> 
                <td colspan="2">Por Descripcion 
                <input type="radio" name="consulta" value="clientes_vales_xd" >
                - Por Codigo 
                <input type="radio" name="consulta" value="clientes_vales_xc"></td>
                <td>&nbsp;</td>
        </tr>
        <tr> 
		<td colspan="2">
		<select id="clientes_registrados" name="clientes_registrados[]" size="7" multiple>
			<?php
			if ($accion == "Buscar") {
				for ($i = 0; $i < $sqlca->numrows('_lista'); $i++) {
					$A = $sqlca->fetchRow('_lista');
				?>
			<option value="<?php echo $A[0]; ?>"><?php echo $A[0] . "  " . $A[1] . " Monto " . $A[2]; ?></option>
			<?php
	    			}
			}
			?>
		</select> </td>
                <td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2"><input type="button" name="btn_agregar" value="Agregar" onClick="javascript:mandarDatos(form1,'Agregar');">
                <input type="button" name="btn_terminar" value="Terminar" onClick="javascript:mandarDatos(form1,'Terminar');">
                <input type="button" name="btn_cancelar" value="Cancelar" onClick="javascript:mandarDatos(form1,'Cancelar');"> 
                </td>
                <td>&nbsp;</td>
       	</tr>
</table>
 <table width="75%" border="1">
	<tr> 
		<td width="24%">Codigo de Cliente</td>
		<td width="50%">Descripcion</td>
		<td width="26%">Marcar</td>
	</tr>
                <?php
                $query = "SELECT 
                		tv.ch_cliente,
				pc.cli_razsocial,
                   		sum(tv.nu_importe) as importe
            		FROM 
                  		vales_temp tv,
                  		int_clientes pc
            		WHERE 
            			tv.ch_cliente = pc.cli_codigo
            		GROUP BY 
            			tv.ch_cliente,pc.cli_razsocial;";

                $sqlca->query($query, '_listado');

                for ($i = 0; $i < $sqlca->numrows('_listado'); $i++) {
                    $A = $sqlca->fetchRow('_listado');
                    ?>
                    <tr> 
                        <td><input type="text" name="cod_cliente[]" value="<?php echo $A[0]; ?>"></td>
                        <td><input type="text" name="desc_cliente[]" value="<?php echo trim($A[1]) . " Importe " . $A[2]; ?>" size="100"></td>
                        <td><input type="checkbox" name="items[]" value="<?php echo $A[0]; ?>"></td>
                    </tr>
                    <?php
                    $query = "	SELECT 
                    			ch_nro_vale,
                    			nu_importe,
                    			to_char(dt_fecha,'dd/mm/YYYY'),
                    			nu_cantidad,
                    			art_codigo    
              			FROM 
                  			vales_temp 
              			WHERE 
                  			ch_cliente = '" . $A[0] . "'
              				ORDER BY dt_fecha, ch_nro_vale ;";
                    $c = 1;
                    $sqlca->query($query, '_listado_vista');

                    for ($y = 0; $y < $sqlca->numrows('_listado_vista'); $y++) {
                        $D = $sqlca->fetchRow('_listado_vista');
                        ?>
                        <tr>
                            <td><b>   VALE NUMERO : <?php echo  $c ?></b></td>
                            <td>
                                <table border="0">
                                    <tr>
                                        <td><input type="text" name="cod_vale[]" size="15" value="<?php echo  trim($D[0]) ?>" readonly="true"></td>
                                        <td><input type="text" name="fecvale[]" size="10" value="<?php echo  $D[2] ?>" readonly="true"></td>
                                        <td><input type="text" name="artvale[]" size="15" value="<?php echo  $D[4] ?>" readonly="true"></td>
                                        <td><input type="text" name="canvale[]" size="10" value="<?php echo  $D[3] ?>" readonly="true"></td>
                                        <td><input type="text" name="monto_vale[]" size="10" value="<?php echo  $D[1] ?>" readonly="true"></td>
                                    </tr>
                                </table>
                            </td>
                            <td><input type="checkbox" name="items2[]" id="chkbox<?php echo $y; ?>" onClick="javascript:hallarSubTotal('chkbox<?php echo $y; ?>',<?php echo $D[1]; ?>);" value="<?php echo  $D[0] . "|" . $D[1] ?>"></td>
                        </tr>
                        <?php
                        $c++;
                    }//Fin for #2
                }//Fin for #1
                ?>
                <tr> 
                    <td>&nbsp;</td>
                    <td><input type="text" name="subtotal" id="subtotal" value="<?php echo $A[2]; ?>"  readonly="true"></td>
                    <td>&nbsp;</td>
                </tr>
                <tr> 
                    <td>&nbsp;</td>
                    <td><input type="button" name="btn_terminar" value="Terminar" onClick="javascript:mandarDatos(form1,'Terminar');">
                        <input type="button" name="btn_cancelar" value="Cancelar" onClick="javascript:mandarDatos(form1,'Cancelar');"></td>
                    <td><input type="button" name="btn_eliminar" value="Eliminar" onClick="javascript:mandarDatos(form1,'Eliminar');"></td>
                </tr>
            </table>
        </form>
    </body>
</html>
<?php $sqlca->db_close(); ?>
