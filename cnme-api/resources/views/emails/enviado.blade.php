Olá <strong>{{ $nome }}</strong>

<p>
Seu projeto CNME do polo {{ $unidade->nome }} está em andamento. 
Equipamentos foram <b>ENVIADOS</b> em {{ $data_inicio }}. 
A empresa {{$responsavel->nome}} é a reponsável
pelo transporte. A data máxima prevista de chegada é {{ $data_fim_prevista }}
</p>

Estão sendo enviados:
<ul>
    @foreach ($equipamentos as $e)
        <li>{{$e->nome}}</li>
    @endforeach
</ul>

<p>
    @if ($numero)
        O número do rastreio é <strong>{{$numero}}</strong>.
    @endif

    @if ($link_externo)
        O endereço da transportadora é <a href="{{$link_externo}}">{{$link_externo}}</a>.
    @endif
</p>

<p>
    Acesse a <a href="{{$APP_URL}}">Plataforma CNME</a>
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