<?php
include('func2.php');
date_default_timezone_set('UTC');

/*
function update_fisico($coneccion,$sfis,$almacen,$periodo,$newcodartic) {
        $mesact=date("m");
        $xsqlbusca=pg_exec($coneccion,"select art_codigo from inv_saldoalma where stk_almacen='".$almacen."' and art_codigo='".$newcodartic."' and stk_periodo='".$periodo."' ");
        if(pg_numrows($xsqlbusca)>0) {
        $xsqlupd=pg_exec($coneccion,"update inv_saldoalma set stk_fisico".$mesact."=".$sfis." where stk_almacen='".$almacen."' and art_codigo='".$newcodartic."' and stk_periodo='".$periodo."' ");
        }
}
*/

	function eliminar_directorio($dirname){
		if (is_dir($dirname)) { //Operate on dirs only
			$result=array();
			if (substr($dirname,-1)!='/') {$dirname.='/';} //Append slash if necessary
			$handle = opendir($dirname);
			while (false !== ($file = readdir($handle))) {
				if ($file!='.' && $file!= '..') { //Ignore . and ..
					$path = $dirname.$file;
					if (is_dir($path)) { //Recurse if subdir, Delete if file
						$result=array_merge($result,eliminar_directorio($path));
					}else{
						unlink($path);
						$result[].=$path;
					}
				}
			}
			closedir($handle);
			rmdir($dirname); //Remove dir
			$result[].=$dirname;
			return true; //Return array of deleted items
		}else{
			return false; //Return false if attempting to operate on a file
		}
	}

function comparafechamayor($xfecmov,$xfecactual) {
        global $f_compara;
        $dia1=substr($xfecmov,8,2);
        $mes1=substr($xfecmov,5,2);
        $ano1=substr($xfecmov,0,4);
        $dia2=substr($xfecactual,8,2);
        $mes2=substr($xfecactual,5,2);
        $ano2=substr($xfecactual,0,4);
        if(mktime(0,0,0,$mes1,$dia1,$ano1)<mktime(0,0,0,$mes2,$dia2,$ano2)){
                $f_compara="A";
        }elseif(mktime(0,0,0,$mes1,$dia1,$ano1)==mktime(0,0,0,$mes2,$dia2,$ano2)){
                $f_compara="B";
        }else{
                $f_compara="C";
        }
}

function nom_proveedor($coneccion,$mcodprov) {
        global $zrazsoc;
        $xsql=pg_exec($coneccion,"select pro_razsocial from int_proveedores where pro_codigo='".$mcodprov."' ");
        if(pg_numrows($xsql)>0) { $zrazsoc=pg_result($xsql,0,0); }
}

function nom_almacen($coneccion,$almac) {
        global $zcodalm,$zdescalma;
        $xsql=pg_exec($coneccion,"select ch_almacen,ch_sigla_almacen from inv_ta_almacenes where ch_almacen='".$almac."' ");
        if(pg_numrows($xsql)>0) { $zcodalm=pg_result($xsql,0,0); $zdescalma=pg_result($xsql,0,1); }
}

function nom_linea($coneccion,$linea) {
        global $zcodlin,$zdesclin;
        $xsql=pg_exec($coneccion,"select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and (tab_elemento like '%".$linea."%' or tab_descripcion like '%".$linea."%') ");
        if(pg_numrows($xsql)>0) { $zcodlin=pg_result($xsql,0,0); $zdesclin=pg_result($xsql,0,1); }
}

function calculastkxdia($coneccion,$sfecha,$artic,$tipform,$almacd) {
    global $sumtotxalma;
        $irowc3=0; $sumtotxalma=0;
        $sqlqry3=" select mov_cantidad from inv_movialma where mov_fecha='".$sfecha."' and art_codigo='".$artic."' and
        tran_codigo='".$tipform."' and mov_almadestino='".$almacd."' ";
//        echo $sqlqry3;
    $xsqlqry3=pg_exec($coneccion,$sqlqry3);
    $ilimitc3=pg_numrows($xsqlqry3);
        $sumtotxalma=0; $fm=$tipform;
        while($irowc3<$ilimitc3) {
                $w0=pg_result($xsqlqry3,$irowc3,0);
                $sumtotxalma=$sumtotxalma+$w0;
//                $fm=pg_result($xsqlqry3,$irowc3,1);
/*                if($fm=='05' or $fm=='08' or $fm=='11' or $fm=='14' or $fm=='21' or $fm=='24' or $fm=='25' or $fm=='28' or $fm=='45' or $fm=='46') { $sumtotxalma=$sumtotxalma-$w0;
                }elseif($fm=='01' or $fm=='07' or $fm=='12' or $fm=='16' or $fm=='17' or $fm=='18' or $fm=='19' or $fm=='23' or $fm=='26' or $fm=='27') { $sumtotxalma=$sumtotxalma+$w0; }
*/
     $irowc3++;
   }
}

function calcula_stkini($coneccion,$codart,$diad,$mesd,$anod,$almac) {
        global $stkini;
        $sqlstk="select stk_stock".$mesd." from inv_saldoalma where stk_periodo='".$anod."' and stk_almacen='".$almac."' and art_codigo='".$codart."' ";
//echo "aqui esta el 1er select".$sqlstk;
        $xsqlstk=pg_exec($coneccion,$sqlstk);
        if(pg_numrows($xsqlstk)>0) { $stkinimes=pg_result($xsqlstk,0,0); }
        $fecd=$anod."/".$mesd."/01";
        $diadx=$diad-1;
        $feca=$anod."/".$mesd."/".$diadx;
        if($diadx==0) { $diadx=31; $mesd=$mesd-1; if($mesd==0) { $mesd=12; $anod--; } }
          $f1=10;
        $g1=25;
  while($f1<$g1)        {
        if(checkdate($mesd,$diadx,$anod)) {
          $f1=30;  //echo $zmesd."/".$zdiad."/".$zanod;
        }else{
          //if($f1==10) { $diadx=31; }
          $diadx--;
        }
        $f1++;
  }
          $feca=$anod."/".$mesd."/".$diadx;
        $sql1="select mov_cantidad,tran_codigo from inv_movialma where mov_almacen='".$almac."' and art_codigo='".$codart."' and mov_fecha between '".$fecd."' and '".$feca."' ";
//echo $sql1;
        $xsql1=pg_exec($coneccion,$sql1);
        $ilimit1=pg_numrows($xsql1);
        while($irow1<$ilimit1) {
                $w0=pg_result($xsql1,$irow1,0);
                $fm=pg_result($xsql1,$irow1,1);
                if($fm=='05' or $fm=='08' or $fm=='11' or $fm=='14' or $fm=='21' or $fm=='24' or $fm=='25' or $fm=='28' or $fm=='45' or $fm=='46') { $summov=$summov-$w0;
                }elseif($fm=='01' or $fm=='07' or $fm=='12' or $fm=='16' or $fm=='17' or $fm=='18' or $fm=='19' or $fm=='23' or $fm=='26' or $fm=='27') { $summov=$summov+$w0; }
                $irow1++;
        }
        $stkini=$stkinimes+$summov;
}


