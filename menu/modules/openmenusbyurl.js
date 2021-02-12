/*
   Milonic DHTML Menu Automatic Menu Opening Module  openmenusbyurl.js version 2.3 - October 12 2008
   This module is only compatible with the Milonic DHTML Menu version 5.62 or higher

   Copyright 2007 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.
   
   The object of this module is to re-open the menu or menus up to point where the user was on a previous page.
   Just add this module to your HTML page after the menu_data.js file.
*/

// <script type="text/javascript" src="openmenusbyurl.js"></script>
/* All of the following paramater are none mandatory*/
//mm_maxLevels=0;         // parameter [INTEGER] to fix the number of menus displayed to the specified maximum value.
resetAutoOpen=true      // parameter This bit allows the menu to re-open if sibling menus have been closed, displaying the menu again if required.
forceChildSubOpen=true  // Forces the opening of a child sub menu if the opening item's URL matches the current page URL and also opens a sub menu

function _ocURL(){
	var i,I,c;
	_cip=new Array();
	for(_b=0;_b<_mi.length;_b++)$q(_b)
	
	if(_W.forceChildSubOpen){
		_cln=_cip.length
		for(c=0;c<_cln;c++){
			if(_mi[_cip[c]][3]){
				_mn=$h(_mi[_cip[c]][3])
				_cip[_cip.length]=_m[_mn][0][0]
			}
		}
	}
	
	_ombcu=new Array()	
	if(_cip.length>0){
		for(c=0;c<_cip.length;c++){
			_ci=_cip[c];
			_mni=$_f(_ci);
			if(_mni==-1)_mni=_ci
			if(_mni+" "!=$u){
				while(_mni!=-1){
					_ombcu[_ombcu.length]=_mni
					_mni=$_f(_mni);
					if(_mni+" "==$u)_mni=-1					
				}
			}
		}
	}
	
	if(_startM){
		setTimeout("_ocURL()", 50)
	}
	else{
		if(_W._lnk&&_lnk!=_jv)return false
		if(_ombcu.length&&(_W.mm_maxLevels>0||_W.mm_maxLevels+" "==$u)){
			for(_oma=_ombcu.length-1;_oma>-1;_oma--){
				i=_ombcu[_oma];
				I=_mi[i];
				h$(i);
				h$(i);
				hidetip();
				var M=_m[$h(I[3])]
				if(!M[7])$K(i);
				_ofMT=1;
				if(I[32]){
					_tI=$c("img"+i)
					if(_tI&&I[29])_tI.src=I[29]
				}
				if(_oma==_W.mm_maxLevels)return
			}
		}
		if(_ombcu.length){
  			d$(_ombcu[0])
  			hmL(_mi[_ombcu[0]][0])
  		}
	}
}
_ocURL()



