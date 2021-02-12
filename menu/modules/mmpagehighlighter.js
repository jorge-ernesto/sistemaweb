/*
   Milonic DHTML Menu Item Activator Module  mmpagehighlighter.js version 1.4 August 5 2008
   This module is only compatible with the Milonic DHTML Menu version 5.16 or higher

   Copyright 2008 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.

	Upload the file to your website, then: 
	Add this line AFTER your menu_data.js file: <script src="/mmpagehighlighter.js" type=text/javascript></script>

	Used for highlighting a menu item by showmenu,text or item number based on pagematch properties
	Syntax is:
	
	mmItemActiveByShowMenu("dhtml menu") // highlights a menu item based on its Showmenu value
	mmItemActivateByText("image map sample") // highlights a menu item based on its Text value
	mmItemActivateByNumber(5) // highlights a menu item based on its Numberical Order
	
	The text properties are non case sensitive
*/

/*
//Syntax Samples:
mmItemActiveByShowMenu("dhtml menu")
mmItemActivateByText("about us")
mmItemActivateByNumber(5)
*/


function _doHLK(_i) {
	if(_mi+" "==$u)return false;
	_M=_m[_mi[_i][0]];
	if(!_M)return;
	if(!_M[23])g$(_mi[_i][0]);
	_I=_mi[_i];
	$Cw(_I);
	
	var LL=$c("lnk"+_i)
	if(LL){
		LL.style.color=_I[8]
	}
	
	e$(_i);
	d$(_i);
}
function _findItem(t, _opt){
	if(_mi+" "==$u)return false;
	for(_a=0;_a<_mi.length;_a++){
		_I=_mi[_a];
		if($tU(_I[_opt])==$tU(t))_doHLK(_a);
	}
}

function resetActiveItemsByMenu(m){
	var M=_m[m][0]
	_M=_m[m];
	if(!_M[23])g$(m);
	for(var A in M){
		var I=_mi[M[A]];
	      I[7]=_m[m][6].offbgcolor;
          	I[8]=_m[m][6].offcolor;
          	$c("el"+M[A]).e$=1;
		d$(M[A]);
	}
}


function mmItemActiveByShowMenu(t){_findItem(t,3)}
function mmItemActivateByText(t){_findItem(t,1)}
function mmItemActivateByNumber(t){_doHLK(t)}
function mmactivateParentItems(t){
	_doHLK(t);
	while(t){
		t=$_f(t);
		if(t)_doHLK(t);
	}
}


