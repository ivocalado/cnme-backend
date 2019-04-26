<?php

namespace App\Services;

use App\User;

class UsuarioService {

    public function admin(){
        $users = User::where('tipo', User::TIPO_ADMINISTRADOR)->first();
        return $users;
    }
}
