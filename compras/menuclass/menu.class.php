<?php
/*****
* 	Author: 			Sebastian Dimitrow
*   Date: 				2003-09-25
*	Version: 			1.0
*	License: 			GPL
*	Personal Status: 	unemployed (can you help me to find a job?)
*
**************/

class menu
{

    var $NoOffFirstLineMenus=0;			// Number of first level items
	var $LowBgColor='white';			// Background color when mouse is not over
	var $LowSubBgColor='white';			// Background color when mouse is not over on subs
	var $HighBgColor='black';			// Background color when mouse is over
	var $HighSubBgColor='black';			// Background color when mouse is over on subs
	var $FontLowColor='black';			// Font color when mouse is not over
	var $FontSubLowColor='black';			// Font color subs when mouse is not over
	var $FontHighColor='white';			// Font color when mouse is over
	var $FontSubHighColor='white';			// Font color subs when mouse is over
	var $BorderColor='#676767';			// Border color
	var $BorderSubColor='black';			// Border color for subs
	var $BorderWidth=1;				// Border width
	var $BorderBtwnElmnts=1;			// Border between elements 1 or 0
	var $FontFamily="arial,comic sans ms,technical";	// Font family menu items
	var $FontSize=9;				// Font size menu items
	var $FontBold=1;				// Bold menu items 1 or 0
	var $FontItalic=0;				// Italic menu items 1 or 0
	var $MenuTextCentered='left';			// Item text position 'left', 'center' or 'right'
	var $MenuCentered='left';			// Menu horizontal position 'left', 'center' or 'right'
	var $MenuVerticalCentered='top';		// Menu vertical position 'top', 'middle','bottom' or static
	var $ChildOverlap='0';				// horizontal overlap child/ parent
	var $ChildVerticalOverlap='0';			// vertical overlap child/ parent
	var $StartTop=0;				// Menu offset x coordinate
	var $StartLeft=1;				// Menu offset y coordinate
	var $VerCorrect=0;				// Multiple frames y correction
	var $HorCorrect=0;				// Multiple frames x correction
	var $LeftPaddng=3;				// Left padding
	var $TopPaddng=2;				// Top padding
	var $FirstLineHorizontal=1;			// SET TO 1 FOR HORIZONTAL MENU, 0 FOR VERTICAL
	var $MenuFramesVertical=1;			// Frames in cols or rows 1 or 0
	var $DissapearDelay=1000;			// delay before menu folds in
	var $TakeOverBgColor=1;			// Menu frame takes over background color subitem frame
	var $FirstLineFrame='navig';			// Frame where first level appears
	var $SecLineFrame='space';			// Frame where sub levels appear
	var $DocTargetFrame='space';			// Frame where target documents appear
	var $TargetLoc='';				// span id for relative positioning
	var $HideTop=0;				// Hide first level when loading new document 1 or 0
	var $MenuWrap=1;				// enables/ disables menu wrap 1 or 0
	var $RightToLeft=0;				// enables/ disables right to left unfold 1 or 0
	var $UnfoldsOnClick=0;			// Level 1 unfolds onclick/ onmouseover
	var $WebMasterCheck=0;			// menu tree checking on or off 1 or 0
	var $ShowArrow=1;				// Uses arrow gifs when 1
	var $KeepHilite=1;				// Keep selected path highligthed
	var $Arrws	=	array	(
    						0	=>	'tri.gif',
    						1	=>	5,
                            2	=>	10,
                            3	=>	'tridown.gif',
                            4	=>	10,
                            5	=>	5,
                            6	=>	'trileft.gif',
                            7	=>	5,
                            8	=>	10
                            );	// Arrow source, width and height

    var $ImgSrc = 	'';
    var $JsSrc	=   '';

    var $MenuItems	=	array();




    function __construct()
    {

    }


    //Menu1=new Array("TextToShow","Link","BgImage",NoOfSubs,Height,Width,BgColor,BgHiColor,FontColor,FontHiColor,BorderColor);
    function add($Item,$TextToShow,$Link,$BgImage,$NoOfSubs,$Height,$Width)
    {

        $this->MenuItems[$Item]['TextToShow']=$TextToShow;
        $this->MenuItems[$Item]['Link']=$Link;
        $this->MenuItems[$Item]['BgImage']=$BgImage;
        $this->MenuItems[$Item]['NoOfSubs']=$NoOfSubs;
        $this->MenuItems[$Item]['Height']=$Height;
        $this->MenuItems[$Item]['Width']=$Width;
    }






    function GetHtml()
    {

        $this->Init_NoOffFirstLineMenus();;

        $html= "<script type='text/javascript'>\n";
		$html.= "function Go(){return}\n";
        $html.= "</script>\n";
        $html.= "<script type='text/javascript'>\n ".$this->menuvars()."</script>\n";
        $html.= "<script type='text/javascript' src='".$this->get_JsSrc()."menu_com.js'></script>\n";
		$html.= "<noscript>Your browser does not support script</noscript>\n";

        return $html;
    }




