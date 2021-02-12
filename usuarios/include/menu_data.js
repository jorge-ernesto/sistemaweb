fixMozillaZIndex=true; //Fixes Z-Index problem  with Mozilla browsers but causes odd scrolling problem, toggle to see if it helps
_menuCloseDelay=500;
_menuOpenDelay=150;
_subOffsetTop=2;
_subOffsetLeft=-2;



with(XPMainStyle=new mm_style()){
    bordercolor="#8A867A";
    borderstyle="solid";
    borderwidth=1;
    fontfamily="Tahoma,Helvetica,Verdana";
    fontsize="70%";
    fontstyle="normal";
    fontweight="normal";
    offbgcolor="#B8D1F8";
    offcolor="#000000";
    onbgcolor="#C1D2EE";
    onborder="1px solid #000080";
    oncolor="#000000";
    overbgimage="include/orange_office2003.gif";
    padding=3;
}

with(XPMenuStyle=new mm_style()){
    styleid=1;
    bordercolor="#8A867A";
    borderstyle="solid";
    borderwidth=1;
    fontfamily="Tahoma,Helvetica,Verdana";
    fontsize="70%";
    fontstyle="normal";
    fontweight="normal";
    image="include/xpblank.gif";
    imagepadding=3;
    menubgimage="include/backoff2003_web.gif";
    offbgcolor="transparent";
    offcolor="#000000";
    onbgcolor="#ffeec2";
    onborder="1px solid #000080";
    oncolor="#000000";
    outfilter="randomdissolve(duration=0.3)";
    overfilter="Fade(duration=0.2);Alpha(opacity=90);Shadow(color=#B8D1F8', Direction=135, Strength=5)";
    padding=4;
    separatoralign="right";
    separatorcolor="#6A8CCB";
    separatorpadding=1;
    separatorwidth="80%";
    subimage="include/arrow.gif";
    subimagepadding=3;
    menubgcolor="#EBF0EC";
}

with(milonic=new menuname("Main Menu")){
    alwaysvisible=1;
    left=0;
    orientation="horizontal";
    style=XPMainStyle;
    top=50;
//    aI("text=Home;url=http://www.milonic.com/;");
    aI("showmenu=Maestros;text=Maestros;");
    aI("showmenu=Permisos;text=Permisos;");
    aI("text=Logout;url=../finsesion.php;");
}

with(milonic=new menuname("Maestros")){
    overflow="scroll";
    style=XPMenuStyle;
    aI("text=Usuarios;url=control.php?rqst=MAESTROS.USERS;target=control");
    aI("text=Grupos;url=control.php?rqst=MAESTROS.GROUPS;target=control");
    aI("text=Modulos;url=control.php?rqst=MAESTROS.MODULES;target=control");
}

with(milonic=new menuname("Permisos")){
    overflow="scroll";
    style=XPMenuStyle;
    aI("text=Acceso a modulos;url=control.php?rqst=PERMISOS.MODULOS;target=control");
    aI("text=Acceso a almacenes;url=control.php?rqst=PERMISOS.ALMACENES;target=control");
    aI("text=Acceso a sistemas;url=control.php?rqst=PERMISOS.SISTEMAS;target=control");
}

drawMenus();

