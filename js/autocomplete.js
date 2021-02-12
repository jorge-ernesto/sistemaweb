var MIN_LENGTH = 2;

$(function() {

	$.post("autocomplete.php", { keyword: keyword },function(data){
			console.log(data);
		/*$.each(obj, function(index, element) {
			console.log(element);
		})*/

			},'json');



});
