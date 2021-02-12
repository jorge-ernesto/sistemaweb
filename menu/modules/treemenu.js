/*
   Milonic DHTML Menu - Tree Menu Module Version 1.29 - June 19 2008
   This module is only compatible with the Milonic DHTML Menu version 5.764 or higher

   Copyright 2007 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.
   
   SYNTAX: <script type="text/javascript" src="/treemenu.js"></script>
*/

var  _tAct
//_Tzi=0

function isOdd(n){ 
    return (n % 2)
}

function treeMenuDisplay(m,_show){                      // This function hides or shows the menu
	
	_m[m][7]=_show;
	_m[m].treemenu=1;
	$Y(m,_show);	
	var M=$c("menu"+m);
	if(_show)M.style.zIndex=_zi+10; else M.style.zIndex=1;
}

_lastItem=-1
_Oi=-1

function resetOI(_oi){
	_otA(_oi)
	_caA(_oi)
	u_=$c("el"+_oi)
	u_.e$=1
	d$(_oi)
}



function _oTree()
{	
	if(_Oi>-1&&_mi[_Oi][34]!="tree")resetOI(_Oi)
	
	
	_Oi=_itemRef
	_otA(_Oi)
	
	if(_W.singleMasterMenu)
	{
		_TI=_mi[_Oi]
		
		for (var _ai=_m[_TI[0]][0][0];_ai<=_m[_TI[0]][0][_m[_TI[0]][0].length-1];_ai++)
		{	
			if(_mi[_ai].childN+" "!=$u&&_ai!=_Oi)
			{
				//alert($6)
				if(_mi[_ai].child.style.visibility==$6)resetOI(_ai)
			}
		}
	}
}


function setTreeClass(_gm_)
{
	_ti=_itemRef
	$m=$h(_mi[_ti][3])
	_gm_=$c("menu"+$m)
	if(_W.treeOffset)_gm_.style.paddingLeft=treeOffset+"px"

	for(_a=0;_a<_m[$m][0].length;_a++)
	{
		_cItem=_m[$m][0][_a]
		_titemObj=$c("el"+_cItem)
		
		//$c("andy").innerHTML=_cItem + " - " + _titemObj + " - " + $m + " - " + _a + " - " + _ti + " - " + _itemRef
		if(_mi[_cItem][3])
		{
			_titemObj.className="treeItemCollapsed";
			_mi[_cItem][87]="treeItemExpanded";
			_mi[_cItem][54]="treeItemCollapsed";
		}
		else
		{
			if(_m[$m][6].clickimage==_mi[_cItem][82])_mi[_cItem][82]=""
			_titemObj.className="treeItemBranch"	
			imgO=$c("_imgO"+_cItem)
			if(_W.treeItemImagePadding)if(imgO)imgO.style.paddingLeft=treeItemImagePadding+"px"
		}
	}

	_tmnu=$_d(_ti)
	
	//if(_ti!=_m[_tmnu][0][_m[_tmnu][0].length-1])
	_gm_.className="treeBranch" // removed the above statement to fix bug with last item not being set in 5.778
	if(_mi[_cItem][3])
	{
		_titemObj.className="treeEndItemCollapsed";
		_mi[_cItem][87]="treeEndItemExpanded";
		_mi[_cItem][54]="treeEndItemCollapsed";
	}
	else
	{
		_titemObj.className="treeEndItem"	
	}
		
		
		
		

}

