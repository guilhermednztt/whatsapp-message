<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Factory;
use App\Http\Controllers\Mensagem;

/**
 * CLASSE PARA OS METODOS DO WEBHOOK DE RECEBIDOS E ENVIADOS
 */
class webhook
{

    public function __construct()
    {
        $this->objFactory = new Factory();
        $this->objMensagem = new Mensagem();
    }


    public function mensagemRecebida(Request $request)
    {
        if($contato = $this->identificarMensagem($request->input("phone"))){
            $arrayDisparoResposta = $this->criarRespostaMensagem($contato);
        } else {
            $arrayDisparoResposta = $this->criarRespostaMensagem([False, False, False, $request->input("phone")], False);
        }

        $this->objMensagem->dispararMensagem($arrayDisparoResposta);
    }


    /**
     * Identificar se mensagem recebida é de cliente e quais são seus dados.
     */
    public function identificarMensagem($dado)
    {
        $final_contato = substr($dado, -4);

        $rs = array();

        try {
            $SQL = "SELECT C.celular, C.nome AS pessoa, F.nome AS unidade, F.celular AS contato
                    FROM clientes C
                    INNER JOIN franquias F ON F.id = C.unidade
                    WHERE C.celular LIKE '%$final_contato'";
            
            $resul = DB::select($SQL);
        }
        catch(PDOException $e) {
            return False;
        }

        foreach($resul AS $cliente){
            if($this->objFactory->formatWhatsApp($cliente->celular) == $dado){
                $nome = explode(" ", $cliente->pessoa);
                return array($cliente->unidade, $cliente->contato, $nome[0], $cliente->celular);
            }
        }

        // Caso nao tenha encontrado Numero
        return False;
    }


    /**
     * Responder mensagem recebida
     */
    public function criarRespostaMensagem($dados, $cliente = True)
    {
        $disparo = array();

        $padroes_para_clientes = array(
            "Entendi!\n\nUma unidade que pode te ajudar é a Mais Top Estética - *$dados[0]*. Por gentileza, manda mensagem pra unidade 😉\n\nNesse número: $dados[1].",
            "Você já é cliente na Mais Top Estética - *$dados[0]*.\n\nPoderia enviar mensagem para essa unidade, por favor❓❗\nO WhatsApp da clínica é (Wpp) $dados[1].",
            "$dados[2], por gentileza, envia mensagem para a Mais Top Estética - *$dados[0]*.\nO WhatsApp da clínica é $dados[1].\n\nEu tenho certeza que essa clínica vai poder te atender e ajudar! 💜",
            "Eii, o WhatsApp da Mais Top Estética - *$dados[0]* é $dados[1].\n\nEnvia suas mensagens nesse número ☝🏼 por gentileza?! <3"
        );

        $padroes_sem_ser_cliente = array(
            "Hmmm! Não posso responder você :-(\n\nMas tenho certeza que alguma das clínicas *Mais Top Estética* pode 😃💜! Encontre a unidades *mais próxima de você*:\nhttps://maistopestetica.com.br/agendamento\n\nObrigada por seu contato 😘",
            "Antes de dar continuidade... Eu não posso responder você :-(\n\nSó que a *Mais Top Estética* tem uma lista ENORME de unidades 🤩, e com certeza uma delas está perto de você e pode te ajudar.\n\nEncontre a mais próxima de você 👇🏻:\nhttps://maistopestetica.com.br/agendamento\n\nEspero ter ajudado 💜",
            "Muitoo obrigada pelo seu contato 💜. É otimo ter você aqui!!\nPeço desculpas por não poder te ajudar muito, eu estou me desenvolvendo ainda como atendente :(\n\n*Mas uma das clínicas COM CERTEZA poderão te audar.*\nEncontre a que está mais pertinho de você:\nhttps://maistopestetica.com.br/agendamento\n\nA unidade que você selecionar é que vai entrar em contato com você. 😉\nNovamente obrigada!!",
            "Eii, infelizmente não consigo dar continuidade em nossa conversa :(\n\nPor gentileza, encontre a clínica mais próxima de você e ela vai entrar em contato 👇🏻:\nhttps://maistopestetica.com.br/agendamento\n\nObrigada! 😁"
        );

        $index = rand(0, 3);
        $mensagem = $cliente == True ? $padroes_para_clientes[$index] : $padroes_sem_ser_cliente[$index];

        array_push($disparo, array(
            "phone" => $dados[3],
            "message" => $mensagem
        ));

        return $disparo;
    }


    /**
     * Executar Disparo de Resposta da Mensagem recebida
     */
}


