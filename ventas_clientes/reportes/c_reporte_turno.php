<?php
class ReporteTurnoController extends Controller {
	function __construct($visor) {
		include("m_reporte_turno.php");
		//include($_SERVER["DOCUMENT_ROOT"]."/ventas_clientes/lib/reportes2.inc.php");
		$this->visor=$visor;
	}

	function Titulos($pdf,$fecha) {
	//Títulos de las columnas
		$header=array('     '.$fecha,'       84','       90','       95','       97','        BD5','        KEROSENE','         Total Liquido','        GLP');
		$header2=array('Galones','importe');
	//Carga de datos
		$pdf->SetFont('Arial','',8);

		foreach($header as $col)
			$pdf->Cell(30,4,$col,1,0);
			$pdf->Cell(20,4,"Market",1,0,L);
			$pdf->Cell(20,4,"Total",1,0,L);  
			$pdf->Ln(); 
			$pdf->Cell(30,4,"",1,0);

			for($i=0;$i<6;$i++) {
				$pdf->Cell(15,4,$header2[0],1,0);
				$pdf->Cell(15,4,$header2[1],1,0);
			}
			$pdf->Cell(15,4,"Litros",1,0);
			$pdf->Cell(15,4,"Importe",1,0);  

			$pdf->Cell(20,4,"Importe",1,0,L);
			$pdf->Cell(20,4,"Importe",1,0,L);     
	}

	function run() {
		$this->vista();
	}

	function vista() {
		ob_start();
		$this->estaciones=ReporteTurnoModel::obtieneListaEstaciones();
		$this->reporte=ReporteTurnoModel::reporte_turno();
		$this->articulos=ReporteTurnoModel::getArticuloDescripcionBreve(); 		
		$pdf=new pdf_turno("P","mm","A3");
		$workbook = new Workbook($chrFileName);
		$formato0 =$workbook->add_format();
		$formato2 =$workbook->add_format();
		$formato5 =$workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =$workbook->add_worksheet('Hoja de Resultados');
		// $worksheet1->set_column(0, 0, 15);

		// $worksheet1->set_zoom(100);
		// $worksheet1->set_landscape(100);
		include("t_reporte_turno.php");
		if($_POST["action"]=="PDF") {
			ob_end_clean();
			$pdf->Output("reporte_turno.pdf",I);    
		} else if($_POST["action"]=="Excel") {
			$workbook->close(); 
			$chrFileName = "reporte_turno";           
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$chrFileName.xls" );
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
			header("Pragma: public");
		} else {
			$out1 = ob_get_contents();
			$this->visor->addComponent("Content", "content", $out1);
		}
	}
}

?>