Olá <strong>{{ $nome }}</strong>

<p>
    Você solicitou a atualização de senha?
    <br/>
    <b>Caso sim</b>, clique no link abaixo e defina uma nova senha.
    <p>
    Acesse a <a href="{{ $APP_URL }}/novasenha/{{$token}}/{{$email}}">Recuperar Senha</a>
    </p>
    Caso o link não funcione, copie o link a seguir no barra de endereço do navegador:
    <p>
        {{ $APP_URL }}/novasenha/validar/{{$email}}/{{$token}}
    </p>
</p>

<b>Caso não tenha solicitado desconsidere essa mensagem.</b>

<p>
    Acesse a <a href="{{ $APP_URL }}">Plataforma CNME</a> e veja mais informações.
</p>

<p>
    <small>
    Atenciosamente<br/>
    --<br/>
    CNME - Centro Nacional de Mídias da Educação<br/>
    MEC - Ministério da Educação<br/>
    NEES - UFAL
    </small>
</p>