Olá <strong>{{ $responsavel->name }}</strong>

<p>
    O chamado <em>{{$chamado->assunto}} ({{$chamado->id}}) </em> foi atualizado pelo usuário 
    {{$usuario->name}} do(a) {{$chamado->usuario->unidade->nome}} através do sistema
    em {{$chamado->updated_at}}.
    <br/>
    @if ($comment->isAuto())
        Alterações:
        <div class="changes">
            <ul>
            @foreach ($messages as $m)
                <li>{{$m}}</li>
            @endforeach
            </ul>
        </div>
    @else
        <div class="comment">
            {{$comment->content}}
        </div>
    @endif
    

    <br/>
    Você está como responsável por esse chamado, o avalie mais rápido possível.
    <br/>

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