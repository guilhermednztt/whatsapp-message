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
     * Método para buscar dados de todos agendamentos do dia seguinte e que nao foram notificados.
     * 
     * Por padrao, busca os agendamentos com status AGENDADO.
     */
    public function index()
    {

        try {
            // Ex.: Se a data é 2022-09-08 10:40:00, resultado sera 10
            $hora_atual = date("Y-m-d");
            $amanha =  date('Y-m-d', strtotime("+1 day", strtotime($hora_atual)));

            $SQL = "SELECT A.id AS id_sessao, C.id AS id_cliente, C.nome AS pessoa, C.celular, A.inicio, F.nome AS unidade,
                    F.id, N.link_whatsapp AS contato, N.contato_atendimento AS numero_contato
                    FROM agenda_evento A
                    INNER JOIN clientes C ON C.id = A.cliente
                    INNER JOIN franquias F ON F.id = A.unidade
                    INNER JOIN notificacao_unidades N ON N.id_unidade = F.id
                    WHERE A.`data` = ?
                    AND C.cod_empresa = 2 AND A.`status` IN ('Agendado')
                    AND F.flg_pendente_pagto = 'N' AND F.id NOT IN (1, 2) AND N.flg_whatsapp = 'S'
                    ORDER BY A.inicio ASC;";
            
            $resul = DB::select($SQL, [$amanha]);
            
            return $resul;
        }
        catch(PDOException $e) {
            echo "Erro(#buscarDados_agendamentos).";
        }
    }


    /**
     * Buscar todos os dados de agendamento com status AGENDADO para fazer LEMBRETE
     * 
     * Por padrao, busca os agendamentos das proximas 4 horas para enviar LEMBRETE
     */
    public function index_lembrete($horas = 5)
    {
        $horario_min = [2, 3, 4, 5];

        try {
            // Ex.: Se a data é 2022-09-08 10:40:00, resultado sera 10
            $hora_atual = (int)explode(":", explode(" ", date("Y-m-d H:i:s"))[1])[0];

            if(\in_array($hora_atual, $horario_min)){
                die("Nao notificar cliente às ". $hora_atual . "h. Muito cedo!");
            } elseif ($hora_atual == 6) {
                $horario_limite = $hora_atual + 5;
            } else {
                $horario_limite = $hora_atual + $horas;
            }

            $SQL = "SELECT A.id AS id_sessao, C.id AS id_cliente, C.nome AS pessoa, C.celular, A.inicio, F.nome AS unidade,
                    F.id, N.link_whatsapp AS contato, N.contato_atendimento AS numero_contato
                    FROM agenda_evento A
                    INNER JOIN clientes C ON C.id = A.cliente
                    INNER JOIN franquias F ON F.id = A.unidade
                    INNER JOIN notificacao_unidades N ON N.id_unidade = F.id
                    WHERE A.`data` = DATE(NOW()) AND ";
            
            $SQL .= $hora_atual == 6 ? "HOUR(A.inicio) <= ? " : "HOUR(A.inicio) = ? ";

            $SQL .= "AND C.cod_empresa = 2 AND A.`status` IN ('Confirmado')
                    AND F.flg_pendente_pagto = 'N' AND F.id NOT IN (1, 2) AND N.flg_whatsapp = 'S'
                    ORDER BY A.inicio ASC;";
            
            $resul = DB::select($SQL, [$horario_limite]);
            
            return $resul;
        }
        catch(PDOException $e) {
            echo "Erro(#buscarDados_agendamentos).";
        }
    }


    /**
     * ATUALIZAR STATUS DA AGENDA E CONTROLE DE SALDO CONFORME INTERACAO DO USUARIO
     */
    public function atualizarStatus($dados)
    {
        echo "\nAtualizando...\n";

        try {
            $resul = DB::update($dados["sql"], [$dados['status'], $dados['id_sessao'], $dados['id_cliente']]);
            echo "\n\nResultado Update:\n";
            var_dump($resul);

            if($this->movimentarSaldo(array("status" => $dados['status'], 'id_sessao' => $dados['id_sessao']))){
                return True;
            } else {
                return False;
            }
        }
        catch(Exception $e) {
            echo "Erro(#atualizarStatus): " . $e;
            
            return False;
        }
    }


    /**
     * RESTITUIR SALDO DO CLIENTE CASO ELE CANCELE A SESSAO
     */
    public function movimentarSaldo($dados)
    {
        $SQL = "UPDATE saldo_item SET flg_status = ?, tms_atualizacao = NOW() WHERE id_agendamento = ?";

        if($dados['status'] == 'Confirmado'){
            $dados['status'] = 'A';
        }
        else if($dados['status'] == 'Cancelado'){
            $dados['status'] = 'D';
        }
        else {
            return True;
        }

        try {
            $resul = DB::update($SQL, [$dados['status'], $dados['id_sessao']]);
            echo "\n\nResultado Update - Saldo:\n";
            var_dump($resul);

            return True;
        }
        catch(Exception $e) {
            echo "Erro(#atualizarStatus): " . $e;
            
            return False;
        }
    }
}
