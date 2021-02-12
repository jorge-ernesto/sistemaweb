<?php
class class_funciones
	{
	var $v_url;
	var $v_path_linux;
	var $v_path_url;
	var $v_host;
	var $v_port;	
	var $v_db;
	var $v_user;
	var $v_password;
	var $v_coneccion;
	var $v_flag;
	var $v_texto;
	var $v_ruta;
	var $v_fecha;
	var $v_form;
	var $v_formb;

	function __construct()
		{
		$this->v_url="http://192.168.1.3/sistemaweb/";
		$this->v_path_linux="/var/www/html/sistemaweb/";
		$this->v_path_url="/sistemaweb/";
		$this->v_host="localhost";
		$this->v_port="5432";
		$this->v_db="integrado";
		$this->v_user="postgres";
		$this->v_password="postgres";
		$this->v_flag=false;
		$this->v_texto="";
		$this->v_ruta="/var/www/html/sistemaweb/compras/rep.txt";
		$this->v_fecha=@date("d/m/Y");
		$this->v_form="DD/MM/YYYY";
		$this->v_formb='';
		}

	function configurar()
		{
		$coneccion = $this->conectar("","","","","");
		$xsql=pg_exec($coneccion,"select ALMAC from TAB_LOGUEO where ID_SESION='".$_COOKIE["PHPSESSID"]."'");
		if(pg_numrows($xsql)>0)
			{
			$almacen=pg_result($xsql,0,0);
			$this->flag=true;
			}
		pg_close($coneccion);
		return $this.flag;
		//$tamPag=15;
		}

	function conectar($host,$port,$db,$user,$password) 
		{
		if ($host!="") $this->v_host=$host;
		if ($port!="") $this->v_port=$port;
		if ($db!="") $this->v_db=$db;
		if ($user!="") $this->v_user=$user;
		if ($password!="") $this->v_password=$password;
		$this->v_coneccion=pg_connect("host=".$this->v_host." port=" .$this->v_port." dbname=".$this->v_db." user=".$this->v_user." password=".$this->v_password  );
		return $this->v_coneccion;
		}
		
	function impr_line($texto)
		{
		if ($texto!="") $this->v_texto=$texto;
		exec( "echo $this->v_texto >> ".$this->v_ruta );
		//return ;
		}
		
	function impr_init()
		{
		exec( "echo ' ' > ".$this->v_ruta );
		}
	
	function date_format($fecha, $form)
		{
		// fecha = yyyy/mm/dd o dd/mm/yyyy o yyyy-mm-dd o dd-mm-yyyy
		// form = 'YYYY/MM/DD' o 'YYYY-MM-DD' O cualquier variante
		// devuelve char de 10 con el formato requerido
		if ($fecha!="") 
			{ $this->v_fecha=$fecha; }
			
		else
			{ $this->v_fecha="__/__/____"; }
		if ($form==',') 
			{$this->v_form=$this->v_form; }
		else {	if ( $form!="" ) { $this->v_form=substr($form,0,10); } }
		
		
		$this->v_formb='';
		
		if (strlen($form)>10) 
			{
			if(substr($form,10,1)==',' ) 
				{
				$this->v_formb=$this->v_form;
				}
			}
		if (strlen($form)<10)
			{
			if(substr($form,0,1)==',')
				{
				$this->v_formb=$this->v_form;
				}
			}
		
		if( substr($this->v_fecha,2,1)=="-" or substr($this->v_fecha,2,1)=="/" )
			{
			$v_dia=substr($this->v_fecha,0,2);
			$v_mes=substr($this->v_fecha,3,2);
			$v_anno=substr($this->v_fecha,6,4);
			}
		else
			{
			$v_dia=substr($this->v_fecha,8,2);
			$v_mes=substr($this->v_fecha,5,2);
			$v_anno=substr($this->v_fecha,0,4);
			}
			
		// $this->v_fecha=sprintf("%02s/%02s/%04s", substr($this->v_fecha,8,2), substr($this->v_fecha,5,2),substr($this->v_fecha,0,4)) ;
		switch ($this->v_form)
			{
			case 'YYYY/MM/DD':
				$this->v_fecha=$v_anno.'/'.$v_mes.'/'.$v_dia;
				break;
			case 'YYYY-MM-DD': 
				$this->v_fecha=$v_anno.'-'.$v_mes.'-'.$v_dia;
				break;
			case 'DD/MM/YYYY': 
				$this->v_fecha=$v_dia.'/'.$v_mes.'/'.$v_anno;
				break;
			case 'DD-MM-YYYY': 
				$this->v_fecha=$v_dia.'-'.$v_mes.'-'.$v_anno;
				break;
			}
		if (($fecha=='' or $fecha=='__/__/____' ) and $this->v_formb=='') { $this->v_fecha=''; }
		
		return $this->v_fecha;
		}
	}


