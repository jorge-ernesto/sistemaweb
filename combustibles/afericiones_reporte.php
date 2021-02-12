<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("/sistemaweb/utils/funcion-texto.php");
include("../functions.php");

require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id = $funcion->conectar("", "", "", "", "");

if (is_null($almacen) or trim($almacen) == "") {
    $almacen = "001";
}


// carga los almacenes en un dropdown
$v_xsqlalma = pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");
$almacen_nombre = pg_result($v_xsqlalma, 0, 1);

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta)) {
    $v_fecha_desde = date("d/m/Y");
    $v_fecha_hasta = date("d/m/Y");
}


//$comando="smbclient //".$v_server."/".$v_printer." -c 'print /tmp/imprimir/inv_kardex.txt' -P -N -I ".$v_ipprint." ";
//exec($comando);
$v_archivo = "/tmp/imprimir/afericion_1.txt";
$v_archivo2 = "/tmp/imprimir/afericion_2.txt";

$v_ilimit = 0;

if ($boton == 'Imprimir') {

    $v_sqlprn = "select es,pump,ch_nombrebreve,dia,fecha,veloc,lineas,trans from pos_ta_afericiones , comb_ta_combustibles
			where trim(pos_ta_afericiones.codigo)=trim(comb_ta_combustibles.ch_codigocombustible) and dia between '" . $funcion->date_format($v_fecha_desde, 'YYYY-MM-DD') . "' and '" . $funcion->date_format($v_fecha_hasta, 'YYYY-MM-DD') . "'
			order by es, pump, codigo, dia, fecha ";
    $v_xsqlprn = pg_exec($conector_id, $v_sqlprn);
    $v_ilimit = pg_numrows($v_xsqlprn);
}
?>
<html><link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
    <head>
        <title>sistemaweb</title>
        <script language="JavaScript" src="js/miguel.js"></script>
        <script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
        <script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
        <script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
        <script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
        <script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
        <!-- Añadido para la funcionalidad de impresión 
              Nestor Hernandez Loli 09/02/2012-->
        <script type = "text/javascript" src = "/sistemaweb/js/jquery-1.7.1.min.js"></script>
        <script type= "text/javascript">
            $(function() {
                $('#btnImprimir').click(function() {
                    $.get("../utils/impresiones.php?imprimir=lpr&archivo=/tmp/imprimir/afericion_2.txt");
                    alert('Imprimiendo');			
                });
            });
        </script>
        <script>
            function activa(){
                // carga de frente el formulario con el foco en diad
                document.f_repo.v_fecha_desde.select()
                document.f_repo.v_fecha_desde.focus()
            }

        </script>

    </head>

    <body onfocus="mueveReloj('f_repo.reloj'); activa()">
        <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

        <form name="f_repo" method="post">

            <div align="center"><font face="Arial, Helvetica, sans-serif">

                REPORTE CONTROL DE AFERICIONES Desde <?php echo $v_fecha_desde; ?> Hasta <?php echo $v_fecha_hasta; ?> <BR>
<?php
$v_sql = "select trim(ch_almacen), ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%" . trim($almacen) . "%' and ch_clase_almacen='1' ";
$v_xsql = pg_query($conector_id, $v_sql);
if (pg_numrows($v_xsql) > 0) {
    $v_descalma = pg_result($v_xsql, 0, 1);
}
?>

                ALMACEN ACTUAL <?php echo $almacen; ?> 	<?php echo $v_descalma; ?>
                <input type="text" name="reloj" size="10" style="background-color : Black; color : White; font-family : Verdana, Arial, Helvetica; font-size : 8pt; text-align : center;" onfocus="window.document.f_repo.reloj.blur()" >
            </div>


            <hr noshade>


<?php
if (is_null($v_almacen)) {
    $v_almacen = $almacen;
}

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta)) {
    $v_fecha_desde = date("d/m/Y");
    $v_fecha_hasta = date("d/m/Y");
}
?>

