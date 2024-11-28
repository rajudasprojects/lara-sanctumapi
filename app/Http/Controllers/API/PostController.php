<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts'] = Post::all();

        return response()->json([
            'status' => true,
            'message' => 'All Post Data.',
            'data' => $data
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatePost = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,jif'
            ]
        );

        if($validatePost->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validatePost->error->all()
            ],401);
        }

        $img = $request->image;
        $ext = $img->getClientOriginalExtension();
        $newImg = time(). '.' .$ext;
        $img->move(public_path(). '/uploadpost',$newImg);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' =>  $newImg,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Post data inserted successfully.',
            'post' => $post
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['post'] = Post::select(
            'id',
            'title',
            'description',
            'image'
        )->where(['id' => $id])->get();

        return response()->json([
            'status' => true,
            'message' => 'Your Single Post.',
            'data' => $data
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatePost = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,jif'
            ]
        );

        if($validatePost->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validatePost->error->all()
            ],401);
        }

        $postimage = Post::select('id', 'image')->where(['id' => $id])->get();

        if($request->image != ""){
            $path = public_path(). '/uploadpost';

            if($postimage[0]->image != '' && $postimage[0]->image != null){
                $old_file = $path . $postimage[0]->image;

                if(file_exists($old_file)){
                    unlink($old_file);
                }
            }

            $img = $request->image;
            $ext = $img->getClientOriginalExtension();
            $newImg = time(). '.' .$ext;
            $img->move(public_path(). '/uploadpost',$newImg);
        }else{
            $newImg = $postimage->img;
        }



        $post = Post::where(['id' => $id])->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' =>  $newImg,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Post updated successfully.',
            'post' => $post
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $imagePath = Post::select('image')->where('id',$id)->get();
        $filepath = public_path(). '/uploadpost/'. $imagePath[0]['image'];
        unlink($filepath);

        $post = Post::where('id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Your Post has been removed.',
            'post' => $post
        ],200);
    }
}