    function set_LowBgColor($vari)
    {
      $this->LowBgColor=$vari;
    }

    function get_LowBgColor()
    {
      return $this->LowBgColor;
    }

    function set_LowSubBgColor($vari)
    {
      $this->LowSubBgColor=$vari;
    }

    function get_LowSubBgColor()
    {
      return $this->LowSubBgColor;
    }


    function set_HighBgColor($vari)
    {
      $this->HighBgColor=$vari;
    }

    function get_HighBgColor()
    {
      return $this->HighBgColor;
    }

    function set_HighSubBgColor($vari)
    {
      $this->HighSubBgColor=$vari;
    }

    function get_HighSubBgColor()
    {
      return $this->HighSubBgColor;
    }

    function set_FontLowColor($vari)
    {
      $this->FontLowColor=$vari;
    }

    function get_FontLowColor()
    {
      return $this->FontLowColor;
    }

    function set_FontSubLowColor($vari)
    {
      $this->FontSubLowColor=$vari;
    }

    function get_FontSubLowColor()
    {
      return $this->FontSubLowColor;
    }


    function set_FontHighColor($vari)
    {
      $this->FontHighColor=$vari;
    }

    function get_FontHighColor()
    {
      return $this->FontHighColor;
    }


    function set_FontSubHighColor($vari)
    {
      $this->FontSubHighColor=$vari;
    }

    function get_FontSubHighColor()
    {
      return $this->FontSubHighColor;
    }


    function set_BorderColor($vari)
    {
      $this->BorderColor=$vari;
    }

    function get_BorderColor()
    {
      return $this->BorderColor;
    }


    function set_BorderSubColor($vari)
    {
      $this->BorderSubColor=$vari;
    }

    function get_BorderSubColor()
    {
      return $this->BorderSubColor;
    }


    function set_BorderWidth($vari)
    {
      $this->BorderWidth=$vari;
    }

    function get_BorderWidth()
    {
      return $this->BorderWidth;
    }

    function set_BorderBtwnElmnts($vari)
    {
      $this->BorderBtwnElmnts=$vari;
    }


    function get_BorderBtwnElmnts()
    {
      return $this->BorderBtwnElmnts;
    }


    function set_FontFamily($vari)
    {
      $this->FontFamily=$vari;
    }


    function get_FontFamily()
    {
      return $this->FontFamily;
    }


    function set_FontSize($vari)
    {
      $this->FontSize=$vari;
    }

    function get_FontSize()
    {
      return $this->FontSize;
    }


    function set_FontBold($vari)
    {
      $this->FontBold=$vari;
    }

    function get_FontBold()
    {
      return $this->FontBold;
    }


    function set_FontItalic($vari)
    {
      $this->FontItalic=$vari;
    }

    function get_FontItalic()
    {
      return $this->FontItalic;
    }


    function set_MenuTextCentered($vari)
    {
      $this->MenuTextCentered=$vari;
    }

    function get_MenuTextCentered()
    {
      return $this->MenuTextCentered;
    }


    function set_MenuCentered($vari)
    {
      $this->MenuCentered=$vari;
    }

    function get_MenuCentered()
    {
      return $this->MenuCentered;
    }


    function set_MenuVerticalCentered($vari)
    {
      $this->MenuVerticalCentered=$vari;
    }


    function get_MenuVerticalCentered()
    {
      return $this->MenuVerticalCentered;
    }


    function set_ChildOverlap($vari)
    {
      $this->ChildOverlap=$vari;
    }


    function get_ChildOverlap()
    {
      return $this->ChildOverlap;
    }


    function set_ChildVerticalOverlap($vari)
    {
      $this->ChildVerticalOverlap=$vari;
    }


    function get_ChildVerticalOverlap()
    {
      return $this->ChildVerticalOverlap;
    }


    function set_StartTop($vari)
    {
      $this->StartTop=$vari;
    }


    function get_StartTop()
    {
      return $this->StartTop;
    }


    function set_StartLeft($vari)
    {
      $this->StartLeft=$vari;
    }


    function get_StartLeft()
    {
      return $this->StartLeft;
    }


    function set_VerCorrect($vari)
    {
      $this->VerCorrect=$vari;
    }


    function get_VerCorrect()
    {
      return $this->VerCorrect;
    }


    function set_HorCorrect($vari)
    {
      $this->HorCorrect=$vari;
    }


    function get_HorCorrect()
    {
      return $this->HorCorrect;
    }


    function set_LeftPaddng($vari)
    {
      $this->LeftPaddng=$vari;
    }


