/*
   Milonic DHTML Menu - Drag & Drop Module dragdrop.js version 1.5 - August 31 2007
   This module is only compatible with the Milonic DHTML Menu version 5.780 or higher

   Copyright 2007 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.
*/

// This module does not contain any user definable parameters.
// In order to enable menus as dragable, the menu must be alwaysvisible
// and the menu item initiating drag/drop must have the property type=dragable; declared

resetFollowScrollers=true; // If you want the menu to remain in the position dragged to for $X menus, set resetFollowScrollers to false

DragLayer=-1;
DragX=0;
DragY=0

function drag_drop(m,item)
{
	DragLayer=m;
	_gm_=$c("menu"+DragLayer);
	if(ns4)_gm_.captureEvents(Event.MOUSEDOWN | Event.MOUSEUP);
	$c("el"+_itemRef).onmousedown=_DDgo;
	$c("el"+_itemRef).onmouseup=_DDstop;
}

function _DDgo()
{
	var t=$h("M_toolTips")	
	if(t)
	{
		$Y(t,0);
		clearTimeout(_Mtip)
		_Mtip=null
	}
	_gm_=$c("menu"+DragLayer);
	_m[DragLayer].ifr=DragLayer
	gp=$D(_gm_);
	DragY=Y_-gp[0];
	DragX=X_-gp[1];
	inDragMode=1;
}

function _DDstop()
{
	_gm_=$c("menu"+DragLayer);
	_gm_.style.zIndex=_zi;
	if(!resetFollowScrollers&&_m[DragLayer][19])_m[DragLayer][19]=Y_-_sT-(gp[2]/2)
	inDragMode=0;
}

function _IdM()
{
	if(inDragMode)
	{
		var g=$c($O+DragLayer);
		$_E(g,Y_-DragY,X_-DragX);
		if(ie55){
			g=$c("iFM"+_m[DragLayer].ifr);
			if(g)$_E(g,Y_-DragY,X_-DragX)
		}
		return 0;
	}	
}
