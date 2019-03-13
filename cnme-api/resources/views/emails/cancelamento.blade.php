Olá <strong>{{ $usuario->nome }}</strong>

<p>
O projeto de implantação de polo CNME {{ $unidade->nome }} foi <b>CANCELADO</b>. 
</p>

@if (isset($projeto->descricao))
    <p>{{ $projeto->descricao }}</p>
@endif

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