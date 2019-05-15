Olá <strong>{{ $responsavel->name }}</strong>

<p>
    Foi criado um chamado na plataforma <a href="{{$APP_URL}}">CNME</a>, com o título
    <em>{{$chamado->assunto}}</em>. 
    <br/>
    Você está como responsável por esse chamado, o avalie mais rápido possível.
    <br/>
    O chamado foi criado pelo usuário {{$chamado->usuario->name}} do polo {{$chamado->usuario->unidade->nome}} através do sistema
    em {{$chamado->created_at}}.

    Para mais detalhes acesse o sistema através do link abaixo.
    <p>
        <a href="{{$APP_URL}}">Plataforma CNME</a>
    </p>
</p>

Atenciosamente<br/>
--<br/>
CNME - Centro Nacional de Mídias da Educação<br/>
MEC - Ministério da Educação<br/>
NEES - UFAL
</p>