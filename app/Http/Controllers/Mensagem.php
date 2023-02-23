<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Agenda;
use App\Http\Controllers\Factory;

/**
 * CLASSE COM METODOS REFERENTES AS MENSAGENS DO WHATSAPP
 */
class Mensagem
{

    public function __construct()
    {
        $this->obj_Factory = new Factory();
    }

    /**
     * Criar lista de mensagem com os numeros a qual deve ser enviado a notificacao.
     */
    public function index()
    {
        $objAgenda = new Agenda();
        $dados = $objAgenda->index(4);

        if(\is_null($dados) || $dados == False){
            return \response(array(
                'status' => 'erro',
                'detalhe' => 'A busca retornou nenhum dado'
            ), 417);
        }

        $disparos = $this->formatarMensagem($dados);

        $this->dispararMensagem($disparos);
        // return response($disparos);
    }


    /**
     * Formatar as mensagens de notificacao a serem enviadas.
     */
    public function formatarMensagem($dados, $antecendencia = 4)
    {
        $disparos = array();

        foreach($dados AS $agendamento){

            //---  FORMATACOES
            $contato = $this->obj_Factory->formatWhatsApp($agendamento->celular); // APENAS NUMEROS, COM 55 NA FRENTE
            $nome = explode(" ", $agendamento->pessoa)[0];
            $nome_intuitivo = strtoupper($nome . substr($nome, -1) . substr($nome, -1)); // NOME MAIUSCULO REPETINDO A ULTIMA LETRA 3 VEZES
            $unidade = explode("- ", $agendamento->unidade)[1]; // NOME DA UNIDADE SEM O CODIGO E O NOME DO FRANQUEADO
            $horario = date("H:i", strtotime($agendamento->inicio)); // APENAS HORA E MINUTO DO AGENDAMENTO

            //--- MENSAGEM
            // CRIA O CONTEUDO DA NOTIFICACAO, SE A UNIDADE TIVER CELULAR VAI SER CONCATENADO.
            $mensagem = "Olá, *" . $nome_intuitivo . "*!! 🤩 Faltam $antecendencia hora(s) para sua sessão acontecer.\n\n";
            $mensagem .= "Por gentileza, anote aí! Estamos te esperando na *Mais Top Estética 💜 ( $unidade)* às $horario horas.\n";
            if($agendamento->contato != '' && !\is_null($agendamento->contato)){
                $mensagem .= "O contato da clínica é: " . $agendamento->contato . ".\n\n";
            } else {
                $mensagem .= "\n";
            }
            $mensagem .= "Temos 10 minutos de tolerância para te esperar, mas é bom já ir ficando de jeito!!\n\n";
            $mensagem .= "Ahh, não sei se já fez isso, mas... salva nosso número aí 😁. Obrigadaa!";

            //--- ESTRUTURA DE DADOS DA NOTIFICACAO
            array_push($disparos, array(
                "phone" => $contato, // PARA QUEM SERA ENVIADO
                "message" => $mensagem, // O QUE SERA ENVIADO
                "buttonList" => array(
                    "buttons" => array(
                        array(
                            "id" => "1",
                            "label" => $nome . "? Não conheço."
                        ),
                        array(
                            "id" => "2",
                            "label" => "😃 Okay, combinado!"
                        )
                    )
                )
            ));
        }

        return $disparos;
    }


    /**
     * Executar envio de Mensagem
     */
    public function dispararMensagem($dados)
    {
        $contTesteDesenvolvimento = 0;
        try{
            foreach($dados as $mensagem){
                $curl = curl_init();

                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.plugzapi.com.br/instances/3B8B65EC02C150785C14865DD3BAD004/token/7C7083ACC020FBC3773CCC34/send-text",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($mensagem),
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/json"
                ),
                ));

                
                $response = curl_exec($curl);
                $err = curl_error($curl);
                
                echo json_encode($mensagem) . "\n\n";
                
                curl_close($curl);
            }
        }
        catch(Exception $e) {
            echo "Erro(#" . __FUNCTION__ . "): " . $e;
        }
    }
    
}



