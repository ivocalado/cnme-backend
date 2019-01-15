<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UnidadeController extends Controller
{
   
    public function index()
    {
        return 'index';
    }

    
    public function store(Request $request)
    {
        return 'store';
    }

   
    public function show($id)
    {
        return 'show';
    }

    
    public function update(Request $request, $id)
    {
        return 'update';
    }

    
    public function destroy($id)
    {
        return 'destroy';
    }
}
