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

            $this->notificarEmail($status);

        }
        catch(Exception $e) {
            echo "Erro(#receberStatus): " . $e;
        }
    }


    /**
     * Notificar por e-mail sobre status de conexao ou desconexao
     */
    public function notificarEmail($status)
    {
        try{
            $hora = date('d/m/Y H:i:s');

            $listaEmails = [
                'guilherme.inovacao@maistopestetica.com.br'
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

