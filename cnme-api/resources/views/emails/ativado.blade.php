Olá <strong>{{ $nome }}</strong>

<p>
O polo CNME {{ $unidade->nome }} está <b>ATIVADO</b>. Vocês estão prontos a aptos a fazer o 
melhor uso da plataforma. 
O polo foi ativado em {{ $data_fim }}. 
@if ($responsavel && $responsavel->isEmpresa())
    A empresa {{ $responsavel->nome }} foi a reponsável pela ativação.
@else
    
@endif

</p>

O polo está equipado com o kit:
<ul>
    @foreach ($equipamentos as $e)
        <li>{{ $e->nome }}
        @if (isset($e->requisitos))
            <small>(Requisitos: {{$e->requisitos}})</small>
        @endif
        </li>
    @endforeach
</ul>

<p>
    Acesse a <a href="{{ $APP_URL }}">Plataforma CNME</a> e siga monitorando 
    o seu centro de mídia.
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