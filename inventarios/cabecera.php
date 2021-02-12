<?php
include("config.php");
?>
<HTML>
<TITLE>NCA</TITLE>
<HEAD>
<SCRIPT LANGUAGE="JavaScript">
<!-- Begin
var isDOM = (document.getElementById ? true : false); 
var isIE4 = ((document.all && !isDOM) ? true : false);
var isNS4 = (document.layers ? true : false);
function getRef(id) {
if (isDOM) return document.getElementById(id);
if (isIE4) return document.all[id];
if (isNS4) return document.layers[id];
}
function getSty(id) {
return (isNS4 ? getRef(id) : getRef(id).style);
} 
// Hide timeout.
var popTimer = 0;
// Array showing highlighted menu items.
var litNow = new Array();
function popOver(menuNum, itemNum) {
clearTimeout(popTimer);
hideAllBut(menuNum);
litNow = getTree(menuNum, itemNum);
changeCol(litNow, true);
targetNum = menu[menuNum][itemNum].target;
if (targetNum > 0) {
thisX = parseInt(menu[menuNum][0].ref.left) + parseInt(menu[menuNum][itemNum].ref.left);
thisY = parseInt(menu[menuNum][0].ref.top) + parseInt(menu[menuNum][itemNum].ref.top);
with (menu[targetNum][0].ref) {
left = parseInt(thisX + menu[targetNum][0].x);
top = parseInt(thisY + menu[targetNum][0].y);
visibility = 'visible';
      }
   }
}
function popOut(menuNum, itemNum) {
if ((menuNum == 0) && !menu[menuNum][itemNum].target)
hideAllBut(0)
else
popTimer = setTimeout('hideAllBut(0)', 500);
}
function getTree(menuNum, itemNum) {

// Array index is the menu number. The contents are null (if that menu is not a parent)
// or the item number in that menu that is an ancestor (to light it up).
itemArray = new Array(menu.length);

while(1) {
itemArray[menuNum] = itemNum;
// If we've reached the top of the hierarchy, return.
if (menuNum == 0) return itemArray;
itemNum = menu[menuNum][0].parentItem;
menuNum = menu[menuNum][0].parentMenu;
   }
}

// Pass an array and a boolean to specify colour change, true = over colour.
function changeCol(changeArray, isOver) {
for (menuCount = 0; menuCount < changeArray.length; menuCount++) {
if (changeArray[menuCount]) {
newCol = isOver ? menu[menuCount][0].overCol : menu[menuCount][0].backCol;
// Change the colours of the div/layer background.
with (menu[menuCount][changeArray[menuCount]].ref) {
if (isNS4) bgColor = newCol;
else backgroundColor = newCol;
         }
      }
   }
}
function hideAllBut(menuNum) {
var keepMenus = getTree(menuNum, 1);
for (count = 0; count < menu.length; count++)
if (!keepMenus[count])
menu[count][0].ref.visibility = 'hidden';
changeCol(litNow, false);
}

// *** MENU CONSTRUCTION FUNCTIONS ***

function Menu(isVert, popInd, x, y, width, overCol, backCol, borderClass, textClass) {
// True or false - a vertical menu?
this.isVert = isVert;
// The popout indicator used (if any) for this menu.
this.popInd = popInd
// Position and size settings.
this.x = x;
this.y = y;
this.width = width;
// Colours of menu and items.
this.overCol = overCol;
this.backCol = backCol;
// The stylesheet class used for item borders and the text within items.
this.borderClass = borderClass;
this.textClass = textClass;
// Parent menu and item numbers, indexed later.
this.parentMenu = null;
this.parentItem = null;
// Reference to the object's style properties (set later).
this.ref = null;
}

function Item(text, href, frame, length, spacing, target) {
this.text = text;
this.href = href;
this.frame = frame;
this.length = length;
this.spacing = spacing;
this.target = target;
// Reference to the object's style properties (set later).
this.ref = null;
}

