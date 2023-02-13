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
        try {
            // Ex.: Se a data é 2022-09-08 10:40:00, resultado sera 10
            $hora_atual = explode(":", explode(" ", date("Y-m-d H:i:s"))[1])[0];
            $horario_limite = $hora_atual + $horas;

            $SQL = "SELECT C.nome AS pessoa, C.celular, A.inicio, F.nome AS unidade, F.id, F.celular AS contato FROM agenda_evento A
                    INNER JOIN clientes C ON C.id = A.cliente
                    INNER JOIN franquias F ON F.id = A.unidade
                    WHERE A.`data` = DATE(NOW()) AND HOUR(A.inicio) = ? AND C.cod_empresa = 2
                    ORDER BY A.inicio ASC;";
            
            $resul = DB::select($SQL, [$horario_limite]);
            
            return $resul;
        }
        catch(PDOException $e) {
            echo "Erro(#buscarDados_agendamentos).";
        }
    }
}
