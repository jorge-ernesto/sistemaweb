<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../valida_sess.php");
include("../config.php");

global $sqlca,$usuario,$gids;
$grupo_fin = $usuario->getGIDs();
$gids = implode(",",$grupo_fin);

function drawSumary($id, $isInternal = true) {
	global $sqlca,$usuario,$gids;
	$mnu_name = "mnu_{$id}";
	if ($gids == "0") {
		$sql = "SELECT
					m.mnu_menu_id,
					first(m.name),
					first(m.issumary),
					first(m.url)
				FROM
					mnu_menu m
				WHERE
					m.parent_id = {$id}
				GROUP BY
					m.mnu_menu_id,
					m.seq
				ORDER BY
					m.seq;";
	} else {
		$sql = "SELECT
					m.mnu_menu_id,
					first(m.name),
					first(m.issumary),
					first(m.url)
				FROM
					mnu_menu m
					LEFT JOIN mnu_access a USING (mnu_menu_id)
				WHERE
					(a.gid IN ({$gids}) OR m.issumary = 1)
					AND m.parent_id = {$id}
				GROUP BY
					m.mnu_menu_id,
					m.seq
				ORDER BY
					m.seq;";
	}

	//echo 'sql: '.$sql;              

	if ($sqlca->query($sql,$mnu_name) < 0) {
		return "ERROR";
	}

	$rm = '';
	/*$rm = "with(milonic=new menuname(\"{$mnu_name}\")){\noverflow=\"scroll\";\n";
	if ($id == 0)
		$rm .= "alwaysvisible=1;\nleft=10;\norientation=\"horizontal\";\ntop=65;\nstyle=AcosaMainStyle;\n";
	else
		$rm .= "style=AcosaMenuStyle;\n";*/

	$xm = "";
	$ym = "";
	for ($i = 0;$i < $sqlca->numrows($mnu_name); $i++) {
		$r = $sqlca->fetchRow($mnu_name);
		if ($r[2] == 1) {
			$sm = drawSumary($r[0]);
			if ($sm != "") {
				//$ym .= "aI(\"showmenu=mnu_{$r[0]};text=" . $r[1] . "\");\n";
				if(!$isInternal) {
					$ym .= '<li class="dropdown">';
					$ym .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$r[1].'<span class="caret"></span></a>';
					$ym .= '<ul class="dropdown-menu">';
					$ym .= $sm;
					$ym .= '</ul>';
					$ym .= '</li>';
				} else {
					$ym .= '<li class="dropdown-submenu">';
					$ym .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$r[1].'</a>';
					$ym .= '<ul class="dropdown-menu">';
					$ym .= $sm;
					$ym .= '</ul>';
					$ym .= '</li>';
				}
			}
		} else {
			//$ym .= "aI(\"text=" . $r[1] . ";url=$r[3]\");\n";
			$ym .= '<li><a href="'.$r[3].'">'.$r[1].'</a></li>';
		}
	}
	//$rm .= $ym . "}\n";
	$rm .= $ym . "\n";

	if ($ym == "") {
		return "";
	}
	return $rm . $xm;
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Items por Almacen - OpenSoft</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
	<script src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js" type="text/javascript"></script>
	<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>-->
	<link rel="stylesheet" href="/sistemaweb/assets/css/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" href="/sistemaweb/assets/css/bootstrap/bootstrap-theme.min.css">
	<script src="/sistemaweb/assets/js/bootstrap/bootstrap.min.js"></script>
	<script src="/sistemaweb/inventarios/js/items_por_almacen.js"></script>
</head>
<body>

	<div class="row">
		<div class="col-md-4" align="left">
			<div class="main-left">
				<img src='/sistemaweb/images/tux223.png' height="35px">
			</div>
		</div>
		<div class="col-md-4" align="center">
			<h5>
				<?php
				$sqlca->query("SELECT ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen = '" . $usuario->obtenerAlmacenActual()."'");
				$a = $sqlca->fetchRow();
				echo '<strong>'.$a[0].'</strong><br>';
				echo 'Almacen: '.$usuario->obtenerAlmacenActual().' - Usuario: '.$usuario->obtenerUsuario();
				?>
			</h5>
		</div>
		<div class="col-md-4" align="right">
			<div class="main-right">
				<img src='/sistemaweb/images/logocia.png' height="35px" >
			</div>
		</div>
	</div>

	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Sistema OpenSoft</a>
			</div>

			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<?php echo drawSumary(0, false) ?>
				</ul>
			</div>
		</div>
	</nav>

	<!--
	<?php
	//include "../menu_princ.php";
	?>
	<div id="footer">&nbsp;</div>
	<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>
	-->
	<?php

	include('/sistemaweb/include/mvc_sistemaweb.php');
	include('reportes/t_items_por_almacen.php');
	include('reportes/m_items_por_almacen.php');

	//Variables de Entrada

	$hoy = date('d/m/Y');
	$model = new ModelItemsPorAlmacen;
	$template = new TemplateItemsPorAlmacen;

	$estaciones	= $model->GetAlmacen('T');
	$lineas		= $model->GetLinea();
	echo $template->Form($estaciones, $lineas, $hoy);

	?>
</body>
</html>