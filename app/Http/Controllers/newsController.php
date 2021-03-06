<?php

namespace crossover\Http\Controllers;

use Illuminate\Http\Request;

use crossover\News;
use crossover\User;
use Illuminate\Support\Facades\Auth;
use PDF;

class newsController extends Controller
{   
    
    // List Latest News on HomePage
    public function index(){        
        $news = News::orderBy('created_at', 'desc')
               ->take(10)
               ->get();

        return view('index')->with(['latest'=> $news]);        
    }    
    
    //Dashboard start page when loged in
    public function dashboard(){
                
        $email = Auth::user()->email;
        $name = Auth::user()->name;        
        $data = News::where('email', $email)
               ->orderBy('created_at', 'desc')               
               ->paginate(5);        
        return view('home')->with(['data'=> $data,'name'=> $name]);
    }    
    
    
    public function addNews($message = null){
        return view('news.add')->with('message',$message);
    }
    
    //Publish the news
    public function saveNews(Request $request){

        $this->validate($request, [
        'title' => 'required|',
        'text' => 'required',        
        'photo' => 'mimes:jpeg,jpg,png|file|dimensions:min_width=100,min_height=200,max_width=2500,max_height=1000',
        ]);
        
        $news = new News();
        $news->title = $request->input('title');
        $news->fulltext = $request->input('text');
        if ($request->input('summary') != '')
            $news->summary = $request->input('summary');
        else
        {
            //generate summary based on fulltext            
            $originalText = $request->input('text');
            $textSummary = explode(" ",$originalText);
            $tam = count($textSummary);
            
            if ($tam > 20)
            {
                $max = $tam / 2;
                $max = intval($max);
                $newText = "";
                
                if ($max > 100) $max=100;
                
                for($i = 0; $i <= $max; $i++)
                {
                    $newText .= $textSummary[$i]. " ";
                }
                
                $newText = trim($newText);
                $newText .= "...";
                $news->summary = $newText;                
            }   
            else
            {
                $news->summary = $request->input('text');
            }    
            
        }    
        
        $news->email = Auth::user()->email; 
        $news->name = Auth::user()->name;
        $userid = Auth::user()->id;
        
        //Upload
        $photo = $request->file('photo');
        if ($photo != '')
        {
            $destinationPath = "/usr/pagina/crossover/public/uploads/$userid";
            
            if (! file_exists($destinationPath))
            {
                mkdir($destinationPath, "0755");
            }   
            
            $name = $photo->getClientOriginalName();

            date_default_timezone_set("America/Sao_Paulo");
            $newname = date("Y-m-d-h-i-s.");
            $extension = $photo->getClientOriginalExtension();
            $newname .= strtoupper($extension);        

            if ($photo->move($destinationPath,  $newname)){            
                $news->photo = "uploads/$userid/$newname";
            }
        }
        else
        {
            $news->photo = "uploads/nopic.jpg";
        }
        
        try{
            $id = $news->save();           
        }
        catch (Exception $e){
            return $e->message();
        }
          
        return view('news.success');
        
        
    }
    
    //Complete article mode
    public function show($id){
        $news = News::where('id', $id)               
               ->get();
        
        return view('news.info')->with(['news'=> $news]);
        
    }
    
    //Removes selected article
    public function remove(Request $request){
        
        $id = $request->input('id');
        $news = News::find($id);
        
        try{
            $news->delete();
        }
        catch (Exception $e){
            return $e->message();
        }
        
        return redirect()->action('newsController@dashboard');
        
    }
    
}
