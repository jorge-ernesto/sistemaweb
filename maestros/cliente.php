<html>
<head>
<TITLE>jQuery AJAX Autocomplete - Country Example</TITLE>
<head>
<style>
#country-list{float:left;list-style:none;margin:0;padding:0;width:190px;}
#country-list li{padding: 10px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
#country-list li:hover{background:#F0F0F0;}
#search-box{padding: 8px;border: #F0F0F0 1px solid;}
</style>
<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
<script>
$(document).ready(function(){
	$("#search-box").keyup(function(){
		$.ajax({
			type: "POST",
			url: "autocomplete_clientes.php",
			data:'keyword='+$(this).val(),
			beforeSend: function(){
				$("#search-box").css("background","#FFF url(/sistemaweb/icons/loader.gif) no-repeat 165px");
			},
			success: function(data){
				$("#suggesstion-box").show();
				$("#suggesstion-box").html(data);
				$("#search-box").css("background","#FFF");
			}
		});
	});
});

function selectCountry(val) {
	$("#search-box").val(val);
	$("#suggesstion-box").hide();
}

</script>
</head>
<body>
<div class="frmSearch">
<input type="text" id="search-box" placeholder="Country Name" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();"/>
<div id="suggesstion-box"></div>
</div>
</body>
</html>
