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
            $contato = $this->obj_Factory->formatWhatsApp($agendamento->celular);

            $nome = explode(" ", $agendamento->pessoa)[0];
            $nome_intuitivo = strtoupper($nome . substr($nome, -1) . substr($nome, -1));

            $unidade = explode("- ", $agendamento->unidade)[1];

            $horario = explode(" ", $agendamento->inicio);
            $horario = explode(":", $horario[1]);
            $horario = $horario[0] . ":" . $horario[1];

            $mensagem = "Olá, *" . $nome_intuitivo . "*!! 🤩 Faltam $antecendencia horas para sua sessão acontecer <3.\n";
            $mensagem .= "Por gentileza, anote aí! Estamos te esperando na *Mais Top Estética ( $unidade)* às $horario horas.\n\n";
            $mensagem .= "Temos 10 minutos de tolerância para te esperar, mas é bom já ir ficando de jeito!!\n\n";
            $mensagem .= "Ahh, não sei se já fez isso, mas... salva nosso número aí 😁. Obrigadaa!";

            array_push($disparos, array(
                "phone" => $contato,
                "message" => $mensagem
            ));
        }

        return $disparos;
    }


    /**
     * Executar envio de Mensagem
     */
    public function dispararMensagem($dados)
    {
        foreach($dados as $mensagem){
            // $curl = curl_init();

            // curl_setopt_array($curl, array(
            // CURLOPT_URL => "https://api.plugzapi.com.br/instances/SUA_INSTANCIA/token/SEU_TOKEN/send-text",
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_ENCODING => "",
            // CURLOPT_TIMEOUT => 30,
            // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // CURLOPT_CUSTOMREQUEST => "POST",
            // CURLOPT_POSTFIELDS => json_encode($mensagem),
            // CURLOPT_HTTPHEADER => array(
            //     "content-type: application/json"
            // ),
            // ));

            // curl_exec($curl);
            // curl_close($curl);

            // $response = curl_exec($curl);
            // $err = curl_error($curl);

            echo json_encode($mensagem);
        }
    }
    
}



