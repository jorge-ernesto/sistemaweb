var MIN_LENGTH = 2;

$(document).ready(function() {

//alert('cargo');

	$("#keyword").keyup(function() {
		var keyword = $("#keyword").val();

		if (keyword.length >= MIN_LENGTH) {

			$.ajax({
                        	type: "POST",
                        	url: "/sistemaweb/combustibles/reportes/c_descuentos_especiales.php",
                        	data: { accion:'Search', keyword:keyword},
				success:function(data){

				$('#results').html('');

				var json = $.parseJSON(data);
$(results).each(function(key, value) {
alert(value);
					$('#results').append('<div class="item">' + value + '</div>');
				})
/*$(json).each(function(i,val){
    $.each(val,function(k,v){
          console.log(k+" : "+ v);     
});

/*$(json).each(function(key, value) {
console.log(value);
					})
/*
					$('.item').click(function() {
				    		var text = $(this).html();
				    		$('#keyword').val(text);
				    	})
*/
					
				}

			});

		} else {
			$('#results').html('');
		}

	});

	$("#keyword").blur(function(){
    		$("#results").fadeOut(500);
    	})

        .focus(function() {		
    	    $("#results").show();
    	});

});