    function get_LeftPaddng()
    {
      return $this->LeftPaddng;
    }


    function set_TopPaddng($vari)
    {
      $this->TopPaddng=$vari;
    }


    function get_TopPaddng()
    {
      return $this->TopPaddng;
    }


    function set_FirstLineHorizontal($vari)
    {
      $this->FirstLineHorizontal=$vari;
    }


    function get_FirstLineHorizontal()
    {
      return $this->FirstLineHorizontal;
    }


    function set_MenuFramesVertical($vari)
    {
      $this->MenuFramesVertical=$vari;
    }


    function get_MenuFramesVertical()
    {
      return $this->MenuFramesVertical;
    }


    function set_DissapearDelay($vari)
    {
      $this->DissapearDelay=$vari;
    }


    function get_DissapearDelay()
    {
      return $this->DissapearDelay;
    }


    function set_TakeOverBgColor($vari)
    {
      $this->TakeOverBgColor=$vari;
    }


    function get_TakeOverBgColor()
    {
      return $this->TakeOverBgColor;
    }


    function set_FirstLineFrame($vari)
    {
      $this->FirstLineFrame=$vari;
    }


    function get_FirstLineFrame()
    {
      return $this->FirstLineFrame;
    }


    function set_SecLineFrame($vari)
    {
      $this->SecLineFrame=$vari;
    }


    function get_SecLineFrame()
    {
      return $this->SecLineFrame;
    }


    function set_DocTargetFrame($vari)
    {
      $this->DocTargetFrame=$vari;
    }


    function get_DocTargetFrame()
    {
      return $this->DocTargetFrame;
    }


    function set_TargetLoc($vari)
    {
      $this->TargetLoc=$vari;
    }


    function get_TargetLoc()
    {
      return $this->TargetLoc;
    }

    function set_HideTop($vari)
    {
      $this->HideTop=$vari;
    }


    function get_HideTop()
    {
      return $this->HideTop;
    }


    function set_MenuWrap($vari)
    {
      $this->MenuWrap=$vari;
    }

    function get_MenuWrap()
    {
      return $this->MenuWrap;
    }


    function set_RightToLeft($vari)
    {
      $this->RightToLeft=$vari;
    }

    function get_RightToLeft()
    {
      return $this->RightToLeft;
    }


    function set_UnfoldsOnClick($vari)
    {
      $this->UnfoldsOnClick=$vari;
    }

    function get_UnfoldsOnClick()
    {
      return $this->UnfoldsOnClick;
    }


    function set_WebMasterCheck($vari)
    {
      $this->WebMasterCheck=$vari;
    }

    function get_WebMasterCheck()
    {
      return $this->WebMasterCheck;
    }


    function set_ShowArrow($vari)
    {
      $this->ShowArrow=$vari;
    }

    function get_ShowArrow()
    {
      return $this->ShowArrow;
    }


    function set_KeepHilite($vari)
    {
      $this->KeepHilite=$vari;
    }

    function get_KeepHilite()
    {
      return $this->KeepHilite;
    }


    function set_Arrws($key,$val)
    {
      $this->Arrws[$key]=$val;
    }

    function get_Arrws($i)
    {
      return $this->Arrws[$i];
    }




    function set_ImgSrc($vari)
    {
      $this->ImgSrc=$vari;
    }

    function get_ImgSrc()
    {
      return $this->ImgSrc;
    }


    function set_JsSrc($vari)
    {
      $this->JsSrc=$vari;
    }

    function get_JsSrc()
    {
      return $this->JsSrc;
    }


    function Set_NoOffFirstLineMenus($vari)
        {
           return $this->NoOffFirstLineMenus=$vari;
        }


    function Get_NoOffFirstLineMenus()
    {
           return $this->NoOffFirstLineMenus;
    }


    //calculates automaticly NoOffFirstLineMenus
    function Init_NoOffFirstLineMenus()
    {

      	if (count($this->MenuItems)==0) echo "MENU.CLASS.PHP WARNING: Empty MenuItems";

       	$i=0;
    	foreach ($this->MenuItems as $key => $value)
       	{
     		if (strlen($key)==1) $i++;
       	}
       	$this->NoOffFirstLineMenus=$i;

    }







    //Private








