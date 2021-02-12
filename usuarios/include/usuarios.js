
function formModificarRegresar()
{
    control.location.href="control.php?rqst=MAESTROS.USERS&action=";
}

function formAgregarUsuario()
{
    control.location.href="control.php?rqst=MAESTROS.USERS&action=Agregar";
}

function listadoGruposAgregar(uid)
{
    control.location.href="control.php?rqst=MAESTROS.USERS&action=GrupoAgregar&uid="+uid;
}

function formAgregarGrupo()
{
    control.location.href="control.php?rqst=MAESTROS.GROUPS&action=Agregar";
}

function formModificarGrupoRegresar()
{
    control.location.href="control.php?rqst=MAESTROS.GROUPS&action=";
}

function formAgregarGrupoRegresar(uid)
{
    control.location.href="control.php?rqst=MAESTROS.USERS&action=Modificar&uid=" + uid;
}

function formSistemaAgregar()
{
    control.location.href="control.php?rqst=PERMISOS.SISTEMAS&action=Agregar";
}

function checkSistemaOption(val)
{
    if (val=="usuario") {
	document.getElementById("GROUP_USUARIO").style.display="inline";
	document.getElementById("GROUP_GRUPO").style.display="none";
    }
    else {
	document.getElementById("GROUP_USUARIO").style.display="none";
	document.getElementById("GROUP_GRUPO").style.display="inline";
    }
}

function formAlmacenAgregar()
{
    control.location.href="control.php?rqst=PERMISOS.ALMACENES&action=Agregar";
}

function formModuloAgregar()
{
    control.location.href="control.php?rqst=MAESTROS.MODULES&action=Agregar";
}

function formAccesoAgregar()
{
    control.location.href="control.php?rqst=PERMISOS.MODULOS&action=Agregar";
}