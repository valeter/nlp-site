function syncList(){} 
syncList.prototype.sync = function()
{
	for (var i=0; i < arguments.length; i++)	document.getElementById(arguments[i]).onchange = (function (o,id1){return function(){o._sync(id1);};})(this, arguments[i]);
}

syncList.prototype._init = function (firstSelectId)
{
	for (key in this.dataList || null) if (key!=firstSelectId) document.getElementById(key).selectedIndex=0;
};

syncList.prototype._sync = function (firstSelectId)
{
	//for (key in this.dataList || null) if (key!=firstSelectId) document.getElementById(key).selectedIndex=0;
	document.getElementById(firstSelectId).form.submit();
};