// APAREMTEMENTE ESTA FUNCION SACA LAS FECHAS DESDE HASTA PARA EL INICIO
// Y FIN DE UN MES

function rangodefechas() {
        global $zdiad,$zmesd,$zanod,$zdiaa,$zmesa,$zanoa;
        $zdiaa=date("d");  $zmesa=date("m"); $zanoa=date("Y");
//        $zdiaa="31";  $zmesa="03"; $zanoa="2004";
//        echo "el mes actual es".$zmesa."dia".$zdiaa;
        if($zmesa==1 or $zmesa=="01" or $zmesa=="1")  {
                $zanod=$zanoa-1;  $zmesd="12"; }
        else {
            $zanod=$zanoa; $zmesd= $zmesa-1 ;
                //echo "Empieza a llamar a funcion completaceros";
                $zmesd= completarceros($zmesd,2,"0") ;
                //echo "Fin de llamada a funcion completaceros";
        }
        $zdiad=$zdiaa;
        $f=10;
        $g=20;
    while($f<$g)        {
                if(checkdate($zmesd,$zdiad,$zanod)) {
                  $f=30;  //echo $zmesd."/".$zdiad."/".$zanod;
                }else{
                  $zdiad--;  //echo $zdiad;
                }
    }
}

function completaceros($nrocaract,$cadena) {
        global $cadena;
        $nroceros1=$nrocaract-strlen($cadena);

        //echo $nrocaract.",".$cadena.",".$nroceros1 ;

        if($nroceros1>0) {
            $ctdtip=0 ;
                while($ctdtip<$nroceros1) { $cadena="0".$cadena; $ctdtip++; }
        }
}

function max_nro_mov($coneccion,$tipo) {
        global $nromov;
        $xsqlmaxnromov=pg_exec($coneccion,"select max(mov_numero) from inv_movialma where tran_codigo='".$tipo."' ");
        $nromov1=pg_result($xsqlmaxnromov,0,0)+1;
        $nroceros=10-strlen($nromov1);
        if($nroceros>0) {
                while($ctd<$nroceros) { $nromov1="0".$nromov1; $ctd++; }
        }
        $nromov=$nromov1;
}

function valida_existe_art($coneccion,$newcodart) {
        global $f_valart;
        $sqlval_art="select art_codigo from int_articulos where art_codigo='".$newcodart."' ";
        $xsqlval_art=pg_exec($coneccion,$sqlval_art);
        if(pg_numrows($xsqlval_art)>0) { $f_valart="";
        }else{ $f_valart=" C�digo de art�culo "; }
}

function valida_prov($coneccion,$updprov1) {
        global $f_prov;
        $sqlval_prov="select pro_codigo from int_proveedores where pro_codigo='$updprov1' ";
        $xsqlval_prov=pg_exec($coneccion,$sqlval_prov);
        if(pg_numrows($xsqlval_prov)>0){ $f_prov=""; } else { $f_prov=" C�digo del proveedor "; }
}

function valida_docr($coneccion,$updtipodocref1) {
        global $f_tipodoc;
        $sqlval_docr="select tab_elemento from int_tabla_general where tab_tabla='08' and tab_elemento='$updtipodocref1' ";
        $xsqlval_docr=pg_exec($coneccion,$sqlval_docr);
        if(pg_numrows($xsqlval_docr)>0) { $f_tipodoc=""; }else{ $f_tipodoc=" Tipo de doc. de referencia "; }
}

function valida_almo($coneccion,$updalmaco1) {
        global $f_almo;
        $sqlval_almo="select tab_elemento from int_tabla_general where tab_tabla='ALMA' and tab_elemento='$updalmaco1' ";
        $xsqlval_almo=pg_exec($coneccion,$sqlval_almo);
        if(pg_numrows($xsqlval_almo)>0) { $f_almo="";

        }else{ $f_almo=" Almac�n de origen "; }
}

function valida_almd($coneccion,$updalmacd1) {
        global $f_almd;
        $sqlval_almd="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='ALMA' and tab_elemento='$updalmacd1' ";
        $xsqlval_almd=pg_exec($coneccion,$sqlval_almd);
        if(pg_numrows($xsqlval_almd)>0) { $f_almd=""; }else{ $f_almd=" Almac�n de destino "; }
}

function nroartic($coneccion) {
        global $nroart,$npartes,$residuo;
        $xsqlnroart=pg_exec($coneccion,"select count(*) from int_articulos");
        $nroart=pg_result($xsqlnroart,0,0);
        $npartes=$nroart/30;
        $residuo=$nroart%30;
}

function ultimoDia($mes,$ano){
    $ultimo_dia=28;
    while (checkdate($mes,$ultimo_dia + 1,$ano)){
       $ultimo_dia++;
    }
    return $ultimo_dia;
}

function valida_fecha($dia,$mes,$ano) {
        global $mens_valida_fecha;
        if(checkdate($mes,$dia,$ano)) { $mens_valida_fecha=""; } else { $mens_valida_fecha=" La fecha ".$dia."/".$mes."/".$ano." no es v�lida !!!"; }
}

function recalcula_costo_art($codart,$cantart) {

}

function tipoform($fm,$coneccion) {
        global $codform,$descform,$valorform,$naturform,$entform,$entorig,$entdest;
        $sql1="select tran_codigo,tran_descripcion,tran_valor,tran_naturaleza,tran_entidad,tran_origen,tran_destino
         from inv_tipotransa where tran_codigo='".$fm."'";
        $xsql1=pg_exec($coneccion,$sql1);
        if(pg_numrows($xsql1)>0) {
                $codform=pg_result($xsql1,0,0);
                $descform=pg_result($xsql1,0,1);
                $valorform=pg_result($xsql1,0,2);
//                echo "xvalorform".$valorform;
                $naturform=pg_result($xsql1,0,3);
                $entform=pg_result($xsql1,0,4);
                $entorig=pg_result($xsql1,0,5);
                $entdest=pg_result($xsql1,0,6);
        }
}

