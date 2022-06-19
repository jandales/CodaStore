<?php
namespace App\Http\Traits;

use App\Models\Photo;
use Illuminate\Http\Request;

trait UpdatePasswordTrait {
  
   public function update(Admin $admin, $password)
   {  
      $admin->password = Hash::make($password);
      $admin->save();
      return $admin;
   }

}