function writeMenus() {
if (!isDOM && !isIE4 && !isNS4) return;

for (currMenu = 0; currMenu < menu.length; currMenu++) with (menu[currMenu][0]) {
// Variable for holding HTML for items and positions of next item.
//var str = '', itemX = 0, itemY = 50;
var str = '', itemX = 0, itemY = 0;

// Remember, items start from 1 in the array (0 is menu object itself, above).
// Also use properties of each item nested in the other with() for construction.
for (currItem = 1; currItem < menu[currMenu].length; currItem++) with (menu[currMenu][currItem]) {
var itemID = 'menu' + currMenu + 'item' + currItem;

// The width and height of the menu item - dependent on orientation!
var w = (isVert ? width : length);
var h = (isVert ? length : width);

// Create a div or layer text string with appropriate styles/properties.
// Thanks to Paul Maden (www.paulmaden.com) for helping debug this in IE4, apparently
// the width must be a miniumum of 3 for it to work in that browser.
if (isDOM || isIE4) {
//str += '<div id="' + itemID + '" style="position: absolute; left: ' + itemX + '; top: ' + itemY + '; width: ' + w + '; height: ' + h + '; visibility: inherit; ';
str += '<div id="' + itemID + '" style="position: absolute; left: ' + itemX + '; top: ' + itemY + '; width: ' + w + '; height: ' + h + '; visibility: inherit; ';
if (backCol) str += 'background: ' + backCol + '; ';
str += '" ';
}
if (isNS4) {
str += '<layer id="' + itemID + '" left="' + itemX + '" top="' + itemY + '" width="' +  w + '" height="' + h + '" visibility="inherit" ';
//str += '<layer id="' + itemID + '" left="' + itemX + '" top="' + 0 + '" width="' +  w + '" height="' + h + '" visibility="inherit" ';
if (backCol) str += 'bgcolor="' + backCol + '" ';
}
if (borderClass) str += 'class="' + borderClass + '" ';

// Add mouseover handlers and finish div/layer.
str += 'onMouseOver="popOver(' + currMenu + ',' + currItem + ')" onMouseOut="popOut(' + currMenu + ',' + currItem + ')">';

// Add contents of item (default: table with link inside).
// In IE/NS6+, add padding if there's a border to emulate NS4's layer padding.
// If a target frame is specified, also add that to the <a> tag.

str += '<table width="' + (w - 8) + '" border="0" cellspacing="0" cellpadding="' + (!isNS4 && borderClass ? 3 : 0) + '"><tr><td align="left" height="' + (h - 7) + '">' + '<a class="' + textClass + '" href="' + href + '"' + (frame ? ' target="' + frame + '">' : '>') + text + '</a></td>';
if (target > 0) {

// Set target's parents to this menu item.
menu[target][0].parentMenu = currMenu;
menu[target][0].parentItem = currItem;

// Add a popout indicator.
if (popInd) str += '<td class="' + textClass + '" align="right">' + popInd + '</td>';
}
str += '</tr></table>' + (isNS4 ? '</layer>' : '</div>');

//if (isVert) itemY += length + spacing;
if (isVert) itemY += length + spacing;
else itemX += length + spacing;
}
if (isDOM) {
var newDiv = document.createElement('div');
document.getElementsByTagName('body').item(0).appendChild(newDiv);
newDiv.innerHTML = str;
ref = newDiv.style;
ref.position = 'absolute';
ref.visibility = 'hidden';
}

// Insert a div tag to the end of the BODY with menu HTML in place for IE4.
if (isIE4) {
document.body.insertAdjacentHTML('beforeEnd', '<div id="menu' + currMenu + 'div" ' + 'style="position: absolute; visibility: hidden">' + str + '</div>');
ref = getSty('menu' + currMenu + 'div');
}

// In NS4, create a reference to a new layer and write the items to it.
if (isNS4) {
ref = new Layer(0);
ref.document.write(str);
ref.document.close();
}

for (currItem = 1; currItem < menu[currMenu].length; currItem++) {
itemName = 'menu' + currMenu + 'item' + currItem;
if (isDOM || isIE4) menu[currMenu][currItem].ref = getSty(itemName);
if (isNS4) menu[currMenu][currItem].ref = ref.document[itemName];
   }
}
with(menu[0][0]) {
ref.left = x;
ref.top = y;
ref.visibility = 'visible';
   }
}

// Syntaxes: *** START EDITING HERE, READ THIS SECTION CAREFULLY! ***
//
// menu[menuNumber][0] = new Menu(Vertical menu? (true/false), 'popout indicator', left, top,
// width, 'mouseover colour', 'background colour', 'border stylesheet', 'text stylesheet');
//
// Left and Top are measured on-the-fly relative to the top-left corner of its trigger, or
// for the root menu, the top-left corner of the page.
//
// menu[menuNumber][itemNumber] = new Item('Text', 'URL', 'target frame', length of menu item,
//  additional spacing to next menu item, number of target menu to popout);
//
// If no target menu (popout) is desired, set it to 0. Likewise, if your site does not use
// frames, pass an empty string as a frame target.
//
// Something that needs explaining - the Vertical Menu setup. You can see most menus below
// are 'true', that is they are vertical, except for the first root menu. The 'length' and
// 'width' of an item depends on its orientation -- length is how long the item runs for in
// the direction of the menu, and width is the lateral dimension of the menu. Just look at
// the examples and tweak the numbers, they'll make sense eventually :).

