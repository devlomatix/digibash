<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use App\Events\Post\PostPublishEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Datatables;
Use Alert;
class PostController extends Controller
{

    public function index(Request $request)
    {
        //$posts = Post::orderby('created_at','asc')->with('author','categories')->get();
        //dd($posts);

        if ($request->ajax()) {
            $posts = Post::orderby('created_at','asc')->with('author','categories')->get();
            
            return Datatables::of($posts)
                ->editColumn('created_at',function(Post $post){
                    return $post->created_at->diffForHumans();
                })
                ->addColumn('author',function($post){
                    return $post->author->name;
                })
                ->addColumn('category',function($post){
                    $categories = $post->categories;
                    //return $categories;
                    $cat = '';
                 
                    if($categories){
                        foreach($categories as $category){
                           $cat = $cat. '<div class="badge badge-info mr-1" >'. $category->name .'</div>';
                        };
                    }
                  
                    return $cat;
                })
                ->addColumn('status',function($post){
                    if($post->status == 'published'){
                        return '<div class="badge badge-success">Published</div>';
                    }else{
                        return '<div class="badge badge-warning">Draft</div>';
                    }
                })             
                ->addColumn('action',function($data){
                    //$link = '<a href="" class="action-link" id="'.$data->id.'">View</a>';
                    //$link .= '<a href="#edit" class="action-link" data-toggle="modal" id="'.$data->id.'">Edit</a>';
                    //$link .= '<a href="" class="action-link" id="'.$data->id.'">Delete</a>';
                    //"<div class='d-flex'>"
                        //$link = '<button class="btn btn-warning btn-xs dt-action-btn d-flex">Edit</button>';
                        //$link .= '<button class="btn btn-danger btn-xs dt-action-btn">Delete</button>';
                    //"</div>"

                    $link = '<div class="d-flex">'.
                                '<a href="#" class="btn btn-default btn-xs mg-r-10 dt-action-btn">view</a>'.
                                '<a href="'.route('post.edit',$data->id).'" class="btn btn-default edit btn-xs mg-r-10 dt-action-btn">Edit</a>'.
                                '<a href="javascript:void(0);" id="'.$data->id.'" class="btn btn-default edit btn-xs mg-r-10 dt-action-btn btn-del delete">Delete</a>'.
                            '</div>';

                    return $link;
                })
                ->rawColumns(['category','action','status'])
                ->make(true);
        }

        return view('admin.pages.post.post')->with('success', 'The Message');

        //return response()->json($posts);
    }

    public function create()
    {
        
        $categories = Category::where('parent_id','<>', 0 )->orderby('created_at','desc')->get();
        return view('admin.pages.post.post_new',compact('categories'));
        //return response()->json($posts);
    }

    public function store(Request $request)
    {   
       
        $validate = $request->validate([
            'title' => 'required|unique:posts,title',
            'feature_image' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048'

        ]);
        
        $post = new Post;
        $post->user_id = Auth::user()->id;
        $post->title = $request->title;
        $post->slug = Str::slug($request->title,'-');
        $post->description = $request->description;
        $post->body = $request->body;

        if($request->status == 'on'){
            $post->status = 'published';
        }else{
            $post->status = 'draft';
        }
        
        
        if($file = $request->file('feature_image')){
            //$image =  $request->file('feature_image');
            $image = $request->file('feature_image');
            $image_name = time().$image->getClientOriginalName();
            $img = Image::make($image);

            $destinationPath = public_path().'/uploads/images/';
            $img->save($destinationPath.$image_name);

            $thumbnail_Image = $img->resize(150,150);
            $thumbnailPath = public_path().'/uploads/thumbnails/';
            $thumbnail_Image->save($thumbnailPath.$image_name);

            $post->image_url = '/uploads/images/'. $image_name;
    
        }
        

        $post->save();
        
        //Categoty Saving
        if(!$request->categories){          
           $post->categories()->sync([$this->defaultCategory()]);
        }else{
           $post->categories()->sync($request->categories);
        }

        //Saving Tags
        $tagIds = [];
        if($request->tags){

            $tags = $request->tags;
            foreach($tags as $tag){

                $ntag = Tag::firstOrCreate(['name'=>$tag,'slug'=>str_slug( $tag)]);
                if($tag)
                {
                    $tagIds[] = $ntag->id;
                }
            }
        }
        $post->tags()->sync($tagIds);

        if($post){
            event(new PostPublishEvent($request));
        }
        

        $posts = Post::latest()->get();
        //return view('admin.pages.post.post',compact('posts'))->with('success','Post created successfully');
        return redirect() ->route('post.index')
        ->with([
           'message'    =>'Post Created Successfully',
           'alert-type' => 'success',
      ]);

    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return response()->json($post);
    }

    public function edit($id)
    {
        $post = Post::findOrFail($id);
        $categories = Category::where('parent_id','<>', 0 )->orderby('created_at','desc')->get();
        $tags = Tag::orderby('created_at','desc')->get();
        return view('admin.pages.post.post_edit',compact('post','categories','tags'));
    }

    public function update(Request $request, $id)
    {

        /*$validate = $request->validate([
            'title' => 'required|unique:posts,title',
            'feature_image' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);*/

        $post = Post::findOrFail($id);
        $post->user_id = Auth::user()->id;
        $post->title = $request->title;
        $post->slug = Str::slug($request->title,'-');
        $post->description = $request->description;
        $post->body = $request->body;

        if($request->status == 'on'){
            $post->status = 'published';
        }else{
            $post->status = 'draft';
        }
        
        
        if($file = $request->file('feature_image')){
            //$image =  $request->file('feature_image');
            $image = $request->file('feature_image');
            $image_name = time().$image->getClientOriginalName();
            $img = Image::make($image);

            $destinationPath = public_path().'/uploads/images/';
            $img->save($destinationPath.$image_name);

            $thumbnail_Image = $img->resize(150,150);
            $thumbnailPath = public_path().'/uploads/thumbnails/';
            $thumbnail_Image->save($thumbnailPath.$image_name);

            $post->image_url = '/uploads/images/'. $image_name;
    
        }
        

        $post->save();
        
        //Categoty Saving
        if(!$request->categories){          
           $post->categories()->sync([$this->defaultCategory()]);
        }else{
           $post->categories()->sync($request->categories);
        }

        //Saving Tags
        $tagIds = [];
        if($request->tags){

            $tags = $request->tags;
            foreach($tags as $tag){

                $ntag = Tag::firstOrCreate(['name'=>$tag,'slug'=>str_slug( $tag)]);
                if($tag)
                {
                    $tagIds[] = $ntag->id;
                }
            }
        }
        $post->tags()->sync($tagIds);

        if($post){
            event(new PostPublishEvent($request));
        }
        
        return redirect() ->route('post.index')
        ->with([
            'message'    =>'Post Updated Successfully',
            'alert-type' => 'success',
        ]);
 
    }

    public function destroy($id)
    {
        //return 'delete';
        Post::destroy($id);

        //return response()->json(null, 204);
        return redirect() ->route('post.index')
        ->with([
            'message'    =>'Post Deleted Successfully',
            'alert-type' => 'success',
        ]);
 
    }

    public function defaultCategory(){
        $category = Category::where('slug','uncategorized')->first();
        if(!$category){
            $category = new Category;
            $category->name = 'Uncategorized';
            $category->slug = 'uncategorized';
            $category->save();

        }
        return $category->id;
    }
}