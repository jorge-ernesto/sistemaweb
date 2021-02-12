function MostarNumeroRecibo(obj){
    var valorcmb=obj.value;
    $.ajax({
        type: "POST",
        url: "control.php",
        data: {
            action:'num_recibo',
            'rqst':'MOVIMIENTOS.REGISTROCAJA',
            'almacen':valorcmb
        },
        success:function(xm){     
            var obj=eval('('+xm+')');  
            $('#recibe_nro').val(obj.dato);
                   
        }
    });
}
;


$(function(){
    
    var availableTags = [
    "ActionScript",
    "AppleScript",
    "Asp",
    "BASIC",
    "C",
    "C++",
    "Clojure",
    "COBOL",
    "ColdFusion",
    "Erlang",
    "Fortran",
    "Groovy",
    "Haskell",
    "Java",
    "JavaScript",
    "Lisp",
    "Perl",
    "PHP",
    "Python",
    "Ruby",
    "Scala",
    "Scheme"
    ];
    
    $( "#id_cliente" ).autocomplete({
        source: availableTags
    });
});