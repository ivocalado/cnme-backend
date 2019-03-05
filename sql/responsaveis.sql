/*
Total por empresa em quantidade mas sem o desempenho
*/

SELECT responsavel, 
	SUM(total) as total, 
	SUM(total_envio) as total_envio, 
	SUM(total_instalacao) as total_instalacao, 
	SUM(total_ativacao) as total_ativacao
FROM
(SELECT responsavel, 
CASE WHEN tipo = 'ENVIO' then sum(total) end total_envio,
CASE WHEN tipo = 'INSTALACAO' then sum(total) end total_instalacao,
CASE WHEN tipo = 'ATIVACAO' then sum(total) end total_ativacao,
sum(total) as total
FROM (SELECT u.nome responsavel, e.tipo, count(*) as total FROM tarefas t
INNER JOIN unidades u on u.id = t.unidade_responsavel_id
INNER JOIN etapas e on e.id = t.etapa_id
GROUP BY u.nome, e.tipo) as t1
GROUP BY responsavel, tipo) as t2
GROUP BY responsavel