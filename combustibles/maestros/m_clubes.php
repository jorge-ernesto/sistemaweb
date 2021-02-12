<?php

class ClubesModel extends Model
{
    function obtenerClubes()
    {
        global $sqlca;

        $sql = "SELECT
                    tab_elemento,
                    tab_descripcion
                FROM
                    int_tabla_general
                WHERE
                        tab_tabla='CLUB'
                    AND tab_elemento!='000000'
                ORDER BY
                    tab_elemento
                ;
                ";
        if ($sqlca->query($sql) < 0) return false;

        $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {
            $a = $sqlca->fetchRow();

            $result[$a[0]] = $a[1];
        }

        return $result;
    }

    function borrar($codigos)
    {
        global $sqlca;

        $sql = "DELETE FROM
                    int_tabla_general
                WHERE
                        tab_tabla='CLUB'
                    AND tab_elemento in (";

        for ($i = 0; $i < count($codigos); $i++) {
            if ($i > 0) $sql .= ",";
            $sql .= "'" . $codigos[$i] . "'";
        }

        $sql .= ")
                 ;";

        if ($sqlca->query($sql) < 0) return false;

        return true;
    }

    function guardar($codigos, $descripciones)
    {
        global $sqlca;

        foreach ($codigos as $i => $codigo) {
            $sql = "UPDATE
                        int_tabla_general
                    SET
                        tab_descripcion='" . pg_escape_string($descripciones[$codigo]) . "'
                    WHERE
                            tab_tabla='CLUB'
                        AND tab_elemento='" . pg_escape_string($codigo) . "'
                    ;
                    ";
            echo $sql;
            if ($sqlca->query($sql) < 0) return false;
        }

        return true;
    }

    function agregar($codigo, $descripcion)
    {
        global $sqlca;

        $sql = "INSERT INTO
                    int_tabla_general
                    (
                        tab_tabla,
                        tab_elemento,
                        tab_descripcion
                    )
                VALUES
                    (
                        'CLUB',
                        '" . pg_escape_string($codigo) . "',
                        '" . pg_escape_string($descripcion) . "'
                    )
                ;
                ";
        if ($sqlca->query($sql) < 0) return false;
        return true;
    }
}

