/*
   Milonic DHTML Menu - Insert Item module version 1.0 - August 26 2004
   This module is only compatible with the Milonic DHTML Menu version 5.16 or higher

   Copyright 2004 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.
   
   SYNTAX: <script type="text/javascript" src="/insertitem.js"></script>
   The above <SCRIPT tag must be entered before the menu_data.js file
*/


function _iI(txt,_pos)
{	
	_oStyle=_m[_mn][6];
	_m[_mn][6]=this.style;
	this.aI(txt);                                 // Build the menu item at the end of the menu items tree
	_mil=_mi.length;
	_M=_m[this.menunumber];                       // _M = menu array for this new item
	_nmi=new Array();                            // create empty array for building new menu items
	if(_pos>=_M[0].length)_pos=_M[0].length;	
	if(!_M[0][_pos])_M[0][_pos]=_M[0][_M[0].length-1]+1;
	_inum=_M[0][_pos];                            // Get the menu item number relative to menu list
	_cnt=0;                                      // reset the menu item counter to zero
	
	for(_a=0;_a<_mil;_a++)
	{
		if(_inum==_a)
		{
			_nmi[_cnt]=_mi[_mi.length-1];
			_nmi[_cnt][0]=this.menunumber;	
			_M[0][_M[0].length]=_cnt;
			_cnt++                               // keep a count of items so that we know which menuitem we are working with
		}
		_nmi[_cnt]=_mi[_a];
		_cnt++                                   // keep a count of items so that we know which menuitem we are working with
	}	
	_mi=_nmi;                                     // replace the old array with the newly created array of menuitems
	
	_tpos=0;
	_omnu=-1;
	for(_a=0;_a<_mil;_a++)
	{		
		_mnu=_mi[_a][0];
		if(_mnu!=_omnu)
		{
			_m[_mnu][0]=new Array();
			_tpos=0
		}	
		_m[_mnu][0][_tpos]=_a;
		_tpos++;
		_omnu=_mnu
	}
	_m[_mn][6]=_oStyle
}

menuname.prototype.insertItem=_iI;
