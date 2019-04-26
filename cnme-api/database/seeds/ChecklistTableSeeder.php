<?php

use Illuminate\Database\Seeder;
use App\Models\Checklist;
use App\Services\UsuarioService;

class ChecklistTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usuarioService = new UsuarioService();

        $admin = $usuarioService->admin();
        Checklist::create([
            "versao" => "Checklist v001",
            "descricao" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Neque vitae tempus quam pellentesque nec nam aliquam. Sit amet mattis vulputate enim nulla aliquet porttitor lacus. Integer enim neque volutpat ac tincidunt. Orci sagittis eu volutpat odio facilisis mauris sit amet massa. Augue neque gravida in fermentum et sollicitudin ac orci. Elit at imperdiet dui accumsan sit. Tellus in hac habitasse platea dictumst vestibulum. Turpis egestas integer eget aliquet nibh praesent. Ac tincidunt vitae semper quis lectus nulla at volutpat. Molestie a iaculis at erat pellentesque adipiscing commodo elit. Arcu cursus vitae congue mauris rhoncus aenean. In tellus integer feugiat scelerisque varius. Turpis massa tincidunt dui ut. Felis imperdiet proin fermentum leo vel orci porta non pulvinar. Quis hendrerit dolor magna eget est lorem ipsum dolor. Cursus risus at ultrices mi tempus imperdiet nulla malesuada pellentesque. Id interdum velit laoreet id donec ultrices.

            Odio aenean sed adipiscing diam donec adipiscing tristique risus nec. Massa sapien faucibus et molestie ac. Fusce id velit ut tortor pretium. Tortor posuere ac ut consequat semper viverra. Viverra nibh cras pulvinar mattis nunc sed. Feugiat sed lectus vestibulum mattis ullamcorper velit. Viverra ipsum nunc aliquet bibendum. Volutpat consequat mauris nunc congue nisi. Quisque egestas diam in arcu. Purus in massa tempor nec feugiat nisl. Nullam ac tortor vitae purus faucibus ornare suspendisse sed. Sapien pellentesque habitant morbi tristique senectus et netus et malesuada. At auctor urna nunc id cursus metus aliquam. Libero enim sed faucibus turpis in.
            
            Est pellentesque elit ullamcorper dignissim cras tincidunt lobortis feugiat vivamus. Eu mi bibendum neque egestas congue quisque egestas diam. Sagittis orci a scelerisque purus. Diam sollicitudin tempor id eu nisl nunc. Sem fringilla ut morbi tincidunt augue interdum velit. Etiam erat velit scelerisque in dictum non consectetur. Placerat in egestas erat imperdiet sed euismod nisi. Integer eget aliquet nibh praesent tristique magna. Tristique et egestas quis ipsum suspendisse ultrices gravida. Nibh tellus molestie nunc non. Dignissim enim sit amet venenatis. Suspendisse faucibus interdum posuere lorem ipsum dolor sit. Nunc non blandit massa enim.
            
            Facilisis magna etiam tempor orci eu lobortis elementum nibh. Neque gravida in fermentum et sollicitudin ac. Sed libero enim sed faucibus turpis in eu mi. Dictumst quisque sagittis purus sit amet volutpat consequat mauris nunc. Ornare arcu dui vivamus arcu. Habitasse platea dictumst vestibulum rhoncus est. Sed augue lacus viverra vitae congue. Risus pretium quam vulputate dignissim suspendisse. Eu turpis egestas pretium aenean pharetra. Dignissim enim sit amet venenatis. Sit amet mattis vulputate enim nulla aliquet porttitor. Et sollicitudin ac orci phasellus egestas. Tortor at auctor urna nunc id cursus metus aliquam. Eu consequat ac felis donec et. Quis imperdiet massa tincidunt nunc pulvinar sapien. Urna cursus eget nunc scelerisque.",
            "usuario_id" => $admin->id
        ]);
    }
}
