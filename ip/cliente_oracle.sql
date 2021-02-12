SELECT 
'005' as compania,--COPEP DEL PERU S.A.C  
'001' as sucursal,--001 es valor prederminado
cb.c_bpartner_id as id_cliente,
'01' as tipo_cliente,
trim(cb.taxid) as ruc,
cb.name as razon_social,
'1111111111' as numero_descuento,
cb.created,
'no tiene dirrecion' as dirrecion,
'no tiene referencia' as refe_dirrecion,
'123456789' as fijo,
'123456789' as celular,
'no tiene email' as refe_dirrecion

FROM c_bpartner cb  limit 10;