old_ZI=0;
MMWID=0
function _otA(_ti)
{
	_dB=_d.body;
	$7=_dB.offsetTop
	$8=_dB.offsetLeft	

	_TI=_mi[_ti]                                             // set menu item shorthand to _TI
	
	if(!_TI[27])_TI[27]=1
	
	if(!_TI[3]||!_TI[34])return                              // If no showmenu has been specified or not a treemenu go back
	if(!_TI.child)                                          // Set the menu that this item will open.
	{		
		_TI.childN=$h(_TI[3])
		if(!_TI.childN)return
		_TI.child=$c("menu"+_TI.childN)
	}
	
	hmL(_TI.childN)
	
	//if(mac){if(!_TI.obj)_TI.obj=$c("pTR"+_ti)              // IE on the The Apple Mac needs to use <TR> for positioning
	//}else{if(!_TI.obj)_TI.obj=$c("OtI"+_ti)}                // All other browsers can use <TD>
	
	
	if(!_TI.obj)
	{
		setTreeClass(_TI[0])
		//setTreeClass($h(_mi[_ti][3]))
		
	}
	
	//NWID=_n
	//$jj=1
	//if($jj>=0)
	//{
		//alert()
		//$c("menu"+$jj).style.zIndex=1
		
	//	if(_m[$jj][17])MMWID=_m[$jj][17]
		//if(MMWID)
		//{
			//depth=getDepthByItem(_ti)+1
			//if(depth>1)depth--
			//NWID=MMWID-(treeOffset*depth)
		//}
	//}
	//NWID=300
	
//	alert(NWID)
	
	
	
	
	_TI.obj=$c("OtI"+_ti)
	$mO=_m[_TI.childN]                                    // This is the object reference to the menu we are about to open
	_tio=_TI.obj                                             // Set _tio as shorthand object reference to this menu item 
	_Pmenu=$_d(_ti)
	if($mO[7])                                           // If the DISPLAY attribute is on the menu is shown and must now be hidden
	{
		if(ie&&!mac)_tio.style.display="none"
		_tAct=0
		treeMenuDisplay(_TI.childN,0)                       // Hide this menu
		_CH=_m[_TI.childN].HGT-_m[_TI.childN].OHGT           // Set _CH as the variable to store this menus height, used for adjusting child and siblings
		closeTMenu(_ti)
	}
	else                                                   // Display this child menu
	{
		if(ie&&!mac)_tio.style.display="block"
		_tAct=1
		treeMenuDisplay(_TI.childN,1)
		_PiGP=$D(_tio)                                    // get the Dimensions of parent menu item
		_cD=$D(_TI.child)   
	
		s_=1
		if(ie)s_=0
		                              // Get the Dimensions of menu we are about to open
		_TI.ttop=_PiGP[0]+_PiGP[2]-_TI[27]+s_+$x3($mO[2]) // set temporary top property for the 
		
		
		
		
		_m[_TI.childN].ttop=_TI.ttop		
		_TI.tleft=_PiGP[1]+treeOffset+$x3($mO[3])
		_TI.tleft=_TI.tleft-treeOffset
		//if(_TI.childN==1)$c("menu"+_TI.childN).style.backgroundImage=""
		if(mac)_TI.tleft-=_TI[27]
		if(sfri)
		{
			if(_m[_Pmenu][14]=="relative")
			{
				_TI.tleft=(_TI.tleft)+$8
				_TI.ttop=(_TI.ttop)+$7
				
			}
		else
			_TI.tleft-=_TI[27]
		}
		
		
		_m[_TI.childN].tleft=_TI.tleft
		$_E(_TI.child,_TI.ttop,_TI.tleft)         // set the position of the menu to open



		if(!_m[_TI.childN].parentItem || (_m[_TI.childN].parentItem.id!=_tio.id))
		{
			
			_m[_TI.childN].parentItem=_tio
			_m[_TI.childN].OHGT=_PiGP[2]
			_m[_TI.childN].parentItemN=_ti
		}			

		
		_m[_TI.childN].HGT=_cD[2]+_PiGP[2] // height correction

		_OH=_m[_mi[_ti].childN].HGT-_m[_mi[_ti].childN].OHGT

		if(_TI.children)
		{
			for(_tm=0;_tm<_TI.children.length;_tm++)
			{
				$m=_TI.children[_tm] // this items menu
				_GP=$D(_m[$m].parentItem) // get position of OtI
				_m[$m].ttop=_GP[0]+_m[$m].OHGT-_TI[27] 
				$_E($c("menu"+$m),_m[$m].ttop,_m[$m].tleft) // set the position of this items menu
				treeMenuDisplay($m,1) // show the menu
			}
		}
	}
	
	_ocURL=new Function(); // Stops openmenusbyurl from changing sub menus
	resetParents(_ti)
}

function resetChildren(_ti)
{	
	_ar=getChildrenByItem(_ti)
	for (var _ai=1; _ai<_ar.length; _ai++)
	{
		_AI=_ar[_ai]
		if(_tAct)_m[_AI].ttop+=_OH; else _m[_AI].ttop-=_CH;
		$_E($c("menu"+_AI),_m[_AI].ttop)
	}
}


function resetSiblings(_ti)
{
	var _TI=_mi[_ti]
	var _ar=_m[_TI[0]][0]
	for (var _ai=0; _ai<_ar.length; _ai++)
	{
		_AI=_ar[_ai]
		if(_ti<_AI)
		{
			if(_mi[_AI].childN)
			{
				$m=_mi[_AI].childN
				if(_tAct)_m[$m].ttop+=_OH; else _m[$m].ttop-=_CH;
				$_E($c("menu"+$m),_m[$m].ttop)
				resetChildren(_AI)
			}
		}
	}
}

