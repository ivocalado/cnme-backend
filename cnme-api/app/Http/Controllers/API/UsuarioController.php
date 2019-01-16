<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;

class UsuarioController extends Controller
{
    
    public function index()
    {
        return UserResource::collection(User::paginate(25));
    }

    
    public function store(Request $request)
    {
        //
    }

   
    public function show($id)
    {
        return new UserResource(User::find($id));
    }

    
    public function update(Request $request, $id)
    {
        //
    }

    
    public function destroy($id)
    {
        //
    }
}
