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
     * Criar lista de mensagem com os numeros a qual deve ser enviado a notificacao 1 dia antes.
     */
    public function index()
    {
        $objAgenda = new Agenda();
        $dados = $objAgenda->index();

        if(\is_null($dados) || $dados == False){
            return \response(array(
                'status' => 'erro',
                'detalhe' => 'A busca retornou nenhum dado'
            ), 417);
        }

        $disparos = $this->formatarMensagem($dados, 24);

        $this->dispararMensagem($disparos);
        // return response($disparos);
    }


    /**
     * Executar disparos dos LEMBRETES
     */
    public function lembretes()
    {
        $objAgenda = new Agenda();
        $dados = $objAgenda->index_lembrete();

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
        $hora_atual = explode(":", explode(" ", date("Y-m-d H:i:s"))[1])[0];

        foreach($dados AS $agendamento){

            //---  FORMATACOES
            $contato = $this->obj_Factory->formatWhatsApp($agendamento->celular); // APENAS NUMEROS, COM 55 NA FRENTE
            $nome = explode(" ", $agendamento->pessoa)[0];
            $nome_intuitivo = strtoupper($nome . substr($nome, -1) . substr($nome, -1)); // NOME MAIUSCULO REPETINDO A ULTIMA LETRA 3 VEZES
            $unidade = explode("- ", $agendamento->unidade)[1]; // NOME DA UNIDADE SEM O CODIGO E O NOME DO FRANQUEADO
            $horario = date("H:i", strtotime($agendamento->inicio)); // APENAS HORA E MINUTO DO AGENDAMENTO
            $dia = date("d/m", strtotime($agendamento->inicio)); // APENAS DIA E MES DO AGENDAMENTO

            //--- MENSAGEM
            // CRIA O CONTEUDO DA NOTIFICACAO, SE A UNIDADE TIVER CELULAR VAI SER CONCATENADO.

            // MENSAGEM DE CONFIRMACAO
            $mensagem = "";
            if($antecendencia == 24) {
                $mensagem .= "Olá, *" . $nome_intuitivo . "*!! 🤩 ";
                $mensagem .= "\n\n*Posso confirmar sua presença AMANHÃ (". $dia ."), às $horario h❓*\n\n";
                $mensagem .= "Te espero na *Mais Top Estética 💜 ( $unidade)*, com 10 minutos de tolerância 😉\n\n";
                $mensagem .= "Lembre-se ⚠️\nApós confirmar sua sessão, o não comparecimento implicará como sessão dada.\n\n";
                $mensagem .= "👉🏼 Para *Confirmar* ou *Cancelar*, use as opções abaixo!\n\n";
            }
            // MENSAGEM DE LEMBRETE
            else {
                $mensagem .= "Oii, *" . $nome . "* 😁\n";
                if($hora_atual == 6){
                    $mensagem .= "Só para te lembrar... Sua sessão é daqui a pouco.\n\n";
                } else {
                    $tempo_antecedente = $this->obj_Factory->formatDiferencaHoras($agendamento->inicio);
                    $mensagem .= "Só para te lembrar... Faltam $tempo_antecedente para sua sessão.\n\n";
                }
                $mensagem .= "Como combinado, vai ser às $horario h, com 10 minutos de tolerância 😉.\n\n";
            }

            // PADRAO PARA TODAS AS MENSAGENS
            if($agendamento->contato != '' && !\is_null($agendamento->contato)){
                $mensagem .= "*Esta mensagem é automática*. Para qualquer caso, o contato da clínica é:\n" . $agendamento->contato . ".\n\n";
            } else {
                $mensagem .= "*Esta mensagem é automática*.\n";
            }

            // CONFIRMACAO
            if($antecendencia == 24) {
                $mensagem .= "😁 Obrigadaa!";
            }
            // LEMBRETE
            else {
                $mensagem .= "🤜🏽🤛🏻 Até logo!";
            }

            $msg_clinica = "*Cliente:* " . $agendamento->pessoa . "\n*Data:* Hoje às " . $horario . "h\n\n";
            $msg_clinica .= "NOTIFICADO";

            //--- OPCOES
            $modelo = $antecendencia == 24 ? "C" : "L";
            $arrayOptions = $this->obj_Factory->formatCriarOpcoes(
                $agendamento->id_cliente, $agendamento->id_sessao, $modelo, $nome, "12974043392", $agendamento->pessoa
            );

            //--- ESTRUTURA DE DADOS DA NOTIFICACAO OU LEMBRETE
            if($antecendencia == 24){
                array_push($disparos, array(
                    "phone" => $contato, // PARA QUEM SERA ENVIADO
                    "message" => $mensagem, // O QUE SERA ENVIADO
                    "buttonList" => array(
                        "buttons" => $arrayOptions
                    )
                ));

            } else {
                array_push($disparos, array(
                    "phone" => $contato, // PARA QUEM SERA ENVIADO
                    "message" => $mensagem, // O QUE SERA ENVIADO
                ));
            }

            //--- CRIAR NOTIFICACAO PARA CLINICA
            array_push($disparos, array(
                "phone" => "12974043392", // $agendamento->numero_contato, // contato da clinica
                "message" => $msg_clinica
            ));
        }

        return $disparos;
    }
    
    /**
     * Executar envio de Mensagem
     */
    public function dispararMensagem($dados)
    {
        $contteste = 0;

        try{
            foreach($dados as $mensagem){
                $contteste++;
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



