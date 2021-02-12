<?php

class FacturasElectronicasController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
    	
    		set_time_limit(0);
		error_reporting(E_ALL ^ E_NOTICE); 
    	
		include 'reportes/m_facelectronicas.php';
		include 'reportes/t_facelectronicas.php';
	
		$this->Init();	
		$result = '';
		$result_f = '';

		switch ($this->action) {

			case "Procesar":				
				echo 'Entra a Mostrar'."\n";
				$archi = $_FILES['ubicacion']['tmp_name'];	
				$nom = $_FILES['ubicacion']['name']; // nombre del archivo	
				$numero = explode("-", $nom);	
				$file = fopen($archi, "r") or exit("No se puede leer el archivo.");
				$c = 0;
				while(!feof($file)) {
					$ls[$c] = explode("/",fgets($file));
					$c++;
				}
				fclose($file);
				
				$res  = FacturasElectronicasModel::filtrar($ls, $numero);
				if($res==5){
					echo '<script>alert("Error! Multiples codigos de proveedor.");</script>';
				} else {
					$result_f = FacturasElectronicasTemplate::reporte($res);
				}								
				break;
				
			case "Ingresar": 
				echo 'Entro a Ingresar'."\n";
				$numfactura = @$_REQUEST['numfactura'];
				$proveedor  = @$_REQUEST['codigopro']; 
				$tipodocu   = @$_REQUEST['tipodocu']; 
				$docurefe   = @$_REQUEST['docurefe'];
				$nroorden   = @$_REQUEST['nroorden'];
				$almacen    = @$_REQUEST['almacen'];
				$cpagar     = @$_REQUEST['cpagar']; 
				$fecha      = @$_REQUEST['fechar']; 
								
				//Datos por linea
				$filas = Array();
				$filas['cod_art']  = @$_REQUEST['cod_art']; 
				$filas['unidad']   = @$_REQUEST['unidad']; 
				$filas['moneda']   = @$_REQUEST['moneda']; 
				$filas['cantidad'] = @$_REQUEST['cantidad']; 
				$filas['precio']   = @$_REQUEST['precio']; 
				$filas['valtot']   = @$_REQUEST['valtot']; 
				
				// Registro de Compras
				$regcompra['rgfechasistema'] 	= $_REQUEST['rgfechasistema']; 
				$regcompra['rgfechadocumento'] 	= $_REQUEST['rgfechadocumento'];
				$regcompra['rgtipodocu'] 	= $_REQUEST['rgtipodocu'];
				$regcompra['rgseriedocu'] 	= $_REQUEST['rgseriedocu'];
				$regcompra['rgnumerodocu'] 	= $_REQUEST['rgnumerodocu'];
				$regcompra['rgrubro'] 		= $_REQUEST['rgrubro'];
				$regcompra['rgvencimiento'] 	= $_REQUEST['rgvencimiento'];
				$regcompra['rgmoneda'] 		= $_REQUEST['rgmoneda'];
				$regcompra['rgvventa'] 		= $_REQUEST['rgvventa'];
				$regcompra['rgimpuesto'] 	= $_REQUEST['rgimpuesto'];
				$regcompra['rgvtotal'] 		= $_REQUEST['rgvtotal'];
				$regcompra['rgtcambio'] 	= $_REQUEST['rgtcambio'];
				$regcompra['rgimpinafecto'] 	= $_REQUEST['rgimpinafecto'];
				$flag = 0;
				if($cpagar=="S") {
					if($regcompra['rgvtotal'] != ($regcompra['rgvventa']+$regcompra['rgimpuesto']+$regcompra['rgimpinafecto'])){
						$flag = 1;
						echo '<script>alert("El Total Compra debe ser igual a Valor Venta + Impuesto + Varios");</script>';
					} else {
						$p = FacturasElectronicasModel::cpagar($almacen, $numfactura, $docurefe, $proveedor, $nroorden, $regcompra);
						if($p==0) {
							$flag = 1;
							echo '<script>alert("Registro de compra duplicado.");</script>';
						}
					}
				}
				if($flag == 0) {
					$res = FacturasElectronicasModel::ingresar($almacen, $numfactura, $docurefe, $cpagar, $fecha, $proveedor, $tipodocu, $filas);					
					if($res==1) {
						FacturasElectronicasModel::actCompraDev($regcompra['rgtipodocu'], $regcompra['rgseriedocu'], $regcompra['rgnumerodocu'], $numfactura);
						echo '<script>alert("Se registro el ingreso por compra.");</script>';
					}
					$result   = FacturasElectronicasTemplate::search_form();
					$result_f = "";
					$this->visor->addComponent("ContentB", "content_body", $result);				
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
				break;

		    	default:
			    	$this->visor->addComponent("ContentT", "content_title", FacturasElectronicasTemplate::titulo());
				$result     	= FacturasElectronicasTemplate::search_form();
				break;
		}
		$this->visor->addComponent("ContentT", "content_title", FacturasElectronicasTemplate::titulo());		
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);		
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
				
	}
}
