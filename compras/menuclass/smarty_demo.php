<?php

/*
*	Require a working Smarty Template Engine !
*	For mor Info go to smarty.php.net
*
*******************************/


include("menu.class.php");

$menu = new menu();

$menu->add(1,'Start','','',3,20,80);
	$menu->add('1_1','Beenden','','',0,20,80);
	$menu->add('1_2','Einstellungen','','',0,20,80);
	$menu->add('1_3','Programme','','',0,20,80);






//calculate NoOffFirstLineMenus
$menu->Init_NoOffFirstLineMenus();



//You can set the IMG location for the Arrows 
//$menu->Set_ImgSrc('img/');


// you can set the Javascript location
//$menu->Set_JsSrc('js/');

$smarty->assign('menu',$menu);

$smarty->display('smarty_demo.tpl');