function obtener_costounit_prom($coneccion,$newcodartic,$cantxingosal,$almac,$fm) {
        global $costopromprod,$costoproducto,$codform,$descform,$valorform,$naturform,$entform;

        $sqlobtcup="select art_costoactual,art_cod_ubicac from int_articulos where art_codigo='".$newcodartic."'";
//        echo $sqlobtcup;
        $xsqlobtcup=pg_exec($coneccion,$sqlobtcup);
        if(pg_numrows($xsqlobtcup)>0) { $costoproducto=pg_result($xsqlobtcup,0,0); $cod_ubic=pg_result($xsqlobtcup,0,1); }
// aqui falta definir con que tablas se consulta para calcular el costo promedio
        tipoform($fm,$coneccion);
//        echo "valorform".$valorform;
//        echo "fm".$fm; echo "naturform".$naturform;
        if($valorform=="N") {
                $sqlobtcup="select stk_costoactual from inv_saldoalma where art_codigo='".$newcodartic."'";
                $xsqlobtctop=pg_exec($coneccion,$sqlobtcup);
                if(pg_numrows($xsqlobtctop)>0) {        $cto_prom_art=pg_result($xsqlobtctop,0,0); }
// ojoooooo                echo "valorform1".$valorform;
        }elseif($valorform=="S" and ($naturform=="1" or $naturform=="2")){
                $sqlobtctop="select art_costoactual,art_stockactual from int_articulos where art_codigo='".$newcodartic."'";
                $xsqlobtctop=pg_exec($coneccion,$sqlobtctop);
                if(pg_numrows($xsqlobtctop)>0) {
                        $cto_act_art=pg_result($xsqlobtctop,0,0);
                        $stk_act_art=pg_result($xsqlobtctop,0,1);
                }
                $valorizac=$stk_act_art*$cto_act_art;
                $valorizac_mov=$cantxingosal*cto_act_art;
                $cto_prom_art=($valorizac+$valorizac_mov)/($stk_act_art+$cantxingosal);
//                echo "valorform2".$valorform;
        }elseif($valorform=="S" and ($naturform=="3" or $naturform=="4")){
                $valorizac=$stk_act_art*$cto_act_art;
                $valorizac_mov=$cantxingosal*cto_act_art;
                $cto_prom_art=($valorizac-$valorizac_mov)/($stk_act_art-$cantxingosal);
//                echo "valorform3".$valorform;
        }
        if($cto_prom_art>0) {   $costopromprod=$cto_prom_art; } else { $costopromprod=0; }

        $period=date("Y");
        $mes=date("m");

        $sqlupd="update inv_saldoalma set stk_ubicacion='".$cod_ubic."',stk_costoactual=".$costopromprod.",stk_costo".$mes."=".$costopromprod." where art_codigo='".$newcodartic."' and stk_periodo='".$period."' and stk_almacen='".$almac."' ";
//        echo $sqlupd;
        $xsqlupd=pg_exec($coneccion,$sqlupd);
        $xsqlupdart=pg_exec($coneccion,"update int_articulos set art_costopromedio=".$costopromprod." where art_codigo='".$newcodartic."' ");
}

function valida_item_mov($coneccion,$coditem,$fm,$nromov) {
        global $f_existeitem;
        $xsqlbusc_mov=pg_exec($coneccion,"select art_codigo from inv_movialma where tran_codigo='$fm' and art_codigo='$coditem' and mov_numero='".$nromov."' ");
        if(pg_numrows($xsqlbusc_mov)>0) {
                $f_existeitem="S";
        } else {
                $f_existeitem="N";
        }
}


function actualiza_stock($coneccion,$codart,$fm,$almac,$cant) {
        global $f_tipo_trans;
        $period=date("Y");
        $xsqlqry=pg_exec($coneccion,"select stk_stockactual from inv_saldoalma where art_codigo='".$codart."' and stk_periodo='".$period."' and stk_almacen='".$almac."'");
        if(pg_numrows($xsqlqry)>0) { $stk_act=pg_result($xsqlqry,0,0); }
        if($fm=='01' or $fm=='07' or $fm=='12' or $fm=='16' or $fm=='17' or $fm=='18' or $fm=='19' or $fm=='23' or $fm=='26' or $fm=='27') {
                $stk_act=$stk_act+$cant;
                $f_tipo_trans="I";
        } elseif($fm=='05' or $fm=='08' or $fm=='11' or $fm=='14' or $fm=='21' or $fm=='24' or $fm=='25' or $fm=='28' or $fm=='45' or $fm=='46') {
                $stk_act=$stk_act-$cant;
                $f_tipo_trans="S";
        }
        $mes=date("m");
        $q="update inv_saldoalma set stk_stockactual=".$stk_act.",stk_stock".$mes."=".$stk_act." where art_codigo='".$codart."' and stk_periodo='".$period."' and stk_almacen='".$almac."' " ;
        echo "carajo stock".$q ;
        $xsqlupd=pg_exec($coneccion,$q);
        $xsqlupdart=pg_exec($coneccion,"update int_articulos set art_stockactual=".$stk_act." where art_codigo='".$codart."' ");

}

function inserta_item_mov($coneccion,$nromov,$fm,$newcodartic,$fecmov,$updalmac,$updalmaco,$updalmad,$updnatu,$updtipodocref,$updnrodocref,$updprov,$newcant,$flagcito,$cu,$entform) {
        global $costopromprod,$costoproducto,$msg_insert,$f_existeitem,$sqlinsdet;
        //echo "YYYYYYYYYYYYYYYYYYYY -->".$flagcito;
        $period=date("Y");  $fecmov=date("Y/m/d H:i:s");

        // $xsqlexist=pg_exec($coneccion,"select art_codigo from inv_saldoalma where art_codigo='".$newcodartic."' and stk_periodo='".$period."' and stk_almacen='".$updalmac."' ");
        // $ilimitexist=pg_numrows($xsqlexist);
        // if($ilimitexist==0) {
        //   $pxsql=pg_exec($coneccion,"insert into inv_saldoalma(stk_almacen,stk_periodo,art_codigo) values('".$updalmac."','".$period."','".$newcodartic."')");
        // }

        valida_item_mov($coneccion,$newcodartic,$fm,$nromov);
        obtener_costounit_prom($coneccion,$newcodartic,$newcant,$updalmac,$fm);
//        echo "costopromprod".$costoproducto;
        if($flagcito=="A") { $costoproducto=$cu; }
        if($flagcito=="B") { $costoproducto=$cu; } //ESTO LO HIZO MIGUEL POR SI ACA
        $costototal=$costoproducto*$newcant;
        if(strlen(trim($costoproducto))==0) { $costoproducto=0; }
        if($f_existeitem=="N") {
           if($entform=="P") {
$sqlinsdet="insert into inv_movialma(mov_numero,tran_codigo,art_codigo,mov_fecha,mov_almacen,mov_almaorigen,mov_almadestino,
mov_naturaleza,mov_tipdocuref,
mov_docurefe,mov_entidad,mov_cantidad,mov_costounitario,mov_costopromedio,mov_costototal)
values('$nromov','$fm','$newcodartic','$fecmov','$updalmac','$updalmaco','$updalmad','$updnatu','$updtipodocref',
'$updnrodocref','$updprov',$newcant,$costoproducto,$costopromprod,$costototal)";
//echo "CARAJO -->".$sqlinsdet;

                } else {
$sqlinsdet="insert into inv_movialma
(mov_numero,tran_codigo,art_codigo,mov_fecha,mov_almacen,mov_almaorigen,mov_almadestino,
mov_naturaleza,mov_tipdocuref,
mov_docurefe,mov_cantidad,mov_costounitario,mov_costopromedio,mov_costototal)
values
('$nromov','$fm','$newcodartic','$fecmov','$updalmac','$updalmaco','$updalmad'
,'$updnatu','$updtipodocref',
'$updnrodocref',$newcant,$costoproducto,$costopromprod,$costototal)";
        }

        //echo "CARAJO".$sqlinsdet;

                $xsqlinsdet=pg_exec($coneccion,$sqlinsdet);
                // actualiza_stock($coneccion,$newcodartic,$fm,$updalmac,$newcant);

                if($f_tipo_trans=="I") {
                  $xsqlfec=pg_exec($coneccion,"select stk_ucompra from inv_saldoalma where art_codigo='".$newcodartic."' and stk_periodo='".$period."' and stk_almacen='".$updalmac."'");
                  if(pg_numrows($xsqlfec)>0) {
                          $fecuc=pg_result($xsqlfec,0,0);
                        if(strlen($fecuv)>0) {
                                comparafechamayor($fecmov,$fecuc);
                                if($f_compara="C"){
                                        $xsqlupdfec=pg_exec($coneccion,"update inv_saldoalma set stk_ucompra='".$fecmov."' where art_codigo='".$newcodartic."' and stk_periodo='".$period."' and stk_almacen='".$updalmac."' ");
                                }
                        } else {
                                $xsqlupdfec=pg_exec($coneccion,"update inv_saldoalma set stk_ucompra='".$fecmov."' where art_codigo='".$newcodartic."' and stk_periodo='".$period."' and stk_almacen='".$updalmac."' ");
                        }
                  }
                }else{
                  $xsqlfec=pg_exec($coneccion,"select stk_uventa from inv_saldoalma where art_codigo='".$newcodartic."' and stk_periodo='".$period."' and stk_almacen='".$updalmac."'");
                  if(pg_numrows($xsqlfec)>0) {
                          $fecuv=pg_result($xsqlfec,0,0);
                        if(strlen($fecuv)>0) {
                                comparafechamayor($fecmov,$fecuv);
                                if($f_compara="C"){
                                        $xsqlupdfec=pg_exec($coneccion,"update inv_saldoalma set stk_uventa='".$fecmov."' where art_codigo='".$newcodartic."' and stk_periodo='".$period."' and stk_almacen='".$updalmac."' ");
                                }
                        } else {
                                $xsqlupdfec=pg_exec($coneccion,"update inv_saldoalma set stk_uventa='".$fecmov."' where art_codigo='".$newcodartic."' and stk_periodo='".$period."' and stk_almacen='".$updalmac."' ");
                        }
                  }
                }
        }
}



