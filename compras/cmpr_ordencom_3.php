<?php
include("../valida_sess.php");
include "lib/reportes2.inc.php";
include_once "/sistemaweb/include/dbsqlca.php";
$sqlca = new pgsqlDB('localhost','postgres','postgres','integrado');
global $usuario;

/*INICIO :: GENERAR LA CONSULTA PARA EL TIPO DE MONEDA*/
$query = "SELECT trim(tab_elemento) AS cod,tab_descripcion 
          FROM int_tabla_general  
          WHERE tab_tabla='MONE' 
          AND tab_elemento!='000000' 
          ORDER BY cod";
$sqlca->query($query,'moneda');
$monedas = array();
while($recordmon = $sqlca->fetchRow('moneda')){$monedas[round($recordmon['cod'])] = $recordmon['tab_descripcion'];//Formar el Arreglo
}
/*FIN :: GENERAR LA CONSULTA PARA EL TIPO DE MONEDA*/

/*INICIO :: GENERAR LA CONSULTA PARA DATOS DE CABECERA*/
$query = "SELECT    pro_codigo,
		    num_tipdocumento,
		    num_seriedocumento,
		    com_cab_numorden,
		    com_cab_almacen,
		    com_cab_fechaorden,
		    com_cab_moneda,
		    com_cab_tipcambio,
		    com_cab_credito,
		    com_cab_formapago,
		    com_cab_imporden,
		    com_cab_recargo1,
		    com_cab_observacion,
		    com_cab_estado,
		    com_cab_fechaofrecida,
		    com_cab_fecharecibida,
		    com_cab_det_glosa
	 FROM com_cabecera
         WHERE pro_codigo||num_tipdocumento||num_seriedocumento||com_cab_numorden='".$_REQUEST['m_clave']."'";
$sqlca->query($query,'orden');
$recordord = $sqlca->fetchRow('orden');
/*FIN :: GENERAR LA CONSULTA PARA DATOS DE CABECERA*/

/*INICIO :: ASIGNAR EL ID DEL TIPO DE PAGO*/
if($recordord['com_cab_credito']=='S'){$m_tab = "96";}
else {$m_tab = "05";}
/*FIN :: GENERAR LA CONSULTA PARA DATOS DE CABECERA*/

/*INICIO :: GENERAR LA CONSULTA PARA RECUPERAR LOS DATOS DE LOS PROVEEDORES*/
$query = "SELECT  pro_razsocial,
                  pro_rsocialbreve,
                  pro_grupo,
                  pro_direccion,
                  pro_comp_direcc,
                  pro_ruc,
	          pro_telefono1,
	          pro_telefono2,
	          pro_contacto
         FROM int_proveedores 
         WHERE pro_codigo='".trim($recordord['pro_codigo'])."'";
$sqlca->query($query,'proveedor');
$recordpro = $sqlca->fetchRow('proveedor');
/*FIN :: GENERAR LA CONSULTA PARA RECUPERAR LOS DATOS DE LOS PROVEEDORES*/

/*INICIO :: GENERAR LA CONSULTA PARA RECUPERAR LOS DATOS DE LOS ALMACENES*/
$query2 = "SELECT trim(ch_sucursal) AS cod, ch_nombre_sucursal,
	       ch_nombre_breve_sucursal, 
	       ch_direccion, 
	       ch_distrito, 
	       ch_telefonos 
	 FROM int_ta_sucursales
	 WHERE ch_sucursal LIKE '".trim($recordord['com_cab_almacen'])."'";
$sqlca->query($query2,'almacenes');
$recordalmac = $sqlca->fetchRow('almacenes');
/*FIN :: GENERAR LA CONSULTA PARA RECUPERAR LOS DATOS DE LOS ALMACENES*/

/*INICIO :: GENERAR LA CONSULTA PARA RECUPERAR LOS DATOS DE FORMA DE PAGO*/
$query = "SELECT substr(tab_elemento,5,2) ,tab_descripcion 
          FROM int_tabla_general 
          WHERE tab_tabla='".$m_tab."' 
          AND tab_elemento!='000000' 
          ORDER BY tab_elemento";
