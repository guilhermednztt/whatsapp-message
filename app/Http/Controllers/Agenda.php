<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

date_default_timezone_set("America/Sao_Paulo");
/**
 * CLASSE PARA OS METODOS RELACIONADOS A AGENDA
 */
class Agenda extends Controller
{

    /**
     * Método para buscar dados de todos agendamentos das proximas X horas e que nao foram notificados.
     * 
     * Por padrao, busca os agendamentos das proximas 4 horas.
     */
    public function index($horas = 4)
    {
        $horario_min = [2, 3, 4, 5];

        try {
            // Ex.: Se a data é 2022-09-08 10:40:00, resultado sera 10
            $hora_atual = (int)explode(":", explode(" ", date("Y-m-d H:i:s"))[1])[0];

            if(\in_array($hora_atual, $horario_min)){
                die("Nao notificar cliente às ". $hora_atual . "h. Muito cedo!");
            } elseif ($hora_atual == 6) {
                $horario_limite = $hora_atual + 4;
            } else {
                $horario_limite = $hora_atual + $horas;
            }

            $SQL = "SELECT C.nome AS pessoa, C.celular, A.inicio, F.nome AS unidade, F.id, N.contato_atendimento AS contato
                    FROM agenda_evento A
                    INNER JOIN clientes C ON C.id = A.cliente
                    INNER JOIN franquias F ON F.id = A.unidade
                    INNER JOIN notificacao_unidades N ON N.id_unidade = F.id
                    WHERE A.`data` = DATE(NOW()) AND ";
            
            $SQL .= $hora_atual == 6 ? "HOUR(A.inicio) <= ? " : "HOUR(A.inicio) = ? ";

            $SQL .= "AND C.cod_empresa = 2 AND A.`status` IN ('Agendado', 'Confirmado')
                    AND F.flg_pendente_pagto = 'N' AND F.id NOT IN (1, 2) AND N.flg_whatsapp = 'S'
                    ORDER BY A.inicio ASC;";
            
            $resul = DB::select($SQL, [$horario_limite]);
            
            return $resul;
        }
        catch(PDOException $e) {
            echo "Erro(#buscarDados_agendamentos).";
        }
    }
}
