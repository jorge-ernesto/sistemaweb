<?php

function combitonumerico($num_ini,$num_fin,$defecto)
{
	$var_ind_null=0;
	for ($num_ini; $num_ini<=$num_fin;$num_ini++)
		{
			if($num_ini==$defecto)
			{
				echo "<option value='".$num_ini."' selected>".$num_ini."</option>";
				//VARIABLE QUE INDICA SI EL COMBO TIENE UNA OPCION POR DEFECTO
				$var_ind_null++;
			}
			else
			{
				echo "<option value='".$num_ini."'>".$num_ini."</option>";
			}
		}
	if($var_ind_null==0)
	{
		echo "<option value='' selected> </option>";
	}
}

function combito_biopcional($defecto,$data1,$data2,$etiqueta1,$etiqueta2)
{
	if($data1==$defecto)
	{
		echo "<option value='".$data1."' selected>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
	}
	elseif($data2==$defecto)
	{
		echo "<option value='".$data1."'>".$etiqueta1."</option>";
		echo "<option value='".$data2."' selected>".$etiqueta2."</option>";
	}
	else
	{
		echo "<option value='' selected> </option>";
		echo "<option value='".$data1."' selected>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
	}
}

function combito_triopcional($defecto,$data1,$data2,$data3,$etiqueta1,$etiqueta2,$etiqueta3)
{
	if($data1==$defecto)
	{
		echo "<option value='".$data1."' selected>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
		echo "<option value='".$data3."'>".$etiqueta3."</option>";
	}
	elseif($data2==$defecto)
	{
		echo "<option value='".$data1."'>".$etiqueta1."</option>";
		echo "<option value='".$data2."' selected>".$etiqueta2."</option>";
		echo "<option value='".$data3."'>".$etiqueta3."</option>";
	}
	elseif($data3==$defecto)
	{
		echo "<option value='".$data1."'>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
		echo "<option value='".$data3."' selected>".$etiqueta3."</option>";
	}
	else
	{
		echo "<option value='' selected> </option>";
		echo "<option value='".$data1."'>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
		echo "<option value='".$data3."'>".$etiqueta3."</option>";
	}

}

function combito_cuatriopcional($defecto,$data1,$data2,$data3,$data4,$etiqueta1,$etiqueta2,$etiqueta3,$etiqueta4)
{
	if($data1==$defecto)
	{
		echo "<option value='".$data1."' selected>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
		echo "<option value='".$data3."'>".$etiqueta3."</option>";
		echo "<option value='".$data4."'>".$etiqueta4."</option>";
	}
	elseif($data2==$defecto)
	{
		echo "<option value='".$data1."'>".$etiqueta1."</option>";
		echo "<option value='".$data2."' selected>".$etiqueta2."</option>";
		echo "<option value='".$data3."'>".$etiqueta3."</option>";
		echo "<option value='".$data4."'>".$etiqueta4."</option>";
	}
	elseif($data3==$defecto)
	{
		echo "<option value='".$data1."'>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
		echo "<option value='".$data3."' selected>".$etiqueta3."</option>";
		echo "<option value='".$data4."'>".$etiqueta4."</option>";
	}
	elseif($data4==$defecto)
	{
		echo "<option value='".$data1."'>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
		echo "<option value='".$data3."'>".$etiqueta3."</option>";
		echo "<option value='".$data4."' selected>".$etiqueta4."</option>";
	}
	else
	{
		echo "<option value='' selected> </option>";
		echo "<option value='".$data1."'>".$etiqueta1."</option>";
		echo "<option value='".$data2."'>".$etiqueta2."</option>";
		echo "<option value='".$data3."'>".$etiqueta3."</option>";
		echo "<option value='".$data4."'>".$etiqueta4."</option>";
	}

}
