Olá <strong>{{ $nome }}</strong>

<p>
Seu projeto CNME do polo {{ $unidade->nome }} está em andamento. 
Equipamentos foram <b>ENTREGUES</b> em {{ $data_fim }}. 
</p>

Os equipamentos previstos nessa entrega são:
<ul>
    @foreach ($equipamentos as $e)
        <li>{{$e->nome}}</li>
    @endforeach
</ul>

@if ($pendentes)
    <p>
        Há outras entregas em andamento. Fique atento ao monirotamento do projeto.
    </p>
@endif

<p>
    Acesse a <a href="{{$APP_URL}}">Plataforma CNME</a>, adiante acompanhe a instalação desses
    equipamentos.
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