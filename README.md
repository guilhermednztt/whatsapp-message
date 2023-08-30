<h1 align="center">Integração WhatsApp</h1>
<p align="center">:incoming_envelope: Notificações automáticas pelo WhatsApp com interação com o usuário. :iphone:</p>

<b><h3>I. Introdução</h3></b>
<p>O projeto consiste em notificações automáticas pelo WhatsApp. As notificações são disparadas com base em uma regra de negócio de simulação, em que todos os clientes que estão agendados para um atendimento serão notificados 1 dia antes. O recurso aqui desenvolvido também inclui lembretes, que notifica com 4 horas de antecedência.<br>Para isso, foi desenvolvido um cron que executa a cada 1 hora verificando quem precisa receber lembretes e notificação. Um webhook também foi criado para pegar a interação do usuário caso ele responda com mensagem de texto, áudio, selecione um botão de opção da mensagem que foi enviada, entre outros. Interações com os botões de opção da mensagem são registradas no banco de dados, assim é possível que os clientes confirmem ou cancelem pelo WhatsApp sem intervenção humana.</p>

<br>
<b><h3>II. Fluxo de Dados</h3></b>
Abaixo consta a imagem com o fluxo de dados do projeto:
    
![image](https://github.com/guilhermednztt/whatsapp-message/assets/121525620/6bc71cec-4122-445d-9eb3-fcc651143d1e)


<br>
<b><h3>III. Validação</h3></b>
O projeto foi validado em ambiente de produção em um case real. Contudo, esse desenvolvimento é a base de todo o esquema de notificação e interação automática por WhatsApp, pois o código-fonte usado em produção recebeu incrementos de recursos e otimizações, diferenciando-se do que está disponível neste repositório.<br>
A validação em Produção teve as seguintes métricas:
<ul>
    <li>720+ notificações disparadas diariamente.</li>
    <li>4 mensagens trocadas com cada usuário/cliente (em média).</li>
    <li>0% de erro na atualização dos status dos agendamentos no banco de dados.</li>
    <li>2 horas de indisponibilidade por semana (em média).</li>
</ul>
Vale destacar que, a indisponibilidade está associada à desconexão do WhatsApp com o recurso e para esses casos há notificação imediata por e-mail.

<br><br>

<p align="center">
    <b>Laravel, WhatsApp, Notificação, Cron, Webhook.</b><br>
    Guilherme Donizetti - 2023.
</p>
