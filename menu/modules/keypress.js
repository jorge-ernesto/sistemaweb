/*
   Milonic DHTML Menu Keypress Module  keypress.js version 2.91 - October 17 2014
   Used for accessibility of the menu. This module will allow the user to navigate Milonic menus via the keyboard.
   
   This module is only compatible with the Milonic DHTML Menu version 5.16 or higher

   Copyright 2005 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.
*/

hotKey_F_Number=2; // This is F2 by default, do not use 1, 4 or 5 as these are reserved by the browser.
hotkey_Ctrl_Char="M"; // Must be in caps
_mainM=-1;
_Omo=_menuOpenDelay;
_Cmo=_menuCloseDelay;
KSHIFT=KCTRL=KALT=0;
_jsk=null;

function chr(c){
	return String.fromCharCode(c);
}

function ord(c){
	return c.charCodeAt(0);
}

function getNextKeyItem(i,m)
{
	var a,o;
	o=i+1;
	if(_mi[i]&&_mi[o]&&_mi[o][0]==_mi[i][0]){
		return o;
	}
	else{
		o=m[0][0];
	}
	return o;
}

function getPreKeyItem(i,m)
{
	var a,o;
	o=i-1;
	if(_mi[i]&&o<0||_mi[o][0]!=_mi[i][0])o=m[0][m[0].length-1];
	return o;
}


function getMenuByKey(e)
{
	_jsk=e.which?e.which:e.keyCode;
	if(_jsk==16)KSHIFT=1
	if(_jsk==17)KCTRL=1
	if(_jsk==18)KALT=1
	
	var i,m,ir,tr,omitMenu=0;
	if(_itemRef<0){
		$I()
		return
	}

	if(KSHIFT&&_jsk==9){
		
		if(_itemRef==_m[$_d(_itemRef)][0][0]){
			$c("lnk"+_itemRef).focus()
			_itemRef=-1
			return
		}
	}
	
	ir=null
	_ofMT=1	
	i=_itemRef;
	
	if(_mi[i][34]=="form"){
		_ofMT=0
		_menuOpenDelay=_Omo
		_menuCloseDelay=_Cmo
		return
	}
	
	m=_m[_mi[i][0]]
	_menuOpenDelay=0
	_menuCloseDelay=0
	if(_jsk==116)return

	if(_jsk==27){ // Escape
		$Z();
		return false;
	}


	hrgm=$c("mmlink"+_mi[i][0])
	if(hrgm.style.visibility="visible")hrgm.focus()	
	_cm3()
	

	if(_jsk==9){
		
		if(i<0){
			return false;
		}
		else{
			if(KSHIFT){
				if(i==m[0][0]){
					ir=_m[_mi[i][0]][21]
				}
				else{						
					ir=getPreKeyItem(i,m)
				}
				if(ir<0){
					$I()
					return;
				}
				
			}
			else{
				if(_mi[i][3]){
					ir=_m[$h(_mi[i][3])][0][0]
				}
				else{
					if(i==m[0][m[0].length-1]){
						ir=i
						ir=$_f(ir);
						if(!ir)ir=_m[getNextMainMenu(m)][0][0]//if last item
						_iRR=getNextKeyItem(ir,m)
						while(_mi[_iRR][0]==_mi[m[0][m[0].length-1]][0]){
							ir=$_f(ir);
							if(ir+" "==$u){
								$I()
								return;
							}
							_iRR=getNextKeyItem(ir,m)
						}
						ir=getNextKeyItem(ir,m)
					}
					else{
						ir=getNextKeyItem(i,m)
					}
				}				
			}
			if(ir>-1)_doKeyAction(ir)
		}
		return false
	}

	if(m[9]){
		if(_jsk==39)ir=getNextKeyItem(i,m)//Right		
		if(_jsk==37)ir=getPreKeyItem(i,m) //left
		if(_jsk==38){ //Up
			_mn=$h(_mi[i][3])
			if(_mn>-1)ir=_m[_mn][0][_m[_mn][0].length-1]
		}
		if(_jsk==40){ //Down
			_mn=$h(_mi[i][3])
			if(_mn>-1)ir=_m[_mn][0][0]
		}		
	}
	else{
		if(_jsk==37){ // left
			ir=$_f(i);
			if(ir>-1&&!_mi[ir][3])ir=getPreKeyItem(ir,m);
			if(ir+" "==$u)return
			if(_mi[ir][3])omitMenu=$h(_mi[ir][3])
		}
		if(_jsk==39){ // right
			if(_mi[i][3]){
				_mn=$h(_mi[i][3])
				if(_mn>-1)ir=_m[_mn][0][0]
			}
			else{
				ir=$_f(i);
				while(ir||ir==0){
					nir=ir
					ir=$_f(ir);
				}
				ir=getNextKeyItem(nir,m)
			}
		}
		if(_jsk==38)ir=getPreKeyItem(i,m) //Up
		if(_jsk==40)ir=getNextKeyItem(i,m) //Down
	}

	if((ir>-1)){
		_doKeyAction(ir,omitMenu);
	}
	

	if(ie||_d.evaluate){
		if(_jsk==13)hrgm.click();
		return false;
	}	
}

function gMBK(e){
	_jsk=e.which?e.which:e.keyCode;
	if(_jsk==13||_jsk==116)return
	if(_itemRef>-1)if(_mi[_itemRef][34]!="form")return false
}



function getNextMainMenu(){
	var a
	if(_mainM>-1){
		for(a=_mainM;a<_m.length;a++){
			if(a>_mainM&&_m[a][7]){
				_mainM=a
				return a;
			}
		}
	}
	
	_mainM=0;
	for(a=0;a<_m.length;a++){
		if(_m[a]&&_m[a][7]){
			_mainM=a
			return a;
		}
	}
	return -1;
}

function getNextMmItem(){
	var m;
	m=getNextMainMenu();
	return _m[m][0][0];
}

function getHotKeys(){
	_hKeys=[];
	for(var a=0;a<_mi.length;a++){
		if(_mi[a][107])_hKeys[_hKeys.length]=$tU(_mi[a][107]);
	}
	return _hKeys;
}

function _doKeyAction(i){
	if(KSHIFT&&_jsk==9&&_itemRef>-1){
		if(_itemRef==_m[$_d(_itemRef)][0][0]){
			$I()
			return
		}
	}
	
	if(_mi[i]){
		if(_mi[i][39]+" "==$u)h$(i); else $K(i);
	}	
}

function getMenuByKeyU(e){
	_jsk=e.which?e.which:e.keyCode;
	_hKeys=getHotKeys()
	if(_jsk==hotKey_F_Number+111)_doKeyAction(getNextMmItem())		
	if(e.ctrlKey && chr(_jsk)==hotkey_Ctrl_Char)_doKeyAction(getNextMmItem()) // if ctrl and hotkey_Ctrl_Char is pressed	
	_menuOpenDelay=_Omo
	_menuCloseDelay=_Cmo
	if(_jsk==16)KSHIFT=0
	if(_jsk==17)KCTRL=0
	if(_jsk==18)KALT=0
}

_d.onkeydown=getMenuByKey;
_d.onkeypress=gMBK;
_d.onkeyup=getMenuByKeyU;

function _iF0C(i){
	_doKeyAction(i)
}

