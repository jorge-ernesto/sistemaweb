function agrega_ceros(campo)
{
   var campo_long = campo.value.length;
   var campo_value = campo.value;
   if(campo_long > 0 && campo_long<=13)
   {
      switch (campo_long)
      {
         case 1:
         ceros = '000000000000';
         break;
         case 2:
         ceros = '00000000000';
         break;

         case 3:
         ceros = '0000000000';
         break;

         case 4:
         ceros = '000000000';
         break;

         case 5:
         ceros = '00000000';
         break;

         case 6:
         ceros = '0000000';
         break;

         default:
         ceros = '';
         break;
               
      }
      
   }else if(campo_long > 13)
   {
	    ceros = '';
   }
   campo.value = ceros + campo_value;
}
function agrega_ceros_prov(campo)
{
   var campo_long = campo.value.length;
   var campo_value = campo.value;
   if(campo_long > 0 && campo_long<=6)
   {
      switch (campo_long)
      {
         case 1:
         ceros = '00000';
         break;
         case 2:
         ceros = '0000';
         break;

         case 3:
         ceros = '000';
         break;

         case 4:
         ceros = '00';
         break;

         case 5:
         ceros = '0';
         break;

         default:
         ceros = '';
         break;
               
      }
      
   }else if(campo_long > 0 && campo_long > 6)
   {
	    ceros = '';
   }
   campo.value = ceros + campo_value;
}