<table border="1">
	<tr>
		<th colspan="7">Reporte Por : RANGO DE FECHAS </th>
	</tr>
	<tr>
		<th>DESDE :</th>
		<th>
			<p>
				<input type="text" name="v_fecha_desde" size="16" maxlength="10" value='<?php echo $v_fecha_desde; ?>'  tabindex="1"  onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
				<a href="javascript:show_calendar('f_repo.v_fecha_desde');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
				<img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
			</p>
		</th>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th>HASTA:</th>
		<th>
			<p>
				<input type="text" name="v_fecha_hasta" size="16" maxlength="10" value='<?php echo $v_fecha_hasta; ?>'  tabindex="2" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)">
				<a href="javascript:show_calendar('f_repo.v_fecha_hasta');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
				<img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
			</p>
		</th>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th colspan="3"><input type="submit" name="boton" value="Imprimir"></th>
	</tr>
	<tr>
		<td colspan=2>
			<a href="#" id="btnImprimir"> Impresion Texto </a>
		</td>
		<td colspan=2>
			<a href="#" onClick="javascript:window.open('afericiones_exportar.php?v_fecha_desde=<?php echo $v_fecha_desde; ?>&v_fecha_hasta=<?php echo $v_fecha_hasta; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar </a>
		</td>
	</tr>
</table><br>

<?php
echo "<table width='990' border='2' cellspacing=0 height='81'>";
echo "<tr>";
echo "<th colspan=18 align='center'><font size='3.5'> <BR>
	DETALLE DE AFERICIONES Desde: " . $v_fecha_desde . "
	Hasta: " . $v_fecha_hasta . " <P></P></th>";
echo "</tr>";

echo "<tr>";
echo "	<td width='85'>Lado</td>";
echo "	<td width='106' align='center'>Producto</td>";
echo "	<td width='247'>Fecha / Hora</td>";
echo "	<td width='69' align='center'>Control</td>";
echo "</tr>";

$col[0] = 10;
$col[1] = 10;
$col[2] = 40;
$col[3] = 70;


$nom[0] = str_pad("Lado", $col[0]);
$nom[1] = str_pad("Producto", $col[1]);
$nom[2] = str_pad("Fecha/Hora", $col[2]);
$nom[3] = str_pad("Control", $col[3]);


$cabecera = "<table>";
$cabecera = $cabecera . "<tr><td>ALMACEN : " . $almacen . " - " . $v_descalma . "</td></tr>";
$cabecera = $cabecera . "<tr>";
$cabecera = $cabecera . "<td> DETALLE DE AFERICIONES Desde: " . $v_fecha_desde . " Hasta: " . $v_fecha_hasta . " </td>";
$cabecera = $cabecera . "</tr>";

$cabecera = $cabecera . "<tr>";
$cabecera = $cabecera . "<td>" . str_pad("Lado", $col[0]) . "</td>";
$cabecera = $cabecera . "<td>" . str_pad("Producto", $col[1]) . "</td>";
$cabecera = $cabecera . "<td>" . str_pad("Fecha/Hora", $col[2]) . "</td>";
$cabecera = $cabecera . "<td>" . str_pad("Control", $col[3]) . "</td>";
$cabecera = $cabecera . "</tr>";
$cabecera = $cabecera . "<tr><td>" . str_pad("-", $col[0] + $col[1] + $col[2] + $col[3] + 3, "-", STR_PAD_LEFT) . "</td></tr>";


$linea = "";

$v_irow = 0;

$v_clave = " ";

