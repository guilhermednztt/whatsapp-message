<?php

namespace App\Http\Controllers;

\date_default_timezone_set('America/Sao_Paulo');

/**
 * CLASSE COM METODOS DE USO COMUM ENTRE AS CLASSES PARA COISAS GENERICAS.
 */
class Factory
{

    /**
     * Remover os caracteres especiais do celular, deixar apenas os algarismos.
     */
    public function formatWhatsApp($dado)
    {
        return preg_replace("/[^0-9]/", "", $dado);
    }

    /**
     * Retornar data e hora no formato brasileiro
     */
    public function formatDataHora()
    {
        return date('d/m/Y H:i:s');
    }
}



