/*
   Milonic DHTML Menu Menu Clock Module  menuclock.js version 1.0 December 5th 2004
   This module is only compatible with the Milonic DHTML Menu version 5.16 or higher

   Copyright 2004 (c) Milonic Solutions Limited. All Rights Reserved.
   This is a commercial software product, please visit http://www.milonic.com/ for more information.
*/

// this module must be placed before the data file in order to configure itself first.
// There are elements inside the datafile that rely on this module being in place before it can execute.


// You just need to change the month and day names to suit your language, please keep the order the same otherwise your dates will be wrong.

monthNames=new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" )
dayNames=new Array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat")

function getMilonicDate()
{
	var today = new Date();
	var _day=today.getDate();
	var _dayNum=today.getDay();
	var _month=today.getMonth();
	var _year=today.getFullYear();
	_time=prefixZero(today.getHours())+":"+prefixZero(today.getMinutes())+":"+prefixZero(today.getSeconds())
	return dayNames[_dayNum]+" "+_day+getNumberSuffix(_day)+" " + monthNames[_month] + " " + _year + "<BR><b>" + _time+"</b>"
}


function getItemByType(_type)
{
	for(_a=0;_a<_mi.length;_a++)
	{
		if(_mi[_a][34]&&(_mi[_a][34]==_type))
		{
			return _a
		}	
	}	
	return -1
}


function getNumberSuffix(_num)
{
	if(_num>=10 && _num<13)return "th"
	_num = _num.toString()
	_endNum=_num.substr(_num.length-1,1)
	_numSuf="th"
	if(_endNum=="1")_numSuf="st"
	if(_endNum=="2")_numSuf="nd"
	if(_endNum=="3")_numSuf="rd"
	return _numSuf
}

function prefixZero(_num)
{
	_num=_num.toString()
	if(_num.length==1)_num= "0"+_num
	return _num
}

function updateMenuClock()
{
	clockItem=getItemByType("clock")
	if(clockItem>-1)
	{
		_gm=gmobj("lnk"+clockItem)
		if(_gm)
		{
			_gm.innerHTML=getMilonicDate()
			_fixMenu(0)
		}
	}
}
setInterval("updateMenuClock()",1000);