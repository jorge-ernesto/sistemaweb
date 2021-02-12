/*
   Milonic DHTML Menu Object Hiding Module divhider.js version 1.0 - November 3 2004
   This module is only compatible with the Milonic DHTML Menu version 5.16 or higher

   Copyright 2004 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.
*/

// The idea is to declare the HTML Object you need to hide when a menu becomes visible
// Declare the Menu Name -> Object Reference followed by a semi colon
// M_hideMenus Syntax: "MenuName->ObjectReference;";

M_hideMenus = "Samples->formdiv1;milonic->formdiv1;"

function M_hideLayer(_mnu,_show){
	if(ie55||ns6||ns7)return
	M_hdar=M_hideMenus.split(";")
	for(_Ma=0;_Ma<M_hdar.length;_Ma++){
		M_hdarI=M_hdar[_Ma].split("->")
		M_mnu=getMenuByName(M_hdarI[0])
		if(M_mnu>-1&&M_mnu==_mnu){
			M_gm=gmobj(M_hdarI[1])
							
			if(_show){
				if(ns4)M_gm.visibility="hide"; else M_gm.style.visibility="hidden"
			}
			else{
				if(ns4)M_gm.visibility="show"; else M_gm.style.visibility="visible"
			}		
		}
	}	
}