function del_item_mov($coneccion,$fm,$codart) {
        $sqlinsdet="delete from inv_movialma where mov_numero='$nromov' and tran_codigo='$fm' and art_codigo='$codart' ";
        $xsqlinsdet=pg_exec($coneccion,$sqlinsdet);
}

function obtenermov($fm,$nromov,$coneccion) {
        global $movfecha,$movalm,$updalmaco,$updalmacd,$updtipodocref,$movnrodocref,$movfecha,$updprov;
        $sql2="select mov_fecha,mov_almacen,mov_almaorigen,mov_almadestino,mov_tipdocuref,mov_docurefe,mov_fecha,mov_entidad
         from inv_movialma where tran_codigo='$fm' and mov_numero='$nromov'";
        $xsql2=pg_exec($coneccion,$sql2);
        if(pg_numrows($xsql2)>0) {
                $movfecha=pg_result($xsql2,0,0);
                $movalm=pg_result($xsql2,0,1);
                $updalmaco=pg_result($xsql2,0,2);
                $updalmacd=pg_result($xsql2,0,3);
                $updtipodocref=pg_result($xsql2,0,4);
                $movnrodocref=pg_result($xsql2,0,5);
                $movfecha=pg_result($xsql2,0,6);
                $updprov=pg_result($xsql2,0,7);
        }
}

function obteneralmamov($fm,$nromov,$coneccion) {
        global $almacen;
        $sql2="select mov_almacen from inv_movialma where tran_codigo='$fm' and mov_numero='$nromov'";
//        echo $sql2;
        $xsql2=pg_exec($coneccion,$sql2);
        if(pg_numrows($xsql2)>0) {
                $almacen=pg_result($xsql2,0,0);
        }
}


function send_mailtxt($myname, $myemail, $contactname, $contactemail, $subject, $message) {
  $headers .= "MIME-Version: 1.0\n";
  $headers .= "Content-type: text/plain; charset=iso-8859-1\n";
  $headers .= "X-Priority: 1\n";
  $headers .= "X-MSMail-Priority: High\n";
  $headers .= "X-Mailer: php\n";
  $headers .= "From: \"".$myname."\" <".$myemail.">\n";
  return(mail("\"".$contactname."\" <".$contactemail.">", $subject, $message, $headers));
}

function send_mailhtm($myname, $myemail, $contactname, $contactemail, $subject, $message) {
  $headers .= "MIME-Version: 1.0\n";
  $headers .= "Content-type: text/html; charset=iso-8859-1\n";
  $headers .= "X-Priority: 1\n";
  $headers .= "X-MSMail-Priority: High\n";
  $headers .= "X-Mailer: php\n";
  $headers .= "From: \"".$myname."\" <".$myemail.">\n";
  return(mail("\"".$contactname."\" <".$contactemail.">", $subject, $message, $headers));
}













// copia de calendario

function calcula_numero_dia_semana($dia,$mes,$ano){
        $numerodiasemana = date('w', mktime(0,0,0,$mes,$dia,$ano));
        if ($numerodiasemana == 0)
                $numerodiasemana = 6;
        else
                $numerodiasemana--;
        return $numerodiasemana;
}


function dame_nombre_mes($mes){
         switch ($mes){
                 case 1:
                        $nombre_mes="Enero";
                        break;
                 case 2:
                        $nombre_mes="Febrero";
                        break;
                 case 3:
                        $nombre_mes="Marzo";
                        break;
                 case 4:
                        $nombre_mes="Abril";
                        break;
                 case 5:
                        $nombre_mes="Mayo";
                        break;
                 case 6:
                        $nombre_mes="Junio";
                        break;
                 case 7:
                        $nombre_mes="Julio";
                        break;
                 case 8:
                        $nombre_mes="Agosto";
                        break;
                 case 9:
                        $nombre_mes="Septiembre";
                        break;
                 case 10:
                        $nombre_mes="Octubre";
                        break;
                 case 11:
                        $nombre_mes="Noviembre";
                        break;
                 case 12:
                        $nombre_mes="Diciembre";
                        break;
        }
        return $nombre_mes;
}

function dame_estilo($dia_imprimir){
        global $mes,$ano,$dia_solo_hoy,$tiempo_actual;
        //dependiendo si el d�a es Hoy, Domigo o Cualquier otro, devuelvo un estilo
        if ($dia_solo_hoy == $dia_imprimir && $mes==date("n", $tiempo_actual) && $ano==date("Y", $tiempo_actual)){
                //si es hoy
                $estilo = " class='hoy'";
        }else{
                $fecha=mktime(12,0,0,$mes,$dia_imprimir,$ano);
                if (date("w",$fecha)==0){
                        //si es domingo
                        $estilo = " class='domingo'";
                }else{
                        //si es cualquier dia
                        $estilo = " class='diario'";
                }
        }
        return $estilo;
}