var menu = new Array();

// Default colours passed to most menu constructors (just passed to functions, not
// a global variable - makes things easier to change later in bulk).
var defOver = '#336699', defBack = '#003366';

// Default 'length' of menu items - item height if menu is vertical, width if horizontal.
//var defLength = 22;

var defLength = 22;

// Menu 0 is the special, 'root' menu from which everything else arises.
menu[0] = new Array();
// A non-vertical menu with a few different colours and no popout indicator, as an example.
// *** MOVE ROOT MENU AROUND HERE ***  it's positioned at (5, 0) and is 17px high now.
menu[0][0] = new Menu(false, '', 5, 0, 17, '#F7D67E', '#F4B202', '', 'itemText');
//menu[0][0] = new Menu(false, '', 5, 0, 17, '#669999', '#006666', '', 'itemText');
// Notice how the targets are all set to nonzero values...
// The 'length' of each of these items is 40, and there is spacing of 10 to the next item.
// Most of the links are set to '#' hashes, make sure you change them to actual files.
menu[0][1] = new Item('  Movimientos', '#', '', 40, 40, 1);
menu[0][2] = new Item('  Reportes', '#', '', 40, 40, 2);
menu[0][3] = new Item('  Sistema', '#', '', 40, 40, 3);
menu[0][4] = new Item('  Salir', '<?php echo $v_path_url;?>finsesion.php', '', 40, 40, 4);
/*
menu[0][5] = new Item('  Proveedores', '#', '', 40, 60, 5);
menu[0][6] = new Item('  Banco', '#', '', 40, 30, 6);
menu[0][7] = new Item('  Contabilidad', '#', '', 40, 60, 7);
menu[0][8] = new Item('  Seguridad', '#', '', 40, 60, 8);
*/
// An example of a link with a target frame/window as well...
//menu[0][4] = new Item('  Site', 'http://gusnz.cjb.net', '_new', 40, 10, 0);

// Tablas.
menu[1] = new Array();
// The File menu is positioned 0px across and 22 down from its trigger, and is 80 wide.
// All text in this menu has the stylesheet class 'item' -- see the <style> section above.
// We've passed a 'greater-than' sign '>' as a popout indicator. Try an image...?
menu[1][0] = new Menu(true, '>', 0, 22, 150, defOver, defBack, 'itemBorder', 'itemText');
menu[1][1]=new Item('Ingreso Guías', '<?php echo $v_path_url;?>menu/movimientos/guia_remision_remitente.php', '',defLength, 0, 0);
//menu[1][2] = new Item('Balanza', '<?php echo $v_path_url;?>menu/movimientos/balanza.php', '', defLength, 0, 0);
menu[1][2] = new Item('Asignacion','<?php echo $v_path_url;?>menu/movimientos/asignacion.php', '', defLength, 0, 0);
/*
menu[1][4] = new Item('', '', '', defLength, 0, 0);
menu[1][5] = new Item('', '', '', defLength, 0, 0);
menu[1][6] = new Item('', '', '', defLength, 0, 0);

menu[1][7] = new Item('Tipo Seguro', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/tipo-seguro.php', '', defLength, 0, 0);
menu[1][8] = new Item('Estado Clientes', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/estado-cliente.php', '', defLength, 0, 0);
menu[1][9] = new Item('Ocupación', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/tabla-ocupacion.php', '', defLength, 0, 0);
menu[1][10] = new Item('Nivel Ingreso Anual', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/tabla-ingreso.php', '', defLength, 0, 0);
//menu[1][11] = new Item('Anual', '#', '', defLength, 0, 0);
menu[1][11] = new Item('Giro Negocio', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/tabla-giro.php', '', defLength, 0, 0);
menu[1][12] = new Item('Código Postal', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/tabla-cod-postal.php', '', defLength, 0, 0);
menu[1][13] = new Item('País', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/tabla-pais.php', '', defLength, 0, 0);
menu[1][14] = new Item('Vendedor', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/tabla-vendedor.php', '', defLength, 0, 0);
menu[1][15] = new Item('Tipo Documento', 'http://mail.pentagrama.com/pentagrama/modules/modulo-tablinea.php?tablas=tabtipodocumento', '', defLength, 0, 0);
menu[1][16] = new Item('Tipo Cambio', 'http://mail.pentagrama.com/pentagrama/nuevos-modulos/tabla-tipo-cambio.php', '', defLength, 0, 0);
menu[1][17] = new Item('Tarifario','http://mail.pentagrama.com/pentagrama/modules/tarifa2.php', '', defLength, 0, 0);
*/