$sqlca->query($query,'fpagos');
$fpagos = array();
while($recordfpagos = $sqlca->fetchRow('fpagos'))
{
   $fpagos[$recordfpagos['substr']] = $recordfpagos['tab_descripcion'];//GENERAR EL ARREGLO
}
/*FIN :: GENERAR LA CONSULTA PARA RECUPERAR LOS DATOS DE FORMA DE PAGO*/

/*INICIO :: GENERAR LA CONSULTA PARA LOS ITEMS DE CADA ORDEN*/
$query = " SELECT   det.pro_codigo||det.num_tipdocumento||det.num_seriedocumento||det.com_cab_numorden||det.art_codigo, 
		    det.art_codigo, 
		    art.art_descripcion,
		    det.com_det_cantidadpedida,
		    det.com_det_precio,
		    det.com_det_descuento1,
		    det.com_det_imparticulo
	    FROM com_detalle det, int_articulos art
	    WHERE det.art_codigo=art.art_codigo and det.pro_codigo||det.num_tipdocumento||det.num_seriedocumento||det.com_cab_numorden='".$m_clave."'";
$sqlca->query($query,'ordenes');
$ordenes = array();
$c=0;
while($record = $sqlca->fetchRow('ordenes'))
{
   $ordenes[$c]['ITEM'] = ($c+1);
   $ordenes[$c]['CANT'] = round($record['com_det_cantidadpedida']);
   $ordenes[$c]['CODIGO'] = $record['art_codigo'];
   $ordenes[$c]['DESCRIPCION'] = $record['art_descripcion'];
   $ordenes[$c]['PRECIO'] = $record['com_det_precio'];
   $ordenes[$c]['DESCUENTO'] = $record['com_det_descuento1'];
   $ordenes[$c]['VALOR VENTA'] = $record['com_det_imparticulo'];
   $c++;
}





$cabecera1 = Array
(
   "ITEM"         =>	"ITEM",
   "CANT"         =>	"CANT",
   "CODIGO"       =>	"CODIGO",
   "DESCRIPCION"  =>	"DESCRIPCION",
   "PRECIO"       =>	"PRECIO",
   "DESCUENTO"    =>	"DESCUENTO",
   "VALOR VENTA"  =>	"VALOR VENTA"
);

$fontsize = 10;
$reporte = new CReportes2();
$reporte->SetMargins(5, 12, 5);
$reporte->SetFont("courier", "", $fontsize);
$reporte->definirCabeceraImagen(1,3,"/sistemaweb/images/logocia.jpeg", 100, 50);
$reporte->definirCabeceraSize(4, "R", "courier,B,15", "ORDEN DE COMPRA Nro. ".$recordord['com_cab_numorden']."");
$reporte->definirCabeceraSize(5, "R", " ", " ");
$reporte->definirCabeceraSize(6, "L", "courier,B,15", "EMPRESA S.A.");
$reporte->definirCabeceraSize(7, "L", "courier,B,9", "  DIRECCION ");
$reporte->definirCabeceraSize(8, "L", "courier,B,9", "     TELEFONOS");
$reporte->definirCabeceraSize(9, "L", "courier,N,8", "       E-mail: ");
$reporte->definirCabecera(10, "R", "Fecha : ".$recordord['com_cab_fechaorden']." ");// Colocar Fecha
$reporte->definirCabecera(11, "R", " ");
$reporte->definirCabecera(12, "L", "Senores          : ".trim($recordpro['pro_razsocial'])."");// Colocar sn
$reporte->definirCabecera(13, "L", "Direccion        : ".trim($recordpro['pro_direccion'])."  ".trim($recordpro['pro_comp_direcc'])."");// Colocar dir
$reporte->definirCabecera(14, "L", "Telefonos        : ".trim($recordpro['pro_telefono1']).", ".trim($recordpro['pro_telefono2'])." ");// Colocar telef.
$reporte->definirCabecera(15, "L", "Lugar de Entrega : ".trim($recordalmac['ch_direccion'])." - ".trim($recordalmac['ch_distrito'])." - ".trim($recordalmac['ch_nombre_sucursal'])."");// Colocar Lugar
$reporte->definirCabecera(16, "L", "Fecha de Entrega : ".$recordord['com_cab_fechaofrecida']."");// Colocar fecha de entrega.
$reporte->definirCabecera(17, "R", " ");
$reporte->definirCabecera(18, "L", "Sirvase a entregarnos a la direccion y en las condiciones que precisamos, lo siguiente :");
$reporte->definirCabecera(19, "R", " ");
$reporte->definirColumna("ITEM", $tipo->TIPO_TEXT, 4, "L");
$reporte->definirColumna("CANT", $tipo->TIPO_TEXT, 4, "R");
$reporte->definirColumna("CODIGO", $tipo->TIPO_TEXT, 17, "C");
$reporte->definirColumna("DESCRIPCION", $tipo->TIPO_TEXT, 33, "C");
$reporte->definirColumna("PRECIO", $tipo->TIPO_IMPORTE, 10, "R");
$reporte->definirColumna("DESCUENTO", $tipo->TIPO_IMPORTE, 10, "R");
$reporte->definirColumna("VALOR VENTA", $tipo->TIPO_IMPORTE, 11, "R");
$reporte->definirCabeceraPredeterminada($cabecera1);
$reporte->addPage();