function mostrar_calendario($mes,$ano){
        global $parametros_formulario;
        //tomo el nombre del mes que hay que imprimir
        $nombre_mes = dame_nombre_mes($mes);

        //construyo la cabecera de la tabla
        echo "<table width=200 cellspacing=3 cellpadding=2 border=0><tr><td colspan=7 align=center class=tit>";
        echo "<table width=100% cellspacing=2 cellpadding=2 border=0><tr><td style=font-size:10pt;font-weight:bold;color:white>";
        //calculo el mes y ano del mes anterior
        $mes_anterior = $mes - 1;
        $ano_anterior = $ano;
        if ($mes_anterior==0){
                $ano_anterior--;
                $mes_anterior=12;
        }
        echo "<a style=color:white;text-decoration:none href=index.php?$parametros_formulario&nuevo_mes=$mes_anterior&nuevo_ano=$ano_anterior>&lt;&lt;</a></td>";
           echo "<td align=center class=tit>$nombre_mes $ano</td>";
           echo "<td align=right style=font-size:10pt;font-weight:bold;color:white>";
        //calculo el mes y ano del mes siguiente
        $mes_siguiente = $mes + 1;
        $ano_siguiente = $ano;
        if ($mes_siguiente==13){
                $ano_siguiente++;
                $mes_siguiente=1;
        }
        echo "<a style=color:white;text-decoration:none href=index.php?$parametros_formulario&nuevo_mes=$mes_siguiente&nuevo_ano=$ano_siguiente>&gt;&gt;</a></td></tr></table></td></tr>";
        echo '        <tr>
                            <td width=14% align=center class=altn>L</td>
                            <td width=14% align=center class=altn>M</td>
                            <td width=14% align=center class=altn>X</td>
                            <td width=14% align=center class=altn>J</td>
                            <td width=14% align=center class=altn>V</td>
                            <td width=14% align=center class=altn>S</td>
                            <td width=14% align=center class=altn>D</td>
                        </tr>';

        //Variable para llevar la cuenta del dia actual
        $dia_actual = 1;

        //calculo el numero del dia de la semana del primer dia
        $numero_dia = calcula_numero_dia_semana(1,$mes,$ano);
        //echo "Numero del dia de demana del primer: $numero_dia <br>";

        //calculo el �ltimo dia del mes
        $ultimo_dia = ultimoDia($mes,$ano);

        //escribo la primera fila de la semana
        echo "<tr>";
        for ($i=0;$i<7;$i++){
                if ($i < $numero_dia){
                        //si el dia de la semana i es menor que el numero del primer dia de la semana no pongo nada en la celda
                        echo "<td></td>";
                } else {
                        echo "<td align=center><a href='javascript:devuelveFecha($dia_actual,$mes,$ano)'". dame_estilo($dia_actual) .">$dia_actual</a></td>";
                        $dia_actual++;
                }
        }
        echo "</tr>";

        //recorro todos los dem�s d�as hasta el final del mes
        $numero_dia = 0;
        while ($dia_actual <= $ultimo_dia){
                //si estamos a principio de la semana escribo el <TR>
                if ($numero_dia == 0)
                        echo "<tr>";
                echo "<td align=center><a href='javascript:devuelveFecha($dia_actual,$mes,$ano)'". dame_estilo($dia_actual) .">$dia_actual</a></td>";
                $dia_actual++;
                $numero_dia++;
                //si es el u�timo de la semana, me pongo al principio de la semana y escribo el </tr>
                if ($numero_dia == 7){
                        $numero_dia = 0;
                        echo "</tr>";
                }
        }

        //compruebo que celdas me faltan por escribir vacias de la �ltima semana del mes
        for ($i=$numero_dia;$i<7;$i++){
                echo "<td></td>";
        }

        echo "</tr>";
        echo "</table>";
}

