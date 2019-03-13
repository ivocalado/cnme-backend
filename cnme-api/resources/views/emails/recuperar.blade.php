Olá <strong>{{ $usuario->nome }}</strong>

<p>
O projeto de implantação do polo CNME {{ $unidade->nome }} foi <b>RETOMADO</b>.
O projeto retornou ao status de <b>{{ $projeto->status }}</b>. 
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