// Stock.

menu[2] = new Array();
menu[2][0] = new Menu(true, '>', 0, 22, 230, defOver, defBack, 'itemBorder', 'itemText');
menu[2][1] = new Item('Guías por estado', '<?php echo $v_path_url;?>menu/reportes/guiasporestado.php', '', defLength,0, 0);
menu[2][2] = new Item('Estado de asignaciones', '<?php echo $v_path_url;?>menu/reportes/estadoasignaciones.php', '', defLength, 0, 0);

/*
menu[2][3] = new Item('Consulta boleto','http://mail.pentagrama.com/pentagrama/modules/modulo-tablinea.php?tablas=tabestadoboleto', '', defLength, 0, 0);
menu[2][4] = new Item('Consulta Stock', 'http://mail.pentagrama.com/pentagrama/modules/consultastock.php', '', defLength, 0, 0);
menu[2][5] = new Item('Reporte de ventas', 'http://mail.pentagrama.com/pentagrama/modules/repventas.php', '', defLength, 0, 0);
menu[2][6] = new Item('Reporte de ventas pendientes de facturar', 'http://mail.pentagrama.com/pentagrama/modules/repvtasxfact.php', '', defLength, 0, 0);
*/
// Ventas
menu[3] = new Array();
menu[3][0] = new Menu(true, '<', 0, 22, 250, defOver, defBack, 'itemBorder', 'itemText');
menu[3][1] = new Item('Contraseña','<?php echo $v_path_url;?>menu/sistema/SEG_password.php', '', defLength, 0, 0);
menu[3][2] = new Item('Estados guías', '<?php echo $v_path_url;?>menu/sistema/estadosguia.php', '', defLength, 0, 0);
menu[3][3] = new Item('Vehículos', '<?php echo $v_path_url;?>menu/sistema/vehiculos.php', '', defLength, 0, 0);
menu[3][4] = new Item('Choferes','<?php echo $v_path_url;?>menu/sistema/choferes.php', '', defLength, 0, 0);
//menu[3][5] = new Item('Usuarios del Sistema', '<?php echo $v_path_url;?>menu/sistema/usuarios.php', '', defLength, 0, 0);
/*
menu[3][6] = new Item('Linea Aerea más vendidas', '#', '', defLength, 0, 0);
menu[3][7] = new Item('Ciudades más visitadas', '#', '', defLength, 0, 0);
*/
// Clientes


