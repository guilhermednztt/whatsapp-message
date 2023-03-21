<?php

namespace App\Http\Controllers;

use DateTime;

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
        return "55" . preg_replace("/[^0-9]/", "", $dado);
    }

    /**
     * Retornar data e hora no formato brasileiro
     */
    public function formatDataHora($padrao = "BR")
    {
        if($padrao == "BR"){
            return date('d/m/Y H:i:s');
        }
        if($padrao == "EUA"){
            return date('Y/m/d H:i:s');
        }
        if($padrao == "DATETIME"){
            return date('Y-m-d H:i:s');
        }
    }

    /**
     * Calcular diferença de Horas e Minutos entre dois horários e retornar uma string.
     */
    public function formatDiferencaHoras($dado)
    {
        $hora_atual = $this->formatDataHora("DATETIME");

        $data1 = new DateTime($dado);
        $data2 = new DateTime($hora_atual);

        $diferenca = $data1->diff($data2);

        $tempo = "";

        if($diferenca->h >= 1){
            $tempo .= $diferenca->h . " horas ";
            if($diferenca->i >= 1){
                $tempo .= " e ";
            }
        }

        if($diferenca->i >= 1) {
            $tempo .= $diferenca->i . " minutos";
        }

        return $tempo;        
    }


    /**
     * CRIAR OPCOES DE RESPOSTAS PARA AS MENSAGENS SE FOR CONFIRMACAO OU LEMBRETE
     */
    public function formatCriarOpcoes($id_cliente, $id_sessao, $modelo, $nome, $contatoclinica, $nomecliente)
    {
        // C => confirmacao
        // L => lembrete

        if($modelo == "L") {
            return array(
                array(
                    "id" => "1#L#" . $id_cliente . "#" . $id_sessao . "#" . $contatoclinica . "#" . $nomecliente, // 1 (confirmacao) + L (lembrete) + ID do cliente + ID agendamento + contato clinica + nome do cliente
                    "label" => "Confirmado 😃"
                ),
                array(
                    "id" => "2#L#" . $id_cliente . "#" . $id_sessao . "#" . $contatoclinica . "#" . $nomecliente, // 2 (cancelamento) + L (lembrete) + ID do cliente + ID agendamento + contato clinica + nome do cliente
                    "label" => "Cancelar 😕"
                )
            );
        }

        if($modelo == "C") {
            return array(
                array(
                    "id" => "1#C#" . $id_cliente . "#" . $id_sessao . "#" . $contatoclinica . "#" . $nomecliente, // 1 (confirmacao) + C (confirmacao) + ID do cliente + ID agendamento + contato clinica + nome do cliente
                    "label" => "CONFIRMADO! 😃"
                ),
                array(
                    "id" => "2#C#" . $id_cliente . "#" . $id_sessao . "#" . $contatoclinica . "#" . $nomecliente, // 2 (cancelamento) + C (confirmacao) + ID do cliente + ID agendamento + contato clinica + nome do cliente
                    "label" => "Cancelar 😕"
                ),
                array(
                    "id" => "3#C#" . $id_cliente . "#" . $id_sessao . "#" . $contatoclinica . "#" . $nomecliente, // 3 (desconhecido) + C (confirmacao) + ID do cliente + ID agendamento + contato clinica + nome do cliente
                    "label" => $nome . "? Não sou eu!"
                )
            );
        }
    }
}



