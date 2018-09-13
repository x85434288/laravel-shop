<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class UserAddressesController extends Controller
{
    //
    public function index(Request $request)
    {

        //dd(Auth::user()->addresses()->get()->toArray());
        $addresses = $request->user()->addresses;
        return view('user_addresses.index',compact('addresses'));

    }

}
