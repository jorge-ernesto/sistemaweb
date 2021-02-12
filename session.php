<?php
		session_start();
		$user = $usuario;
		session_destroy();

        session_start();
        $usuario=$pmUser;

        $perfil=$pmPerfil;
		$sist=$pmSist;
        session_register("usuario");
        session_register("perfil");
        session_register("sist");
		session_register("txtalmac");
		session_register("almacen");
//--------------------------------------------------------------------------------------------------------------------
//SESSION PARA ELECCION DE IMPRESORAS

include("config.php");

        $ip=$_SERVER['REMOTE_ADDR'];

           $sql1="select print_server from list_impresoras where trim(print_server)=trim('".$ip."') ";

        $sql_print=pg_exec($sql1);

        $rs_print=pg_numrows($sql_print);


   if($rs_print!=0){

        $z=pg_result($sql_print,0,0);

        }

   else {

        $rs = pg_exec("select par_valor as print_server from int_parametros
            where par_nombre ='print_server' ");

            $A = pg_fetch_array($rs,0);
            $z= $A["print_server"];


        }

        $_SESSION['ip_printer_default']=$z;

        echo $_SESSION['ip_printer_default'];

        //$_SESSION['ip_printer_default']='192.12.15.12';


//--------------------------------------------------------------------------------------------------------------------



// no carga en el primer momento el valor de PHPSESSID se toma por defecto de la variable SID
if ( strlen(trim($_COOKIE["PHPSESSID"])) >0)
	{	$sesion=trim($_COOKIE["PHPSESSID"]);}
else
	{	$sesion=trim(substr(SID,10,50));	}
	
//echo $sesion;
//include("config.php");

$sfecha=date("Y-m-d H:i:s");
$xsqldel=pg_exec($coneccion,"delete from tab_logueo where usr='".$usuario."' ");
$sql="insert into tab_logueo(usr,fec_ini,ip,almac,id_sesion,sist) values('".$usuario."','".$sfecha."','".$_SERVER["REMOTE_ADDR"]."','".$txtalmac."','".$sesion."','".$sist."') ";
$xsql=pg_exec($coneccion,$sql);
echo "<meta http-equiv='refresh' content='0;URL=menu_princ.php?pmPerfil=".$pmPerfil."&pmSist=".$pmSist."'>";
//    echo "<meta http-equiv='refresh' content='0;URL=menu_princ.php'>";
pg_close($coneccion);