//menu[4] = new Array();
menu[4] = new Array();
menu[4][0] = new Menu(true, '<', 0, 22, 250, defOver, defBack, 'itemBorder', 'itemText');
/*
menu[4][1] = new Item('Ficha del Cliente','http://mail.pentagrama.com/pentagrama/modules/fichacliente.php', '',defLength, 0, 0); 
menu[4][2] = new Item('Pendiente de facturar', '#', '', defLength, 0, 0);
menu[4][3] = new Item('Pendiente de facturar ajustes', '#', '', defLength, 0, 0);
menu[4][4] = new Item('Registrar documentos', '#', '', defLength, 0, 0);
menu[4][5] = new Item('Pagos del cliente', '#', '', defLength, 0, 0);
menu[4][6] = new Item('Anular documento', '#', '', defLength, 0, 0);
menu[4][7] = new Item('Liquidacion de cobranzas', '#', '', defLength, 0, 0);
menu[4][8] = new Item('Consulta Cuenta Corriente', '#', '', defLength, 0, 0);
menu[4][9] = new Item('Consulta monto por tipo de documento', '#', '', defLength, 0, 0);
menu[4][10] = new Item('Reporte Saldos', '#', '', defLength, 0, 0);

// Help About popout
//Proveedores
menu[5] = new Array();
menu[5][0] = new Menu(true, '<', 0, 22, 200, defOver, defBack, 'itemBorder', 'itemText');
menu[5][1] = new Item('Ficha del proveedor','http://mail.pentagrama.com/pentagrama/modules/modulo-tablinea.php?tablas=proveedor', '', defLength, 0, 0);
menu[5][2] = new Item('Comisiones por facturar', '#', '', defLength, 0, 0);
menu[5][3] = new Item('Registra documnetos', '#', '', defLength, 0, 0);
menu[5][4] = new Item('Pago a proveedores', '#', '', defLength, 0, 0);
menu[5][5] = new Item('Anulación de documentos', '#', '', defLength, 0, 0);
menu[5][6] = new Item('Liquidación de los pagos', '#', '', defLength, 0, 0);
menu[5][7] = new Item('Consulta Cuenta Corriente', '#', '', defLength, 0, 0);
menu[5][8] = new Item('Reporte saldos', '#', '', defLength, 0, 0);
//Banco
menu[6] = new Array();
//Leftwards popout with a negative x and y relative to its trigger.
menu[6][0] = new Menu(true, '<', 0, 22, 200, defOver, defBack, 'itemBorder', 'itemText');
menu[6][1] = new Item('Registro de cuentas bancarias', 'http://mail.pentagrama.com/pentagrama/modules/reg_ctas_banc.php?tablas=banco', '', defLength, 0, 0);
menu[6][2] = new Item('Ingreso y salida de bancos', '#', '', defLength, 0, 0);
menu[6][3] = new Item('Transferencia entre bancos', '#', '', defLength, 0, 0);
menu[6][4] = new Item('Confirmación de los montos', '#', '', defLength, 0, 0);
menu[6][5] = new Item('Consulta estado bancarios', '#', '', defLength, 0, 0);
menu[6][6] = new Item('Reporte de montos de caja', '#', '', defLength, 0, 0);
menu[6][7] = new Item('Reporte de posición de caja', '#', '', defLength, 0, 0);
//menu[6][0] = new Menu(true, '>', -85, -17, 80, defOver, defBack, 'itemBorder', 'itemText');
//menu[6][1] = new Item('defLength!<br>And up!', '#', '', 40, 0, 0);

//Contabilidad
menu[7] = new Array();
menu[7][0] = new Menu(true, '>', 0, 22, 150, defOver, defBack, 'itemBorder', 'itemText');
menu[7][1] = new Item('Registros documentos', 'http://mail.pentagrama.com/pentagrama/modules/regisdoc.php?ff=A', '',defLength, 0, 0);
//menu[7][2] = new Item('Save', '#', '', defLength, 0, 0);

//Seguridad
menu[8] = new Array();
menu[8][0] = new Menu(true, '>', 0, 22, 150, defOver, defBack, 'itemBorder', 'itemText');
menu[8][1] = new Item('Registros de perfiles','http://mail.pentagrama.com/pentagrama/modules/SEG_perfil.php', '', defLength, 0, 0);
menu[8][2] = new Item('Cambiar contraseña','http://mail.pentagrama.com/pentagrama/modules/SEG_password.php?lolo=<?php echo $txtuser; ?>','',defLength,0,0); 
*/

// *** OPTIONAL CODE FROM HERE DOWN ***

// These two lines handle the window resize bug in NS4. See <body onResize="...">.
// I recommend you leave this here as otherwise when you resize NS4's width menus are hidden.

var popOldWidth = window.innerWidth;
nsResizeHandler = new Function('if (popOldWidth != window.innerWidth) location.reload()');


// This is a quick snippet that captures all clicks on the document and hides the menus
// every time you click. Use if you want.

if (isNS4) document.captureEvents(Event.CLICK);
document.onclick = clickHandle;

function clickHandle(evt)
{
 if (isNS4) document.routeEvent(evt);
 hideAllBut(0);
}


// This is just the moving command for the example.

function moveRoot()
{
 with(menu[0][0].ref) left = ((parseInt(left) < 100) ? 100 : 5);
}
//  End -->
</script>

<!-- *** IMPORTANT STYLESHEET SECTION - Change the border classes and text colours *** -->

<style>
<!--

.itemBorder { border: 1px solid black }
.itemText { text-decoration: none; color: #FFFFFF; font: 12px Arial, Helvetica }

.crazyBorder { border: 2px outset #000000 }
.crazyText { text-decoration: none; color: #000000; font: Bold 12px Arial, Helvetica }

-->
</style>




<!-- STEP TWO: Insert the onLoad event handler into your BODY tag#ECECEC -->

<BODY marginwidth="0" marginheight="0" style="margin: 0" onLoad="writeMenus()" onResize="if (isNS4) nsResizeHandler()" bgcolor="#FFFFFF" vlink="#003399" alink="#003399">
<!--<img src="images/banner.jpg">-->
<font color="#FFFFFF" size="1" face="Verdana, Arial, Helvetica, sans-serif"> 
<!-- STEP THREE: Copy this code into the BODY of your HTML document  -->
<!-- It's important that you position the menu over a background, like a table/image -->
</font> 
<table bgcolor="#F4B202" width="768" border="0" cellpadding="1" cellspacing="0">
  <tr> 
    <td height="17">&nbsp;</td>
  </tr>
</table>
<p><font size="1" color="#FFFFFF" face="Verdana, Arial, Helvetica, sans-serif">
  <!-- Script Size:  13.87 KB -->
  </font>
