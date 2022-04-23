<?php
namespace App\Http\Traits;


use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;

trait DateAndTimeFormat 
{
 
  protected $newDateFormat;
  protected $newTimeFormat;

  public function __construct()
  {
      $setting = GeneralSetting::find(1);   
      $this->newDateFormat = $setting->date_format;
      $this->newTimeFormat = $setting->time_format;
  }

  public function createdAtDate()
  {
     $date =  $this->attributes['created_at'];
     return Carbon::parse($date)->format($this->newDateFormat);
  }

  public function updatedAtDate()
  {
     $date = $this->attributes['updated_at'];
     return Carbon::parse($date)->format($this->newDateFormat);

  }

  public function createdAtTime()
  {
     $date =  $this->attributes['created_at'];
     return Carbon::parse($date)->format($this->newTimeFormat);
  }

  public function updatedAtTime()
  {
     $date =  $this->attributes['updated_at'];
     return Carbon::parse($date)->format($this->newTimeFormat);
  }

  public function dateFormat($date)
  {       
     return Carbon::parse($date)->format($this->newDateFormat);
  }

  public function timeFormat($time)
  {    
     return Carbon::parse($time)->format($this->newTimeFormat);
  }

  



}