/*
   Milonic DHTML Menu Color Changing Module Module  changecolorsbyclick.js version 1.1 April 20 2005
   This module is only compatible with the Milonic DHTML Menu version 5.70 or higher

   Copyright 2004 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.   
   
   For use with the following properties:
   
        clickcolor
    	clickbgcolor
        clickimage
        clicksubimage
   
*/

_oldItem=-1

function mmClick()
{
	_i=_itemRef
	
	_cA(80,8,_i)
	_cA(81,7,_i)
	_cA(82,29,_i)
	_cA(83,24,_i)
	_cA(87,54,_i)
	_cA(88,46,_i)
	_cA(91,71,_i)
	
	if(_oldItem>-1)
	{
		_cA(80,8,_oldItem)
		_cA(81,7,_oldItem)
		_cA(82,29,_oldItem)
		_cA(83,24,_oldItem)
		_cA(87,54,_oldItem)
		_cA(88,46,_oldItem)
		_cA(91,71,_oldItem)
		
	}
	_oldItem=_i
}