if ($v_ilimit > 0) {
    while ($v_irow < $v_ilimit) {
        $a0 = pg_result($v_xsqlprn, $v_irow, 0);
        $a1 = pg_result($v_xsqlprn, $v_irow, 1);
        $a2 = pg_result($v_xsqlprn, $v_irow, 2);
        $a3 = pg_result($v_xsqlprn, $v_irow, 3);
        $a4 = pg_result($v_xsqlprn, $v_irow, 4);

        echo "<tr>";
        echo "<th align='left' >" . $a1 . " </th>";
        echo "<th align='left' >" . $a2 . " </th>";
        echo "<th align='left' >" . $a3 . " " . $a4 . " </th>";
        echo "<td>";
        $linea = $linea . "<tr>";
        $linea = $linea . "<td>" . str_pad($a1, $col[0]) . "</td>";
        $linea = $linea . "<td>" . str_pad($a2, $col[1]) . "</td>";
        $linea = $linea . "<td>" . str_pad(substr(trim($a3) . " " . trim($a4), 0, $col[2]), $col[2]) . "</td>";
        $linea = $linea . "<td>";


        $v_clave = $a1 . $a2;

        while ($v_irow < $v_ilimit and $v_clave == $a1 . $a2) {
            $a5 = pg_result($v_xsqlprn, $v_irow, 5);
            $a6 = pg_result($v_xsqlprn, $v_irow, 6);
            $a7 = pg_result($v_xsqlprn, $v_irow, 7);

            echo " " . $a5 . " " . $a6 . " (" . $a7 . ") ";
            $linea = $linea . trim($a5) . " " . trim($a6) . " (" . trim($a7) . ") ";

            $v_irow++;
            //antes de regresar al bucle tiene que comprobar el dato
            if ($v_irow < $v_ilimit) {
                $a1 = pg_result($v_xsqlprn, $v_irow, 1);
                $a2 = pg_result($v_xsqlprn, $v_irow, 2);
            }
        }

        echo "</td>";
        echo "</tr>";
        $linea = $linea . "</td>";
        $linea = $linea . "</tr>";
    }

    echo "</table>";
    $linea = $linea . "<tr><td>" . str_pad("-", $col[0] + $col[1] + $col[2] + $col[3] + 3, "-", STR_PAD_LEFT) . "</td></tr>";
    $linea = $linea . "</table>";
    imprimir2($cabecera . $linea, $col, $nom, $v_archivo, " ");


    echo "<br>";



    $col[0] = 4;
    $col[1] = 7;
    $col[2] = 7;
    $col[3] = 7;
    $col[4] = 7;
    $col[5] = 7;
    $col[6] = 7;
    $col[7] = 7;
    $col[8] = 7;
    $col[9] = 7;
    $col[10] = 7;
    $col[11] = 7;
    $col[12] = 7;
    $col[13] = 7;
    $col[14] = 7;
    $col[15] = 7;
    $col[16] = 7;


    echo "<table width='990' border='2' cellspacing=0 height='81'>";
    echo "<tr>";
    echo "<th colspan=18 align='center'><font size='3.5'> <BR>
		RESUMEN DE AFERICIONES <P></P></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<td width='85'>Lado</td>";
    echo "<td width='106' colspan=2 align='center'>84 Oct</td>";
    echo "<td width='106' colspan=2 align='center'>90 Oct</td>";
    echo "<td width='106' colspan=2 align='center'>97 Oct</td>";
    echo "<td width='106' colspan=2 align='center'>D2 </td>";
    echo "<td width='106' colspan=2 align='center'>95 Oct</td>";
    echo "<td width='106' colspan=2 align='center'>D1</td>";
    echo "<td width='106' colspan=2 align='center'>GLP</td>";
    echo "<td width='247' colspan=2>Total</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td width='85'>Lado</td>";
    echo "<td width='106' align='center'>Cant</td>";
    echo "<td width='106' align='center'>Val</td>";
    echo "<td width='106' align='center'>Cant</td>";
    echo "<td width='106' align='center'>Val</td>";
    echo "<td width='106' align='center'>Cant</td>";
    echo "<td width='106' align='center'>Val</td>";
    echo "<td width='106' align='center'>Cant</td>";
    echo "<td width='106' align='center'>Val</td>";
    echo "<td width='106' align='center'>Cant</td>";
    echo "<td width='106' align='center'>Val</td>";
    echo "<td width='106' align='center'>Cant</td>";
    echo "<td width='106' align='center'>Val</td>";
    echo "<td width='106' align='center'>Cant</td>";
    echo "<td width='106' align='center'>Val</td>";
    echo "<td width='106' align='center'>Cant</td>";
    echo "<td width='106' align='center'>Val</td>";
    echo "</tr>";



    $nom[0] = str_pad("Lado", $col[0]);
    $nom[1] = str_pad("Cant", $col[1]);
    $nom[2] = str_pad("Val", $col[2]);
    $nom[3] = str_pad("Cant", $col[3]);
    $nom[4] = str_pad("Val", $col[4]);
    $nom[5] = str_pad("Cant", $col[5]);
    $nom[6] = str_pad("Val", $col[6]);
    $nom[7] = str_pad("Cant", $col[7]);
    $nom[8] = str_pad("Val", $col[8]);
    $nom[9] = str_pad("Cant", $col[9]);
    $nom[10] = str_pad("Val", $col[10]);
    $nom[11] = str_pad("Cant", $col[11]);
    $nom[12] = str_pad("Val", $col[12]);
    $nom[13] = str_pad("Cant", $col[13]);
    $nom[14] = str_pad("Val", $col[14]);
    $nom[15] = str_pad("Cant", $col[15]);
    $nom[16] = str_pad("Val", $col[16]);

    $cabecera = "<table>";
    $cabecera = $cabecera . "<tr>";
    $cabecera = $cabecera . "<td> RESUMEN DE AFERICIONES </td>";
    $cabecera = $cabecera . "</tr>";

    $cabecera = $cabecera . "<tr>";
    $cabecera = $cabecera . "<td>" . str_pad("Lado", $col[0]) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("84 Oct", $col[1] + $col[2] + 1, " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("90 Oct", $col[3] + $col[4] + 1, " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("97 Oct", $col[5] + $col[6] + 1, " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("D2", $col[7] + $col[8] + 1, " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("95 Oct", $col[9] + $col[10] + 1, " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("D1", $col[11] + $col[12] + 1, " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("GLP", $col[13] + $col[14] + 1, " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Total", $col[15] + $col[16] + 1, " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "</tr>";
    $cabecera = $cabecera . "<tr>";
    $cabecera = $cabecera . "<td>" . str_pad(" ", $col[0]) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Cant", $col[1], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Val", $col[2], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Cant", $col[3], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Val", $col[4], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Cant", $col[5], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Val", $col[6], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Cant", $col[7], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Val", $col[8], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Cant", $col[9], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Val", $col[10], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Cant", $col[11], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Val", $col[12], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Cant", $col[13], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Val", $col[14], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Cant", $col[15], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "<td>" . str_pad("Val", $col[16], " ", STR_PAD_BOTH) . "</td>";
    $cabecera = $cabecera . "</tr>";
    $cabecera = $cabecera . "<tr><td>" . str_pad("-", $col[0] + $col[1] + $col[2] + $col[3] + $col[4] + $col[5] + $col[6] + $col[7] + $col[8] + $col[9] + $col[10] + $col[11] + $col[12] + $col[13] + $col[14] + $col[15] + $col[16] + 16, "-", STR_PAD_LEFT) . "</td></tr>";

    $linea = "";

    $clado = 6;

    $coltot01 = 0;
    $coltot02 = 0;
    $coltot03 = 0;
    $coltot04 = 0;
    $coltot05 = 0;
    $coltot06 = 0;
    $coltot07 = 0;
    $voltot01 = 0;
    $voltot02 = 0;
    $voltot03 = 0;
    $voltot04 = 0;
    $voltot05 = 0;
    $voltot06 = 0;
    $voltot07 = 0;

    $v_sqlprn = "select es, pump, trim(codigo), sum(cantidad) as cant, sum(importe) as impo
							from pos_ta_afericiones
							where dia between '" . $funcion->date_format($v_fecha_desde, 'YYYY-MM-DD') . "' and '" . $funcion->date_format($v_fecha_hasta, 'YYYY-MM-DD') . "'
							group by es, pump, codigo
							order by es, pump, codigo";

    $v_xsqlprn = pg_exec($conector_id, $v_sqlprn);
    $v_ilimit = pg_numrows($v_xsqlprn);
    $v_irow = 0;

    while ($v_irow < $v_ilimit) {
        $a0 = pg_result($v_xsqlprn, $v_irow, 0);
        $a1 = pg_result($v_xsqlprn, $v_irow, 1);

        $col01 = 0;
        $col02 = 0;
        $col03 = 0;
        $col04 = 0;
        $col05 = 0;
        $col06 = 0;
        $col07 = 0;
        $vol01 = 0;
        $vol02 = 0;
        $vol03 = 0;
        $vol04 = 0;
        $vol05 = 0;
        $vol06 = 0;
        $vol07 = 0;


        $v_codigo = $a1;
        $v_clave = $a0 . $a1;
        while ($v_irow < $v_ilimit and $v_clave == $a0 . $a1) {
            $a2 = pg_result($v_xsqlprn, $v_irow, 2);
            $a3 = pg_result($v_xsqlprn, $v_irow, 3);
            $a4 = pg_result($v_xsqlprn, $v_irow, 4);
            switch ($a2) {
                case "11620301":
                    $col01 = $a3;
                    $vol01 = $a4;
                    $coltot01 = $coltot01 + $a3;
                    $voltot01 = $voltot01 + $a4;
                    break;
                case "11620302":
                    $col02 = $a3;
                    $vol02 = $a4;
                    $coltot02 = $coltot02 + $a3;
                    $voltot02 = $voltot02 + $a4;
                    break;
                case "11620303":
                    $col03 = $a3;
                    $vol03 = $a4;
                    $coltot03 = $coltot03 + $a3;
                    $voltot03 = $voltot03 + $a4;
                    break;
                case "11620304":
                    $col04 = $a3;
                    $vol04 = $a4;
                    $coltot04 = $coltot04 + $a3;
                    $voltot04 = $voltot04 + $a4;
                    break;
                case "11620305":
                    $col05 = $a3;
                    $vol05 = $a4;
                    $coltot05 = $coltot05 + $a3;
                    $voltot05 = $voltot05 + $a4;
                    break;
                case "11620306":
                    $col06 = $a3;
                    $vol06 = $a4;
                    $coltot06 = $coltot06 + $a3;
                    $voltot06 = $voltot06 + $a4;
                    break;
                case "11620307":
                    $col07 = $a3;
                    $vol07 = $a4;
                    $coltot07 = $coltot07 + $a3;
                    $voltot07 = $voltot07 + $a4;
                    break;
            }


            $v_irow++;
            //antes de regresar al bucle tiene que comprobar el dato
            if ($v_irow < $v_ilimit) {
                $a0 = pg_result($v_xsqlprn, $v_irow, 0);
                $a1 = pg_result($v_xsqlprn, $v_irow, 1);
            }
        }

        $colto = $col01 + $col02 + $col03 + $col04 + $col05 + $col06 + $col07;
        $volto = $vol01 + $vol02 + $vol03 + $vol04 + $vol05 + $vol06 + $vol07;

        echo "<tr>";
        echo "<td align='left' >" . $v_codigo . " </td>";
        echo "<td align='right' >" . number_format($col01, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($vol01, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($col02, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($vol02, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($col03, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($vol03, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($col04, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($vol04, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($col05, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($vol05, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($col06, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($vol06, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($col07, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($vol07, 2, '.', '') . " </td>";

        echo "<td align='right' >" . number_format($colto, 2, '.', '') . " </td>";
        echo "<td align='right' >" . number_format($volto, 2, '.', '') . " </td>";
        echo "</tr>";

        $linea = $linea . "<tr>";
        $linea = $linea . "<td>" . str_pad($v_codigo, $col[0]) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($col01, 2, '.', ''), $col[1], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($vol01, 2, '.', ''), $col[2], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($col02, 2, '.', ''), $col[3], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($vol02, 2, '.', ''), $col[4], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($col03, 2, '.', ''), $col[5], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($vol03, 2, '.', ''), $col[6], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($col04, 2, '.', ''), $col[7], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($vol04, 2, '.', ''), $col[8], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($col05, 2, '.', ''), $col[9], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($vol05, 2, '.', ''), $col[10], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($col06, 2, '.', ''), $col[11], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($vol06, 2, '.', ''), $col[12], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($col07, 2, '.', ''), $col[13], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($vol07, 2, '.', ''), $col[14], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($colto, 2, '.', ''), $col[15], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "<td>" . str_pad(number_format($volto, 2, '.', ''), $col[16], " ", STR_PAD_LEFT) . "</td>";
        $linea = $linea . "</tr>";
    }
    $coltot = $coltot01 + $coltot02 + $coltot03 + $coltot04 + $coltot05 + $coltot06 + $coltot07;
    $voltot = $voltot01 + $voltot02 + $voltot03 + $voltot04 + $voltot05 + $voltot06 + $voltot07;

    echo "<tr>";
    echo "<td align='left' >TOTAL </td>";
    echo "<td align='right' >" . number_format($coltot01, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($voltot01, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($coltot02, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($voltot02, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($coltot03, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($voltot03, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($coltot04, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($voltot04, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($coltot05, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($voltot05, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($coltot06, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($voltot06, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($coltot07, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($voltot07, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($coltot, 2, '.', '') . " </td>";
    echo "<td align='right' >" . number_format($voltot, 2, '.', '') . " </td>";
    echo "</tr>";
    $linea = $linea . "<tr><td>" . str_pad("-", $col[0] + $col[1] + $col[2] + $col[3] + $col[4] + $col[5] + $col[6] + $col[7] + $col[8] + $col[9] + $col[10] + $col[11] + $col[12] + $col[13] + $col[14] + $col[15] + $col[16] + 16, "-", STR_PAD_LEFT) . "</td></tr>";
    $linea = $linea . "<tr>";
    $linea = $linea . "<td>" . str_pad("TOTAL", $col[0]) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($coltot01, 2, '.', ''), $col[1], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($voltot01, 2, '.', ''), $col[2], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($coltot02, 2, '.', ''), $col[3], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($voltot02, 2, '.', ''), $col[4], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($coltot03, 2, '.', ''), $col[5], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($voltot03, 2, '.', ''), $col[6], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($coltot04, 2, '.', ''), $col[7], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($voltot04, 2, '.', ''), $col[8], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($coltot05, 2, '.', ''), $col[9], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($voltot05, 2, '.', ''), $col[10], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($coltot06, 2, '.', ''), $col[11], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($voltot06, 2, '.', ''), $col[12], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($coltot07, 2, '.', ''), $col[13], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($voltot07, 2, '.', ''), $col[14], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($coltot, 2, '.', ''), $col[15], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "<td>" . str_pad(number_format($voltot, 2, '.', ''), $col[16], " ", STR_PAD_LEFT) . "</td>";
    $linea = $linea . "</tr>";
    $linea = $linea . "<tr><td>" . str_pad("=", $col[0] + $col[1] + $col[2] + $col[3] + $col[4] + $col[5] + $col[6] + $col[7] + $col[8] + $col[9] + $col[10] + $col[11] + $col[12] + $col[13] + $col[14] + $col[15] + $col[16] + 16, "=", STR_PAD_LEFT) . "</td></tr>";
}

echo "</table>";
$linea = $linea . "</table>";
imprimir2($cabecera . $linea, $col, $nom, $v_archivo2, " ");
?>


            <br>
            <br>


        </form>
    </body>
</html>
            <?php
// comprueba si la conexion existe y la cierra
            if ($conector_id)
                pg_close($conector_id);
// restaura el control de errores original
            $clase_error->_error();
            ?>