    function menuvars()
    {

        $ret= "var NoOffFirstLineMenus=".$this->get_NoOffFirstLineMenus().";\n";
	    $ret.= "var LowBgColor='".$this->get_LowBgColor()."';\n";
	    $ret.= "var LowSubBgColor='".$this->get_LowSubBgColor()."';\n";
	    $ret.= "var HighBgColor='".$this->get_HighBgColor()."';\n";
	    $ret.= "var HighSubBgColor='".$this->get_HighSubBgColor()."';\n";         // Background color when mouse is over on subs
	    $ret.= "var FontLowColor='".$this->get_FontLowColor()."';\n";           // Font color when mouse is not over
	    $ret.= "var FontSubLowColor='".$this->get_FontSubLowColor()."';\n";            // Font color subs when mouse is not over
	    $ret.= "var FontHighColor='".$this->get_FontHighColor()."';\n";          // Font color when mouse is over
	    $ret.= "var FontSubHighColor='".$this->get_FontSubHighColor()."';\n";
	    $ret.= "var BorderColor='".$this->get_BorderColor()."';\n";
	    $ret.= "var BorderSubColor='".$this->get_BorderSubColor()."';\n";
	    $ret.= "var BorderWidth=".$this->get_BorderWidth().";\n";
	    $ret.= "var BorderBtwnElmnts=".$this->get_BorderBtwnElmnts().";\n";
	    $ret.= "var FontFamily=\"".$this->get_FontFamily()."\";\n";
	    $ret.= "var FontSize=".$this->get_FontSize().";\n";
	    $ret.= "var FontBold=".$this->get_FontBold().";\n";
	    $ret.= "var FontItalic=".$this->get_FontItalic().";\n";
	    $ret.= "var MenuTextCentered='".$this->get_MenuTextCentered()."';\n";
	    $ret.= "var MenuCentered='".$this->get_MenuCentered()."';\n";
	    $ret.= "var MenuVerticalCentered='".$this->get_MenuVerticalCentered()."';\n";
	    $ret.= "var ChildOverlap=".$this->get_ChildOverlap().";\n";
	    $ret.= "var ChildVerticalOverlap=".$this->get_ChildVerticalOverlap().";\n";
	    $ret.= "var StartTop=".$this->get_StartTop().";\n";
	    $ret.= "var StartLeft=".$this->get_StartLeft().";\n";
	    $ret.= "var VerCorrect=".$this->get_VerCorrect().";\n";
	    $ret.= "var HorCorrect=".$this->get_HorCorrect().";\n";
	    $ret.= "var LeftPaddng=".$this->get_LeftPaddng().";\n";
	    $ret.= "var TopPaddng=".$this->get_TopPaddng().";\n";
	    $ret.= "var FirstLineHorizontal=".$this->get_FirstLineHorizontal().";\n";
	    $ret.= "var MenuFramesVertical=".$this->get_MenuFramesVertical().";\n";
	    $ret.= "var DissapearDelay=".$this->get_DissapearDelay().";\n";
	    $ret.= "var TakeOverBgColor=".$this->get_TakeOverBgColor().";\n";
	    $ret.= "var FirstLineFrame='".$this->get_FirstLineFrame()."';\n";
	    $ret.= "var SecLineFrame='".$this->get_SecLineFrame()."';\n";
	    $ret.= "var DocTargetFrame='".$this->get_DocTargetFrame()."';\n";
	    $ret.= "var TargetLoc='".$this->get_TargetLoc()."';\n";
	    $ret.= "var HideTop=".$this->get_HideTop().";\n";
	    $ret.= "var MenuWrap=".$this->get_MenuWrap().";\n";
	    $ret.= "var RightToLeft=".$this->get_RightToLeft().";\n";
	    $ret.= "var UnfoldsOnClick=".$this->get_UnfoldsOnClick().";\n";
	    $ret.= "var WebMasterCheck=".$this->get_WebMasterCheck().";\n";
	    $ret.= "var ShowArrow=".$this->get_ShowArrow().";\n";
	    $ret.= "var KeepHilite=".$this->get_KeepHilite().";\n";
	    $ret.= "var Arrws=['".$this->get_ImgSrc().$this->get_Arrws(0)."',".$this->get_Arrws(1).",".$this->get_Arrws(2).",'".$this->get_ImgSrc().$this->get_Arrws(3)."',".$this->get_Arrws(4).",".$this->get_Arrws(5).",'".$this->get_ImgSrc().$this->get_Arrws(6)."',".$this->get_Arrws(7).",".$this->get_Arrws(8)."];\n";



        $ret.= "function BeforeStart(){return}\n";
		$ret.= "function AfterBuild(){return}\n";
		$ret.= "function BeforeFirstOpen(){return}\n";
		$ret.= "function AfterCloseAll(){return}\n";


        $ret.=$this->MenuItems();

        return $ret;
    }







    function MenuItems()
    {


        $ret= '';

        foreach ($this->MenuItems as $key => $value)
        {

        	$ret.= "Menu".$key."=new Array(\"".$this->MenuItems[$key]['TextToShow']."\",\"".$this->MenuItems[$key]['Link']."\",\"".$this->MenuItems[$key]['BgImage']."\",".$this->MenuItems[$key]['NoOfSubs'].",".$this->MenuItems[$key]['Height'].",".$this->MenuItems[$key]['Width'].");\n";

        }


        return $ret;

    }







}
