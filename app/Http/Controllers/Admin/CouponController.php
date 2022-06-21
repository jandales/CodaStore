<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Services\CouponServices;
use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Models\CouponRestrictionProduct;
use App\Http\Requests\UpdateCouponRequest;


class CouponController extends Controller
{
    private $services;

    public function __construct(CouponServices $services)
    {
        

        $this->services = $services;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {     
        return view('admin.coupons.index')->with('coupons', Coupon::paginate(10));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CouponRequest $request)
    { 
        $this->services->store($request);  

        return redirect()->route('admin.coupons')->with('success', 'Coupon successfully Created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $coupon)
    {
        return  view('admin.coupons.show')->with('coupon', $coupon);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {    
        return  view('admin.coupons.edit')->with('coupon', $coupon);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
            
       $this->services->update($request, $coupon);

       return redirect()->route('admin.coupons')->with('success', 'Coupon successfully updated');
    } 

    /**
     * Remove the specified resource from storage.
     *
     * @param  Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coupon $coupon)
    {   
        $this->authorize('delete', $coupon);
        
        $coupon->delete();

        return back()->with('success','Coupon successfully deleted');        
    }

    public function destroySelectedItem(Request $request)
    {
        $this->services->destroySelectedItem($request);

        return back()->with('success', 'Coupon successfully deleted');
    }

    public function search(Request $request)
    {        
        $coupons = Coupon::Search($request->keyword)->paginate(10);
        
        return view('admin.coupons.index')->with(['coupons' => $coupons, 'keyword' => $request->keyword]);
    }

public function findByNameSku(Request $request)
    {
        $keyword = $request->keyword;
        $product = Coupon::where('name',$keyword)->orWhere('sku', $keyword)->first();
        return response()->json(['product' => $product]);
    }
    

   
}
