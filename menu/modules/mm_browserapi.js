/*
   Milonic Web Browser API Module mm_browserapi.js version 0.7 - July 29 2009
   
   Contains AJAX Load and Save modules.

   Copyright 2007 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.
   Open Source and Non-Profit Licenses are available on request.
*/

function mm_ajax(){}
var _mO=new Object
var _oA=[];
mm_ajax.prototype={
	version:"1.0",
	errorReporting:0,
	getAjaxObj:function()
	{ 
		var o
		try{o=new XMLHttpRequest()}
		catch(e){
			try{o=new ActiveXObject("Msxml2.XMLHTTP")}
			catch(e){o=new ActiveXObject("Microsoft.XMLHTTP")}
		}
		return o;
	},	
	stateChanged:function()
	{
		if(_mO.o.readyState==4||_mO.o.readyState=="complete")
		{
			if(_mO.o.status>=200 && _mO.o.status<300)
			{
				if(typeof(_mO.func)+" "=="function "){
					eval(_mO.func(_mO.o))
				}
				else
				{
					for(var c in _mO.func){
						if(c==_mO.p)_mO.func[c]=_mO.o.responseText
					}
				}
			}
			else
			{
				if(_mO.t.errorReporting)alert("Error: Return status: "+_mO.o.status)
			}
		}
	},
	load:function()
	{
		var g=arguments
		_mO.func=g[1];
		_mO.o=this.getAjaxObj();
		_mO.p=g[2];
		_mO.t=this
		if(!g[0])
		{
			if(!this.url)
			{
				if(this.errorReporting)alert("No URL Specified")
				return
			}
			g[0]=this.url
			if(this.queryString)g[0]+="?"+this.queryString
		}
		if(!g[1])
		{
			if(!this.action)
			{
				if(this.errorReporting)alert("No Action Specified")
				return
			}
			_mO.func=this.action;
		}
		if(_mO.o)
		{
			_mO.o.onreadystatechange=this.stateChanged
			_mO.o.open("GET",g[0],true)

			_mO.o.setRequestHeader("Pragma","no-cache");
			_mO.o.setRequestHeader("Cache-control","no-cache");			

			_mO.o.send(null)
		}

	},
	upload:function()
	{
		_mO.o=this.getAjaxObj();
		_mO.o.onreadystatechange=this.stateChanged
		_mO.o.open("POST",this.url,true)
		_mO.o.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		_mO.o.setRequestHeader("Content-length", this.content.length);
		_mO.o.setRequestHeader("Connection", "close");
		_mO.o.send(this.content);
	}
}

function _maxm(f){
	
	var AM=$h("ajaxLoading")
	if(AM>-1){
		_m[AM].tooltip=1;
		oi=_itemRef
		popup("ajaxLoading","el"+oi);
		_itemRef=oi
	}	
	
	_AJMnuTo=$P(_W._AJMnuTo);
	if(!_lDd){
		_AJMnuTo=_StO(function(){_maxm(f)},50);
		return
	}
	
	_ajM=f.substr(5,f.length);
	var MA=_m[_m.length-1]
	MA.ajax=new mm_ajax();
	MA.ajax.errorReporting=true;
	MA.ajax.url=_ajM;
	MA.i=_itemRef
	_mi[MA.i].ajaxMenu=f
	MA.ajax.action=function(e){
		var i,M,T;
		eval(e.responseText);
		i=MA.i
		M=_m.length-1;
		_mi[i][3]=_m[M][1];
		T=$h(_mi[i][3]);
		if(!$c("menu"+T))createNewMenu(M); else M=T;
		_ofMT=1;
		if(_itemRef==i)h$(i);
	}
	MA.ajax.load();
}