function resetParents(_ti)
{
	_TI=_mi[_ti]                                             // menu item
	$m=_TI.childN
	_th=0
	while(_m[$m].parentItem)
	{
		if(_tAct)
		{
			_m[$m].HGT=_m[$m].HGT+_th	
			if(_th==0)_th=_m[$m].HGT-_m[_TI.childN].OHGT;			
		}
		else
		{
			_m[$m].HGT=_m[$m].HGT-_CH
		}
		//alert(_m[$m].HGT)
		
		s_=0
		if(ie)s_=1

		
		$_E(_m[$m].parentItem,_n,_n,_m[$m].HGT-s_)
		//$z($m)
		resetSiblings(_m[$m].parentItemN)
		$m=_mi[_m[$m].parentItemN][0]
	}
	
	if(mac)
	{	
		_macP=$D($c("tbl"+$m))
		$_E($c("menu"+$m),_n,_n,_macP[2])
	}
}



function closeTMenu(_ti)
{	
	_ar=getChildrenByItem(_ti)
	_TI=_mi[_ti]
	_TI.children=_ar	
	for(_tm=0;_tm<_ar.length;_tm++)
	{		
		treeMenuDisplay(_ar[_tm],0)
	}	
	$_E(_m[_TI.childN].parentItem,_n,_n,_m[_TI.childN].OHGT)
}



gChildren=new Array();
function crawlChildren(_ti) // returns menus that are children of declared menu item
{	
	_cHm=_mi[_ti].childN
	if(!_cHm)return
	var _ar=_m[_cHm][0]
	if(_mi[_ti].childN && _m[_mi[_ti].childN][7])gChildren[gChildren.length]=_mi[_ti].childN;
	for (var _ai=_ar[0]; _ai<_ar[_ar.length-1]+1; _ai++)
	{		
		if(_mi[_ai].childN && _m[_mi[_ai].childN][7])crawlChildren(_ai)
	}
}


function getChildrenByItem(_ti)
{
	gChildren=new Array();
	crawlChildren(_ti)
	return gChildren;
}

function clickBranch(_titem){
	$m=$h(_mi[_titem][3])
	_M=_m[$m]
	if(_M&&!_M[23])g$($m)
	$m=-1; 
	_itemRef=_titem
	d$(_titem,1)
	_caA(_titem)
	_oTree()
}


function closeBranchByName($mN){
	if(_startM)return _StO('closeBranchByName("'+$mN+'")',50);
	$mN=$tL($mN)
	for(var _ga=0;_ga<_mi.length;_ga++){
		if(_mi[_ga][3]){
			if($mN==$tL(_mi[_ga][3])){
				j_m=$c("menu"+$h(_mi[_ga][3]))
				if(j_m.style.visibility==$6)clickBranch(_ga)
			}
		}
	}
}

function openBranchByName($mN) // declare the name of a sub menu and open it as though the user had clicked on a menu item
{
	if(_startM)return _StO('openBranchByName("'+$mN+'")',50);
	$mN=$tL($mN)
	for(var _ga=0;_ga<_mi.length;_ga++){
		if(_mi[_ga][3]){
			if($mN==$tL(_mi[_ga][3])){
				j_m=$c("menu"+_mi[_ga][0])
				if(j_m.style.visibility==$5)openBranchByName(_m[_mi[_ga][0]][1])
				j_m=$c("menu"+$h(_mi[_ga][3]))
				if(j_m.style.visibility==$5)clickBranch(_ga)
				d$(_ga,1)
			}
		}
	}
}


function allBranches(action)
{
	//start=new Date().getTime()
	for(_ga=0;_ga<_mi.length;_ga++){
		if(_mi[_ga][3]&&_mi[_ga][34]=="tree"){
			j_m=$c("menu"+$h(_mi[_ga][3]))
			if(j_m){
				if(action)
				{
					if(j_m.style.visibility==$5)clickBranch(_ga)
				}
				else{	
					if(j_m.style.visibility==$6)clickBranch(_ga)
				}
			}
		}
	}
	//end=new Date().getTime()
	//alert(end-start)
}


function openAllBranches(){
	if(_startM)return _StO('openAllBranches()',50);
	if(!_W.singleMasterMenu)allBranches(1)
}

function closeAllBranches(){
	if(_startM)return _StO('closeAllBranches()',50);
	allBranches(0)
}


function getDepthByItem(i)
{
	var D=0;
	var m
	_mni=$_f(i);
	if(_mni+" "!=$u){
		while(_mni!=-1){
			D++
			_mni=$_f(_mni);
		}
	}
	return D
}

function _tMR()
{
	return
	np=$D($c("menu14"))
	for(var a=15;a<_m.length;a++)
	{
		aaa=$D($c("menu14"))
		$_E($c("menu"+a),_n,aaa[1])
	}
	
}
