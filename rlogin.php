<?php
	SetCookie("txtuser",$_REQUEST[txtlogin],time()+604800);
	$txtlogin=$_REQUEST[txtlogin];
	$txtpassword=$_REQUEST[txtpassword];
	$txtalmac=$_REQUEST[txtalmac];
	$sist=$_REQUEST[sist];

	include("config.php");
	$txtlogin=trim($txtlogin);  $txtpassword=trim($txtpassword);
	$sql="select * from usuario where codusuario='".$txtlogin."' and passw='".$txtpassword."' ";
	$resultado=pg_exec($coneccion, $sql);
	$total=pg_numrows($resultado);

	if($total==0) {
?>
	<html>
		<link rel="stylesheet" href="<?php echo $v_path_url; ?>sistemaweb.css" type="text/css">
		<head>
			<title>AUTENTIFICACION</title>
			<meta http-equiv='Expires' content='Tue, 01 Jan 1980 1:00:00 GMT'>
			<meta http-equiv='Pragma' content='no-cache'>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
			<?php echo"<meta http-equiv='Refresh' content='2;URL=login.php?txtlogin=".$txtlogin."'>"; ?>
		</head>
		<body>
			<br>
			<br>
			<font size="2" face="Verdana, Arial, Helvetica, sans-serif" color="#003399">LA CONTRASEÑA ES INCORRECTA<br></font><font size="2"> </font><br>
		</body>
        </html>
<?php
	} else {
	    	if(trim($txtalmac)!="") {
			$almacen = trim($txtalmac);
			$perfil=rtrim(pg_result($resultado,0,"codperfil"));
		    	echo "<meta http-equiv='refresh' content='0;URL=session.php?pmUser=".$txtlogin."&pmPerfil=".$perfil."&txtalmac=".$txtalmac."&pmSist=".$sist."&almacen=".$almacen."'>";
		} else {
			header("location: login.php");
		}	}
	pg_close($coneccion);
