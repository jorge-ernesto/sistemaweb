<script src="/sistemaweb/menu/milonic_src.js" type="text/javascript"></script>
<script src="/sistemaweb/menu/mmenudom.js" type="text/javascript"></script>
<script>
fixMozillaZIndex=true; //Fixes Z-Index problem  with Mozilla browsers but causes odd scrolling problem, toggle to see if it helps
_menuCloseDelay=500;
_menuOpenDelay=150;
_subOffsetTop=0;
_subOffsetLeft=0;

with(AcosaMainStyle=new mm_style()){
    //bordercolor="#8A867A";
    borderstyle="none";
   // borderwidth=1;
    fontfamily="Arial";
    fontsize="14px";
    fontstyle="normal";
    fontweight="normal";
    //offbgcolor="#ffffff";
    offbgcolor="#30767F"; // color de fondo del menu principal
    offcolor="white";
    onbgcolor="white";
    onborder="1px solid #000080";
    oncolor="#30767F";  // letras del menu superior al pasar sobre ellas
    //oncolor="#ffffff";

    padding=3;
}

with(AcosaMenuStyle=new mm_style()){
    styleid=1;
    //bordercolor="#000000";
    borderstyle="none";
    //borderwidth=1;
    fontfamily="Arial";
    fontsize="14px";
    fontstyle="normal";
    fontweight="normal";
    image="/sistemaweb/images/isla.png";
    imagepadding=3;
    //menubgimage="/sistemaweb/menu/backoff2003_web.gif";
    offbgcolor="#30767F";  // color de fondo del menu
    offcolor="white";
    onbgcolor="white";
    onborder="1px solid #000080";
    oncolor="#30767F";  //letras del menu al pasar sobre ellas
    outfilter="randomdissolve(duration=0.3)";
    overfilter="Fade(duration=0.2);Alpha(opacity=90);Shadow(color=#B8D1F8', Direction=135, Strength=5)";
    //padding=4;
    separatoralign="right";
    //separatorcolor="#6A8CCB";
    separatorpadding=1;
    separatorwidth="10px";
    subimage="/sistemaweb/menu/arrow.gif";
    subimagepadding=3;
    menubgcolor="#EBF0EC";
}
<?php //</script><?php
global $sqlca,$usuario,$gids;
$grupo_fin = $usuario->getGIDs();
$gids = implode(",",$grupo_fin);

function drawSumary($id) {
	global $sqlca,$usuario,$gids;

	$mnu_name = "mnu_{$id}";

	if ($gids == "0") {
		$sql = "	SELECT
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
		$sql = "	SELECT
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
                                
                          

	if ($sqlca->query($sql,$mnu_name) < 0)
		return "ERROR";

	$rm = "with(milonic=new menuname(\"{$mnu_name}\")){\noverflow=\"scroll\";\n";
	if ($id == 0)
		$rm .= "alwaysvisible=1;\nleft=10;\norientation=\"horizontal\";\ntop=65;\nstyle=AcosaMainStyle;\n";
	else
		$rm .= "style=AcosaMenuStyle;\n";

	$xm = "";
	$ym = "";
	for ($i = 0;$i < $sqlca->numrows($mnu_name);$i++) {
		$r = $sqlca->fetchRow($mnu_name);

		if ($r[2] == 1) {	
			$sm = drawSumary($r[0]);
			if ($sm != "") {
				$ym .= "aI(\"showmenu=mnu_{$r[0]};text=" . $r[1] . "\");\n";
				$xm .= $sm;
			}
		} else
			$ym .= "aI(\"text=" . $r[1] . ";url=$r[3]\");\n";
	}
	$rm .= $ym . "}\n";

	if ($ym == "")
		return "";

	return $rm . $xm;
}
echo drawSumary(0);
?>
drawMenus();
</script>

<table cellpadding="0" cellspacing="0" border="0" width="100%"><TR><TD width="25%" height="50px"><img src='/sistemaweb/images/tux223.png'></TD><TD width="50%" align="center"><h2>SISTEMA OpenSoft<br/>Almacen : <?php echo $usuario->obtenerAlmacenActual()." - ";$sqlca->query("SELECT ch_nombre_almacen FROM inv_ta_almacenes WHERE ch_almacen = '" . $usuario->obtenerAlmacenActual()."'");$a = $sqlca->fetchRow(); echo $a[0];
?> Usuario : <?php echo $usuario->obtenerUsuario() ?></h2></TD><TD width="25%" align="right"><img src='/sistemaweb/images/logocia.png' height="45px" >&nbsp;</TD></TR><TR><TD width="100%" colspan="3" height="35px" style="border-top: 1px solid rgb(0,0,0); " align="right" valign="middle">&nbsp;</TD></TR></table>

<!--<iframe src="" marginwidth="0" marginheight="0" name="ventana_iframe" scrolling="no" border="1" 
frameborder="0" width="100%" height="600">
</iframe>-->
<div id="centr"><div>