class OpensoftError
	{
    function error()
	    {
        function ver_error($numero, $mensaje, $archivo, $linea, $contexto, $retorna=0)
    	    {
			switch ($numero)
				{
				case 2:
//					echo('<script languaje="JavaScript">');
//					$mensaje1=substr($mensaje,0,strlen($mensaje)-1);
//					$mensaje1='ERROR!!! \n no  : '.$numero . ' \n str  : ' . $mensaje1 . " Archivo: $archivo Linea: $linea" ;
//					echo('alert( \' '.addslashes($mensaje1).' \' )');
//					echo('</script>');
//					exit -1;
					break;
				default:
					//echo('<script languaje="JavaScript">');
					//$mensaje1=substr($mensaje,0,strlen($mensaje)-1 );
					//$mensaje1='ERROR!!! \n no  : '.$numero . ' \n str  : ' . $mensaje1 .$linea.$archivo;
					//echo('alert( \' '.$mensaje1.' \' )');
					//echo('</script>');
//					exit -1;
					break;
				}
        	}
        
        function vererrorFatal($buffer)
        	{
            $buffer_temporal = $buffer;
            $texto = strip_tags($buffer_temporal);
            if(preg_match('/Fatal error: (.+) in (.+)? on line (d+)/', $texto, $c))
                return adm_error(E_USER_ERROR, $c[1], $c[2], $c[3], "", true);
            return $buffer;
        	}
        ob_start('vererrorFatal');
        set_error_handler('ver_error');
    	}
	function _error()
		{
		restore_error_handler();
		}
		
	}


class AdminError
{
    function __construct()
    {
        function adm_error($numero, $mensaje, $archivo, $linea, $contexto, $retorna=0)
        {
            $objContexto = new Contexto($numero, $mensaje, $archivo, $linea, $contexto);
            if($retorna) 
                return $objContexto->leer();
            else
                print $objContexto->leer();
        }
        
        function errorFatal($buffer)
        {
            $buffer_temporal = $buffer;
            $texto = strip_tags($buffer_temporal);
            if(preg_match('/Fatal error: (.+) in (.+)? on line (d+)/', $texto, $c))
                return adm_error(E_USER_ERROR, $c[1], $c[2], $c[3], "", true);
            return $buffer;
        }
        ob_start('errorFatal');
        set_error_handler('adm_error');
    }
}

/** 
 * Clase Contexto
 * Devuelve el contexto de la linea de un archivo.
 *
 **/
class Contexto
{
    /**
     * Atributo
     *
     **/
    
    var $_numero = "";
    
    /**
     * Atributo
     *
     **/
    
    var $_mensaje = "";
    
    /**
     * Atributo
     *
     **/
    
    var $_lineas = 5;

    /**
     * Constructor
     * @access protected
     */
    function __construct($numero, $mensaje, $archivo, $linea, $contexto)
    {
        $this->_mensaje = "
		javascript
        <b>Error:</b> $mensaje<br><hr>
        <b>Archivo:</b> $archivo<br><hr>
        <b>L�nea:</b> $linea<br><hr>
        <b>Contexto del C�digo:</b><br><pre>".
		$this->obtenerContexto($archivo, (int) $linea)."</pre><hr>";
    }
    
    /**
     *
     * @access public
     * @return void 
     **/
    function leer()
    {
        return $this->_mensaje;
    }
    
    /**
     *
     * @access public
     * @return void 
     **/
    function obtenerContexto($archivo, $linea)
    {
        if (!file_exists($archivo)) 
        { 
            //  Nos fijamos que el archivo exista
            return "El contexto no puede mostrarse - ($archivo) no existe"; 
        } elseif ((!is_int($linea)) OR ($linea <= 0)) { 
            //  Verificamos que el numero de linea sea v�lido
            return "El contexto no puede mostrarse - ($linea) es un n�mero inv�lido de linea"; 
        } else {
            //  leemos el codigo
            $codigo = file( $archivo );
            $lineas = count($codigo);

            //  calculamos los numeros de linea
            $inicio = $linea - $this->_lineas; 
            $fin = $linea + $this->_lineas; 
            //  verificaciones de seguridad
            if ($inicio < 0) $inicio = 0;
            if ($fin >= $lineas) $fin = $lineas;
            $largo_fin= strlen($fin) + 2;
            
            for ($i = $inicio-1; $i < $fin; $i++)
            { 
                //  marcamos la linea en cuestion.
                $color=($i==$linea-1?"red":"black");
                $salida[] = "<span style='background-color: lightgrey'>".($i+1).
                            str_repeat("&nbsp;", $largo_fin - strlen($i+1)).
                            "</span><span style='color: $color'>".
                            htmlentities($codigo[$i]).'</span>';
            } 
            return trim(join("", $salida)); 
        }
    }
}

