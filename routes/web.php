<?php

use App\Http\Controllers\Mensagem;
use App\Http\Controllers\Webhook;
use App\Http\Controllers\WebhookStatus;
use App\Mail\EmailNotificacao;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rota da busca de mensagens a ser disparadas 1 dia antes
Route::get('/mensagens', [Mensagem::class, 'index']);

// Rota de notificacao X horas antes a fim de lembrar o cliente
Route::get('/lembrete', [Mensagem::class, 'lembretes']);

// Rota para receber o disparo de webhook de mensagens recebidas
Route::post('/webhook/recebidos', [Webhook::class, 'mensagemRecebida']);

// Rota para receber o disparo de webhook de Conexao/Desconexao da sessao
Route::post('/webhook/status', [WebhookStatus::class, 'receberStatus']);
