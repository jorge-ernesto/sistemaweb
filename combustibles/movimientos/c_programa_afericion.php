<?phpclass ProgramaAfericionController extends Controller {	function Init() {		$this->visor = new Visor();		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';	}	function Run() {		include 'movimientos/m_programa_afericion.php';		include 'movimientos/t_programa_afericion.php';		$this->Init();		$result = "";		$result_f = "";		$form_search = false;		$listado = false;		$actualizar = false;		switch($this->action) {			case "Programar":
				$result_f = ProgramaAfericionTemplate::muestraResultado(ProgramaAfericionModel::programaAfericion($_REQUEST['lado'],$_REQUEST['modo'],$_REQUEST['lineas']));				break;			default:				$result = ProgramaAfericionTemplate::formProgramar(ProgramaAfericionModel::obtenerLados(),ProgramaAfericionModel::obtenerModos());
				$result_f = "";
				break;		}		$this->visor->addComponent("ContentT", "content_title", ProgramaAfericionTemplate::titulo());		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	}}?>
