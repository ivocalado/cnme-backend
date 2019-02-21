Olá <strong>{{ $nome }}</strong>,

<p>
Bem vindo à plataforma <a href="{{$APP_URL}}">CNME - Centro Nacional de Mídias da Educação.</a>
<br/>
Você foi adicionado(a) ao portal de gestão dos CNME. Seu usuário está associado polo {{$unidade}}.
<br/>
Faça seu primeiro acesso com seu email({{$email}}) e confirme os dados no link abaixo
<p>
    <a href="{{$APP_URL}}/usuarios/confirmar?token={{$token}}">Clique aqui </a>
</p>

<p>
Ou copie o link abaixo na barra de endereço <br/>
{{$APP_URL}}/confirmar?token={{$token}}

</p>
Atenciosamente<br/>
--<br/>
CNME - Centro Nacional de Mídias da Educação
MEC - Ministério da Educação<br/>
NEES - UFAL
</p>