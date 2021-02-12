<?php
include_once('/sistemaweb/valida_sess.php');
?>
fixMozillaZIndex=true; //Fixes Z-Index problem  with Mozilla browsers but causes odd scrolling problem, toggle to see if it helps
_menuCloseDelay=500;
_menuOpenDelay=150;
_subOffsetTop=0;
_subOffsetLeft=0;

with(AcosaMainStyle=new mm_style()){
    bordercolor="#8A867A";
    borderstyle="solid";
    borderwidth=1;
    fontfamily="Arial";
    fontsize="14px";
    fontstyle="normal";
    fontweight="normal";
    offbgcolor="#d7eaff";
    offcolor="#000000";
    onbgcolor="#EBF0EC";
    onborder="1px solid #000080";
    oncolor="#000000";
    padding=3;
}

with(AcosaMenuStyle=new mm_style()){
    styleid=1;
    bordercolor="#000000";
    borderstyle="solid";
    borderwidth=1;
    fontfamily="Arial";
    fontsize="14px";
    fontstyle="normal";
    fontweight="normal";
    image="/sistemaweb/menu/xpblank.gif";
    //image="/sistemaweb/menu/fondo.gif";
    imagepadding=3;
    menubgimage="/sistemaweb/menu/backoff2003_web.gif";
    offbgcolor="transparent";
    offcolor="#000000";
    onbgcolor="#d7eaff";
    onborder="1px solid #000080";
    oncolor="#000000";
    outfilter="randomdissolve(duration=0.3)";
    overfilter="Fade(duration=0.2);Alpha(opacity=90);Shadow(color=#B8D1F8', Direction=135, Strength=5)";
    padding=4;
    separatoralign="right";
    separatorcolor="#6A8CCB";
    separatorpadding=1;
    separatorwidth="80%";
    subimage="/sistemaweb/menu/arrow.gif";
    subimagepadding=3;
    menubgcolor="#EBF0EC";
}
<?php
global $sqlca, $usuario;
function drawMenu($parent){
    global $sqlca, $usuario;
	$grupo_fin = $usuario->getGIDs();
    $sql = "SELECT
		trim(m.ch_id),
		trim(m.ch_texto),
		m.b_submenu,
		trim(m.ch_url)
	    FROM
		int_menu m, int_grupo_opcion_menu op
	    WHERE op.gid=".trim($grupo_fin[0])." and m.ch_sistema=op.ch_sistema and m.ch_parent=op.ch_parent and m.ch_id=op.ch_id and m.ch_sistema='" . pg_escape_string($usuario->obtenerSistemaActual()) . "'
		AND trim(m.ch_parent)='" . pg_escape_string($parent) . "'
		AND (m.ch_opcion in (";
    $opciones = $usuario->obtenerModulosPermitidos();

    for ($i = 0; $i < count($opciones); $i++) {
	if ($i > 0) $sql .= ",";
	$sql .= "'" . pg_escape_string($opciones[$i]) . "'";
    }
    
    $sql .= "	) OR m.ch_opcion is NULL)
	    ORDER BY m.ch_id;";
	//print_r($sql);
    if ($sqlca->query($sql) < 0) return;
      
    $submenus = Array();
?>
with(milonic=new menuname("<?php if ($parent=="00") echo "Main Menu"; else echo $parent; ?>")){
    overflow="scroll";
<?php
    if ($parent=="00") {
?>
    alwaysvisible=1;
    left=0;
    orientation="horizontal";
    top=0;
    style=AcosaMainStyle;
<?php
    }
    else {
?>
    style=AcosaMenuStyle;
<?php
    }

    for($i = 0; $i < $sqlca->numrows(); $i++) {
	$a = $sqlca->fetchRow();
	
	$ch_id = $a[0];
	$ch_texto = $a[1];
	$b_submenu = $a[2];
	$ch_url = $a[3];

	if ($b_submenu=='t') {	
	    $submenus[$i] = $ch_id;
?>
    aI("showmenu=<?php echo $ch_id; ?>;text=<?php echo $ch_texto; ?>");
<?php
	}
	else {
?>
    aI("text=<?php echo $ch_texto; ?>;url=<?php echo $ch_url; ?>");
<?php
	}
    }
?>
}
<?php

    foreach($submenus as $dummy=>$id) {
	drawMenu($id);
    }
}
drawMenu("00");
?>
drawMenus();