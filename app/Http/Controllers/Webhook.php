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


    /**
     * Recebe qualquer mensagem, seja de resposta a uma mensagem disparada ou de uma nova conversa.
     */
    public function mensagemRecebida(Request $request)
    {
        // IF PARA IGNORAR STICKERS E REACOES
        if(!isset($request->sticker) && !isset($request->reaction) && $request->isGroup == False){

                // SE A MENSAGEM FOR UM DOS BOTOES DE OPCAO SELECIONADO
                if(isset($request->buttonsResponseMessage)){
                    $idBotao = $request->input("buttonsResponseMessage");
                    $arrayDisparoResposta = $this->criarRespostaMensagemOpcoes(
                        [$request->input("phone"), $idBotao["buttonId"]]
                    );
                } else {
                    // IDENTIFICAR O TIPO DA MENSAGEM, PADRAO = TEXTO
                    $tipo = "texto";
                    $tipo = isset($request->video) ? "video" : $tipo;
                    $tipo = isset($request->image) ? "image" : $tipo;
                    $tipo = isset($request->audio) ? "audio" : $tipo;

                    // SE O CONTATO DO USUARIO EXISTIR NA TABELA DE CLIENTES
                    if($contato = $this->identificarMensagem($request->input("phone"))){
                        $arrayDisparoResposta = $this->criarRespostaMensagem($contato, $tipo);
                    } else {
                        // SE NAO ESTIVER CADASTRADO COMO CLIENTE
                        $arrayDisparoResposta = $this->criarRespostaMensagem(
                            array(False, False, False, $request->input("phone")),
                            $tipo,
                            False
                        );
                    }
                }

            $this->objMensagem->dispararMensagem($arrayDisparoResposta);
        }
    }


    /**
     * Identificar se mensagem recebida é de cliente e quais são seus dados.
     */
    public function identificarMensagem($dado)
    {
        $final_contato = substr($dado, -4);  // PEGAR OS 4 ULTIMOS DIGITOS (PORQUE NAO TEM FORMATACO DE DDD, HIFEN, OU ESPACO)

        // BUSCAR NO BD OS CLIENTES COM NUMEROS COM FINAL IGUAL
        $rs = array();
        try {
            $SQL = "SELECT C.celular, C.nome AS pessoa, F.nome AS unidade, N.contato_atendimento AS contato
                    FROM clientes C
                    INNER JOIN franquias F ON F.id = C.unidade
                    INNER JOIN notificacao_unidades N ON N.id_unidade = C.unidade
                    WHERE C.celular LIKE '%$final_contato' AND F.flg_pendente_pagto = 'N'";
            
            $resul = DB::select($SQL);
        }
        catch(PDOException $e) {
            return False;
        }

        // PERCORRER OS RESULTADOS, FORMATANDO O WHATSAPP E COMPRANDO COM O CONTATO.
        foreach($resul AS $cliente){
            if($this->objFactory->formatWhatsApp($cliente->celular) == $dado){
                $nome = explode(" ", $cliente->pessoa);
                return array($cliente->unidade, $cliente->contato, $nome[0], $cliente->celular); // RETORNA SE FOR CLIENTE
            }
        }

        // Caso nao tenha encontrado Numero
        return False;
    }


    /**
     * Responder mensagem recebida
     */
    public function criarRespostaMensagem($dados, $tipo, $cliente = True)
    {
        $disparo = array();

        // VARIACAO DE MENSAGEM PARA RESPONDER QUEM EH CLIENTE
        $padroes_para_clientes = array(
            "Entendi!\n\nUma unidade que pode te ajudar é a Mais Top Estética - *$dados[0]*. Por gentileza, manda mensagem pra unidade 😉\n\nNesse número: $dados[1].",
            "Você já é cliente na Mais Top Estética - *$dados[0]*.\n\nPoderia enviar mensagem para essa unidade, por favor❓❗\nO WhatsApp da clínica é (Wpp) $dados[1].",
            "$dados[2], por gentileza, envia mensagem para a Mais Top Estética - *$dados[0]*.\nO WhatsApp da clínica é $dados[1].\n\nEu tenho certeza que essa clínica vai poder te atender e ajudar! 💜",
            "Eii, o WhatsApp da Mais Top Estética - *$dados[0]* é $dados[1].\n\nEnvia suas mensagens nesse número ☝🏼 por gentileza?! <3"
        );

        // VARIACAO DE MENSAGEM PARA RESPONDER QUEM NAO EH CLIENTE
        $padroes_sem_ser_cliente = array(
            "Hmmm! Não posso responder você :-(\n\nMas tenho certeza que alguma das clínicas *Mais Top Estética* pode 😃💜! Encontre a unidades *mais próxima de você*:\nhttps://maistopestetica.com.br/agendamento\n\nObrigada por seu contato 😘",
            "Antes de dar continuidade... Eu não posso responder você :-(\n\nSó que a *Mais Top Estética* tem uma lista ENORME de unidades 🤩, e com certeza uma delas está perto de você e pode te ajudar.\n\nEncontre alguma 👇🏻:\nhttps://maistopestetica.com.br/agendamento\n\nEspero ter ajudado 💜",
            "Muitoo obrigada pelo seu contato 💜. É otimo ter você aqui!!\nPeço desculpas por não poder te ajudar muito, eu estou me desenvolvendo ainda como atendente :(\n\n*Mas uma das clínicas COM CERTEZA poderão te audar.*\nEncontre a que está mais pertinho de você:\nhttps://maistopestetica.com.br/agendamento\n\nA unidade que você selecionar é que vai entrar em contato com você. 😉\nNovamente obrigada!!",
            "Eii, infelizmente não consigo dar continuidade em nossa conversa :(\n\nPor gentileza, encontre uma clínica perto de você e ela vai entrar em contato 👇🏻:\nhttps://maistopestetica.com.br/agendamento\n\nObrigada! 😁"
        );

        // VARIACAO DE MENSAGEM PARA RESPONDER CASO A MENSAGEM RECEBIDA NAO SEJA TEXTO, INDEPENDENTE DE SER CLIENTE OU NAO
        $padrao_tipo_mensagem = array(
            "image" => "Não entendo imagens 😔\nEnvia mensagem de *texto* por favor!",
            "video" => "Não consigo ver vídeos e GIFs, digita um *texto* por gentileza!",
            "audio" => "Ainda não entendo áudios, então... 🔇 rsrs.\nDigita um *texto* por favor."
        );

        $index = rand(0, 3); // ESCOLHE UM INDICE ALEATORIO ENTRE 0 E 3

        // SE FOR TEXTO
        if($tipo == "texto"){
            // ATRIBUI A MENSAGEM QUE ESTA NO INDICE ESCOLHIDO, DO ARRAY PARA CLIENTES OU NAO
            $mensagem = $cliente == True ? $padroes_para_clientes[$index] : $padroes_sem_ser_cliente[$index];
        } else {
            // ATRIBUI A MENSAGEM DE ACORDO COM O TIPO
            $mensagem = $padrao_tipo_mensagem[$tipo];
        }

        array_push($disparo, array(
            "phone" => $dados[3],
            "message" => $mensagem
        ));

        return $disparo;
    }


    /**
     * Criar resposta para as mensagens especificas (das opcoes). Parametro deve ser array [phone, buttonID]
     */
    public function criarRespostaMensagemOpcoes($dados)
    {
        $disparo = array();

        // VARIACAO DE MENSAGEM SE A OPCAO SELECIONADA FOR A QUE O USUARIO NAO SE IDENTIFICA COM O NOME
        $lista_desconhecido = [
            "Ahh, então desculpas!\nEncerrando aqui.",
            "Obrigado pela sua atenção, desculpa o incômodo!\n\nEncerrando aqui.",
            "Okay, desculpas!\nEncerrado."
        ];

        // VARIACAO DE MENSAGEM SE A OPCAO SELECIONADA FOR A QUE CONFIRMA
        $lista_confirmado = [
            "💜 Mais Top agradece!!\nEncerrado.",
            "😃💜 Gratidão!\n\nEncerrado.",
            "Até mais! 😃\nEncerrado"
        ];

        $index = rand(0, 2); // ESCOLHE UM INDICE ENTRE 0 E 2

        array_push($disparo, array(
            "phone" => $dados[0], // PARA QUEM ENVIAR
            "message" => $dados[1] == "1" ? $lista_desconhecido[$index] : $lista_confirmado[$index], // O QUE ENVIAR
            "delayMessage" => 4
        ));

        echo "\n" . \json_encode($disparo) . "\n";

        return $disparo;
    }


    /**
     * Executar Disparo de Resposta da Mensagem recebida
     */
}


