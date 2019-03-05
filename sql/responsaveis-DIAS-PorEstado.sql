SELECT empresa, estado, status_tarefa, count(*) as total,
	avg(prazo_total) as prazo_medio,
	avg(dias_atrasos) as atraso_medio
FROM
(	
SELECT 
u.nome as empresa, 
p.id as ṕrojeto_cnme_id,
u2.nome as polo,
es.nome as estado,
m.nome as municipio,
CASE 
	WHEN t.data_fim_prevista > now() and t.data_fim is null THEN 'DENTRO DO PRAZO' 
	WHEN t.data_fim_prevista = CURRENT_DATE and t.data_fim is null THEN 'VENCE HOJE'
	WHEN t.data_fim_prevista < now() and t.data_fim is null THEN 'ATRASADA'
	WHEN t.data_fim <= t.data_fim_prevista THEN 'CONCLUIDA'
	WHEN t.data_fim > t.data_fim_prevista THEN 'CONCLUIDA COM ATRASO'
END AS status_tarefa,
EXTRACT(DAY FROM AGE(t.data_fim_prevista,t.data_inicio)) as prazo_total,
CASE 
	WHEN t.data_fim_prevista < now() and t.data_fim is null  THEN EXTRACT(DAY FROM AGE(t.data_fim_prevista))
	WHEN t.data_fim is not null THEN  EXTRACT(DAY FROM AGE(t.data_fim, t.data_fim_prevista))
ELSE 0 
END dias_atrasos,
e.id as etapa_id,
t.id as tarefa_id,
t.descricao,
t.numero,
t.link_externo,
t.data_inicio_prevista,
t.data_fim_prevista,
t.data_inicio,
t.data_fim
FROM tarefas t
INNER JOIN unidades u on u.id = t.unidade_responsavel_id
INNER JOIN localidades l on l.id = u.localidade_id
INNER JOIN estados es on es.id =  l.estado_id
INNER JOIN municipios m on m.id = l.municipio_id
INNER JOIN etapas e on e.id = t.etapa_id and e.tipo = 'ENVIO'
INNER JOIN projeto_cnmes p on p.id = e.projeto_cnme_id
INNER JOIN unidades u2 on u2.id = p.unidade_id
) as t
GROUP BY empresa, estado, status_tarefa
