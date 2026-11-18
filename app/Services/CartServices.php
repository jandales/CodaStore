<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Cookie;

class CartServices
{

    public function index()
    {
        return Cart::ByUser()->first();
    }

    public function store(Request  $request, Product $product)
    {

        $attributes = [];
        foreach ($request->input('properties', []) as $item)
        {
            $attributes[$item['name']] = $item['value'];
        }

        // Products with variants: the chosen size / colour has its own stock and price.
        $sku = null;
        if ($product->hasSkus()) {
            $sku = $product->skuFor($attributes);
            if (!$sku) {
                return response()->json(['status' => 422, 'message' => "That option isn't available. Please choose another."], 422);
            }
            if ($sku->isSoldOut()) {
                return response()->json(['status' => 422, 'message' => $sku->label() . ' is sold out. Please choose another option.'], 422);
            }
            $productQuantity = (int) $sku->qty;
        } else {
            $productQuantity = (int) optional($product->stock)->qty;
        }

        $newQuantity = (int)$request->qty;

        if($productQuantity <= 0)  return response()->json(['status' => 422, 'message' => 'This item is sold out.'], 422);

        $cart = Cart::ByUser()->first();

        // Never more in the bag than there is in stock.
        $inBag = $cart ? (int) optional($cart->lineFor($product->id, optional($sku)->id))->qty : 0;
        if ($inBag >= $productQuantity) {
            return response()->json(['status' => 422, 'message' => "You already have all {$productQuantity} in your bag."], 422);
        }
        if($productQuantity - $inBag < $newQuantity) $newQuantity = $productQuantity - $inBag;

        $total = $newQuantity * ($sku ? $sku->effectivePrice() : $product->effectivePrice());

        if($cart)
        {
            // Remember whose bag this is, so a reminder can be sent if it is left behind.
            if (auth()->check()) {
                $cart->forceFill(['user_id' => auth()->id(), 'reminded_at' => null])->save();
            }

            // Each variant is its own line (an M and an L are two lines).
            $item = $cart->lineFor($product->id, optional($sku)->id);

            if($item)
                self::updateCartItem($item, $newQuantity, $attributes);
            else
                self::storeCartItem($cart->id, $product->id, $newQuantity, $attributes, optional($sku)->id);

            Cart::UpdateTotal();

            return response()->json(['count' => Cart::TotalItems()]);


        };

       // stored new cart and item
       $cart = Cart::create([
             'total' => $total,
             'cart_id' =>  Cookie::get('cart-id'),
             'expired_at' => Carbon::now()->addDays(5),
             'user_id' => auth()->id(),
       ]);

       self::storeCartItem($cart->id, $product->id, $newQuantity, $attributes, optional($sku)->id);
       return response()->json(['count' => Cart::TotalItems()]);
    }

    private function updateCartItem(CartItem $cartitem, $qty, $attributes)
    {

        $cartitem->qty += $qty;
        $cartitem->attributes = $attributes;
        $cartitem->save();
        return $cartitem;
    }

    private function storeCartItem($cart, $product, $quantity, $attributes, $skuId = null)
    {
        return CartItem::create([
            'cart_id' => $cart,
            'product_id' => $product,
            'sku_id' => $skuId,
            'qty' => $quantity,
            'attributes' => $attributes,
        ]);
    }


    public function update(Request $request, CartItem $cartitem)
    {
       $cartitem->qty = (integer)$request->quantity;
       $cartitem->save();
       $cart = Cart::UpdateTotal();
       return response()->json([
           'status' => 200,
           'item_subtotal' => $cartitem->subtotal(),
           'items_count' => $cart->totalItems(),
           'subtotal' => $cart->total,
           'grand_total' => $cart->grandTotal()
        ]);

    }

    public function count()
    {
        $cart =  Cart::ByUser()->first();
        $cartItemsCount = $cart == null ? 0 : $cart->items->sum('qty');
        return response()->json(['cartItemsCount' => $cartItemsCount]);
    }

    public function total()
    {
        $cart = Cart::ByUser()->first();
        return response()->json(['grand_total' => $cart->grandTotal()]);
    }

    public function getCart()
    {
        $cart = Cart::ByUser()->first();
        return response()->json(['cart' => $cart]);
    }


    public function couponActivate(Request $request)
    {

        $coupon = Coupon::where('name', $request->coupon_code)->first();

        if($coupon == null) return response()->json(["status" => 500, "message" => "Coupon not Found"]);

        if(Self::validateCoupon($coupon))
        {
            $cart = Cart::ByUser()->first();
            $total = $cart->total;
            $cart->coupon_id = $coupon->id;
            $cart->discount = $coupon->discount($total);
            $cart->save();

            return response()->json([
                'status' => 200,
                'coupon' => $coupon,
                'discount' => $cart->discount,
                'subtotal' => $cart->total,
                'grand_total' => $cart->grandTotal()
        ]);
        }
    }

    public function destroyCartItem(CartItem $item)
    {
        $item->delete();
        Cart::UpdateTotal();
        return response()->json(['status' => 200, 'count' =>  Cart::TotalItems(), 'total' => Cart::Total() ]);
    }

    public function getCoupon()
    {
        $cart = Cart::ByUser()->first();
        $coupon = Coupon::find($cart->coupon_id);
        return $coupon;
    }


    public function couponRemove()
    {
        $cart = Cart::ByUser()->first();
        $cart->coupon_id = null;
        $cart->discount = 0;
        $cart->save();
        return response()->json(['status' => 200, 'grand_total' => $cart->grandTotal()]);

    }

    public function selectShippingMethod(ShippingMethod $shipping_method)
    {
        // create session shipping charge
        session(['shipping_charge' => $shipping_method->amount]);
        $cart = Cart::ByUser()->first();
        $grand_total = $cart->grandTotal() + session()->get('shipping_charge');
        return response()->json(['shipping_method' => $shipping_method, 'grand_total' => $grand_total]);
    }

    public function deleteExpiredCart()
    {
        $currentDate = now();
        $carts = Cart::with('items')->where('expired_at', '<', $currentDate)->get();
        foreach($carts as $cart){
            if($cart->expired_at < now()){
                foreach($cart->items as $item){
                    $item->delete();
                }
                $cart->delete();
            }
        }
    }


    private function validateCoupon(Coupon $coupon)
    {

        $state = true;
        $message = '';
        $currentDate = date('Y-m-d H:i:s');

        if ($currentDate > $coupon->expire_at){
            $state = false;
            $message = "coupon already Expired";
        }

        if ($coupon->limit_per_coupon < $coupon->usage){
            $state = false;
            $message = 'This coupon already reach the limit';
        }
        if (!$coupon->limitPerUser()){
            $state = false;
            $message = 'You Already Used this Coupon';
        }

        return ['status' => $state, 'message' => $message];

    }


}