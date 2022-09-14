<?php
require('fpdf2.php');

class PDF_MC_Table extends FPDF{
    var $widths;
    var $aligns;

    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';

    //var extend
    var $tipoHeader;
    var $dataHeader;

    function SetWidths($w){
        //Set the array of column widths
        $this->widths=$w;
    }

    function SetAligns($a){
        //Set the array of column alignments
        $this->aligns=$a;
    }

    function Row($conf, $data){
        //Calculate the height of the row
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]['text']));
        $h=2.5*$nb; //$h=5*$nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($data[$i]['align']) ? $data[$i]['align'] : 'L';
            //Save the current position
            $x=$this->GetX();
            $y=$this->GetY();

    		//Draw the border
    		if ($conf['border'] > 0) {
    			$this->SetDrawColor(0, 0, 0);
    		} else {
    			$this->SetFillColor(255, 255, 255);
    			$this->SetDrawColor(255, 255, 255);
    		}

            //Draw the border
            $this->Rect($x,$y,$w,$h,'D');
            //Print the text
            $this->MultiCell($w,2.5,$data[$i]['text'],0,$a); //$this->MultiCell($w,5,$data[$i]['text'],0,$a);
            //Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h){
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt){
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }

    function WriteHTML($html)
    {
        // HTML parser
        $html = str_replace("\n",' ',$html);
        $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                // Text
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                else
                    $this->Write(5,$e);
            }
            else
            {
                // Tag
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    // Extract attributes
                    $a2 = explode(' ',$e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }

    function OpenTag($tag, $attr)
    {
        // Opening tag
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,true);
        if($tag=='A')
            $this->HREF = $attr['HREF'];
        if($tag=='BR')
            $this->Ln(5);
    }

    function CloseTag($tag)
    {
        // Closing tag
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF = '';
    }

    function SetStyle($tag, $enable)
    {
        // Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach(array('B', 'I', 'U') as $s)
        {
            if($this->$s>0)
                $style .= $s;
        }
        $this->SetFont('',$style);
    }

    function PutLink($URL, $txt)
    {
        // Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }

    function Header()
    {
        $tipo = $this->tipoHeader;
        $response = $this->dataHeader;        

        if ($tipo == 'LIBRO_DIARIO') {
            $this->HeaderLibroDiario($response);
        } else if ($tipo == 'LIBRO_MAYOR') {
            $this->HeaderLibroMayor($response);
        }
    }

    function DefinirParametrosHeader($tipo, $data)
    {
        $this->tipoHeader = $tipo;
        $this->dataHeader = $data;
    }

    function HeaderLibroDiario($response) 
    {
        $sTipoLetra = 'Helvetica';

        //SETEAMOS FONT
        $this->SetFont($sTipoLetra, 'B', 13);

        //1 FILA
        //IZQUIERDA
        $this->Multicell(0, 4, $this->printText(wordwrap("FORMATO 5.1: \"LIBRO DIARIO\"", 80, "\n")));

        //SETEAMOS FONT
        $this->SetFont($sTipoLetra, 'B', 8);

        //2 FILA
        //IZQUIERDA
        $meses = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
        $periodo = $meses[intval($response->param->Fe_Mes)-1] . " " . $response->param->Fe_Periodo;
        $this->Cell(10, 10, 'PERIODO: ' . $periodo, 0, 0, 'L');
        $this->Ln(4);

        //3 FILA
        //IZQUIERDA	
        $this->Cell(10, 10, 'RUC: ' . $this->printText($response->data_company->ruc), 0, 0, 'L');
        $this->Ln(4);

        //4 FILA
        //IZQUIERDA
        $razsocial = $response->data_company->razsocial;
        $this->Cell(10, 10, 'RAZON SOCIAL: ' . $this->printText($response->data_company->razsocial), 0, 0, 'L');
        $this->Ln(4);

        //HEADER 1
        $arrHeaderTableDetail1 = array(
            array('text' => 'CORRELATIVO DEL ASIENTO', 'align' => 'C'),
            array('text' => 'FECHA OPE.', 'align' => 'C'),
            array('text' => $this->printText('GLOSA O DESCRIPCIÓN DE LA OPERACION'), 'align' => 'C'),
            array('text' => $this->printText('REFERENCIA DE LA OPERACIÓN'), 'align' => 'C'),
            array('text' => 'CUENTA CONTABLE ASOCIADA', 'align' => 'C'),
            array('text' => 'MOVIMIENTO', 'align' => 'C'),
        );
        //HEADER 2
        $arrHeaderTableDetail2 = array(
            array('text' => 'M', 'align' => 'C'),
            array('text' => 'S/D', 'align' => 'C'),
            array('text' => 'ASI', 'align' => 'C'),
            array('text' => '', 'align' => 'C'),
            array('text' => '', 'align' => 'C'),
            array('text' => $this->printText('CÓDIGO DE LIBRO O REGISTRO'), 'align' => 'C'),
            array('text' => $this->printText('NÚMERO CORRELA.'), 'align' => 'C'),
            array('text' => $this->printText('NÚMERO DEL DOCUMENTO SUSTENTATORIO'), 'align' => 'C'),
            array('text' => $this->printText('CÓDIGO'), 'align' => 'C'),
            array('text' => $this->printText('DENOMINACIÓN'), 'align' => 'C'),
            array('text' => 'DEBE', 'align' => 'C'),
            array('text' => 'HABER', 'align' => 'C'),
        );

        $this->Ln(5);

        //HEADER 1
        $w = array(19,13,30,49,48,32);
        $this->SetWidths($w);
        $this->SetFont($sTipoLetra, '', 6);
        $this->Row(
            array('border' => 1),
            $arrHeaderTableDetail1
        );

        //HEADER 2
        $w = array(5,7,7,13,30,13,13,23,13,35,16,16);
        $this->SetWidths($w);
        $this->SetFont($sTipoLetra, '', 5);
        $this->Row(
            array('border' => 1),
            $arrHeaderTableDetail2
        );

        $this->Ln(2.5);

        //SETEAMOS FONT
        // $this->SetFont($sTipoLetra, '', 5);
    }

    function printText($text) {
		return utf8_decode($text);
	}

    function HeaderLibroMayor($response) 
    {
        $sTipoLetra = 'Helvetica';

        //SETEAMOS FONT
        $this->SetFont($sTipoLetra, 'B', 13);

        //1 FILA
        //IZQUIERDA
        $this->Multicell(0, 4, $this->printText(wordwrap("FORMATO 6.1: \"LIBRO MAYOR\"", 80, "\n")));

        //SETEAMOS FONT
        $this->SetFont($sTipoLetra, 'B', 8);

        //2 FILA
        //IZQUIERDA
        $meses = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
        $periodo = $meses[intval($response->param->Fe_Mes)-1] . " " . $response->param->Fe_Periodo;
        $this->Cell(10, 10, 'PERIODO: ' . $periodo, 0, 0, 'L');
        $this->Ln(4);

        //3 FILA
        //IZQUIERDA	
        $this->Cell(10, 10, 'RUC: ' . $this->printText($response->data_company->ruc), 0, 0, 'L');
        $this->Ln(4);

        //4 FILA
        //IZQUIERDA
        $razsocial = $response->data_company->razsocial;
        $this->Cell(10, 10, 'RAZON SOCIAL: ' . $this->printText($response->data_company->razsocial), 0, 0, 'L');
        $this->Ln(4);

        //HEADER 1
        $arrHeaderTableDetail1 = array(
            array('text' => $this->printText('FECHA DE LA OPERACIÓN'), 'align' => 'C'),
            array('text' => $this->printText('NÚMERO CORRELATIVO DEL LIBRO DIARIO.'), 'align' => 'C'),
            array('text' => $this->printText('DESCRIPCIÓN O GLOSA DE LA OPERACION'), 'align' => 'C'),
            array('text' => $this->printText('SALDOS Y MOVIMIENTOS'), 'align' => 'C'),
        );
        //HEADER 2
        $arrHeaderTableDetail2 = array(
            array('text' => '', 'align' => 'C'),
            array('text' => 'M', 'align' => 'C'),
            array('text' => 'S/D', 'align' => 'C'),
            array('text' => 'ASI', 'align' => 'C'),
            array('text' => '', 'align' => 'C'),
            array('text' => 'DEUDOR', 'align' => 'C'),
            array('text' => 'ACREEDOR', 'align' => 'C'),
        );

        $this->Ln(5);

        //HEADER 1
        $w = array(16,30,95,40);
        $this->SetWidths($w);
        $this->SetFont($sTipoLetra, '', 6);
        $this->Row(
            array('border' => 1),
            $arrHeaderTableDetail1
        );

        //HEADER 2
        $w = array(16,10,10,10,95,20,20);
        $this->SetWidths($w);
        $this->SetFont($sTipoLetra, '', 5);
        $this->Row(
            array('border' => 1),
            $arrHeaderTableDetail2
        );

        $this->Ln(2.5);

        //SETEAMOS FONT
        // $this->SetFont($sTipoLetra, '', 5);
    }
}
?>