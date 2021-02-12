<?php
//load libraries
include "../valida_sess.php";
include_once('/sistemaweb/include/dbsqlca.php');

/**
 * Considerar modificacion de placas para tener consistencia entre este reporte y lo enviado a ebi
 */

//define global variables
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

/*******************************************************************************
* FPDF                                                                         *
*                                                                              *
* Version: 1.81                                                                *
* Date:    2015-12-20                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

define('FPDF_VERSION','1.81');

class FPDF
{
protected $page;               // current page number
protected $n;                  // current object number
protected $offsets;            // array of object offsets
protected $buffer;             // buffer holding in-memory PDF
protected $pages;              // array containing pages
protected $state;              // current document state
protected $compress;           // compression flag
protected $k;                  // scale factor (number of points in user unit)
protected $DefOrientation;     // default orientation
protected $CurOrientation;     // current orientation
protected $StdPageSizes;       // standard page sizes
protected $DefPageSize;        // default page size
protected $CurPageSize;        // current page size
protected $CurRotation;        // current page rotation
protected $PageInfo;           // page-related data
protected $wPt, $hPt;          // dimensions of current page in points
protected $w, $h;              // dimensions of current page in user unit
protected $lMargin;            // left margin
protected $tMargin;            // top margin
protected $rMargin;            // right margin
protected $bMargin;            // page break margin
protected $cMargin;            // cell margin
protected $x, $y;              // current position in user unit
protected $lasth;              // height of last printed cell
protected $LineWidth;          // line width in user unit
protected $fontpath;           // path containing fonts
protected $CoreFonts;          // array of core font names
protected $fonts;              // array of used fonts
protected $FontFiles;          // array of font files
protected $encodings;          // array of encodings
protected $cmaps;              // array of ToUnicode CMaps
protected $FontFamily;         // current font family
protected $FontStyle;          // current font style
protected $underline;          // underlining flag
protected $CurrentFont;        // current font info
protected $FontSizePt;         // current font size in points
protected $FontSize;           // current font size in user unit
protected $DrawColor;          // commands for drawing color
protected $FillColor;          // commands for filling color
protected $TextColor;          // commands for text color
protected $ColorFlag;          // indicates whether fill and text colors are different
protected $WithAlpha;          // indicates whether alpha channel is used
protected $ws;                 // word spacing
protected $images;             // array of used images
protected $PageLinks;          // array of links in pages
protected $links;              // array of internal links
protected $AutoPageBreak;      // automatic page breaking
protected $PageBreakTrigger;   // threshold used to trigger page breaks
protected $InHeader;           // flag set when processing header
protected $InFooter;           // flag set when processing footer
protected $AliasNbPages;       // alias for total number of pages
protected $ZoomMode;           // zoom display mode
protected $LayoutMode;         // layout display mode
protected $metadata;           // document properties
protected $PDFVersion;         // PDF version number

/*******************************************************************************
*                               Public methods                                 *
*******************************************************************************/

function __construct($orientation='P', $unit='mm', $size='A4')
{
	// Some checks
	$this->_dochecks();
	// Initialization of properties
	$this->state = 0;
	$this->page = 0;
	$this->n = 2;
	$this->buffer = '';
	$this->pages = array();
	$this->PageInfo = array();
	$this->fonts = array();
	$this->FontFiles = array();
	$this->encodings = array();
	$this->cmaps = array();
	$this->images = array();
	$this->links = array();
	$this->InHeader = false;
	$this->InFooter = false;
	$this->lasth = 0;
	$this->FontFamily = '';
	$this->FontStyle = '';
	$this->FontSizePt = 12;
	$this->underline = false;
	$this->DrawColor = '0 G';
	$this->FillColor = '0 g';
	$this->TextColor = '0 g';
	$this->ColorFlag = false;
	$this->WithAlpha = false;
	$this->ws = 0;
	// Font path
	if(defined('FPDF_FONTPATH'))
	{
		$this->fontpath = FPDF_FONTPATH;
		if(substr($this->fontpath,-1)!='/' && substr($this->fontpath,-1)!='\\')
			$this->fontpath .= '/';
	}
	/*elseif(is_dir(dirname(__FILE__).'/font'))
		$this->fontpath = dirname(__FILE__).'/font/';*/
	elseif(is_dir(dirname(__FILE__).'/'))
		$this->fontpath = dirname(__FILE__).'/';
	else
		$this->fontpath = '';
	// Core fonts
	$this->CoreFonts = array('courier', 'courier2', 'helvetica', 'times', 'symbol', 'zapfdingbats');
	// Scale factor
	if($unit=='pt')
		$this->k = 1;
	elseif($unit=='mm')
		$this->k = 72/25.4;
	elseif($unit=='cm')
		$this->k = 72/2.54;
	elseif($unit=='in')
		$this->k = 72;
	else
		$this->Error('Incorrect unit: '.$unit);
	// Page sizes
	$this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
		'letter'=>array(612,792), 'legal'=>array(612,1008));
	$size = $this->_getpagesize($size);
	$this->DefPageSize = $size;
	$this->CurPageSize = $size;
	// Page orientation
	$orientation = strtolower($orientation);
	if($orientation=='p' || $orientation=='portrait')
	{
		$this->DefOrientation = 'P';
		$this->w = $size[0];
		$this->h = $size[1];
	}
	elseif($orientation=='l' || $orientation=='landscape')
	{
		$this->DefOrientation = 'L';
		$this->w = $size[1];
		$this->h = $size[0];
	}
	else
		$this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation = $this->DefOrientation;
	$this->wPt = $this->w*$this->k;
	$this->hPt = $this->h*$this->k;
	// Page rotation
	$this->CurRotation = 0;
	// Page margins (1 cm)
	$margin = 28.35/$this->k;
	$this->SetMargins($margin,$margin);
	// Interior cell margin (1 mm)
	$this->cMargin = $margin/10;
	// Line width (0.2 mm)
	$this->LineWidth = .567/$this->k;
	// Automatic page break
	$this->SetAutoPageBreak(true,2*$margin);
	// Default display mode
	$this->SetDisplayMode('default');
	// Enable compression
	$this->SetCompression(true);
	// Set default PDF version number
	$this->PDFVersion = '1.3';
}

function SetMargins($left, $top, $right=null)
{
	// Set left, top and right margins
	$this->lMargin = $left;
	$this->tMargin = $top;
	if($right===null)
		$right = $left;
	$this->rMargin = $right;
}

function SetLeftMargin($margin)
{
	// Set left margin
	$this->lMargin = $margin;
	if($this->page>0 && $this->x<$margin)
		$this->x = $margin;
}

function SetTopMargin($margin)
{
	// Set top margin
	$this->tMargin = $margin;
}

function SetRightMargin($margin)
{
	// Set right margin
	$this->rMargin = $margin;
}

function SetAutoPageBreak($auto, $margin=0)
{
	// Set auto page break mode and triggering margin
	$this->AutoPageBreak = $auto;
	$this->bMargin = $margin;
	$this->PageBreakTrigger = $this->h-$margin;
}

function SetDisplayMode($zoom, $layout='default')
{
	// Set display mode in viewer
	if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
		$this->ZoomMode = $zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
		$this->LayoutMode = $layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress)
{
	// Set page compression
	if(function_exists('gzcompress'))
		$this->compress = $compress;
	else
		$this->compress = false;
}

function SetTitle($title, $isUTF8=false)
{
	// Title of document
	$this->metadata['Title'] = $isUTF8 ? $title : utf8_encode($title);
}

function SetAuthor($author, $isUTF8=false)
{
	// Author of document
	$this->metadata['Author'] = $isUTF8 ? $author : utf8_encode($author);
}

function SetSubject($subject, $isUTF8=false)
{
	// Subject of document
	$this->metadata['Subject'] = $isUTF8 ? $subject : utf8_encode($subject);
}

function SetKeywords($keywords, $isUTF8=false)
{
	// Keywords of document
	$this->metadata['Keywords'] = $isUTF8 ? $keywords : utf8_encode($keywords);
}

function SetCreator($creator, $isUTF8=false)
{
	// Creator of document
	$this->metadata['Creator'] = $isUTF8 ? $creator : utf8_encode($creator);
}

function AliasNbPages($alias='{nb}')
{
	// Define an alias for total number of pages
	$this->AliasNbPages = $alias;
}

function Error($msg)
{
	// Fatal error
	throw new Exception('FPDF error: '.$msg);
}

function Close()
{
	// Terminate document
	if($this->state==3)
		return;
	if($this->page==0)
		$this->AddPage();
	// Page footer
	$this->InFooter = true;
	$this->Footer();
	$this->InFooter = false;
	// Close page
	$this->_endpage();
	// Close document
	$this->_enddoc();
}

function AddPage($orientation='', $size='', $rotation=0)
{
	// Start a new page
	if($this->state==3)
		$this->Error('The document is closed');
	$family = $this->FontFamily;
	$style = $this->FontStyle.($this->underline ? 'U' : '');
	$fontsize = $this->FontSizePt;
	$lw = $this->LineWidth;
	$dc = $this->DrawColor;
	$fc = $this->FillColor;
	$tc = $this->TextColor;
	$cf = $this->ColorFlag;
	if($this->page>0)
	{
		// Page footer
		$this->InFooter = true;
		$this->Footer();
		$this->InFooter = false;
		// Close page
		$this->_endpage();
	}
	// Start new page
	$this->_beginpage($orientation,$size,$rotation);
	// Set line cap style to square
	$this->_out('2 J');
	// Set line width
	$this->LineWidth = $lw;
	$this->_out(sprintf('%.2F w',$lw*$this->k));
	// Set font
	if($family)
		$this->SetFont($family,$style,$fontsize);
	// Set colors
	$this->DrawColor = $dc;
	if($dc!='0 G')
		$this->_out($dc);
	$this->FillColor = $fc;
	if($fc!='0 g')
		$this->_out($fc);
	$this->TextColor = $tc;
	$this->ColorFlag = $cf;
	// Page header
	$this->InHeader = true;
	$this->Header();
	$this->InHeader = false;
	// Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth = $lw;
		$this->_out(sprintf('%.2F w',$lw*$this->k));
	}
	// Restore font
	if($family)
		$this->SetFont($family,$style,$fontsize);
	// Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor = $dc;
		$this->_out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor = $fc;
		$this->_out($fc);
	}
	$this->TextColor = $tc;
	$this->ColorFlag = $cf;
}

function Header()
{
	// To be implemented in your own inherited class
}

function Footer()
{
	// To be implemented in your own inherited class
}

function PageNo()
{
	// Get current page number
	return $this->page;
}

function SetDrawColor($r, $g=null, $b=null)
{
	// Set color for all stroking operations
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->DrawColor = sprintf('%.3F G',$r/255);
	else
		$this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
	if($this->page>0)
		$this->_out($this->DrawColor);
}

function SetFillColor($r, $g=null, $b=null)
{
	// Set color for all filling operations
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->FillColor = sprintf('%.3F g',$r/255);
	else
		$this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
	if($this->page>0)
		$this->_out($this->FillColor);
}

function SetTextColor($r, $g=null, $b=null)
{
	// Set color for text
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->TextColor = sprintf('%.3F g',$r/255);
	else
		$this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
}

function GetStringWidth($s)
{
	// Get width of a string in the current font
	$s = (string)$s;
	$cw = &$this->CurrentFont['cw'];
	$w = 0;
	$l = strlen($s);
	for($i=0;$i<$l;$i++)
		$w += $cw[$s[$i]];
	return $w*$this->FontSize/1000;
}

function SetLineWidth($width)
{
	// Set line width
	$this->LineWidth = $width;
	if($this->page>0)
		$this->_out(sprintf('%.2F w',$width*$this->k));
}

