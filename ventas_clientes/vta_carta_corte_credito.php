<?php
//include "../valida_sess.php";
//include "../config.php";
//include "inc_top.php";
include "../menu_princ.php";
include "include/functions.inc.php";

if (isset($_REQUEST['fecha'])) $fecha = $_REQUEST['fecha'];
else $fecha = date("Y-m-d");

/*************** COMPROBACION DE FLAGS PARA DESHABILITAR/HABILITAR CONTROLER ******************************/
if (trim($_REQUEST['cod_cliente']) != "" && trim($_REQUEST['fecha']) != "") { 
    $bEnableInput = false; 
    $bShowResult = true;
}
else {
    $bEnableInput = true; 
    $bShowResult = false;
}
/******************* FIN DE COMPROBACION DE FLAGS **************************************/

function mostrarListaDeEmpresa() {
    global $fecha;
    
    $documentos = obtieneDocumentosVencidosPorCliente($_REQUEST['cod_cliente'], $fecha);
?>
	    <form name="documentos" method="get" action="generar_carta_corte_credito.php" target="_blank">
		<input type="hidden" name="action" value="porcliente">
		<input type="hidden" name="fecha" value="<?php echo htmlentities($fecha); ?>">
		<input type="hidden" name="codigo" value="<?php echo htmlentities($_REQUEST['cod_cliente']); ?>">
		<table width="615px">
    		    <tr>
			<td width="70px">
	    		    N&uacute;mero
			</td>
			<td width="60px">
	    		    Vencimiento
			</td>
			<td width="50px">
	    		    Moneda
			</td>
			<td width="60px" align="right">
	    		    Importe
			</td>
			<td width="10px">
			    &nbsp;
			</td>
			<td width="60px" align="right">
	    		    Saldo
			</td>
			<td width="10px">
			    &nbsp;
			</td>
			<td width="280px">
	    		    Raz&oacute;n social
			</td>
			<td width="15px">
			    &nbsp;
			</td>
		    </tr>
<?php
    for ($i = 0; $i < count($documentos); $i++) {
?>
		    <tr>
			<td>
			    <?php echo $documentos[$i]['numero']; ?>
			</td>
			<td>
	    		    <?php echo $documentos[$i]['vencimiento']; ?>
			</td>
			<td>
	    		    <?php echo $documentos[$i]['moneda']; ?>
			</td>
			<td align="right">
	    		    <?php echo $documentos[$i]['importe']; ?>
			</td>
			<td>
			</td>
			<td align="right">
	    		    <?php echo $documentos[$i]['saldo']; ?>
			</td>
			<td>
			</td>
			<td>
			    <?php echo trim($documentos[$i]['nombre']); ?>
			</td>
			<td>
			    <input type="checkbox" name="indices[]" value="<?php echo $documentos[$i]['numero'];?>" checked>
			</td>
		    </tr>
<?php
    }
    
    if (count($documentos) == 0) {
?>
		    <tr>
			<td colspan="9">
			    <b>No se encontraron documentos vencidos</b>
			</td>
		    </tr>
		</table>
		<input type="button" name="regresar" value="<- Regresar" onClick="javascript:regresa()">
	    </form>
<?php
    }
    else {
?>
		</table>
		<input type="button" name="regresar" value="<- Regresar" onClick="javascript:regresa()">
		<input type="submit" name="imprimir" value="Imprimir carta">
	    </form>
<?php
    }
}

function mostrarListaGeneral() {
    global $fecha;
    $deudas = obtieneDeuda($fecha);
?>
	    <form name="deudas" method="get" action="generar_carta_corte_credito.php" target="_blank">
		<input type="hidden" name="action" value="masivo">
		<input type="hidden" name="fecha" value="<?php echo htmlentities($fecha); ?>">
		<table width="615px">
		    <tr>
			<td width="50px" align="right">
			    Monto total
			</td>
			<td width="10px">
			</td>
			<td width="70px" align="right">
			    Saldo de deuda
			</td>
			<td width="10px">
			</td>
			<td width="*">
			    Razon Social
			</td>
			<td width="20px">
			</td>
		    </tr>
<?php
    for ($i = 0; $i < count($deudas); $i++) {
?>
		    <tr>
			<td align="right">
			    <?php echo $deudas[$i]['deuda']; ?>
			</td>
			<td>
			</td>
			<td align="right">
			    <?php echo $deudas[$i]['saldo']; ?>
			</td>
			<td>
			</td>
			<td>
			    <a href="vta_carta_corte_credito.php?cod_cliente=<?php echo trim($deudas[$i]['codigo']); ?>&fecha=<?php echo $fecha; ?>"><?php echo $deudas[$i]['nombre']; ?></a>
			</td>
			<td>
			    <input type="checkbox" name="indices[]" value="<?php echo trim($deudas[$i]['codigo']); ?>" checked>
			</td>
		    </tr>
<?php
    }
    
    if (count($deudas) == 0) {
?>
		    <tr>
			<td colspan="6">
			    <b>No se encontraron documentos vencidos.</b>
			</td>
		    </tr>
<?php
    }
?>
		</table>
		<input type="submit" name="imprimir" value="Imprimir cartas">

	    </form>
<?php
}

?>
<script language="JavaScript">
<!--
function regresa() {
    parent.location.href="/sistemaweb/ventas_clientes/vta_carta_corte_credito.php?fecha=<?php echo htmlentities($fecha); ?>";
}

function imprimir() {
    window.open("generar_carta_corte_credito.php?codigo=<?php echo htmlentities($_REQUEST['cod_cliente']); ?>&fecha=<?php echo htmlentities($_REQUEST['fecha']); ?>", "imprimir", "scrollbar=yes, menubar=no, width=700, height=500");
}
//-->
</script>
<script language="JavaScript" src="js/miguel.js"></script>
<br>
<table width="620px">
    <tr>
	<td width="100%">
	    <form name="cliente" method="get">
		<table width="615px">
    		    <tr>
    			<td width="100%">
    			    C&otilde;digo de cliente: <input type="text" size="12" onblur="" name="cod_cliente" value="<?php echo htmlentities($_REQUEST['cod_cliente']); ?>" <?php if (!$bEnableInput) echo "disabled"; ?>>
    	    		    <img src="../images/help.gif"<?php if ($bEnableInput) { echo " onClick=\"javascript:mostrarAyuda('lista_ayuda.php','cliente.cod_cliente','cliente.nomb_cliente','clientes')\""; } ?>>
    			    <br>	
	    		    Nombre del cliente:<input type="text" size="40" name="nomb_cliente" value="<?php echo obtieneRazonSocialPorCodigo($_REQUEST['cod_cliente']); ?>" disabled>
	    		    <br>
	    		    Fecha:<input type="text" size="10" name="fecha" value="<?php echo htmlentities($fecha); ?>" <?php if (!$bEnableInput) echo "disabled"; ?>><br>
	    		    <input type="submit" name="buscar"<?php if (!$bEnableInput) echo " onClick=\"javascript:void()\""; ?>>
			</td>
		    </tr>
		</table>
	    </form>
<?php
    if ($bShowResult) mostrarListaDeEmpresa();
    else mostrarListaGeneral();
?>
	</td>
    </tr>
</table>
</body>
</html>
