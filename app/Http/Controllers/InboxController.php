<?php

namespace App\Http\Controllers;

use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InboxController extends Controller
{
    private $inbox_id;

    private $token = 'b73cb8d4a9d1e83fac54016f0545309a';

    private $header_accept = 'application/json';

    public function __construct()
    {       
        $this->authorizeResource(Inbox::class, 'inbox');

        $response = Http::withToken($this->token)->get('https://mailtrap.io/api/v1/inboxes');

        if ($response->successful()) 

            return $this->inbox_id = $response[0]['id'];            
       
    }

    public function index()
    {     
      
        $messages = Self::get_message();

        return view('admin.inbox.index')->with(['messages' => $messages,'inbox' => null, 'content' => null, 'id' => null]);
    }

    public function view($id)
    {   
              
        $inbox = Self::update($id);

        $messages = Self::get_message();

        $response = Http::accept($this->header_accept)
                        ->withToken($this->token)
                        ->get("https://mailtrap.io/api/v1/inboxes/{$this->inbox_id}/messages/{$id}/body.htmlsource");      
                           //body.txt body.raw body.htmlsource body.html
        return view('admin.inbox.index')->with(['messages' => $messages, 'inbox' => $inbox, 'content' => $response,  'id' => $id])->render();
    }

    public function destroy($id)
    {
        Http::withToken($this->token)
                ->delete("https://mailtrap.io/api/v1/inboxes/{$this->inbox_id}/messages/{$id}");

        return redirect()->route('admin.inbox')->with(['success' => 'Successfuly deleted']);
    }

    public function update($id, $is_read = true)
    {
        
        return Http::withHeaders([
                            'Content-Type' => 'application/json',
                        ]) 
                        ->withToken($this->token)
                        ->patch("https://mailtrap.io/api/v1/inboxes/{$this->inbox_id}/messages/{$id}",["message" => ["is_read" => $is_read] ])->json();
    }

    public function unread($id)
    {
         Self::update($id, false);

         return redirect()->route('admin.inbox')->with(['success' => 'Successfuly deleted']);
    }

    public function get_message()
    {
        return Http::accept($this->header_accept)
                    ->withToken($this->token)
                    ->get("https://mailtrap.io/api/v1/inboxes/{$this->inbox_id}/messages")
                    ->collect();
    }

    
    
}
