<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SolicitacaoCnme;

class SolicitacaoProjetoController extends Controller
{
  
    public function index()
    {
        return response()->json(
            SolicitacaoCnme::paginate(25)
        );
    }

   
    public function store(Request $request)
    {
        
    }

    
    public function show($id)
    {

    }

   
    public function update(Request $request, $id)
    {
        
    }

    
    public function destroy($id)
    {
        
    }
}
