<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\City;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{

    public function all()
    {
        $user = Auth::user();

        $address = Address::with(['province', 'city.cityType', 'addressCategory'])->whereUserId($user->id)->orderByDesc('updated_at')->get();

        return ResponseFormatter::success(
            $address,
            'Data address berhasil diambil'
        );
    }

    public function store(AddressRequest $request)
    {
        $user = Auth::user();

        $province = Province::whereName($request->province)->first();
        if (!$province) {
            $province = Province::create([
                'name' => $request->province,
                'province_id' => $request->province_id
            ]);
        }

        $city = City::whereName($request->city_name)->first();
        if (!$city) {
            $city = City::create([
                'city_type_id' => $request->city_type_id,
                'name' => $request->city_name,
                'city_id' => $request->city_id
            ]);
        }

        $address = Address::create([
            'user_id' => $user->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
            'address_category_id' => $request->address_category_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'detail' => $request->detail,
            'additional' => $request->additional,
        ]);

        return ResponseFormatter::success(
            $address,
            'Address Added Successfully'
        );
    }

    public function update($id, AddressRequest $request)
    {
        $address = Address::findOrFail($id);

        $province_old = $address->province;
        $city_old = $address->city;

        $province = Province::whereName($request->province)->first();
        if (!$province) {
            $province = Province::create([
                'name' => $request->province,
                'province_id' => $request->province_id
            ]);
        }

        $city = City::whereName($request->city_name)->first();
        if (!$city) {
            $city = City::create([
                'city_type_id' => $request->city_type_id,
                'name' => $request->city_name,
                'city_id' => $request->city_id
            ]);
        }

        $address->update(
            [
                'province_id' => $province->id,
                'city_id' => $city->id,
                'address_category_id' => $request->address_category_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'detail' => $request->detail,
                'additional' => $request->additional,
            ]
        );

        if ($province_old->addresses->count() == 0) {
            $province_old->delete();
        }

        if ($city_old->addresses->count() == 0) {
            $city_old->delete();
        }
        return ResponseFormatter::success(
            null,
            'Address Updated Successfully'
        );
    }

    public function delete($id)
    {
        $address = Address::findOrFail($id);

        $province_old = $address->province;
        $city_old = $address->city;

        $address->delete();

        if ($province_old->addresses->count() == 0) {
            $province_old->delete();
        }

        if ($city_old->addresses->count() == 0) {
            $city_old->delete();
        }

        return ResponseFormatter::success(
            null,
            'Address Deleted Successfully'
        );
    }
}