foreach($ordenes as $llave => $valor)
{
   $datos['ITEM']       = $valor['ITEM'];
   $datos['CANT']       = $valor['CANT'];
   $datos['CODIGO']     = $valor['CODIGO'];
   $datos['DESCRIPCION']= $valor['DESCRIPCION'];
   $datos['PRECIO']     = $valor['PRECIO'];
   $datos['DESCUENTO']  = $valor['DESCUENTO'];
   $datos['VALOR VENTA']= $valor['VALOR VENTA'];
   $total_cantidad += $valor['CANT'];
   $total_venta += $valor['VALOR VENTA'];
   $reporte->nuevaFila($datos);
}

$total_venta = money_format('%.2n',$total_venta);
$valor_venta = money_format('%.2n',($total_venta / 1.19));
$igv = money_format('%.2n',($total_venta-$valor_venta));
$reporte->Ln();
$reporte->Multicell(585,12,''.$recordord['com_cab_det_glosa'].'',0,'L');
$reporte->Ln();
$reporte->Ln();
$reporte->cell(0,10,'VALOR VENTA : '.$valor_venta.'             I.G.V. : '.$igv.'              '.(round($recordord['com_cab_moneda'])==1?'S./':'US$').'     PRECIO TOTAL  : '.$total_venta.'',1,0,'L');
$reporte->Lnew();
$reporte->Lnew();
$reporte->SetFont('');
$reporte->SetFont('Courier','',9); 
$reporte->cell(0,0,'Forma de Pago       : '.$fpagos[trim($recordord['com_cab_formapago'])].'',0,0,'L');
$reporte->Lnew();
$reporte->cell(0,0,'Moneda              : '.strtoupper($monedas[round($recordord['com_cab_moneda'])]).'',0,0,'L');
$reporte->Lnew();
$reporte->Multicell(585,12,'Otras Instrucciones : '.$recordord['com_cab_observacion'].'',0,'L');
$valY = (705 - $reporte->GetY());
$setY = ($valY + $reporte->GetY());
$reporte->SetY($setY); //Asignar el valor de Y para el Pie de página

//INICIO : Imprimir Valores del Pie de página
$reporte->SetFont('Courier','',8); 
$reporte->Ln();
$reporte->cell(0,7,'Preparado por : '.$usuario->nombre.'                                              Aprobado por : __________',0,0,'C');
$reporte->Ln();
$reporte->SetFont('Courier','B',8); 
$reporte->Lnew();
$reporte->cell(0,7,'**IMPORTANTE**',0,0,'L');
$reporte->SetFont('Courier','',8); 
$reporte->Ln();
$reporte->cell(0,7,'1.- Los precios facturados seran los mismos contenidos en esta O/C.',0,0,'L');
$reporte->Ln();
$reporte->cell(0,7,'2.- No incluya en sus guias o facturas, productos no comprendidos en esta O/C.',0,0,'L');
$reporte->Ln();
$reporte->Multicell(585,7,'3.- Los productos nuevos, o con nuevo codigo de barra deben matricularse antes de entregarlos, caso contrario el sistema los rechaza y no es posible procesar el pago.',0,'J');
$reporte->Multicell(585,7,'4.- Para el pago es necesario anexar a su factura la nota de ingreso que obligatoriamente le entregaremos al recepcionar los productos.',0,'J');
$reporte->Ln();
//FIN : Imprimir Valores del Pie de página
$reporte->Output("OrdenCompra_".$recordord['com_cab_numorden'].".pdf","I");