function formularioCalendario($mes,$ano){
        global $parametros_formulario;
echo '
        <br>
        <table align="center" cellspacing="2" cellpadding="2" border="0" class=tform>
        <tr><form action="index.php?' . $parametros_formulario . '" method="POST">';
echo '
    <td align="center" valign="top">
                Mes: <br>
                <select name=nuevo_mes>
                <option value="1"';
if ($mes==1)
 echo "selected";
echo'>Enero
                <option value="2" ';
if ($mes==2)
        echo "selected";
echo'>Febrero
                <option value="3" ';
if ($mes==3)
        echo "selected";
echo'>Marzo
                <option value="4" ';
if ($mes==4)
        echo "selected";
echo '>Abril
                <option value="5" ';
if ($mes==5)
                echo "selected";
echo '>Mayo
                <option value="6" ';
if ($mes==6)
        echo "selected";
echo '>Junio
                <option value="7" ';
if ($mes==7)
        echo "selected";
echo '>Julio
                <option value="8" ';
if ($mes==8)
        echo "selected";
echo '>Agosto
                <option value="9" ';
if ($mes==9)
        echo "selected";
echo '>Septiembre
                <option value="10" ';
if ($mes==10)
        echo "selected";
echo '>Octubre
                <option value="11" ';
if ($mes==11)
        echo "selected";
echo '>Noviembre
                <option value="12" ';
if ($mes==12)
    echo "selected";
echo '>Diciembre
                </select>
                </td>';
echo '
            <td align="center" valign="top">
                A&ntilde;o: <br>
                <select name=nuevo_ano>';

for ($cont=1900;$cont<$ano+3;$cont++){
        echo "<option value='$cont'";
        if ($ano==$cont)
                   echo " selected";
           echo ">$cont";
}
echo '
        </select>
                </td>';
echo '
        </tr>
        <tr>
            <td colspan="2" align="center"><input type="Submit" value="[ IR A ESE MES ]"></td>
        </tr>
        </table><br>

        <br>

        </form>';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Funci�n que escribe en la p�gina un fomrulario preparado para introducir una fecha y enlazado con el calendario para seleccionarla comodamente
////////////////////////////////////////////////////////////////////////////////////////////////////////////
function escribe_formulario_fecha_vacio($nombrecampo,$nombreformulario){
        global $raiz;
        echo '
        <INPUT name="'.$nombrecampo.'" size="10">
        <input type=button value="Seleccionar fecha" onclick="muestraCalendario(\''. $raiz.'\',\''. $nombreformulario .'\',\''.$nombrecampo.'\')">
        ';
}

function cortarCadena($cadena){
                $tok = strtok($cadena,"__");
        $i = 0;
        while($tok){
                $datos[$i] = $tok;
                $tok = strtok("__");
                $i++;
        }

        return $datos;
}

function cortarCadena2($cadena,$delimitador){
                $tok = strtok($cadena,$delimitador);
        $i = 0;
        while($tok){
                $datos[$i] = $tok;
                $tok = strtok($delimitador);
                $i++;
        }

        return $datos;
}

function reporteExcel($user,$cabecera,$q1,$B,$prin,$TI){
        $archivo_csv = $user.'_exp';
        $archivo_temp = $user.'_cont';
        $ruta = "/sistemaweb/tmp/$archivo_csv.csv";
        exec("rm -Rf /sistemaweb/tmp/$archivo_temp.csv");
 for($i=0;$i<count($q1);$i++){
        $url_csv = sacarExcel($user,$cabecera[$i],$q1[$i],$TI[$i]);
        exec("echo $prin[$i] >> /sistemaweb/tmp/$archivo_temp.csv");
        exec("cat $ruta >> /sistemaweb/tmp/$archivo_temp.csv");
        exec("echo '' >> /sistemaweb/tmp/$archivo_temp.csv");
        //echo count($q1);
        }
        exec("echo '$B[0]' >> /sistemaweb/tmp/$archivo_temp.csv");
        return "/sistemaweb/tmp/$archivo_temp.csv";
}

function sacarExcel($user,$cabecera,$q1,$titulo){

	$tabla_temp = $user.'_tmp';
	$archivo_csv = $user.'_exp';
	
	pg_exec("create table $tabla_temp as $q1");
	pg_exec("copy $tabla_temp to '/sistemaweb/tmp/$tabla_temp.csv' with delimiter as ','");
	pg_exec("drop table $tabla_temp");
	if($titulo!=""){exec("echo -e '$titulo' > /sistemaweb/tmp/$tabla_temp.txt");}
	exec("echo ',,,,,,,,,,' s>> /sistemaweb/tmp/$tabla_temp.txt");
	exec("echo '$cabecera' >> /sistemaweb/tmp/$tabla_temp.txt");
	exec("rm -Rf /sistemaweb/tmp/$tabla_temp2.csv");
	exec("cat /sistemaweb/tmp/$tabla_temp.csv >> /sistemaweb/tmp/$tabla_temp2.csv");
	exec("cat /sistemaweb/tmp/$tabla_temp2.csv >>/sistemaweb/tmp/$tabla_temp.txt" );
	exec("cat /sistemaweb/tmp/$tabla_temp.txt >/sistemaweb/tmp/$archivo_csv.csv" );
	exec("rm -Rf /sistemaweb/tmp/$tabla_temp.txt");
	//retorna la url donde deja el csv
	return "/sistemaweb/tmp/$archivo_csv.csv";
}

function completarCeros($cadena, $long_final, $complemento){

        $long_inicial = strlen($cadena);
        for($i=0;$i<$long_final - $long_inicial;$i++){
        $cadena = $complemento.$cadena ;
        }
        return $cadena;

}

function diferenciarArrays($A,$limit){
        /*Se saca un juego de opciones haasta el limit
         con ese limit se crea otro array con sus valores de cero hasta limit
         para luego filtrarle los valores del array que estan pasando como $A
        */
        for($a=0;$a<$limit;$a++){
        if ( $a+1<10 ) {
             $B[$a] = "0".($a+1);
        } else {
             $B[$a] = $a+1;
             }
        }
        $X = array_diff($B,$A);
        $K = array_keys($X);

        for($n=0;$n<count($K);$n++){
        $Res[$n] = $X[$K[$n]];
        }
        return $Res;

}



function sobrantesyfaltantesReporte($almacen,$cod_tanque,$fechad,$fechaa, $unidadmedida, $detallecompras, $buscar_por_tanque = true){
	
	//Aqui se saca el codigo del combustible de una vez para no estar metiendo mas cosas
	$comb = pg_exec("SELECT 
				ch_codigocombustible 
			FROM 
				comb_ta_tanques 
			WHERE 
				ch_tanque='$cod_tanque'
				AND ch_sucursal=trim('$almacen')");
	$C = pg_fetch_row($comb,0);
	$cod_combustible=$C[0];
	// Lo movi aca JCP 04/09/2010
	if(trim($cod_combustible)=='11620307'&&$unidadmedida=='Litros_a_Galones'){
		$factor=3.785411784;
                $operacion='/';
	}else if(trim($cod_combustible)=='11620307'&&$unidadmedida=='Galones_a_Litros'){
		$factor=3.785411784;
                $operacion='*';
        }else{
                $factor=1; //Por defecto todo esta en litros, mencionar que segun lo indicado en OPENSOFT-93, por defecto la unidad de medida de GLP vendra en Galones, ya no en Litros
                $operacion='/';
        }
        
        // echo "Query 1:";
	// echo "<pre>";
	// echo $comb;
	// echo "</pre>";	
	
        //FECHA
        if($buscar_por_tanque){
                $qf = "SELECT
			to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
			SUM(nu_medicion) AS saldo,
			to_char(dt_fechamedicion- interval '1 day','DD-MM-YYYY') AS fecha2
		FROM 
			comb_ta_mediciondiaria
		WHERE 
			ch_sucursal=trim('$almacen')
			AND ch_tanque='$cod_tanque'
			AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
			AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
		GROUP BY 
			dt_fechamedicion,
			fecha2 
		ORDER BY 
			dt_fechamedicion";
        }else{
                $qf = "SELECT 
			to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
			SUM(nu_medicion) AS saldo,
			to_char(dt_fechamedicion- interval '1 day','DD-MM-YYYY') AS fecha2
		FROM 
			comb_ta_mediciondiaria med
                        INNER JOIN comb_ta_tanques tan ON (med.ch_tanque = tan.ch_tanque)
		WHERE 
			med.ch_sucursal=trim('$almacen')
                        AND tan.ch_sucursal=trim('$almacen')
                        AND tan.ch_codigocombustible = '$cod_combustible'
			AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
			AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
		GROUP BY 
			dt_fechamedicion,
			fecha2 
		ORDER BY 
			dt_fechamedicion";
        }
        // echo "FECHA";
        // echo "<pre>";
        // echo $qf;
        // echo "</pre>";
        $rs1 = pg_exec($qf);
        
        // echo "Query 2:";
        // echo "<pre>";
        // echo $qf;
        // echo "</pre>";
	
	//IF POR LO QUE ENCONTO FRED
	if(pg_numrows($rs1)>0){
		for($i=0;$i<pg_numrows($rs1);$i++){
		
			$A = pg_fetch_row($rs1,$i);
		
			$rep[$i][0] = $A[0];
			$fec[$i] = $A[0];
			$fec_saldo[$i] = $A[2];
		}
	
                //SALDO
                if($buscar_por_tanque){
                        $qe =  "SELECT
				to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
				ROUND(SUM(nu_medicion) $operacion '$factor',3) AS saldo
			FROM	
				comb_ta_mediciondiaria
			WHERE 
				ch_sucursal=trim('$almacen')
				AND dt_fechamedicion >= to_date('$fec_saldo[0]','dd-mm-yyyy')
				AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
				AND ch_tanque='$cod_tanque'
			GROUP BY 
				dt_fechamedicion 
			ORDER BY 
				dt_fechamedicion";
                }else{
                        $qe =  "SELECT 
				to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
				ROUND(SUM(nu_medicion) $operacion '$factor',3) AS saldo
			FROM	
				comb_ta_mediciondiaria med
                                INNER JOIN comb_ta_tanques tan ON (med.ch_tanque = tan.ch_tanque)
			WHERE 
                                med.ch_sucursal=trim('$almacen')
                                AND tan.ch_sucursal=trim('$almacen')
				AND dt_fechamedicion >= to_date('$fec_saldo[0]','dd-mm-yyyy')
				AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
                                AND tan.ch_codigocombustible = '$cod_combustible'
			GROUP BY 
				dt_fechamedicion 
			ORDER BY 
				dt_fechamedicion";
                }
                // echo "SALDO";
                // echo "<pre>";
                // echo $qe;
                // echo "</pre>";
	
                $rs1 = pg_exec($qe) ;
                
                // echo "Query 3:";
                // echo "<pre>";
                // echo $qe;
                // echo "</pre>";
	
		for($i=0;$i<pg_numrows($rs1);$i++){
			$A = pg_fetch_row($rs1,$i);
			$Fe[$i] = $A[0];
			$Saldo[$i] = $A[1];
		}
	
		for($i=0;$i<count($fec_saldo);$i++){
			$rep[$i][1] = "0.000";
			for($a=0;$a<count($Fe);$a++){
				if($Fe[$a]==$fec_saldo[$i]){  
					$rep[$i][1] = $Saldo[$a];
				}
			}
		}

		//COMPRA
		$limit = count($fec)-1;
		$rs1 = pg_exec("SELECT 
					to_char(mov_fecha::DATE,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(mov_cantidad) $operacion '$factor',3) AS compra
				FROM
					inv_movialma mov 
				WHERE 
					tran_codigo	= '21'
					AND mov_almacen	= trim('$almacen')
					AND art_codigo	= '$cod_combustible'
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','dd-mm-yyyy')
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','dd-mm-yyyy')
				GROUP BY 
					mov_fecha::DATE");

		for($a=0;$a<pg_numrows($rs1);$a++){
				$A = pg_fetch_row($rs1,$a);
				$F2[$a] = $A[0];
				$COMPRA[$a] = $A[1];
		}

		for($i=0;$i<count($fec);$i++){
			$rep[$i][2] = "0.000";
			for($b=0;$b<count($F2);$b++)
			if($F2[$b]==$fec[$i]){  $rep[$i][2] = $COMPRA[$b];  }
		}
	
		//MEDICION O AFERICION

		$rs1 = pg_exec("
				SELECT
					TO_CHAR(a.dia, 'DD-MM-YYYY') AS fecha,
					ROUND(SUM(a.cantidad) $operacion '$factor',3) AS medicion
				FROM
					pos_ta_afericiones a
					LEFT JOIN comb_ta_tanques t ON(t.ch_codigocombustible = a.codigo AND t.ch_sucursal = a.es)
				WHERE
					a.es = trim('$almacen')
                                        AND t.ch_sucursal = trim('$almacen')
					AND a.dia BETWEEN to_date('$fechad','dd-mm-yyyy') AND to_date('$fechaa','dd-mm-yyyy')
					AND t.ch_tanque = '$cod_tanque'
				GROUP BY
					a.dia
				ORDER BY
					a.dia
				");

		for($a=0;$a<pg_numrows($rs1);$a++){
			$A = pg_fetch_row($rs1,$a);
			$F3[$a] = $A[0];
			$AFE[$a] = $A[1];
		}

		for($i=0;$i<count($fec);$i++){
			$rep[$i][3] = "0.000";
			for($b=0;$b<count($F3);$b++)
			if($F3[$b]==$fec[$i]){  $rep[$i][3] = $AFE[$b];  }
		}
                //VENTA
                if($buscar_por_tanque){
		$rs1 = pg_exec("SELECT 
					to_char(dt_fechaparte,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(cont.nu_ventagalon) $operacion '$factor',3) AS venta,
                    CASE 
                    WHEN SUM(cont.nu_ventagalon) = 0 THEN 0.00
                    ELSE
                    ROUND((COALESCE(SUM(cont.nu_ventavalor),0) / COALESCE(SUM(cont.nu_ventagalon),1)) , 2) 
                    END AS nu_precio_venta
				FROM 
					comb_ta_contometros cont
				WHERE 
					cont.ch_sucursal=trim('$almacen')
					AND dt_fechaparte >= to_date('$fechad','dd-mm-yyyy')
					AND dt_fechaparte <= to_date('$fechaa','dd-mm-yyyy')
					AND cont.ch_tanque='$cod_tanque'
				GROUP BY 
                                        dt_fechaparte");
                }else{
                $rs1 = pg_exec("SELECT 
					to_char(dt_fechaparte,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(cont.nu_ventagalon) $operacion '$factor',3) AS venta,
                    CASE 
                    WHEN SUM(cont.nu_ventagalon) = 0 THEN 0.00
                    ELSE
                    ROUND((COALESCE(SUM(cont.nu_ventavalor),0) / COALESCE(SUM(cont.nu_ventagalon),1)) , 2) 
                    END AS nu_precio_venta
				FROM 
					comb_ta_contometros cont
				WHERE 
					cont.ch_sucursal=trim('$almacen')
					AND dt_fechaparte >= to_date('$fechad','dd-mm-yyyy')
					AND dt_fechaparte <= to_date('$fechaa','dd-mm-yyyy')
                                        AND cont.ch_codigocombustible = '$cod_combustible'
				GROUP BY 
                                        dt_fechaparte");        
                }
                // echo "VENTA";
                // echo "<pre>";
                // echo $rs1;
                // echo "</pre>";

		for($a=0;$a<pg_numrows($rs1);$a++){
			$A = pg_fetch_row($rs1,$a);
			$F4[$a]             = $A[0];
			$VENTA[$a]          = $A[1];
            $PRECIO_VENTA[$a]   = $A[2];
		}

		for($i = 0; $i < count($fec); $i++){

			$rep[$i][4] = "0.000";

			for($b = 0; $b < count($F4); $b++)

			if($F4[$b]==$fec[$i]){
                $rep[$i][4] = $VENTA[$b];
                $rep[$i][12] = $PRECIO_VENTA[$b];
            }

		}

		//INGRESO
		$rs1 = pg_exec("SELECT 
					to_char(mov_fecha::date,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(mov_cantidad) $operacion '$factor',3) AS compra
				FROM 
					inv_movialma
				WHERE 
					mov_almacen=trim('$almacen')
					AND art_codigo='$cod_combustible'
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','DD-MM-YYYY')
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','DD-MM-YYYY')
					AND tran_codigo='27' 
				GROUP BY 
					mov_fecha::date");



		for($a=0;$a<pg_numrows($rs1);$a++){
			$A = pg_fetch_row($rs1,$a);
			$F5[$a] = $A[0];
			$ING[$a] = $A[1];
		}

		for($i=0;$i<count($fec);$i++){
			$rep[$i][5] = "0.000";
			for($b=0;$b<count($F5);$b++)
			if($F5[$b]==$fec[$i]){  $rep[$i][5] = $ING[$b];  }
		}
	
		//SALIDA
		$rs1 = pg_exec("SELECT 
					to_char(mov_fecha::date,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(mov_cantidad) $operacion '$factor',3) AS compra
				FROM 
					inv_movialma
				WHERE 
					mov_almacen=trim('$almacen')
					AND art_codigo='$cod_combustible'
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') >= to_date('$fec[0]','DD-MM-YYYY')
					AND to_date(to_char(mov_fecha,'DD-MM-YYYY'),'DD-MM-YYYY') <= to_date('$fec[$limit]','DD-MM-YYYY')
					AND tran_codigo='28' 
				GROUP BY 
					mov_fecha::date");

		for($a=0;$a<pg_numrows($rs1);$a++){
			$A = pg_fetch_row($rs1,$a);
			$F6[$a] = $A[0];
			$SAL[$a] = $A[1];
		}

		for($i=0;$i<count($fec);$i++){
			$rep[$i][6] = "0.000";
			for($b=0;$b<count($F6);$b++)
			if($F6[$b]==$fec[$i]){  $rep[$i][6] = $SAL[$b];  }
		}
	
		//PARTE
		for($i=0;$i<count($fec);$i++){
			$rep[$i][7] = $rep[$i][1]+$rep[$i][2]+$rep[$i][3]-$rep[$i][4]+$rep[$i][5]-$rep[$i][6];
		}
	
                //VARILLA
                if($buscar_por_tanque){
		$rs1 = pg_exec("SELECT 
					to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(nu_medicion) $operacion '$factor',3) AS saldo
				FROM 
					comb_ta_mediciondiaria
				WHERE 
					ch_sucursal=trim('$almacen')
					AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
					AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
					AND ch_tanque='$cod_tanque'
	
				GROUP BY 
					dt_fechamedicion 
                                ORDER BY dt_fechamedicion");
                }else{
                $rs1 = pg_exec("SELECT 
					to_char(dt_fechamedicion,'DD-MM-YYYY') AS fecha,
					ROUND(SUM(nu_medicion) $operacion '$factor',3) AS saldo
				FROM 
					comb_ta_mediciondiaria med
                                        INNER JOIN comb_ta_tanques tan ON (med.ch_tanque = tan.ch_tanque)
				WHERE 
                                        med.ch_sucursal=trim('$almacen')
                                        AND tan.ch_sucursal=trim('$almacen')
					AND dt_fechamedicion >= to_date('$fechad','dd-mm-yyyy')
					AND dt_fechamedicion <= to_date('$fechaa','dd-mm-yyyy')
                                        AND tan.ch_codigocombustible = '$cod_combustible'
	
				GROUP BY 
					dt_fechamedicion 
                                ORDER BY dt_fechamedicion");
                }
                // echo "VARILLA";
                // echo "<pre>";
                // echo $rs1;
                // echo "</pre>";
	
		for($i=0;$i<pg_numrows($rs1);$i++){
			$A = pg_fetch_row($rs1,$i);
			$FE8[$i] = $A[0];
			$VARI[$i] = $A[1];
		}
	
		for($i=0;$i<count($fec);$i++){
			$rep[$i][8] = "0.000";
			for($a=0;$a<count($FE8);$a++){
				if($FE8[$a]==$fec[$i]){  $rep[$i][8] = $VARI[$a];  }
			}
		}
	
		//DIARIA
		for($i=0;$i<count($fec);$i++){
			$rep[$i][9] = $rep[$i][8]-$rep[$i][7];
		}
	
		//ACUMULADA
		for($i=0;$i<count($fec);$i++){
		
			$rep[$i][10] = $rep[$i][9]+$rep[$i-1][10];
		
		}
	
	} //FIN DEL IF DE LO QUE ENCONTRO FRED
	
	return $rep;
}
//FIN DE REPORTE DE SOBRANTES Y FALTANTES POR TANQUE O COMBUSTIBLE

function DetalleComprasReporte($almacen,$cod_tanque,$fechad,$fechaa,$unidadmedida,$detallecompras){

	//Aqui se saca el codigo del combustible de una vez para no estar metiendo mas cosas
	$comb = pg_exec("SELECT 
				ch_codigocombustible 
			FROM 
				comb_ta_tanques 
			WHERE 
				ch_tanque='$cod_tanque'
				AND ch_sucursal=trim('$almacen')");
	$C = pg_fetch_row($comb,0);
	$cod_combustible=$C[0];

        if(trim($cod_combustible)=='11620307'&&$unidadmedida=='Litros_a_Galones'){
		$factor=3.785411784;
                $operacion='/';
	}else if(trim($cod_combustible)=='11620307'&&$unidadmedida=='Galones_a_Litros'){
		$factor=3.785411784;
                $operacion='*';
        }else{
                $factor=1; //Por defecto todo esta en litros, mencionar que segun lo indicado en OPENSOFT-93, por defecto la unidad de medida de GLP vendra en Galones, ya no en Litros
                $operacion='/';
        }

	$qf = "	SELECT
			to_char(a.mov_fecha,'DD-MM-YYYY') as fecha,
			a.mov_tipdocuref||' - '||a.mov_docurefe as documento,
			b.kilos as kilos,
			b.ge as ge,
			CASE WHEN '$codigo_combustible'='11620307' THEN  b.galones  ELSE round(a.mov_cantidad $operacion $factor,5) END as galones
		FROM
			inv_movialma  a 
			LEFT JOIN inv_calculo_glp b ON (a.mov_numero=b.mov_numero AND a.tran_codigo=b.tran_codigo AND a.art_codigo=b.art_codigo AND date_trunc('day',a.mov_fecha)=date_trunc('day', b.mov_fecha))
		WHERE
			a.tran_codigo='21'
			AND a.mov_fecha BETWEEN to_date('$fechad','dd-mm-yyyy') AND to_date('$fechaa','dd-mm-yyyy')
			AND a.mov_almacen=trim('$almacen')
			AND a.art_codigo=trim('$cod_combustible')
		ORDER BY 
			a.mov_fecha ASC";

	$rs1 = pg_exec($qf);
	
	if(pg_numrows($rs1)>0){
		for($i=0;$i<pg_numrows($rs1);$i++){
		
			$A = pg_fetch_row($rs1,$i);
		
			$rep[$i][0] = $A[0];
			$rep[$i][1] = $A[1];
			$rep[$i][2] = $A[2];
			$rep[$i][3] = $A[3];
			$rep[$i][4] = $A[4];

			//$fec[$i] = $A[0];
			//$fec_saldo[$i] = $A[2];
		}
	
	} 
	
	return $rep;
}

/*
*
*        FUNCION QUE OBTIENE LA DESCRIPCION LARGA DE UNA ALMACEN
*        a partir del codigo de almacen
*/

function otorgarAlmacen($conector_id, $codigo_almacen)
{
        $query = "select ch_nombre_almacen from inv_ta_almacenes where trim(ch_almacen)='".trim($codigo_almacen)."'";
        //echo $query;
        $xquery = pg_query($conector_id, $query);
        if(0>=pg_num_rows($xquery))
        {
                $resultado = "ERROR: NO EXISTE ALMACEN";
        }else
        {
                $resultado = pg_result($xquery,0,0);
        }
        return $resultado;
}








