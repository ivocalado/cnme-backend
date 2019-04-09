<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CommentResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{

    public function addComment(Request $request, $commentType, $commentableId ){
        try{
            $commentType = "App\\Models\\".ucfirst($commentType);
            if(class_exists($commentType)){
                $comment = new Comment();
                $comment->build($request->content,
                            Auth::user(),
                            $commentType,
                            $commentableId
                        );

                $comment->save();

                return new CommentResource($comment);
            }else{
                return response()->json(
                    array('message' => 'Classe '.$commentType.' não existe no modelo.') , 422);
            }

        }catch(\Exception $e){
            return response()->json(
                array('message' => $e->getMessage()) , 500);
        }
    }

    public function comments($commentType, $commentableId ){


        $commentType = "App\\Models\\".ucfirst($commentType);

        if(class_exists($commentType)){
            $comments = Comment::where([
                ['comment_type', '=', $commentType ],
                ['comment_id', '=', $commentableId ]
            ])->get();
        }else{
            return response()->json(
                array('message' => 'Classe '.$commentType.' não existe no modelo.') , 422);
        }
       

        return CommentResource::collection($comments);
    }

    public function update(Request $request, $id){
        $comment = Comment::find($id);

        if($comment && isset($request->content)){

            $comment->content = $request->content;
            $comment->save();
            return new CommentResource($comment);
        }else{
            return response()->json(
                array('message' => 'Comentário não existe e/ou [content] não informado.') , 422);
        }
    }


    public function destroy($id){
        DB::beginTransaction();

        try {

            $comment = Comment::find($id);

            if(isset($comment)){
                $comment->delete();
                DB::commit();
                return response(null,204);
            }else{
                return response()->json(
                    array('message' => 'Comentário não encontrado.') , 404);
            }

        }catch(\Exception $e){
            DB::rollback();

            Log::error('CommentController::destroy - '.$e->getMessage());

            return response()->json(
                array('message' => $e->getMessage()) , 500);

        }
    }
}










