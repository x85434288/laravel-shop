<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\UserAddress;
use App\Http\Requests\UserAddressesRequest;

class UserAddressesController extends Controller
{
    //
    public function index(Request $request)
    {
        //dd(Auth::user()->addresses()->get()->toArray());
        $addresses = $request->user()->addresses;
        return view('user_addresses.index',compact('addresses'));
    }

    public function create(UserAddress $address)
    {
        return view('user_addresses.create_and_edit',compact('address'));
    }


    public function store(UserAddressesRequest $request)
    {

        $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
        return redirect()->route('user_addresses.index');

    }


    public function edit(UserAddress $userAddress)
    {

        $this->authorize('own', $userAddress);
        return view('user_addresses.create_and_edit', compact('userAddress'));
    }

    public function update(UserAddress $userAddress, UserAddressesRequest $request)
    {

        $this->authorize('own', $userAddress);
        $userAddress->update($request->all());
        return redirect()->route('user_addresses.index');
    }

    public function destroy(UserAddress $userAddress)
    {

        $this->authorize('own', $userAddress);
        $userAddress->delete();

        //return redirect()->route('user_addresses.index');
        return [];

    }
}
