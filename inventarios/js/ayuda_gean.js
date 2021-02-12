// Este codigo se ejecuta una vez, cuando la pagina termina de cargar
$(function() {
    /* Cada vez que el usuario escriba algo en uno de los input con la clase
    clonable_text_input, llamamos a la funcion handle_keypress, esto incluye
    a los nuevos text input que generemos porque ellos tambien van a ser de
    la clase clonable_text_input */
    $('input.clonable_text_input').live('keypress', handle_keypress)
})

/* Esta funcion es llamada cuando el usuario escribe algo en uno de los input
con la clase clonable_text_input */
function handle_keypress(event) {
    // Si la tecla que apreto fue Enter
    if (event.keyCode == 13)
        // Creamos una variable con el nuevo input y la clase correcta (para que
        // tambien pesque cuando en ese nuevo apretemos algo)
        var new_input = $('<input type="text" class="clonable_text_input" />')
        // Y lo colocamos en el div correspondiente que definimos en el HTML
        $('#text_inputs_container').append(new_input)
}
