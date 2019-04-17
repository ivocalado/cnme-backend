<?php

use Illuminate\Database\Seeder;
use App\Models\ProjetoCnme;
use App\Models\Chamado;
use App\Services\UnidadeService;
use App\Models\Comment;

class ChamadoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $unidadeService = new UnidadeService();
        $tvEescola = $unidadeService->tvescola();
        
        $projetos = ProjetoCnme::all();
        $i = 1;

        foreach($projetos as $p){
            if($p->isAndamento()){


                $chamado = Chamado::create([
                    'assunto' => 'Chamado de Teste('.$i.')', 
                    'descricao' => 'Chamando a por assuntos aleatórios..........',
                    'usuario_id' => $p->unidade->responsavel->id,
                    'projeto_cnme_id' => $p->id,
                    'unidade_responsavel_id' => $tvEescola->id,
                    'usuario_responsavel_id' => $tvEescola->responsavel->id
                ]);

                $comment = new Comment();
                $comment->usuario_id = $p->unidade->responsavel->id;
                
                $comment->content = "Estou no aguando da solução e até agora nada.";
                $comment->comment()->associate($chamado);

                $comment->save();
                
                }
            $i++;
        }
    }
}
