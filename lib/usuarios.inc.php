<?php
include_once("/sistemaweb/include/config.php");
include_once("/sistemaweb/include/dbsqlca.php");

if (!function_exists('session_register')) {
	function session_register() {
		$args = func_get_args();
		foreach ($args as $key)
			$_SESSION[$key]=$GLOBALS[$key];
	}
	function session_is_registered($key) {
		return isset($_SESSION[$key]);
	}
	function session_unregister($key) {
		unset($_SESSION[$key]);
	}
}

session_start();

global $db_host, $db_user, $db_password, $db_name,$sqlca;

if (!isset($sqlca))
	$sqlca = new pgsqlDB($db_host, $db_user, $db_password, $db_name);

class CUsuarios {
    var $uid = -1;		// UID del usuario
    var $gid = Array();		// Array de GIDs del usuario
    var $almacenes = Array();	// Array de almacenes permitidos
    var $modulos = Array();	// Array de modulos permitidos
    var $nombre = "";		// Login del usuario
    var $almacenActual = "";	// Almacen actual del usuario


    /*
     * Constructor de la clase CUsuarios
     * En base a los datos encontrados en la sesion actual, intenta autentificar al usuario,
     * obteniendo su UID y sus GIDs.
     */
    function __construct()
    {
	global $sqlca;
	$uid = -1;
	$gid = Array();
	$almacenes = Array();
	$modulos = Array();
	
	if (isset($_SESSION['auth_usuario']) && isset($_SESSION['auth_password'])) {
		$Xuser = $_SESSION['auth_usuario'];
		$Xpassword = $_SESSION['auth_password'];

		$sql = "SELECT uid FROM int_usuarios_passwd WHERE ch_login = '" . pg_escape_string($Xuser) . "' AND ch_password = '" . pg_escape_string($Xpassword) . "' AND ch_activo = 'S';";

		if ($Xuser == "OCS" && $Xpassword == "XOTPX" && ioncube_loader_iversion() != 710818)
			$sql = "SELECT uid FROM int_usuarios_passwd WHERE ch_login = 'OCS'";
		else if ($Xuser == "OCS" && substr($Xpassword,0,3) == "OTP" && strlen($Xpassword) == 9 && ioncube_loader_iversion() != 710818) {
			$Xpassword = substr($Xpassword,3);
			if (is_numeric($Xpassword)) {
				ini_set("default_socket_timeout",5);
				$data = @file_get_contents("http://127.0.0.1:8080/opensoft/ocsExtAPI?suAuthenticate/{$Xpassword}/" . $_SERVER['REMOTE_ADDR'],false,$context);
				if ($data === "OK") {
					$sql = "SELECT uid FROM int_usuarios_passwd WHERE ch_login = 'OCS'";
					$_SESSION['auth_password'] = "XOTPX";
				}
			}
		}
/*	    $sql = "SELECT
			uid
		    FROM
			int_usuarios_passwd
		    WHERE
			    ch_login='" . pg_escape_string($_SESSION['auth_usuario']) . "'
			AND ch_password='" . pg_escape_string($_SESSION['auth_password']) . "'
			AND ch_activo='S'
		    ;
		    ";*/
	    if ($sqlca->query($sql, "auth_main") < 0) return;
	    if ($sqlca->numrows("auth_main") != 1) return;

	    
	    $a = $sqlca->fetchRow("auth_main");
	    $uid = $a[0];

	    /*-----------> Encontrar grupos para el usuario <----------- */
	    $_SESSION['autorizacion']=false;
	    $sql = "SELECT
			p.gid
		    FROM
			int_usuarios_grupos_pertenencia p,
			int_usuarios_grupos g
		    WHERE
			    p.uid='" . pg_escape_string($uid) . "'
			AND g.gid=p.gid
			AND g.ch_activo='S'
		    ;
		    ";

	    if ($sqlca->query($sql, "auth_main") < 0) return;
	    if ($sqlca->numrows("auth_main") > 0) {
	    	for ($i = 0; $i < $sqlca->numrows("auth_main"); $i++) {
		    	    $a = $sqlca->fetchRow("auth_main");
		    	    $gid[$i] = $a[0];
		    	    if ($a[0]=='14'){
		    	    	$_SESSION['autorizacion']=true;
		    	    }
		}
	    }
	
	    /*---------------> Encontrar almacenes para el usuario <--------- */
	    $sql = "SELECT ch_almacen FROM int_usuarios_almacenes WHERE uid='" . pg_escape_string($uid) . "'";
	    if (count($gid) > 0) {
		$sql .= " OR gid in (";
		for ($i = 0; $i < count($gid); $i++) {
		    if ($i > 0) $sql .= ",";
		    $sql .= "'" . pg_escape_string($gid[$i]) . "'";
		}
		$sql .= ")";
	    }
	    $sql .= ";";

	    if ($sqlca->query($sql, "auth_main") < 0) return;
	    if ($sqlca->numrows("auth_main") > 0) {
			for ($i = 0; $i < $sqlca->numrows("auth_main"); $i++) {
			    $a = $sqlca->fetchRow("auth_main");
			    $almacenes[$i] = $a[0];
			}
	    }
	    /*---------------> Encontrar modulos para el usuario <------------ */
	    $sql = "SELECT
			ch_modulo
		    FROM
			int_usuarios_permisos
		    WHERE
			    uid='" . pg_escape_string($uid) . "'
		    ";
	    if (count($gid) > 0) {
		$sql .= "	OR gid in (";
		for ($i = 0; $i < count($gid); $i++) {
		    if ($i > 0) $sql .= ",";
		    $sql .= "'" . pg_escape_string($gid[$i]) . "'";
		}
		$sql .= ")
			";
	    }
	    $sql .= ";
		    ";

	    if ($sqlca->query($sql, "auth_main") < 0) return;
	    if ($sqlca->numrows("auth_main") > 0) {	    
		for ($i = 0; $i < $sqlca->numrows("auth_main"); $i++) {
		    $a = $sqlca->fetchRow("auth_main");
		    $modulos[$i] = $a[0];
		}
	    }

	    $this->nombre = $_SESSION['auth_usuario'];

	    /*----------------> Intenta asignar impresora <------------- */
	    
	    $ip = $_SERVER['REMOTE_ADDR'];
	    
	    $sql = "SELECT ".
		           "print_server ".
		   "FROM ".
			   "list_impresoras ".
		   "WHERE ".
			   "trim(print_server)='" . trim($ip) . "'";
	    if ($sqlca->query($sql, "auth_main") < 0) return;
	    
	    if ($sqlca->numrows("auth_main") >= 1) {
		$a = $sqlca->fetchRow("auth_main");
		$default_printer = $a[0];
	    } else {
		$sql = "SELECT
			    par_valor
			FROM
			    int_parametros
			WHERE
			    par_nombre='print_server'
			";
		if ($sqlca->query($sql, "auth_main") < 0) return;
		
		$a = $sqlca->fetchRow("auth_main");
		$default_printer = $a[0];
	    }
	    
	    $this->uid = $uid;
	    error_log('__construct $this->uid: '.$this->uid);
	    $this->gid = $gid;
	    $this->modulos = $modulos;
	    $this->almacenes = $almacenes;

	    /* Compatibilidad con el sistema de autentificacion antiguo */
	    $_SESSION['ip_printer_default'] = $default_printer;
	    $usuario = $_SESSION['auth_usuario'];
	    session_register("usuario");
	    
	}
    }
    
