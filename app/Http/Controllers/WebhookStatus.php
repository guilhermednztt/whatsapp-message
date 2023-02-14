<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Controllers\Factory;
use App\Mail\EmailNotificacao;

/**
 * CLASSE DE WEBHOOK PARA STATUS, CASO DESCONECTE O WHATSAPP
 */
class WebhookStatus
{


    /**
     * Receber e identificar status da conta WhatsApp
     */
    public function receberStatus(Request $request)
    {
        try{
            $status = "";
             
            if ($request->input("disconnected") == True) {
                $status = "Desconectado";
            }
            if ($request->input("connected") == True) {
                $status = "Conectado";
            }

            $this->notificarEmail($status, $request->input("time"));

        }
        catch(Exception $e) {
            echo "Erro(#receberStatus): " . $e;
        }
    }


    /**
     * Notificar por e-mail sobre status de conexao ou desconexao
     */
    public function notificarEmail($status, $time)
    {
        try{
            $hora = date('d/m/Y H:i:s', strtotime("-3 hour", $time)); // subtrair 3 horas (para o hr de Brasilia)

            $listaEmails = [
                'guilherme.inovacao@maistopestetica.com.br', 'guilhermedonizettiads@gmail.com'
            ];
            
            
            foreach($listaEmails AS $email){
                $objEmail = new EmailNotificacao(
                    $status,
                    $hora
                );

                Mail::to($email)->send($objEmail);
            }
        } catch(Exception $e) {
            echo "Erro(#notificarEmail): " . $e;
        }
    }
}

