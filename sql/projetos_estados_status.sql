﻿SELECT estado, uf, 
sum(total_criado) as total_criado,
sum(total_planejamento) as total_planejamento,
sum(total_enviado) as total_enviado,
sum(total_entregue) as total_entregue,
sum(total_instalado) as total_instalado,
sum(total_finalizado) as total_finalizado
FROM
	(SELECT 
	estado, 
	uf,
	CASE WHEN status = 'CRIADO' THEN SUM(total) ELSE 0 END total_criado,
	CASE WHEN status = 'PLANEJAMENTO' THEN SUM(total) ELSE 0 END total_planejamento,
	CASE WHEN status = 'ENVIADO' THEN SUM(total) ELSE 0 END total_enviado,
	CASE WHEN status = 'ENTREGUE' THEN SUM(total) ELSE 0 END total_entregue,
	CASE WHEN status = 'INSTALADO' THEN SUM(total) ELSE 0 END total_instalado,
	CASE WHEN status = 'FINALIZADO' THEN SUM(total) ELSE 0 END total_finalizado
	FROM
	 (select e.nome estado, e.sigla as uf, p.status,count(*) as total from estados e
		left join localidades l on l.estado_id = e.id
		left join unidades u on u.localidade_id = l.id
		left join projeto_cnmes p on p.unidade_id = u.id
	group by e.nome, e.sigla, p.status
	order by e.sigla) t
	GROUP BY estado, uf, status) t2

GROUP BY estado, uf
ORDER BY uf