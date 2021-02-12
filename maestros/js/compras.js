function pluUpdate(value){
if (value == 1 || value == 3) {
    document.getElementById("GRUPO_ESTANDARD").style.display="inline";
	document.getElementById("btEnlaces").style.display = "none";
    } else {
	document.getElementById("GRUPO_ESTANDARD").style.display="inline";
	document.getElementById("btEnlaces").style.display = "inline";
    }
}

function openAlias(){
var url = "contenedor_alias.php?rqst=MAESTROS.ITEMS&action=Alias&codigo=" + document.getElementsByName("codigo")[0].value;
window.open(url, "wndAlias", "dependent,height=420,menubar=no,resizable=no,toolbar=no,width=400");
}

function borrarAlias(){
top.document.getElementById("action").value="Alias.Action";
}

function regresarAlias(codigo)
{
    var url = "control.php?rqst=MAESTROS.ITEMS&action=Alias&codigo=" + codigo;
    control.location.href=url;
}

function openEnlaces()
{
    var url = "contenedor.php?rqst=MAESTROS.ITEMS&action=Enlaces&codigo=" + document.getElementsByName("codigo")[0].value;
    window.open(url, "wndEnlaces", "dependent,height=420,menubar=no,resizable=no,toolbar=no,width=400");
}

function openListaPrecios()
{
    var url = "contenedor.php?rqst=MAESTROS.ITEMS&action=Precios&codigo=" + document.getElementsByName("codigo")[0].value;
    window.open(url, "wndEnlaces", "dependent,height=420,menubar=no,resizable=no,toolbar=no,width=400");
}

function regresarMaesItems()
{
    control.location.href="control.php?rqst=MAESTROS.ITEMS";
}

function getTipoLinea(codigo,cod,desc,descb,manual)
{
    url = 'control.php?rqst=MAESTROS.ITEMS&action=Agregar&task=ValTipoLi&codigo='+codigo+'&cod='+cod+'&descripcion='+desc+'&descbreve='+descb+'&manual='+manual;
    document.getElementById('control').src = url;
    return;
}

function getTipoLinea2(codigo,cod)
{
    url = 'control.php?rqst=MAESTROS.ITEMS&action=Modificar&codigo='+codigo+'&cod='+cod;
    document.getElementById('control').src = url;
    return;
}

function rellenarCampo(campo, campo2)
{
    var campo3 = campo.value;
    campo2.value = campo3.substring(0,20);

}

function enlacesCerrar()
{
    window.close();
}

function regresarEnlace(codigo)
{
    var url = "control.php?rqst=MAESTROS.ITEMS&action=Enlaces&codigo=" + codigo;
    control.location.href=url;
}

function updateDescripcion(objeto)
{
    control.location.href="control.php?rqst=MAESTROS.ITEMS&action=Enlaces.Action&method=UpdateDesc&codigo=" + objeto.value;
}

function borrarEnlaces()
{
    top.document.getElementById("action").value="Enlaces.Action";
}

function itemsAgregar()
{
    control.location.href="control.php?rqst=MAESTROS.ITEMS&action=Agregar";
}

function agregarUpdatePLU(value)
{
    if (value == 1) {
	document.getElementById("GRUPO_ESTANDARD").style.display="inline";
    }
    else {
	document.getElementById("GRUPO_ESTANDARD").style.display="inline";
    }
}

function regresarPrecios(codigo)
{
    control.location.href = "control.php?rqst=MAESTROS.ITEMS&action=Precios&codigo=" + codigo;
}

function goAgregarPrecio(codigo)
{
    control.location.href="control.php?rqst=MAESTROS.ITEMS&action=Precios.Agregar&codigo=" + codigo;
}
