<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InboxController extends Controller
{
    private $inbox_id;

    private $token = 'b73cb8d4a9d1e83fac54016f0545309a';

    private $header_accept = 'application/json';

    public function __construct()
    {
        $response = Http::withToken($this->token)->get('https://mailtrap.io/api/v1/inboxes');

        if ($response->successful()) 
            return $this->inbox_id = $response[0]['id'];            
       
    }

    public function index()
    {      
        $messages = Self::get_message();
        return view('admin.inbox.index')->with(['messages' => $messages, 'content' => null, 'id' => null]);
    }

    public function show($id)
    {   
              
        return    Self::to_read($id);
        $messages = Self::get_message();

        $response = Http::accept($this->header_accept)
                        ->withToken($this->token)
                        ->get("https://mailtrap.io/api/v1/inboxes/{$this->inbox_id}/messages/{$id}/body.htmlsource");

      
                           
        return view('admin.inbox.index')->with(['messages' => $messages, 'content' => $response,  'id' => $id])->render();
    }

    public function destroy($id)
    {
        Http::withToken($this->token)
                ->delete("https://mailtrap.io/api/v1/inboxes/{$this->inbox_id}/messages/{$id}");

        return redirect()->route('admin.inbox');
    }

    public function to_read($id)
    {     
       return Http::withToken($this->token)
                    ->patch('https://mailtrap.io/api/v1/inboxes/1342219/messages/2814292996');
    }

    public function get_message()
    {
        return Http::accept($this->header_accept)
                                ->withToken($this->token)
                                ->get("https://mailtrap.io/api/v1/inboxes/{$this->inbox_id}/messages")
                                ->collect();
    }

    
    
}