function Line($x1, $y1, $x2, $y2)
{
	// Draw a line
	$this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

function Rect($x, $y, $w, $h, $style='')
{
	// Draw a rectangle
	if($style=='F')
		$op = 'f';
	elseif($style=='FD' || $style=='DF')
		$op = 'B';
	else
		$op = 'S';
	$this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

function AddFont($family, $style='', $file='')
{
	// Add a TrueType, OpenType or Type1 font
	$family = strtolower($family);
	if($file=='')
		$file = str_replace(' ','',$family).strtolower($style).'.php';
	$style = strtoupper($style);
	if($style=='IB')
		$style = 'BI';
	$fontkey = $family.$style;
	if(isset($this->fonts[$fontkey]))
		return;
	$info = $this->_loadfont($file);
	$info['i'] = count($this->fonts)+1;
	if(!empty($info['file']))
	{
		// Embedded font
		if($info['type']=='TrueType')
			$this->FontFiles[$info['file']] = array('length1'=>$info['originalsize']);
		else
			$this->FontFiles[$info['file']] = array('length1'=>$info['size1'], 'length2'=>$info['size2']);
	}
	$this->fonts[$fontkey] = $info;
}

function SetFont($family, $style='', $size=0)
{
	// Select a font; size given in points
	if($family=='')
		$family = $this->FontFamily;
	else
		$family = strtolower($family);
	$style = strtoupper($style);
	if(strpos($style,'U')!==false)
	{
		$this->underline = true;
		$style = str_replace('U','',$style);
	}
	else
		$this->underline = false;
	if($style=='IB')
		$style = 'BI';
	if($size==0)
		$size = $this->FontSizePt;
	// Test if font is already selected
	if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
		return;
	// Test if font is already loaded
	$fontkey = $family.$style;
	if(!isset($this->fonts[$fontkey]))
	{
		// Test if one of the core fonts
		if($family=='arial')
			$family = 'helvetica';
		if(in_array($family,$this->CoreFonts))
		{
			if($family=='symbol' || $family=='zapfdingbats')
				$style = '';
			$fontkey = $family.$style;
			if(!isset($this->fonts[$fontkey]))
				$this->AddFont($family,$style);
		}
		else
			$this->Error('Undefined font: '.$family.' '.$style);
	}
	// Select it
	$this->FontFamily = $family;
	$this->FontStyle = $style;
	$this->FontSizePt = $size;
	$this->FontSize = $size/$this->k;
	$this->CurrentFont = &$this->fonts[$fontkey];
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

function SetFontSize($size)
{
	// Set font size in points
	if($this->FontSizePt==$size)
		return;
	$this->FontSizePt = $size;
	$this->FontSize = $size/$this->k;
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

function AddLink()
{
	// Create a new internal link
	$n = count($this->links)+1;
	$this->links[$n] = array(0, 0);
	return $n;
}

function SetLink($link, $y=0, $page=-1)
{
	// Set destination of internal link
	if($y==-1)
		$y = $this->y;
	if($page==-1)
		$page = $this->page;
	$this->links[$link] = array($page, $y);
}

function Link($x, $y, $w, $h, $link)
{
	// Put a link on the page
	$this->PageLinks[$this->page][] = array($x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link);
}

function Text($x, $y, $txt)
{
	// Output a string
	if(!isset($this->CurrentFont))
		$this->Error('No font has been set');
	$s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
	if($this->underline && $txt!='')
		$s .= ' '.$this->_dounderline($x,$y,$txt);
	if($this->ColorFlag)
		$s = 'q '.$this->TextColor.' '.$s.' Q';
	$this->_out($s);
}

function AcceptPageBreak()
{
	// Accept automatic page break or not
	return $this->AutoPageBreak;
}

function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
	// Output a cell
	$k = $this->k;
	if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
	{
		// Automatic page break
		$x = $this->x;
		$ws = $this->ws;
		if($ws>0)
		{
			$this->ws = 0;
			$this->_out('0 Tw');
		}
		$this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
		$this->x = $x;
		if($ws>0)
		{
			$this->ws = $ws;
			$this->_out(sprintf('%.3F Tw',$ws*$k));
		}
	}
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$s = '';
	if($fill || $border==1)
	{
		if($fill)
			$op = ($border==1) ? 'B' : 'f';
		else
			$op = 'S';
		$s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	}
	if(is_string($border))
	{
		$x = $this->x;
		$y = $this->y;
		if(strpos($border,'L')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
		if(strpos($border,'T')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
		if(strpos($border,'R')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		if(strpos($border,'B')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	}
	if($txt!=='')
	{
		if(!isset($this->CurrentFont))
			$this->Error('No font has been set');
		if($align=='R')
			$dx = $w-$this->cMargin-$this->GetStringWidth($txt);
		elseif($align=='C')
			$dx = ($w-$this->GetStringWidth($txt))/2;
		else
			$dx = $this->cMargin;
		if($this->ColorFlag)
			$s .= 'q '.$this->TextColor.' ';
		$s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt));
		if($this->underline)
			$s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
		if($this->ColorFlag)
			$s .= ' Q';
		if($link)
			$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
	}
	if($s)
		$this->_out($s);
	$this->lasth = $h;
	if($ln>0)
	{
		// Go to next line
		$this->y += $h;
		if($ln==1)
			$this->x = $this->lMargin;
	}
	else
		$this->x += $w;
}

function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
{
	// Output text with automatic or explicit line breaks
	if(!isset($this->CurrentFont))
		$this->Error('No font has been set');
	$cw = &$this->CurrentFont['cw'];
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
	$s = str_replace("\r",'',$txt);
	$nb = strlen($s);
	if($nb>0 && $s[$nb-1]=="\n")
		$nb--;
	$b = 0;
	if($border)
	{
		if($border==1)
		{
			$border = 'LTRB';
			$b = 'LRT';
			$b2 = 'LR';
		}
		else
		{
			$b2 = '';
			if(strpos($border,'L')!==false)
				$b2 .= 'L';
			if(strpos($border,'R')!==false)
				$b2 .= 'R';
			$b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
		}
	}
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$ns = 0;
	$nl = 1;
	while($i<$nb)
	{
		// Get next character
		$c = $s[$i];
		if($c=="\n")
		{
			// Explicit line break
			if($this->ws>0)
			{
				$this->ws = 0;
				$this->_out('0 Tw');
			}
			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if($border && $nl==2)
				$b = $b2;
			continue;
		}
		if($c==' ')
		{
			$sep = $i;
			$ls = $l;
			$ns++;
		}
		$l += $cw[$c];
		if($l>$wmax)
		{
			// Automatic line break
			if($sep==-1)
			{
				if($i==$j)
					$i++;
				if($this->ws>0)
				{
					$this->ws = 0;
					$this->_out('0 Tw');
				}
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			}
			else
			{
				if($align=='J')
				{
					$this->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
					$this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
				}
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
				$i = $sep+1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if($border && $nl==2)
				$b = $b2;
		}
		else
			$i++;
	}
	// Last chunk
	if($this->ws>0)
	{
		$this->ws = 0;
		$this->_out('0 Tw');
	}
	if($border && strpos($border,'B')!==false)
		$b .= 'B';
	$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
	$this->x = $this->lMargin;
}

function Write($h, $txt, $link='')
{
	// Output text in flowing mode
	if(!isset($this->CurrentFont))
		$this->Error('No font has been set');
	$cw = &$this->CurrentFont['cw'];
	$w = $this->w-$this->rMargin-$this->x;
	$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
	$s = str_replace("\r",'',$txt);
	$nb = strlen($s);
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$nl = 1;
	while($i<$nb)
	{
		// Get next character
		$c = $s[$i];
		if($c=="\n")
		{
			// Explicit line break
			$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			if($nl==1)
			{
				$this->x = $this->lMargin;
				$w = $this->w-$this->rMargin-$this->x;
				$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
			continue;
		}
		if($c==' ')
			$sep = $i;
		$l += $cw[$c];
		if($l>$wmax)
		{
			// Automatic line break
			if($sep==-1)
			{
				if($this->x>$this->lMargin)
				{
					// Move to next line
					$this->x = $this->lMargin;
					$this->y += $h;
					$w = $this->w-$this->rMargin-$this->x;
					$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
					$i++;
					$nl++;
					continue;
				}
				if($i==$j)
					$i++;
				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
			}
			else
			{
				$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',false,$link);
				$i = $sep+1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			if($nl==1)
			{
				$this->x = $this->lMargin;
				$w = $this->w-$this->rMargin-$this->x;
				$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
		}
		else
			$i++;
	}
	// Last chunk
	if($i!=$j)
		$this->Cell($l/1000*$this->FontSize,$h,substr($s,$j),0,0,'',false,$link);
}

function Ln($h=null)
{
	// Line feed; default value is the last cell height
	$this->x = $this->lMargin;
	if($h===null)
		$this->y += $this->lasth;
	else
		$this->y += $h;
}

function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
{
	// Put an image on the page
	if($file=='')
		$this->Error('Image file name is empty');
	if(!isset($this->images[$file]))
	{
		// First use of this image, get info
		if($type=='')
		{
			$pos = strrpos($file,'.');
			if(!$pos)
				$this->Error('Image file has no extension and no type was specified: '.$file);
			$type = substr($file,$pos+1);
		}
		$type = strtolower($type);
		if($type=='jpeg')
			$type = 'jpg';
		$mtd = '_parse'.$type;
		if(!method_exists($this,$mtd))
			$this->Error('Unsupported image type: '.$type);
		$info = $this->$mtd($file);
		$info['i'] = count($this->images)+1;
		$this->images[$file] = $info;
	}
	else
		$info = $this->images[$file];

	// Automatic width and height calculation if needed
	if($w==0 && $h==0)
	{
		// Put image at 96 dpi
		$w = -96;
		$h = -96;
	}
	if($w<0)
		$w = -$info['w']*72/$w/$this->k;
	if($h<0)
		$h = -$info['h']*72/$h/$this->k;
	if($w==0)
		$w = $h*$info['w']/$info['h'];
	if($h==0)
		$h = $w*$info['h']/$info['w'];

	// Flowing mode
	if($y===null)
	{
		if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
		{
			// Automatic page break
			$x2 = $this->x;
			$this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
			$this->x = $x2;
		}
		$y = $this->y;
		$this->y += $h;
	}

	if($x===null)
		$x = $this->x;
	$this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
	if($link)
		$this->Link($x,$y,$w,$h,$link);
}

function GetPageWidth()
{
	// Get current page width
	return $this->w;
}

function GetPageHeight()
{
	// Get current page height
	return $this->h;
}

function GetX()
{
	// Get x position
	return $this->x;
}

function SetX($x)
{
	// Set x position
	if($x>=0)
		$this->x = $x;
	else
		$this->x = $this->w+$x;
}

function GetY()
{
	// Get y position
	return $this->y;
}

function SetY($y, $resetX=true)
{
	// Set y position and optionally reset x
	if($y>=0)
		$this->y = $y;
	else
		$this->y = $this->h+$y;
	if($resetX)
		$this->x = $this->lMargin;
}

function SetXY($x, $y)
{
	// Set x and y positions
	$this->SetX($x);
	$this->SetY($y,false);
}

function Output($dest='', $name='', $isUTF8=false)
{
	// Output PDF to some destination
	$this->Close();
	if(strlen($name)==1 && strlen($dest)!=1)
	{
		// Fix parameter order
		$tmp = $dest;
		$dest = $name;
		$name = $tmp;
	}
	if($dest=='')
		$dest = 'I';
	if($name=='')
		$name = 'doc.pdf';
	switch(strtoupper($dest))
	{
		case 'I':
			// Send to standard output
			$this->_checkoutput();
			if(PHP_SAPI!='cli')
			{
				// We send to a browser
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; '.$this->_httpencode('filename',$name,$isUTF8));
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
			}
			echo $this->buffer;
			break;
		case 'D':
			// Download file
			$this->_checkoutput();
			header('Content-Type: application/x-download');
			header('Content-Disposition: attachment; '.$this->_httpencode('filename',$name,$isUTF8));
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');
			echo $this->buffer;
			break;
		case 'F':
			// Save to local file
			if(!file_put_contents($name,$this->buffer))
				$this->Error('Unable to create output file: '.$name);
			break;
		case 'S':
			// Return as a string
			return $this->buffer;
		default:
			$this->Error('Incorrect output destination: '.$dest);
	}
	return '';
}

/*******************************************************************************
*                              Protected methods                               *
*******************************************************************************/

protected function _dochecks()
{
	// Check mbstring overloading
	if(ini_get('mbstring.func_overload') & 2)
		$this->Error('mbstring overloading must be disabled');
	// Ensure runtime magic quotes are disabled
	if(get_magic_quotes_runtime())
		@set_magic_quotes_runtime(0);
}

protected function _checkoutput()
{
	if(PHP_SAPI!='cli')
	{
		if(headers_sent($file,$line))
			$this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
	}
	if(ob_get_length())
	{
		// The output buffer is not empty
		if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents()))
		{
			// It contains only a UTF-8 BOM and/or whitespace, let's clean it
			ob_clean();
		}
		else
			$this->Error("Some data has already been output, can't send PDF file");
	}
}

protected function _getpagesize($size)
{
	if(is_string($size))
	{
		$size = strtolower($size);
		if(!isset($this->StdPageSizes[$size]))
			$this->Error('Unknown page size: '.$size);
		$a = $this->StdPageSizes[$size];
		return array($a[0]/$this->k, $a[1]/$this->k);
	}
	else
	{
		if($size[0]>$size[1])
			return array($size[1], $size[0]);
		else
			return $size;
	}
}

protected function _beginpage($orientation, $size, $rotation)
{
	$this->page++;
	$this->pages[$this->page] = '';
	$this->state = 2;
	$this->x = $this->lMargin;
	$this->y = $this->tMargin;
	$this->FontFamily = '';
	// Check page size and orientation
	if($orientation=='')
		$orientation = $this->DefOrientation;
	else
		$orientation = strtoupper($orientation[0]);
	if($size=='')
		$size = $this->DefPageSize;
	else
		$size = $this->_getpagesize($size);
	if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
	{
		// New size or orientation
		if($orientation=='P')
		{
			$this->w = $size[0];
			$this->h = $size[1];
		}
		else
		{
			$this->w = $size[1];
			$this->h = $size[0];
		}
		$this->wPt = $this->w*$this->k;
		$this->hPt = $this->h*$this->k;
		$this->PageBreakTrigger = $this->h-$this->bMargin;
		$this->CurOrientation = $orientation;
		$this->CurPageSize = $size;
	}
	if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
		$this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
	if($rotation!=0)
	{
		if($rotation%90!=0)
			$this->Error('Incorrect rotation value: '.$rotation);
		$this->CurRotation = $rotation;
		$this->PageInfo[$this->page]['rotation'] = $rotation;
	}
}

protected function _endpage()
{
	$this->state = 1;
}

/*protected function _loadfont($font)
{
	// Load a font definition file from the font directory
	if(strpos($font,'/')!==false || strpos($font,"\\")!==false)
		$this->Error('Incorrect font definition file name: '.$font);
	error_log($this->fontpath.$font);
	include($this->fontpath.$font);
	if(!isset($name))
		$this->Error('Could not include font definition file');
	if(isset($enc))
		$enc = strtolower($enc);
	if(!isset($subsetted))
		$subsetted = false;
	return get_defined_vars();
}*/
protected function _loadfont($font)
{
	if ($font == 'courier.php') {
		$type = 'Core';
		$name = 'Courier';
		$up = -100;
		$ut = 50;
		for($i=0;$i<=255;$i++)
			$cw[chr($i)] = 600;
		$enc = 'cp1252';
		$uv = array(0=>array(0,128),128=>8364,130=>8218,131=>402,132=>8222,133=>8230,134=>array(8224,2),136=>710,137=>8240,138=>352,139=>8249,140=>338,142=>381,145=>array(8216,2),147=>array(8220,2),149=>8226,150=>array(8211,2),152=>732,153=>8482,154=>353,155=>8250,156=>339,158=>382,159=>376,160=>array(160,96));
	} else if ($font == 'courierb.php') {
		$type = 'Core';
		$name = 'Courier-Bold';
		$up = -100;
		$ut = 50;
		for($i=0;$i<=255;$i++)
			$cw[chr($i)] = 600;
		$enc = 'cp1252';
		$uv = array(0=>array(0,128),128=>8364,130=>8218,131=>402,132=>8222,133=>8230,134=>array(8224,2),136=>710,137=>8240,138=>352,139=>8249,140=>338,142=>381,145=>array(8216,2),147=>array(8220,2),149=>8226,150=>array(8211,2),152=>732,153=>8482,154=>353,155=>8250,156=>339,158=>382,159=>376,160=>array(160,96));
	}

	return get_defined_vars();
}

protected function _isascii($s)
{
	// Test if string is ASCII
	$nb = strlen($s);
	for($i=0;$i<$nb;$i++)
	{
		if(ord($s[$i])>127)
			return false;
	}
	return true;
}

protected function _httpencode($param, $value, $isUTF8)
{
	// Encode HTTP header field parameter
	if($this->_isascii($value))
		return $param.'="'.$value.'"';
	if(!$isUTF8)
		$value = utf8_encode($value);
	if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false)
		return $param.'="'.rawurlencode($value).'"';
	else
		return $param."*=UTF-8''".rawurlencode($value);
}

protected function _UTF8toUTF16($s)
{
	// Convert UTF-8 to UTF-16BE with BOM
	$res = "\xFE\xFF";
	$nb = strlen($s);
	$i = 0;
	while($i<$nb)
	{
		$c1 = ord($s[$i++]);
		if($c1>=224)
		{
			// 3-byte character
			$c2 = ord($s[$i++]);
			$c3 = ord($s[$i++]);
			$res .= chr((($c1 & 0x0F)<<4) + (($c2 & 0x3C)>>2));
			$res .= chr((($c2 & 0x03)<<6) + ($c3 & 0x3F));
		}
		elseif($c1>=192)
		{
			// 2-byte character
			$c2 = ord($s[$i++]);
			$res .= chr(($c1 & 0x1C)>>2);
			$res .= chr((($c1 & 0x03)<<6) + ($c2 & 0x3F));
		}
		else
		{
			// Single-byte character
			$res .= "\0".chr($c1);
		}
	}
	return $res;
}

protected function _escape($s)
{
	// Escape special characters
	if(strpos($s,'(')!==false || strpos($s,')')!==false || strpos($s,'\\')!==false || strpos($s,"\r")!==false)
		return str_replace(array('\\','(',')',"\r"), array('\\\\','\\(','\\)','\\r'), $s);
	else
		return $s;
}

protected function _textstring($s)
{
	// Format a text string
	if(!$this->_isascii($s))
		$s = $this->_UTF8toUTF16($s);
	return '('.$this->_escape($s).')';
}

protected function _dounderline($x, $y, $txt)
{
	// Underline text
	$up = $this->CurrentFont['up'];
	$ut = $this->CurrentFont['ut'];
	$w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
	return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
}

protected function _parsejpg($file)
{
	// Extract info from a JPEG file
	$a = getimagesize($file);
	if(!$a)
		$this->Error('Missing or incorrect image file: '.$file);
	if($a[2]!=2)
		$this->Error('Not a JPEG file: '.$file);
	if(!isset($a['channels']) || $a['channels']==3)
		$colspace = 'DeviceRGB';
	elseif($a['channels']==4)
		$colspace = 'DeviceCMYK';
	else
		$colspace = 'DeviceGray';
	$bpc = isset($a['bits']) ? $a['bits'] : 8;
	$data = file_get_contents($file);
	return array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data);
}

protected function _parsepng($file)
{
	// Extract info from a PNG file
	$f = fopen($file,'rb');
	if(!$f)
		$this->Error('Can\'t open image file: '.$file);
	$info = $this->_parsepngstream($f,$file);
	fclose($f);
	return $info;
}

protected function _parsepngstream($f, $file)
{
	// Check signature
	if($this->_readstream($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
		$this->Error('Not a PNG file: '.$file);

	// Read header chunk
	$this->_readstream($f,4);
	if($this->_readstream($f,4)!='IHDR')
		$this->Error('Incorrect PNG file: '.$file);
	$w = $this->_readint($f);
	$h = $this->_readint($f);
	$bpc = ord($this->_readstream($f,1));
	if($bpc>8)
		$this->Error('16-bit depth not supported: '.$file);
	$ct = ord($this->_readstream($f,1));
	if($ct==0 || $ct==4)
		$colspace = 'DeviceGray';
	elseif($ct==2 || $ct==6)
		$colspace = 'DeviceRGB';
	elseif($ct==3)
		$colspace = 'Indexed';
	else
		$this->Error('Unknown color type: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Unknown compression method: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Unknown filter method: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Interlacing not supported: '.$file);
	$this->_readstream($f,4);
	$dp = '/Predictor 15 /Colors '.($colspace=='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;

	// Scan chunks looking for palette, transparency and image data
	$pal = '';
	$trns = '';
	$data = '';
	do
	{
		$n = $this->_readint($f);
		$type = $this->_readstream($f,4);
		if($type=='PLTE')
		{
			// Read palette
			$pal = $this->_readstream($f,$n);
			$this->_readstream($f,4);
		}
		elseif($type=='tRNS')
		{
			// Read transparency info
			$t = $this->_readstream($f,$n);
			if($ct==0)
				$trns = array(ord(substr($t,1,1)));
			elseif($ct==2)
				$trns = array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
			else
			{
				$pos = strpos($t,chr(0));
				if($pos!==false)
					$trns = array($pos);
			}
			$this->_readstream($f,4);
		}
		elseif($type=='IDAT')
		{
			// Read image data block
			$data .= $this->_readstream($f,$n);
			$this->_readstream($f,4);
		}
		elseif($type=='IEND')
			break;
		else
			$this->_readstream($f,$n+4);
	}
	while($n);

	if($colspace=='Indexed' && empty($pal))
		$this->Error('Missing palette in '.$file);
	$info = array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'dp'=>$dp, 'pal'=>$pal, 'trns'=>$trns);
	if($ct>=4)
	{
		// Extract alpha channel
		if(!function_exists('gzuncompress'))
			$this->Error('Zlib not available, can\'t handle alpha channel: '.$file);
		$data = gzuncompress($data);
		$color = '';
		$alpha = '';
		if($ct==4)
		{
			// Gray image
			$len = 2*$w;
			for($i=0;$i<$h;$i++)
			{
				$pos = (1+$len)*$i;
				$color .= $data[$pos];
				$alpha .= $data[$pos];
				$line = substr($data,$pos+1,$len);
				$color .= preg_replace('/(.)./s','$1',$line);
				$alpha .= preg_replace('/.(.)/s','$1',$line);
			}
		}
		else
		{
			// RGB image
			$len = 4*$w;
			for($i=0;$i<$h;$i++)
			{
				$pos = (1+$len)*$i;
				$color .= $data[$pos];
				$alpha .= $data[$pos];
				$line = substr($data,$pos+1,$len);
				$color .= preg_replace('/(.{3})./s','$1',$line);
				$alpha .= preg_replace('/.{3}(.)/s','$1',$line);
			}
		}
		unset($data);
		$data = gzcompress($color);
		$info['smask'] = gzcompress($alpha);
		$this->WithAlpha = true;
		if($this->PDFVersion<'1.4')
			$this->PDFVersion = '1.4';
	}
	$info['data'] = $data;
	return $info;
}

protected function _readstream($f, $n)
{
	// Read n bytes from stream
	$res = '';
	while($n>0 && !feof($f))
	{
		$s = fread($f,$n);
		if($s===false)
			$this->Error('Error while reading stream');
		$n -= strlen($s);
		$res .= $s;
	}
	if($n>0)
		$this->Error('Unexpected end of stream');
	return $res;
}

protected function _readint($f)
{
	// Read a 4-byte integer from stream
	$a = unpack('Ni',$this->_readstream($f,4));
	return $a['i'];
}

protected function _parsegif($file)
{
	// Extract info from a GIF file (via PNG conversion)
	if(!function_exists('imagepng'))
		$this->Error('GD extension is required for GIF support');
	if(!function_exists('imagecreatefromgif'))
		$this->Error('GD has no GIF read support');
	$im = imagecreatefromgif($file);
	if(!$im)
		$this->Error('Missing or incorrect image file: '.$file);
	imageinterlace($im,0);
	ob_start();
	imagepng($im);
	$data = ob_get_clean();
	imagedestroy($im);
	$f = fopen('php://temp','rb+');
	if(!$f)
		$this->Error('Unable to create memory stream');
	fwrite($f,$data);
	rewind($f);
	$info = $this->_parsepngstream($f,$file);
	fclose($f);
	return $info;
}

protected function _out($s)
{
	// Add a line to the document
	if($this->state==2)
		$this->pages[$this->page] .= $s."\n";
	elseif($this->state==1)
		$this->_put($s);
	elseif($this->state==0)
		$this->Error('No page has been added yet');
	elseif($this->state==3)
		$this->Error('The document is closed');
}

protected function _put($s)
{
	$this->buffer .= $s."\n";
}

protected function _getoffset()
{
	return strlen($this->buffer);
}

protected function _newobj($n=null)
{
	// Begin a new object
	if($n===null)
		$n = ++$this->n;
	$this->offsets[$n] = $this->_getoffset();
	$this->_put($n.' 0 obj');
}

protected function _putstream($data)
{
	$this->_put('stream');
	$this->_put($data);
	$this->_put('endstream');
}

protected function _putstreamobject($data)
{
	if($this->compress)
	{
		$entries = '/Filter /FlateDecode ';
		$data = gzcompress($data);
	}
	else
		$entries = '';
	$entries .= '/Length '.strlen($data);
	$this->_newobj();
	$this->_put('<<'.$entries.'>>');
	$this->_putstream($data);
	$this->_put('endobj');
}

protected function _putpage($n)
{
	$this->_newobj();
	$this->_put('<</Type /Page');
	$this->_put('/Parent 1 0 R');
	if(isset($this->PageInfo[$n]['size']))
		$this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageInfo[$n]['size'][0],$this->PageInfo[$n]['size'][1]));
	if(isset($this->PageInfo[$n]['rotation']))
		$this->_put('/Rotate '.$this->PageInfo[$n]['rotation']);
	$this->_put('/Resources 2 0 R');
	if(isset($this->PageLinks[$n]))
	{
		// Links
		$annots = '/Annots [';
		foreach($this->PageLinks[$n] as $pl)
		{
			$rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
			$annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
			if(is_string($pl[4]))
				$annots .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
			else
			{
				$l = $this->links[$pl[4]];
				if(isset($this->PageInfo[$l[0]]['size']))
					$h = $this->PageInfo[$l[0]]['size'][1];
				else
					$h = ($this->DefOrientation=='P') ? $this->DefPageSize[1]*$this->k : $this->DefPageSize[0]*$this->k;
				$annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',$this->PageInfo[$l[0]]['n'],$h-$l[1]*$this->k);
			}
		}
		$this->_put($annots.']');
	}
	if($this->WithAlpha)
		$this->_put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
	$this->_put('/Contents '.($this->n+1).' 0 R>>');
	$this->_put('endobj');
	// Page content
	if(!empty($this->AliasNbPages))
		$this->pages[$n] = str_replace($this->AliasNbPages,$this->page,$this->pages[$n]);
	$this->_putstreamobject($this->pages[$n]);
}

protected function _putpages()
{
	$nb = $this->page;
	for($n=1;$n<=$nb;$n++)
		$this->PageInfo[$n]['n'] = $this->n+1+2*($n-1);
	for($n=1;$n<=$nb;$n++)
		$this->_putpage($n);
	// Pages root
	$this->_newobj(1);
	$this->_put('<</Type /Pages');
	$kids = '/Kids [';
	for($n=1;$n<=$nb;$n++)
		$kids .= $this->PageInfo[$n]['n'].' 0 R ';
	$this->_put($kids.']');
	$this->_put('/Count '.$nb);
	if($this->DefOrientation=='P')
	{
		$w = $this->DefPageSize[0];
		$h = $this->DefPageSize[1];
	}
	else
	{
		$w = $this->DefPageSize[1];
		$h = $this->DefPageSize[0];
	}
	$this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$w*$this->k,$h*$this->k));
	$this->_put('>>');
	$this->_put('endobj');
}

protected function _putfonts()
{
	foreach($this->FontFiles as $file=>$info)
	{
		// Font file embedding
		$this->_newobj();
		$this->FontFiles[$file]['n'] = $this->n;
		$font = file_get_contents($this->fontpath.$file,true);
		if(!$font)
			$this->Error('Font file not found: '.$file);
		$compressed = (substr($file,-2)=='.z');
		if(!$compressed && isset($info['length2']))
			$font = substr($font,6,$info['length1']).substr($font,6+$info['length1']+6,$info['length2']);
		$this->_put('<</Length '.strlen($font));
		if($compressed)
			$this->_put('/Filter /FlateDecode');
		$this->_put('/Length1 '.$info['length1']);
		if(isset($info['length2']))
			$this->_put('/Length2 '.$info['length2'].' /Length3 0');
		$this->_put('>>');
		$this->_putstream($font);
		$this->_put('endobj');
	}
	foreach($this->fonts as $k=>$font)
	{
		// Encoding
		if(isset($font['diff']))
		{
			if(!isset($this->encodings[$font['enc']]))
			{
				$this->_newobj();
				$this->_put('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$font['diff'].']>>');
				$this->_put('endobj');
				$this->encodings[$font['enc']] = $this->n;
			}
		}
		// ToUnicode CMap
		if(isset($font['uv']))
		{
			if(isset($font['enc']))
				$cmapkey = $font['enc'];
			else
				$cmapkey = $font['name'];
			if(!isset($this->cmaps[$cmapkey]))
			{
				$cmap = $this->_tounicodecmap($font['uv']);
				$this->_putstreamobject($cmap);
				$this->cmaps[$cmapkey] = $this->n;
			}
		}
		// Font object
		$this->fonts[$k]['n'] = $this->n+1;
		$type = $font['type'];
		$name = $font['name'];
		if($font['subsetted'])
			$name = 'AAAAAA+'.$name;
		if($type=='Core')
		{
			// Core font
			$this->_newobj();
			$this->_put('<</Type /Font');
			$this->_put('/BaseFont /'.$name);
			$this->_put('/Subtype /Type1');
			if($name!='Symbol' && $name!='ZapfDingbats')
				$this->_put('/Encoding /WinAnsiEncoding');
			if(isset($font['uv']))
				$this->_put('/ToUnicode '.$this->cmaps[$cmapkey].' 0 R');
			$this->_put('>>');
			$this->_put('endobj');
		}
		elseif($type=='Type1' || $type=='TrueType')
		{
			// Additional Type1 or TrueType/OpenType font
			$this->_newobj();
			$this->_put('<</Type /Font');
			$this->_put('/BaseFont /'.$name);
			$this->_put('/Subtype /'.$type);
			$this->_put('/FirstChar 32 /LastChar 255');
			$this->_put('/Widths '.($this->n+1).' 0 R');
			$this->_put('/FontDescriptor '.($this->n+2).' 0 R');
			if(isset($font['diff']))
				$this->_put('/Encoding '.$this->encodings[$font['enc']].' 0 R');
			else
				$this->_put('/Encoding /WinAnsiEncoding');
			if(isset($font['uv']))
				$this->_put('/ToUnicode '.$this->cmaps[$cmapkey].' 0 R');
			$this->_put('>>');
			$this->_put('endobj');
			// Widths
			$this->_newobj();
			$cw = &$font['cw'];
			$s = '[';
			for($i=32;$i<=255;$i++)
				$s .= $cw[chr($i)].' ';
			$this->_put($s.']');
			$this->_put('endobj');
			// Descriptor
			$this->_newobj();
			$s = '<</Type /FontDescriptor /FontName /'.$name;
			foreach($font['desc'] as $k=>$v)
				$s .= ' /'.$k.' '.$v;
			if(!empty($font['file']))
				$s .= ' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$font['file']]['n'].' 0 R';
			$this->_put($s.'>>');
			$this->_put('endobj');
		}
		else
		{
			// Allow for additional types
			$mtd = '_put'.strtolower($type);
			if(!method_exists($this,$mtd))
				$this->Error('Unsupported font type: '.$type);
			$this->$mtd($font);
		}
	}
}

protected function _tounicodecmap($uv)
{
	$ranges = '';
	$nbr = 0;
	$chars = '';
	$nbc = 0;
	foreach($uv as $c=>$v)
	{
		if(is_array($v))
		{
			$ranges .= sprintf("<%02X> <%02X> <%04X>\n",$c,$c+$v[1]-1,$v[0]);
			$nbr++;
		}
		else
		{
			$chars .= sprintf("<%02X> <%04X>\n",$c,$v);
			$nbc++;
		}
	}
	$s = "/CIDInit /ProcSet findresource begin\n";
	$s .= "12 dict begin\n";
	$s .= "begincmap\n";
	$s .= "/CIDSystemInfo\n";
	$s .= "<</Registry (Adobe)\n";
	$s .= "/Ordering (UCS)\n";
	$s .= "/Supplement 0\n";
	$s .= ">> def\n";
	$s .= "/CMapName /Adobe-Identity-UCS def\n";
	$s .= "/CMapType 2 def\n";
	$s .= "1 begincodespacerange\n";
	$s .= "<00> <FF>\n";
	$s .= "endcodespacerange\n";
	if($nbr>0)
	{
		$s .= "$nbr beginbfrange\n";
		$s .= $ranges;
		$s .= "endbfrange\n";
	}
	if($nbc>0)
	{
		$s .= "$nbc beginbfchar\n";
		$s .= $chars;
		$s .= "endbfchar\n";
	}
	$s .= "endcmap\n";
	$s .= "CMapName currentdict /CMap defineresource pop\n";
	$s .= "end\n";
	$s .= "end";
	return $s;
}

protected function _putimages()
{
	foreach(array_keys($this->images) as $file)
	{
		$this->_putimage($this->images[$file]);
		unset($this->images[$file]['data']);
		unset($this->images[$file]['smask']);
	}
}

protected function _putimage(&$info)
{
	$this->_newobj();
	$info['n'] = $this->n;
	$this->_put('<</Type /XObject');
	$this->_put('/Subtype /Image');
	$this->_put('/Width '.$info['w']);
	$this->_put('/Height '.$info['h']);
	if($info['cs']=='Indexed')
		$this->_put('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
	else
	{
		$this->_put('/ColorSpace /'.$info['cs']);
		if($info['cs']=='DeviceCMYK')
			$this->_put('/Decode [1 0 1 0 1 0 1 0]');
	}
	$this->_put('/BitsPerComponent '.$info['bpc']);
	if(isset($info['f']))
		$this->_put('/Filter /'.$info['f']);
	if(isset($info['dp']))
		$this->_put('/DecodeParms <<'.$info['dp'].'>>');
	if(isset($info['trns']) && is_array($info['trns']))
	{
		$trns = '';
		for($i=0;$i<count($info['trns']);$i++)
			$trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
		$this->_put('/Mask ['.$trns.']');
	}
	if(isset($info['smask']))
		$this->_put('/SMask '.($this->n+1).' 0 R');
	$this->_put('/Length '.strlen($info['data']).'>>');
	$this->_putstream($info['data']);
	$this->_put('endobj');
	// Soft mask
	if(isset($info['smask']))
	{
		$dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns '.$info['w'];
		$smask = array('w'=>$info['w'], 'h'=>$info['h'], 'cs'=>'DeviceGray', 'bpc'=>8, 'f'=>$info['f'], 'dp'=>$dp, 'data'=>$info['smask']);
		$this->_putimage($smask);
	}
	// Palette
	if($info['cs']=='Indexed')
		$this->_putstreamobject($info['pal']);
}

protected function _putxobjectdict()
{
	foreach($this->images as $image)
		$this->_put('/I'.$image['i'].' '.$image['n'].' 0 R');
}

protected function _putresourcedict()
{
	$this->_put('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->_put('/Font <<');
	foreach($this->fonts as $font)
		$this->_put('/F'.$font['i'].' '.$font['n'].' 0 R');
	$this->_put('>>');
	$this->_put('/XObject <<');
	$this->_putxobjectdict();
	$this->_put('>>');
}

protected function _putresources()
{
	$this->_putfonts();
	$this->_putimages();
	// Resource dictionary
	$this->_newobj(2);
	$this->_put('<<');
	$this->_putresourcedict();
	$this->_put('>>');
	$this->_put('endobj');
}

protected function _putinfo()
{
	$this->metadata['Producer'] = 'FPDF '.FPDF_VERSION;
	$this->metadata['CreationDate'] = 'D:'.@date('YmdHis');
	foreach($this->metadata as $key=>$value)
		$this->_put('/'.$key.' '.$this->_textstring($value));
}

protected function _putcatalog()
{
	$n = $this->PageInfo[1]['n'];
	$this->_put('/Type /Catalog');
	$this->_put('/Pages 1 0 R');
	if($this->ZoomMode=='fullpage')
		$this->_put('/OpenAction ['.$n.' 0 R /Fit]');
	elseif($this->ZoomMode=='fullwidth')
		$this->_put('/OpenAction ['.$n.' 0 R /FitH null]');
	elseif($this->ZoomMode=='real')
		$this->_put('/OpenAction ['.$n.' 0 R /XYZ null null 1]');
	elseif(!is_string($this->ZoomMode))
		$this->_put('/OpenAction ['.$n.' 0 R /XYZ null null '.sprintf('%.2F',$this->ZoomMode/100).']');
	if($this->LayoutMode=='single')
		$this->_put('/PageLayout /SinglePage');
	elseif($this->LayoutMode=='continuous')
		$this->_put('/PageLayout /OneColumn');
	elseif($this->LayoutMode=='two')
		$this->_put('/PageLayout /TwoColumnLeft');
}

protected function _putheader()
{
	$this->_put('%PDF-'.$this->PDFVersion);
}

protected function _puttrailer()
{
	$this->_put('/Size '.($this->n+1));
	$this->_put('/Root '.$this->n.' 0 R');
	$this->_put('/Info '.($this->n-1).' 0 R');
}

protected function _enddoc()
{
	$this->_putheader();
	$this->_putpages();
	$this->_putresources();
	// Info
	$this->_newobj();
	$this->_put('<<');
	$this->_putinfo();
	$this->_put('>>');
	$this->_put('endobj');
	// Catalog
	$this->_newobj();
	$this->_put('<<');
	$this->_putcatalog();
	$this->_put('>>');
	$this->_put('endobj');
	// Cross-ref
	$offset = $this->_getoffset();
	$this->_put('xref');
	$this->_put('0 '.($this->n+1));
	$this->_put('0000000000 65535 f ');
	for($i=1;$i<=$this->n;$i++)
		$this->_put(sprintf('%010d 00000 n ',$this->offsets[$i]));
	// Trailer
	$this->_put('trailer');
	$this->_put('<<');
	$this->_puttrailer();
	$this->_put('>>');
	$this->_put('startxref');
	$this->_put($offset);
	$this->_put('%%EOF');
	$this->state = 3;
}
}

class reporte_lv extends FPDF {

	protected $font         = 'Courier';
	protected $B            = 0;
	protected $I            = 0;
	protected $U            = 0;
	protected $HREF         = '';

	protected $valorBruto   = 0;
	protected $impuesto     = 0;
	protected $valorTotal   = 0;

	protected $moneda       = '';

	protected $dir          = array();
	protected $ebiauth      = '';
	protected $ebiURL       = '';


	//implementacion para tabla con multilineas

	var $widths;
	var $aligns;

	function SetWidths($w)
	{
		//Set the array of column widths
		$this->widths=$w;
	}

	function SetAligns($a)
	{
		//Set the array of column alignments
		$this->aligns=$a;
	}


	function Row($conf, $data){
		//Calculate the height of the row
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]['text']));
		$h=5*$nb;
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
				//$this->SetFillColor(255, 555, 255);
				$this->SetDrawColor(0, 0, 0);
			} else {
				$this->SetFillColor(255, 255, 255);
				$this->SetDrawColor(255, 255, 255);
			}

	        //Draw the border
			$this->Rect($x,$y,$w,$h,'D');
			//Print the text
			$this->MultiCell($w,5,$data[$i]['text'],0,$a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}

	function CheckPageBreak($h)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}

	function NbLines($w,$txt) {
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
		while($i<$nb) {
			$c=$s[$i];
			if($c=="\n") {
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
			if($l>$wmax) {
				if($sep==-1) {
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



	function _setFont($font) {
		$this->font = 'Courier';
	}

	function printText($text) {
		return utf8_decode($text);
	}

	function cabecera($data) {
		$result = $this->getDataCabecera($data);
		$result[1] = TRIM($result[1]);
		$liquidacion = $this->obtenerLiquidacion(array('serie' => $data['serie'], 'documento' => $data['documento'], 'tipoDocumento' => $data['tipoDocumento'], 'codCliente' => $result[1]));
		$placa = $this->getPlaca(array('ch_liquidacion' => $liquidacion, 'ch_almacen' => $result[13]));
		$result_ = $this->getDataEmpresa($result[13]);

		$warehouse = $this->getDateWarehouse($result[13]);

		$this->SetFont($this->font, 'B', 10);
		$this->Multicell(0,4,$this->printText(wordwrap($result_[1][1], 80, "\n")));

		$this->Cell(10,10,'RUC: '.$result_[1][0],0,0,'L');
		$this->Cell(180,10,'DOCUMENTO DE CONTROL INTERNO',0,0,'R');

		$this->getDireccionSucursal($result_[1][2],$result_[0][2]);
		$this->Ln(5);
		/*$this->Cell(10,10,$this->printText($this->dir[1]).' '.$this->printText($this->dir[2]),0,0,'L');
		$this->Cell(180,10,$result[12].' ELECTRONICA',0,0,'R');*/

		$this->Ln(2);
		$w = array(125,67);//total 192
		$this->SetWidths($w);
		$this->Row(
			array('border' => 0),
			array(
				array('text' => $this->printText($this->dir[1]).' '.$this->printText($this->dir[2]). "\n" .$this->printText($this->dir[3].' - '.$this->dir[4].' - '.$this->dir[5]), 'align' => 'L'),
				array('text' => '  '.$result[12].' ELECTRONICA'."\n  ".$data['serie'].' - '.$data['documento'], 'align' => 'L'),
			)
		);

		$this->SetFont($this->font, '', 10);
		//$this->Ln(2);

		if (isset($warehouse['warehouse_name']) && $warehouse['warehouse_name'] != NULL && $warehouse['warehouse_name'] != '') {
			if (isset($warehouse['warehouse_addr']) && $warehouse['warehouse_addr'] != NULL && $warehouse['warehouse_addr'] != '') {
				$del = '|';
				$addr = explode($del, $warehouse['warehouse_addr']);
				if (isset($addr[1]) && isset($addr[2]) && isset($addr[3]) &&  isset($addr[4]) && isset($addr[5])) {
					if ($addr[1] != '' && $addr[2] != '' && $addr[3] != '' &&  $addr[4] != '' && $addr[5] != '') {
						$this->Cell(10, 10, $result[13].' - '.$warehouse['warehouse_name']."\n", 0, 0, 'L');
						$this->Ln(4);
						$this->Cell(10, 10, $addr[1].' '.$addr[2]."\n", 0, 0, 'L');
						$this->Ln(4);
						$this->Cell(10, 10, $addr[3].' - '.$addr[4].' - '.$addr[5], 0, 0, 'L');
						
						$this->Ln(5);
					}
				}
			}
		}
		$this->Ln(2);

		$this->ebiauth  = $result_[1][3];
		$this->ebiURL   = $result_[1][4];

		$date = date_create($result[2]);

		//$this->Cell(10,10,'FECHA: '.date_format($date, 'd/m/Y'),0,0,'L');
		$this->Cell(10,10,'FECHA: '.date_format($date, 'Y-m-d'),0,0,'L');
		$this->Ln(5);
		$this->Cell(10,10,'MONEDA: '.$result[4],0,0,'L');
		$this->moneda = $result[4];
		$this->Ln(5);
		$this->Cell(10,10,$this->printText('RUC CLIENTE: '.$result['ruc']),0,0,'L');
		$this->Ln(8);
		//$this->Cell(10,10,'RAZON SOCIAL: '.$this->printText($result[5]),0,0,'L');
		$this->Multicell(0,4,'RAZON SOCIAL: '.$this->printText($result[5]));
		$this->Ln(2);
		//$this->Multicell(0,4,$this->printText('DIRECCION: '.wordwrap($result[6], 80, "\n")));

		if ($result['es_factura'] == '1') {//SOLO SE INCLUYE LA PLACA CUANDO ES FACTURA
			if ($placa != '') {
				$this->Ln(1);
				//$this->Cell(10,10,'PLACA: '.$this->printText($placa),0,0,'L');
				$this->Multicell(0,4,'PLACA: '.$this->printText($placa));
				//$this->Ln(1);
			}
		}

		//$this->Ln(1);
		$doc = '';

		if ($result[10] != '') {
			$dataDoc    = explode("*", $result[10]);
			$serieDoc   = $dataDoc[1];
			$numDoc     = $dataDoc[0];
			$typeDoc    = $dataDoc[2];
			$doc        = $result[10] == '' ? '' : $serieDoc.' - '.$numDoc;
		}

		//11 nota de credito, 20 nota de debito

		error_log('ch_fac_tipodocumento: '.trim($result['ch_fac_tipodocumento']));
		error_log('nu_tipo_pago: '.trim($result['nu_tipo_pago']));
		error_log('no_tipo_pago: '.trim($result['no_tipo_pago']));
		if (trim($result['ch_fac_tipodocumento']) != '11' && trim($result['ch_fac_tipodocumento']) != '20') {
			if (trim($result['nu_tipo_pago']) != '05' && trim($result['nu_tipo_pago']) != '') {
				$this->Ln(3);
				$this->Cell(10,10,'TIPO DE PAGO: '.$result['no_tipo_pago'],0,0,'L');
				$this->Ln(4);
				if (trim($result['nu_tipo_pago']) == '06') {
					$this->Cell(10,10,'FECHA VENCIMIENTO: '.$result['fe_vencimiento'],0,0,'L');
				}
				//$this->Multicell(0,2,$this->printText('Tipo pago('.trim($result['nu_tipo_pago']).'): '.wordwrap($result['no_tipo_pago'], 80, "\n")));
				//$this->Multicell(0,6,$this->printText('Fecha Vencimiento('.trim($result['ch_fac_tipodocumento']).'): '.wordwrap($result['fe_vencimiento'], 50, "\n")));
			}
		}

		$_ref = $this->getOriginDocument($data['tipoDocumento'].''.$data['serie'].''.$data['documento'].''.$result[1]);

		$docRef = '';
		$dateRef = '';
		if (!$_ref['error']) {
			$docRef = $_ref['serie'].' - '.$_ref['number'];
			$dateRef = $_ref['date'];
		}
		if (trim($result['ch_fac_tipodocumento']) == '11' || trim($result['ch_fac_tipodocumento']) == '20') {
			$this->Ln(5);
			$this->Cell(10,10,'REFERENCIA: '.$docRef,0,0,'L');
			$this->Ln(5);
			$this->Cell(10,10,'FECHA REFERENCIA: '.$dateRef,0,0,'L');
			$this->Ln(9);
			$this->Multicell(0,4,$this->printText('OBS: '.wordwrap($result[11], 88, "\n\n\n\n")));
		} else {
			if (trim($result[11]) != '') {
				$this->Ln(9);
				$this->Multicell(0,4,$this->printText('OBS: '.wordwrap($result[11], 88, "\n\n\n\n")));
			}
		}

		if(trim($result["no_detraccion_cuenta"]) != '' && $result["nu_detraccion_importe"] > 0 && trim($result["nu_detraccion_porcentaje"]) != '' && trim($result["nu_detraccion_codigo"]) != '') {
			$this->Ln(7);
			$leyenda_detraccion = "Operacion sujeta a detraccion";
			$this->Multicell(0,4,$this->printText('LEYENDA: '.wordwrap($leyenda_detraccion, 80, "\n")));
			$this->Multicell(0,4,$this->printText('NRO. CUENTA DETRACCION: '.wordwrap($result["no_detraccion_cuenta"], 30, "\n")));
			$this->Multicell(0,4,$this->printText('CODIGO DE BIENES Y SERVICIOS: '.wordwrap($result["nu_detraccion_codigo"], 5, "\n")));
		}

		$this->valorBruto   = $result[7];
		$this->impuesto     = $result[8];
		$this->valorTotal   = $result[9];
	}

	function detalle($data) {
		$w = array(30,62,18,20,33,30);
		$this->SetWidths($w);
		$this->SetFont($this->font, 'B', 10);
		$this->Row(array('border' => 1), $data[0]);

		$this->SetFont($this->font, '', 10);
		//$this->Ln();

		$results    = $this->getDetalleFactura($data[1]);
		$first      = 0;
		$last       = count($results) -1;

		$start_x    = $this->GetX(); //initial x (start of column position)
		$current_y  = $this->GetY();
		$current_x  = $this->GetX();

		$cell_height = 10;    //define cell height

		foreach ($results as $key => $result) {
			$str = $this->printText($result[1]);

			$h  = 0;
			$vu = 0;

			if ($result[9] == '0') {
				$vu = $result[10];
			} else if ($result[9] == '1') {
				$vu = $result[10];
			} else {
				$vu = $result[10];
			}

			$result[6] = $result[11];

			$this->Row(
				array('border' => 1),
				array(
					array('text' => $this->printText($result[0]), 'align' => 'L'),
					array('text' => $str, 'align' => 'L'),
					array('text' => $this->printText($result[2]), 'align' => 'L'),
					array('text' => $this->getFormatNumber(array('number' => $result[3], 'decimal' => 3)), 'align' => 'R'),
					array('text' => $this->getFormatNumber(array('number' => $vu, 'decimal' => 3)), 'align' => 'R'),
					array('text' => $this->getFormatNumber(array('number' => $result[6], 'decimal' => 2)), 'align' => 'R'),
				)
			);
		}
		$this->Ln();
	}

	function pie($data) {
		$result = $this->getDataPie($data);

		$oldY = $this->getY();
		$this->Line(10,$oldY-1,202,$oldY-1);
		$total = 0.0;
		$_igv = $this->getIGVActual();

		$op = 'OPERACIONES GRAVADAS:';
		if ($result['typetax'] == '1') {
			$op = 'OPERACIONES EXONERADAS:';
			$_igv[0] = 0;
		}

		if ($result['nu_fac_impuesto1'] > 0 && $result['nu_fac_descuento1'] > 0) {
			$header             = array('1','2','3', '4');
			$text               = array($op,'TOTAL DESCUENTOS:','I.G.V. ('.$_igv[0].'%)','IMPORTE TOTAL:');
			$v_igv = ($result['nu_fac_valorbruto'] - $result['nu_fac_descuento1']) * ($_igv[0]/100);
			$v_gt = 0.0;
			if ($result['typetax'] != '2') {
				$v_gt = $v_igv + ($result['nu_fac_valorbruto'] - $result['nu_fac_descuento1']);
			}

			$value = array(
				(string)$this->getFormatNumber(array('number' => ($result['nu_fac_valorbruto'] - $result['nu_fac_descuento1']), 'decimal' => 2)),//(19/05/18)
				//(string)$this->getFormatNumber(array('number' => ($result['nu_fac_valorbruto']), 'decimal' => 2)),
				(string)$this->getFormatNumber(array('number' => $result['nu_fac_descuento1'], 'decimal' => 2)),
				(string)$this->getFormatNumber(array('number' => $v_igv, 'decimal' => 2)),
				(string)$this->getFormatNumber(array('number' => $v_gt, 'decimal' => 2))
			);
			error_log($result['nu_fac_valortotal'].' - (('.$result['nu_fac_descuento1'].' * (1 + ('.$igv.' / 100)) ) - '.$result['nu_fac_descuento1'].'))');
			error_log($value[3]);
		} else if ($result['nu_fac_impuesto1'] == 0 && $result['nu_fac_descuento1'] > 0) {
			$header             = array('1','2','3', '4');
			$text               = array($op,'TOTAL DESCUENTOS:','I.G.V. ('.$_igv[0].'%)','IMPORTE TOTAL:');

			$value = array(
				(string)$this->getFormatNumber(array('number' => ($result['nu_fac_valorbruto'] - $result['nu_fac_descuento1']), 'decimal' => 2)),
				(string)$this->getFormatNumber(array('number' => $result['nu_fac_descuento1'], 'decimal' => 2)),
				(string)$this->getFormatNumber(array('number' => $result['nu_fac_impuesto1'] - (($result['nu_fac_descuento1'] ) - $result['nu_fac_descuento1']), 'decimal' => 2)),
				(string)$this->getFormatNumber(array('number' => $result['nu_fac_valortotal'] - (($result['nu_fac_descuento1'] ) - $result['nu_fac_descuento1']), 'decimal' => 2))
			);
		} else {
			$header = array('1','2','3');
			if ($result['typetax'] == '2') {
				$result['nu_fac_valortotal'] = 0;
				$result['nu_fac_impuesto1'] = 0;
				$result['nu_fac_valorbruto'] = 0;
			}
			$igv;
			$text = array($op,'I.G.V. ('.$_igv[0].'%)','IMPORTE TOTAL:');
			$value = array(
				(string)$this->getFormatNumber(array('number' => $result['nu_fac_valorbruto'], 'decimal' => 2)),
				(string)$this->getFormatNumber(array('number' => $result['nu_fac_impuesto1'], 'decimal' => 2)),
				(string)$this->getFormatNumber(array('number' => $result['nu_fac_valortotal'], 'decimal' => 2))
			);
			$value[3] = $result['nu_fac_valortotal'];
		}

		if ($result['typetax'] == '2') {
			//Cero con cero décimos - CERO CON CERO - CERO SOLES
			$letras = 'SON: CERO Y 00/100 '.$this->moneda;
			$letras .= "\n".'TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE';
		} else if ($result['typetax'] == '1') {
			$letras = 'SON: '.$this->MontoMonetarioEnLetras($value[3],$this->moneda);
			$letras .= "\n".'BIENES TRANSFERIDOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA';
		} else {
			$letras = 'SON: '.$this->MontoMonetarioEnLetras($value[3],$this->moneda);
		}

		//if para saber si tienen el descuendo
		$this->MultiCell(150, 4, $this->printText($letras), 0, "L");
		$oldY = $this->getY();
		$this->Line(10,$oldY+1,202,$oldY+1);
		$this->Ln(4);

		$igv    = $this->getIGVActual();
		$igv    = $igv[0];

		$wf     = 10;
		$max    = $this->getMaxChar($value);
		$wc     = $this->getWC($value,$wf);

		foreach($header as $key => $col) {

			$this->Cell(110,7,'',0,0);
			$this->Cell(62,7,$text[$key],0,0,'L');

			if ($key == 2) {
				$oldY = $this->getY();
				$this->Line(118,$oldY,200,$oldY);
			}

			$num    = $value[$key];
			$w      = $wf + $wc[$key];

			$this->Cell($w,7,$num,0,0,'R');
			$this->Ln();

		}

		$this->Ln(4);
		$this->Cell(186,10,'COPIA PARA CONTROL ADMINISTRATIVO',0,0,'C');
		$this->Ln(5);
		$this->Cell(182,10,'CONSULTE LA REPRESENTACION IMPRESA EN '.$this->ebiURL,0,0,'C');
		$this->Ln(5);
		$this->Cell(188,10,'AUTORIZADO MEDIANTE R.I. NRO. '.$this->ebiauth,0,0,'C');

		$this->updateIGV($data);
	}

	function getDataEmpresa($almacen_id) {
		global $sqlca;
		$result = array();
		$sql = "
		SELECT
			TRIM (A .ch_nombre_almacen) AS _a,
			A .ch_almacen AS _b,
			A .ch_direccion_almacen AS _c,
			'' AS _d,
			'' AS _f
		FROM
			inv_ta_almacenes A
		WHERE
			A .ch_almacen = '$almacen_id'
		UNION ALL
		SELECT
			ruc as _a, razsocial as _b, ch_direccion as _c, ebiauth as _d, ebiurl as _f
		FROM
			int_ta_sucursales
		WHERE
			ebiauth != '' AND
			ruc = (
				SELECT DISTINCT
					SUCUR.ruc
				FROM
					inv_ta_almacenes ALMA
				JOIN int_ta_sucursales SUCUR ON (
					SUCUR.ch_sucursal = ALMA.ch_sucursal
				)
				WHERE
					ebiauth != '' AND ALMA.ch_almacen = '$almacen_id'
			);
		";
		if ($sqlca->query($sql) < 0) {
			return null;
		} else {
			while ($val = $sqlca->fetchRow()) {
				$result[] = array($val[0],$val[1],$val[2],$val[3],$val[4]);
			}
			return $result;
		}
	}

	function getDateWarehouse($warehouse_id) {
		global $sqlca;

		$sql = "SELECT
TRIM(ch_direccion_almacen) AS warehouse_addr,
TRIM(ch_nombre_almacen) AS warehouse_name
FROM inv_ta_almacenes WHERE ch_clase_almacen = '1' AND ch_almacen = '".$warehouse_id."';";
		if ($sqlca->query($sql) < 0) {
			return null;
		} else {
			return $sqlca->fetchRow();
		}
	}

	function getDataCabecera($data) {
		global $sqlca;

		$sql = "
SELECT
 complemento.ch_fac_tipodocumento,
 complemento.cli_codigo,
 complemento.dt_fac_fecha, --2
 cabecera.ch_fac_moneda,
 TMONE.tab_descripcion, --4
 clientes.cli_razsocial,
 clientes.cli_direccion, --6
 cabecera.nu_fac_valorbruto,
 cabecera.nu_fac_impuesto1, --8
 cabecera.nu_fac_valortotal,
 complemento.ch_fac_observacion2, --ref
 complemento.ch_fac_observacion1, --11: observacion
 TDOCU.tab_descripcion, --12
 cabecera.ch_almacen,
 (string_to_array(complemento.nu_fac_complemento_direccion, '*'))[1] AS no_detraccion_cuenta,
 (string_to_array(complemento.nu_fac_complemento_direccion, '*'))[2] AS nu_detraccion_importe,
 (string_to_array(complemento.nu_fac_complemento_direccion, '*'))[3] AS nu_detraccion_porcentaje,
 (string_to_array(complemento.nu_fac_complemento_direccion, '*'))[4] AS nu_detraccion_codigo,
 cabecera.nu_tipo_pago,
 TPAGO.tab_descripcion AS no_tipo_pago,
 cabecera.fe_vencimiento,
 RFC.fe_emision,
 CASE
  WHEN cabecera.ch_fac_tiporecargo2 IS NULL OR cabecera.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
  WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 = 0 THEN 1 -- EXO
  WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 > 0 THEN 2 -- TG
 END AS typetax,
 CASE WHEN cabecera.ch_fac_tipodocumento = '10' THEN 1 ELSE 0 END AS es_factura,
 clientes.cli_ruc AS ruc
FROM
 fac_ta_factura_cabecera cabecera
 LEFT JOIN fac_ta_factura_complemento complemento
  USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento)
 JOIN int_tabla_general AS TMONE
  ON (SUBSTRING(TMONE.tab_elemento, 5) = cabecera.ch_fac_moneda AND TMONE.tab_tabla ='04' AND TMONE.tab_elemento != '000000')
 JOIN int_clientes clientes
  ON (cabecera.cli_codigo = clientes.cli_codigo)
 JOIN int_tabla_general AS TDOCU
  ON (SUBSTRING(TDOCU.tab_elemento, 5) = cabecera.ch_fac_tipodocumento AND TDOCU.tab_tabla ='08' AND TDOCU.tab_elemento != '000000')
 LEFT JOIN (
  SELECT
   ch_fac_tipodocumento AS nu_tipodoc,
   ch_fac_seriedocumento AS nu_seriedoc,
   ch_fac_numerodocumento AS nu_numerodoc,
   dt_fac_fecha AS fe_emision
  FROM
   fac_ta_factura_cabecera
 ) AS RFC ON (
 RFC.nu_numerodoc = (string_to_array(complemento.ch_fac_observacion2, '*'))[1]
 AND RFC.nu_seriedoc = (string_to_array(complemento.ch_fac_observacion2, '*'))[2]
 AND RFC.nu_tipodoc = (string_to_array(complemento.ch_fac_observacion2, '*'))[3]
 )
 LEFT JOIN int_tabla_general AS TPAGO
  ON (SUBSTRING(TPAGO.tab_elemento, 5) = cabecera.nu_tipo_pago AND TPAGO.tab_tabla ='05' AND TPAGO.tab_elemento != '000000')
 WHERE
  cabecera.ch_fac_seriedocumento      = '" . $data['serie'] . "'
  AND cabecera.ch_fac_numerodocumento = '" . $data['documento'] . "'
  AND cabecera.ch_fac_tipodocumento   = '" . $data['tipoDocumento'] . "';
		";

		if ($sqlca->query($sql) < 0) {
			return null;
		} else {
			return $sqlca->fetchRow();
		}
	}

	function getOriginDocument($_id) {
		error_log('>getOriginDocument $_id: '.$_id);
		$res = array();
		global $sqlca;

		$sql = "SELECT
 com.ch_fac_observacion2 AS reference,
 com.ch_fac_observacion3 AS _date,
 com.cli_codigo AS client
FROM
 fac_ta_factura_complemento AS com
WHERE
 com.ch_fac_tipodocumento||com.ch_fac_seriedocumento||com.ch_fac_numerodocumento||com.cli_codigo = '" . $_id . "';";

		error_log('checkOriginDocument: '.$sql);
		if ($sqlca->query($sql) < 0) {
			return array('error' => true, 'errorCode' => 0);
		}

		$row = $sqlca->fetchrow();
		if (empty($row['reference']) || $row['reference'] == NULL) {
			return array('error' => true, 'errorCode' => 1);
		}
		if (empty($row['_date']) || $row['_date'] == NULL) {
			return array('error' => true, 'errorCode' => 2);
		}
		if (empty($row['client']) || $row['client'] == NULL) {
			return array('error' => true, 'errorCode' => 3);
		}

		$reference = explode('*', $row['reference']);
		$serie = $reference[1];
		$number = $reference[0];
		$documenttype = $reference[2];
		$client = trim($row['client']);
		$_date = explode('/', $row['_date']);
		$year = $_date[2];
		$month = $_date[1];
		$day = $_date[0];
		$row = array();

		$sql = "SELECT
 ch_fac_tipodocumento AS documenttype
FROM
 fac_ta_factura_cabecera AS com
WHERE
 ch_fac_tipodocumento = '$documenttype'
 AND ch_fac_seriedocumento = '$serie'
 AND ch_fac_numerodocumento = '$number'
 AND cli_codigo = '$client'
 AND dt_fac_fecha = '$year-$month-$day';";

 		error_log('getOriginDocument: '.$sql);
		if ($sqlca->query($sql) < 0)
			return array('error' => true, 'errorCode' => 11);

		$row = $sqlca->fetchrow();
		if (empty($row['documenttype']) || $row['documenttype'] == NULL) {

			$sql = "SELECT 1
FROM information_schema.tables
WHERE table_schema = 'public'
 AND table_name = 'pos_trans$year$month';";

			if ($sqlca->query($sql) == 1) {
				$td = 'F';
				$where = "usr = '$serie-$number'
 AND ruc = '$client'
 AND td = '$td'";
				if ($documenttype == '35') {
					$td = 'B';
					$where = "usr = '$serie-$number'
 AND td = '$td'";
				}
				$sql = "SELECT
 usr AS document
FROM
 pos_trans$year$month
WHERE
 $where
GROUP BY 1;";

				error_log('getOriginDocument: '.$sql);
				if ($sqlca->query($sql) < 0)
					return array('error' => true, 'errorCode' => 22);

				$row = $sqlca->fetchrow();
				if (empty($row['document']) || $row['document'] == NULL) {
					return array('error' => true, 'errorCode' => 23);
				} else {
					return array(
						'error' => false,
						'errorCode' => 2,
						'serie' => $serie,
						'number' => $number,
						'serie' => $serie,
						'date' => "$year-$month-$day",
					);//La referencia existe en postrans
				}
			} else {
				return array('error' => true, 'errorCode' => 21);//No existe postrans
			}
		} else {
			return array(
				'error' => false,
				'errorCode' => 1,
				'serie' => $serie,
				'number' => $number,
				'serie' => $serie,
				'date' => "$year-$month-$day",
			);
			return array('error' => false, 'errorCode' => 1);//La referencia existe en facturas manuales
		}
		//return true;
	}

	function getDetalleFactura($data) {
		global $sqlca;
		$result = array();
		$sql = "SELECT
  articulo.art_codigo,
 articulo.art_descripcion,
 articulo.art_unidad,
 detalle.nu_fac_cantidad,
 detalle.nu_fac_precio,
 detalle.nu_fac_valortotal,
 detalle.nu_fac_importeneto,
 detalle.nu_fac_importeneto/detalle.nu_fac_cantidad as _precio_sin_igv,
 --(detalle.nu_fac_precio * (1 - util_fn_igv()/100)) as precio_sin_igv
 CASE WHEN cabecera.ch_fac_tiporecargo2 = 'S' THEN
  detalle.nu_fac_precio
 ELSE
  (detalle.nu_fac_precio * (1 - util_fn_igv()/100))
 END as precio_sin_igv,
 CASE WHEN cabecera.ch_fac_tiporecargo2 IS NULL OR cabecera.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
 WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 = 0 THEN 1 -- EXO
 WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 > 0 THEN 2 -- TG
 END AS typetax,
 CASE WHEN cabecera.ch_fac_tiporecargo2 IS NULL OR cabecera.ch_fac_tiporecargo2 = '' THEN
  detalle.nu_fac_precio/(1 + util_fn_igv()/100) -- NORMAL
 WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 = 0 THEN
  detalle.nu_fac_precio -- EXO
 WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 > 0 THEN
  0.00--detalle.nu_fac_precio -- TG
 END AS precio,
 CASE WHEN cabecera.ch_fac_tiporecargo2 IS NULL OR cabecera.ch_fac_tiporecargo2 = '' THEN
  detalle.nu_fac_importeneto -- NORMAL
 WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 = 0 THEN
  detalle.nu_fac_valortotal -- EXO
 WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 > 0 THEN
  detalle.nu_fac_valortotal -- TG
 END AS importe

 FROM fac_ta_factura_detalle detalle
 JOIN fac_ta_factura_cabecera cabecera ON (
  cabecera.ch_fac_seriedocumento = detalle.ch_fac_seriedocumento
  AND cabecera.ch_fac_numerodocumento = detalle.ch_fac_numerodocumento
  AND cabecera.ch_fac_tipodocumento = detalle.ch_fac_tipodocumento
 )
 JOIN int_articulos articulo ON (detalle.art_codigo = articulo.art_codigo)
 WHERE detalle.ch_fac_seriedocumento = '".$data['serie']."'
 AND detalle.ch_fac_numerodocumento = '".$data['documento']."'
 AND detalle.ch_fac_tipodocumento = '".$data['tipoDocumento']."';";
		if ($sqlca->query($sql) > 0) {
			while ($reg = $sqlca->fetchRow()) {
				$result[] = array($reg[0], $reg[1], $reg[2], $reg[3], $reg[4], $reg[5], $reg[6], $reg[7], $reg[8], $reg[9], $reg[10], $reg[11]);
			}
		}
		return $result;
	}

	function getPlaca($data) {
		global $sqlca;
		$result = '';
		$plate = array();

		/*$query = "SELECT
DISTINCT(vtc.ch_placa) as plate
FROM
fac_ta_factura_cabecera ftfc
JOIN val_ta_complemento_documento vtcd ON(ftfc.ch_fac_tipodocumento = vtcd.ch_fac_tipodocumento AND ftfc.ch_fac_seriedocumento = vtcd.ch_fac_seriedocumento AND ftfc.ch_fac_numerodocumento = vtcd.ch_fac_numerodocumento)
JOIN val_ta_cabecera vtc ON (vtcd.ch_numeval = vtc.ch_documento AND vtcd.ch_cliente = vtc.ch_cliente)
WHERE ftfc.ch_fac_tipodocumento = '".$data['tipoDocumento']."'
AND ftfc.ch_fac_seriedocumento = '".$data['serie']."'
AND ftfc.ch_fac_numerodocumento = '".$data['documento']."'
AND ftfc.cli_codigo = '".$data['codCliente']."';";*/

		$query = "SELECT
DISTINCT(c.ch_placa) AS plate
FROM
val_ta_complemento_documento d
JOIN val_ta_cabecera c ON (d.ch_sucursal = c.ch_sucursal AND d.dt_fecha = c.dt_fecha AND d.ch_numeval = c.ch_documento)
WHERE
d.ch_liquidacion = '".$data['ch_liquidacion']."' AND d.ch_sucursal = '".$data['ch_almacen']."'
AND octet_length(c.ch_placa) > 3;";

		error_log('->query: '.$query);

		if ($sqlca->query($query) < 0) {
			$result = '';
		} else {
			$count = $sqlca->numrows();

			for ($i = 0; $i < $count; $i++) {
				$row = $sqlca->fetchRow();
				$row['plate'] = trim($row['plate']);
				error_log('placa(1): '.$row['plate']);
				if ($row['plate'] != '' && substr($row['plate'], -1) != '-' && substr($row['plate'], 0, 1) != '-' && strlen($row['plate']) >= 3) {
					$plate[] = $row['plate'];
				}
			}

			// Antes
			$countj = count($plate);
			$countj_ = $countj-1;
			for ($j=0; $j < $countj; $j++) {
				$result .= $j == $countj_ ? $plate[$j] : $plate[$j] . ', ';
			}

			/*	
			// Despues
			if (count($plate) > 1) {//Si son varias placas
				$countj = count($plate);
				$countj_ = $countj-1;
				for ($j=0; $j < $countj; $j++) {
					$result .= $j == $countj_ ? $plate[$j] : $plate[$j] . ', ';
				}
			} else //Si solo es una placa
				$result .= $row['plate'];
			*/
		}
		error_log('placa(2): '.$result);
		return $result;
	}

	function obtenerLiquidacion($data) {
		global $sqlca;
		$result = '';
		$liquidacion = array();
		$query = "
SELECT
ch_liquidacion
FROM fac_ta_factura_cabecera
WHERE
ch_fac_seriedocumento = '".$data['serie']."'
AND ch_fac_numerodocumento = '".$data['documento']."'
AND ch_fac_tipodocumento = '".$data['tipoDocumento']."';
";

		error_log('->query: '.$query);

		if ($sqlca->query($query) < 0) {
			$result = '';
		} else {
			$row = $sqlca->fetchRow();
			$result = trim($row['ch_liquidacion']);
		}
		error_log('liquidacion(8): '.$result);
		return $result;
	}

	function getDataPie($data) {
		global $sqlca;

		$sql = "SELECT
 cabecera.nu_fac_valorbruto,
 cabecera.nu_fac_descuento1,
 cabecera.nu_fac_impuesto1,
 (CASE WHEN cabecera.nu_fac_descuento1 > 0 THEN (cabecera.nu_fac_valortotal - cabecera.nu_fac_descuento1) ELSE cabecera.nu_fac_valortotal END) nu_fac_valortotal
 ,CASE WHEN cabecera.ch_fac_tiporecargo2 IS NULL OR cabecera.ch_fac_tiporecargo2 = '' THEN 0 -- NORMAL
   WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 = 0 THEN 1 -- EXO
   WHEN cabecera.ch_fac_tiporecargo2 = 'S' AND cabecera.nu_fac_impuesto1 > 0 THEN 2 -- TG
  END AS typetax
FROM fac_ta_factura_cabecera cabecera
WHERE
 cabecera.ch_fac_seriedocumento      = '".$data['serie']."'
 AND cabecera.ch_fac_numerodocumento = '".$data['documento']."'
 AND cabecera.ch_fac_tipodocumento   = '".$data['tipoDocumento']."';";

		if ($sqlca->query($sql) < 0) {
			return null;
		} else {
			return $sqlca->fetchRow();
		}
	}

	function getIGVActual() {
		global $sqlca;
		$sql = "SELECT util_fn_igv();";
		if ($sqlca->query($sql) < 0) {
			return null;
		} else {
			return $sqlca->fetchRow();
		}
	}

	function updateIGV($params) {
		if($params['isUIGV'] == 'N') {
		
					//DETALLE
					$query2 = "
						UPDATE
							fac_ta_factura_detalle
						SET
							nu_fac_impuesto1    = 0,
							nu_fac_importeneto  = nu_fac_valortotal
						WHERE
							ch_fac_tipodocumento        ='" . pg_escape_string($params['tipoDocumento']) . "'
							AND ch_fac_seriedocumento   ='" . pg_escape_string($params['serie']) . "'
							AND ch_fac_numerodocumento  ='" . pg_escape_string($params['documento']) . "'
					";

					$rs = pg_exec($query2);

					//CABECERA

					$query = "
						UPDATE
							fac_ta_factura_cabecera
						SET
							nu_fac_impuesto1    = 0,
							nu_fac_valorbruto   = nu_fac_valortotal,
							ch_fac_tiporecargo2 = 'S',
							ch_fac_tiporecargo3 = 0
						WHERE
							ch_fac_tipodocumento        = '" . pg_escape_string($params['tipoDocumento']) . "'
							AND ch_fac_seriedocumento   = '" . pg_escape_string($params['serie']) . "'
							AND ch_fac_numerodocumento  = '" . pg_escape_string($params['documento']) . "'
					";

					$rs = pg_exec($query);
		
				}  else if($params['isUIGV'] == 'S') {

					//DETALLE
		
					$query2 = "
						UPDATE
							fac_ta_factura_detalle
						SET
							nu_fac_impuesto1    = ROUND(nu_fac_valortotal - (nu_fac_valortotal / 1.18), 2),
							nu_fac_importeneto  = ROUND((nu_fac_valortotal / 1.18), 2)
						WHERE
							ch_fac_tipodocumento        = '" . pg_escape_string($params['tipoDocumento']) . "'
							AND ch_fac_seriedocumento   = '" . pg_escape_string($params['serie']) . "'
							AND ch_fac_numerodocumento  = '" . pg_escape_string($params['documento']) . "'
					";

					$rs = pg_exec($query2);

					//CABECERA

					$query = "
						UPDATE
							fac_ta_factura_cabecera
						SET
							nu_fac_impuesto1    = ROUND(nu_fac_valortotal - (nu_fac_valortotal / 1.18), 2),
							nu_fac_valorbruto   = ROUND((nu_fac_valortotal / 1.18), 2),
							ch_fac_tiporecargo2     = NULL,
							ch_fac_tiporecargo3 = NULL
						WHERE
							ch_fac_tipodocumento        = '" . pg_escape_string($params['tipoDocumento']) . "'
							AND ch_fac_seriedocumento   = '" . pg_escape_string($params['serie']) . "'
							AND ch_fac_numerodocumento  = '" . pg_escape_string($params['documento']) . "'
					";

					$rs = pg_exec($query);

				}
	}

	function getDireccionSucursal($str,$str2) {
		$result = '';
		$del = '|';
		$this->dir = explode($del, $str);
	}

	function NumerosALetras($monto){
		$maximo             = pow(10,9);
		$unidad             = array(1=>"UNO", 2=>"DOS", 3=>"TRES", 4=>"CUATRO", 5=>"CINCO", 6=>"SEIS", 7=>"SIETE", 8=>"OCHO", 9=>"NUEVE" );
		$decena             = array(10=>"DIEZ", 11=>"ONCE", 12=>"DOCE", 13=>"TRECE", 14=>"CATORCE", 15=>"QUINCE", 20=>"VEINTE", 30=>"TREINTA", 40=>"CUARENTA", 50=>"CINCUENTA", 60=>"SESENTA", 70=>"SETENTA", 80=>"OCHENTA", 90=>"NOVENTA");
		$prefijoDecena      = array(10=>"DIECI", 20=>"VEINTI", 30=>"TREINTA Y ", 40=>"CUARENTA Y ", 50=>"CINCUENTA Y ", 60=>"SESENTA Y ", 70=>"SETENTA Y ", 80=>"OCHENTA Y ", 90=>"NOVENTA Y ");
		$centena            = array(100=>"CIEN", 200=>"DOSCIENTOS", 300=>"TRESCIENTOS", 400=>"CUATROCIENTOS", 500=>"QUINIENTOS", 600=>"SEISCIENTOS", 700=>"SETECIENTOS", 800=>"OCHOCIENTOS", 900=>"NOVECIENTOS");  
		$prefijoCentena     = array(100=>"CIENTO ", 200=>"DOSCIENTOS ", 300=>"TRESCIENTOS ", 400=>"CUATROCIENTOS ", 500=>"QUINIENTOS ", 600=>"SEISCIENTOS ", 700=>"SETECIENTOS ", 800=>"OCHOCIENTOS ", 900=>"NOVECIENTOS ");
		$sufijoMiles        = "MIL";
		$sufijoMillon       = "UN MILLON";
		$sufijoMillones     = "MILLONES";
		$base               = strlen(strval($monto));
		$pren               = intval(floor($monto/pow(10,$base-1)));
		$prencentena        = intval(floor($monto/pow(10,3)));
		$prenmillar         = intval(floor($monto/pow(10,6)));
		$resto              = $monto%pow(10,$base-1);
		$restocentena       = $monto%pow(10,3);
		$restomillar        = $monto%pow(10,6);
		
		if (!$monto) return "";
		
		if (is_int($monto) && $monto > 0 && $monto < abs($maximo)) {
			switch ($base) {
				case 1: return $unidad[$monto]; 
				case 2: return array_key_exists($monto, $decena)  ? $decena[$monto]  : $prefijoDecena[$pren*10]   . $this->NumerosALetras($resto);
				case 3: return array_key_exists($monto, $centena) ? $centena[$monto] : $prefijoCentena[$pren*100] . $this->NumerosALetras($resto);
				case 4: case 5: case 6: return ($prencentena>1) ? $this->NumerosALetras($prencentena). " ". $sufijoMiles . " " . $this->NumerosALetras($restocentena) : $sufijoMiles. " " . $this->NumerosALetras($restocentena);
				case 7: case 8: case 9: return ($prenmillar>1)  ? $this->NumerosALetras($prenmillar). " ". $sufijoMillones . " " . $this->NumerosALetras($restomillar)  : $sufijoMillon. " " . $this->NumerosALetras($restomillar);
			}
		} else return false;
	}

	function MontoMonetarioEnLetras($monto,$text) {
		$monto = str_replace(',', '', $monto);
		$pos = strpos($monto, '.');
			
		if ($pos == false) {
			$monto_entero = $monto;
			$monto_decimal = '00';
		} else {
			$monto_entero = substr($monto,0,$pos);
			$monto_decimal = substr($monto,$pos,strlen($monto)-$pos);
			$monto_decimal = $monto_decimal * 100;
		}
		$monto = (int)($monto_entero);
		$texto_con = " Y $monto_decimal/100 ".$text;
		//echo NumerosALetras($monto).$texto_con;
		return $this->NumerosALetras($monto).$texto_con;
	}

	function getFormatNumber($data) {
		//return number_format($data['number'], 2, '.', ',');
		return number_format($data['number'], $data['decimal'], '.', ',');
	}

	function cutStr($param) {
		$str = '';
		$cstr = strlen($param['str']);
		if ($cstr > $param['limit']) {
			$str = substr($param['str'], 0, $param['limit']);
			$str = $str.$param['ext'];
		} else {
			$str = $param['str'];
		}
		$str = $this->printText($str);

		return $str;
	}

	function getMaxChar($data) {
		$max = strlen($data[0]);
		if (strlen($data[1] > $max)) {
			$max = strlen($data[1]);
		}
		if (strlen($data[2]) > $max) {
			$max = strlen($data[2]);
		}
		return $max;//.' 0 => '.strlen($data[0]).' 1 => '.strlen($data[1]).' 2 => '.strlen($data[2]);
	}

	function getWC($data, $w) {

		$max = strlen($data['nu_fac_valorbruto']);

		if (strlen($data['nu_fac_impuesto1'] > $max)) {
			$max = strlen($data['nu_fac_impuesto1']);
		}

		if (strlen($data['nu_fac_valortotal']) > $max) {
			$max = strlen($data['nu_fac_valortotal']);
		}

		$data0 = $max-strlen($data['nu_fac_valorbruto']);
		$data1 = $max-strlen($data['nu_fac_impuesto1']);
		if (strpos($data['nu_fac_valorbruto'], ',') !== false) {
			$data1 += 1;
			if(strpos($data['nu_fac_impuesto1'], ',') === false) {
				$data1 += 0.9;
			}
		} else {
			if(strlen($data['nu_fac_valorbruto']) > strlen($data['nu_fac_impuesto1'])) {
				$data1 += 1;
			}
		}

		if (strlen($data['nu_fac_valortotal']) == 8 && strlen($data['nu_fac_valorbruto']) < 8) {
			$data0 += 1.9;
			$data1 += 1.9;
		}

		$data2 = $max-strlen($data['nu_fac_valortotal']);
		return array($data0, $data1, $data2);

	}
}

if(isset($_GET['serie']) && isset($_GET['documento']) && isset($_GET['tipoDocumento'])) {
	$isDownload = isset($_GET['isDownload']) ? $_GET['isDownload'] : 'true';

	error_log('[general_lv_print v. 0.180503]');
	$serie = $_GET['serie'];
	$documento = $_GET['documento'];
	$tipoDocumento = 
	//$codCliente = $_GET['codCliente'];

	$font = 'Courier';
	$pdf = new reporte_lv();

	//$pdf->_setFont($font);
	//$pdf->SetFont($font,'',10);
	$pdf->AddPage();
	$params = array(
		'serie' => $serie,
		'documento' => $documento,
		'tipoDocumento' => $_GET['tipoDocumento'],
		'isUIGV' => $_GET['isUIGV'],
		//'codCliente' => $codCliente,
	);

	$pdf->cabecera($params);
	$pdf->Ln(10);
	$header = array(
		array('text' => 'CODIGO', 'align' => 'L'),
		array('text' => 'DESCRIPCION', 'align' => 'L'),
		array('text' => 'UNIDAD', 'align' => 'L'),
		array('text' => 'CANTIDAD', 'align' => 'R'),
		array('text' => 'V. U.', 'align' => 'R'),
		array('text' => 'IMPORTE', 'align' => 'R'),
	);
	// Carga de datos
	$pdf->detalle(array($header, $params));
	$pdf->Ln(4);
	$pdf->pie($params);

	if($isDownload == 'false') {
		$pdf->Output();
	} else {
		/*
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="detalleDocumento.pdf"');
		readfile($file);*/
		$pdf->Output('D','detalleDocumento.pdf');
	}

} else {
	echo 'Error en parametros';
}

