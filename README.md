<p align="center"><img src="https://static.wixstatic.com/media/398e64_130aa93e4a2648af8adf8ad0a98a3a7c~mv2.png/v1/fill/w_223,h_120,al_c,q_85,usm_0.66_1.00_0.01,enc_auto/Plus%20Cinza.png" width="200" alt="Plus Logo"></p>

## Sobre

Este algoritmo é um middleware de notificação por WhatsApp. O intuito é periodicamente disparar uma mensagem de notificação para o WhatsApp dos clientes agendados. A notificação possui dois botões de possíveis respostas, independente da opção seleciona, o recurso deve retornar uma mensagem automática de acordo com o que foi escolhido pelo usuário do WhatsApp. Envio de mídias (imagens, vídeos/GIF, áudio) é respondido com uma mensagem automática que mostra que identificou o conteúdo, mas não pode entender, ex.: "Ainda não posso entender áudios.".

Mensagens de textos aleatórias recebidas serão classificadas como cliente ou não. O algoritmo tentará encontrar o contato da mensagem na base de dados de clientes e, se encontrar, o classifica como cliente e retorna uma mensagem automática com o contato e nome da unidade do cliente. Caso contrário, a mensagem é automática informando um link para ele procurar a unidade mais próxima.

O recurso deve sempre respeitar as seguintes regras:

- Unidades inadimplentes não terão clientes notificados nem respondidos pelo recurso.
- O número usado para notificação irá recusar imediatamente as tentativas de ligação.
- As notificações poderão representar uma empresa específica, mas o recurso de notificação é do sistema Plus e pode corresponder aos demais clientes.

## Propriedade

O recurso funciona com acesso à base de dados do [Plus Intelligence](https://www.plusintelligence.com.br/) e quaisquer alteração de rotas ou consultas deve ser alinhado com o mesmo.


<p align="center">
PLUS INTELLIGENCE
</p>
