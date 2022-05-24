<?php
namespace App\Http\Traits;


use Carbon\Carbon;

trait DateTimeFormat 
{ 

  public function createdAtDate()
  {     
     $date = siteSettings()->date_format;
     return Carbon::parse($this->attributes['created_at'])->format($date);
  }

  public function updatedAtDate()
  {  
     $date = siteSettings()->date_format;
     return Carbon::parse($this->attributes['updated_at'])->format($date);
  }

  public function createdAtTime()
  {   
     $time = siteSettings()->date_format;
     return Carbon::parse($this->attributes['created_at'])->format($time);
  }

  public function updatedAtTime()
  {    
     $time = siteSettings()->time_format;
     return Carbon::parse($this->attributes['updated_at'])->format($time);
  }
  public function dateFormat($date)
  {
      $dateformat = siteSettings()->date_format;
      return Carbon::parse($date)->format($dateformat);
  }

  public function timeFormat($date)
  {
      $time = siteSettings()->time_format;
      return Carbon::parse($date)->format($time);
  }



  



}