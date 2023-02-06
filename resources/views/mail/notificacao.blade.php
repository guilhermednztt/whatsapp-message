<!DOCTYPE html>
<html>

<body>
    <br><br>
        <h1>WhatsApp <b>{{ $statusSessao }}</b></h1>
        <p>Houve uma alteração no status da sua sessão do WhatsApp, os detalhes estão na tabela abaixo:</p>
        <table border='1' style='border-collapse: collapse;'>
            <tr>
                <td><b>Status</b></td>
                <td><b>Hora</b></td>
            </tr>
            <tr>
                <td>{{ $statusSessao }}</td>
                <td>{{ $hora }}</td>
            </tr>
        </table>

        <br><p>Para alterar, acesse o <a href=''>painel de conexão</a>.</p>

</body>

</html>
