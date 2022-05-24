<?php
namespace App\Http\Traits;


use Illuminate\Support\Facades\Crypt;

trait Crypted
{ 
    public function encryptedId()
    {       
        return Crypt::encrypt($this->attributes['id']);
    }

    public function decryptedId()
    {
        return Crypt::decrypt($this->attributes['id']);
    }

    public function resolveRouteBinding($encryptedId, $field = null)
    {
        return $this->where('id', decrypt($encryptedId))->firstOrFail();
    }

}