Olá <strong>{{ $usuario->name }}</strong>

<p>
Seu projeto CNME do polo {{ $unidade->nome }} está em andamento. 
Todos os equipamentos foram <b>ENVIADOS</b>. Acompanhe as datas previstas de chegada e 
as empresas responsáveis.
</p>
<hr/>

@foreach ($envios as $env)
    <p>
        A empresa {{ $env->unidadeResponsavel->nome }} já está com os equipamentos listados a seguir.
        A operação  foi registrada em {{  date_format(new \DateTime($env->data_inicio),"d/m/Y") }}, 
        a previsão de chegada está para {{  date_format(new \DateTime($env->data_fim_prevista),"d/m/Y") }}.
        <br/>
        @if ($env->numero)
            O número de rastreio da entrega é o <b>{{$env->numero}}</b> e pode se acompanhando no link da 
            transportadora em 
            @if ($env->link_externo)
                <a href="{{$env->link_externo}}">{{$env->link_externo}}</a>
            @else
                <a href="{{$env->unidadeResponsavel->url}}">{{$env->unidadeResponsavel->url}}</a>
            @endif.
        @endif

        <ul>
            @foreach ($env->equipamentosProjetos->pluck('equipamento') as $eq)
                <li>{{$eq->nome}}</li>
            @endforeach
        </ul>
    </p>
    <hr/>
@endforeach


<p>
    Acesse a <a href="{{ $APP_URL }}">Plataforma CNME</a> e siga monitorando o 
    processo de implantação.
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