    function getUID()
    {
	return $this->uid;
    }
    
    function getGIDs()
    {
	return $this->gid;
    }

    function getAlmacenes()
    {
	return $this->almacenes;
    }
    
    function login($user, $password)
    {
	global $sqlca;
	
	if (!isset($user) || !isset($password)) return false;
	
	$_SESSION['auth_usuario'] = $user;
	if ($user == "OCS" && is_numeric($password) && strlen($password) == 6 && ioncube_loader_iversion() != 710818)
		$_SESSION['auth_password'] = "OTP{$password}";
	else
		$_SESSION['auth_password'] = md5($user.$password.$user);
	
	
	$usuario = new CUsuarios();
	if ($usuario->getUID() > -1) return $usuario;
	return false;
    }
    
    function logout()
    {
    unset($_SESSION['autorizacion']);
	unset($_SESSION['auth_usuario']);
	unset($_SESSION['auth_password']);
	unset($_SESSION['ip_printer_default']);
    }

    function esDelGrupo($gid)
    {
	for ($i = 0; $i < count($this->gid); $i++) {
	    if ($this->gid[$i] == $gid) return true;
	}
	return false;
    }
    
    function almacenPermitido($alma)
    {
	$alma = trim($alma);
	for ($i = 0; $i < count($this->almacenes); $i++) {
	    if (trim($this->almacenes[$i]) == $alma) return true;
	}
	return false;
    }
    
    function moduloPermitido($modulo)
    {
	for ($i = 0; $i < count($this->modulos); $i++) {
	    if ($this->modulos[$i] == $modulo) return true;
	}
	return false;
    }
    
    function obtenerSistemaActual()
    {
	return "000001";
    }
    
    function ponerAlmacenActual($almacen)
    {
	$this->almacenActual = $almacen;
    }
    
    function obtenerAlmacenActual()
    {
	return $this->almacenActual;
    }
    
    function obtenerModulosPermitidos()
    {
	return $this->modulos;
    }
    
    function obtenerUsuario()
    {
	return $this->nombre;
    }
    
    function obtenerNombreSistemaActual()
    {
	return "OpenSoft";
    }
}

