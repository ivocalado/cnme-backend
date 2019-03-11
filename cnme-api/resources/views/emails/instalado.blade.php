Olá <strong>{{ $nome }}</strong>

<p>
Seu projeto CNME do polo {{ $unidade->nome }} está em andamento. 
Equipamentos foram <b>INSTALADOS</b> em {{ $data_fim }}. 
@if ($responsavel && $responsavel->isEmpresa())
    A empresa {{ $responsavel->nome }} foi a reponsável pela instalação.
@else
    
@endif

</p>

Foram instalados os equipamentos abaixo:
<ul>
    @foreach ($equipamentos as $e)
        <li>{{ $e->nome }}</li>
    @endforeach
</ul>

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