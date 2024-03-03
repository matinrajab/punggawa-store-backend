<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\City;
use App\Models\District;
use App\Models\PostalCode;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{

    public function all()
    {
        $user = Auth::user();

        $address = Address::with(['province', 'city', 'district', 'postalCode', 'addressCategory'])->whereUserId($user->id)->orderByDesc('updated_at')->get();

        return ResponseFormatter::success(
            $address,
            'Data address berhasil diambil'
        );
    }

    public function store(AddressRequest $request)
    {
        $user = Auth::user();

        $province = Province::whereName($request->province_name)->first();
        if (!$province) {
            $province = Province::create([
                'name' => $request->province_name,
                'id_from_api' => $request->province_id
            ]);
        }

        $city = City::whereName($request->city_name)->first();
        if (!$city) {
            $city = City::create([
                'name' => $request->city_name,
                'id_from_api' => $request->city_id
            ]);
        }

        $district = District::whereName($request->district_name)->first();
        if (!$district) {
            $district = District::create([
                'name' => $request->district_name,
                'id_from_api' => $request->district_id
            ]);
        }

        $postal_code = PostalCode::whereName($request->postal_code_name)->first();
        if (!$postal_code) {
            $postal_code = PostalCode::create([
                'name' => $request->postal_code_name,
                'id_from_api' => $request->postal_code_id
            ]);
        }

        $address = Address::create([
            'user_id' => $user->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
            'district_id' => $district->id,
            'postal_code_id' => $postal_code->id,
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
        $district_old = $address->district;
        $postal_code_old = $address->postalCode;

        $province = Province::whereName($request->province_name)->first();
        if (!$province) {
            $province = Province::create([
                'name' => $request->province_name,
                'id_from_api' => $request->province_id
            ]);
        }

        $city = City::whereName($request->city_name)->first();
        if (!$city) {
            $city = City::create([
                'name' => $request->city_name,
                'id_from_api' => $request->city_id
            ]);
        }

        $district = District::whereName($request->district_name)->first();
        if (!$district) {
            $district = District::create([
                'name' => $request->district_name,
                'id_from_api' => $request->district_id
            ]);
        }

        $postal_code = PostalCode::whereName($request->postal_code_name)->first();
        if (!$postal_code) {
            $postal_code = PostalCode::create([
                'name' => $request->postal_code_name,
                'id_from_api' => $request->postal_code_id
            ]);
        }

        $address->update(
            [
                'province_id' => $province->id,
                'city_id' => $city->id,
                'district_id' => $district->id,
                'postal_code_id' => $postal_code->id,
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

        if ($district_old->addresses->count() == 0) {
            $district_old->delete();
        }

        if ($postal_code_old->addresses->count() == 0) {
            $postal_code_old->delete();
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
        $district_old = $address->district;
        $postal_code_old = $address->postalCode;

        $address->delete();

        if ($province_old->addresses->count() == 0) {
            $province_old->delete();
        }

        if ($city_old->addresses->count() == 0) {
            $city_old->delete();
        }

        if ($district_old->addresses->count() == 0) {
            $district_old->delete();
        }

        if ($postal_code_old->addresses->count() == 0) {
            $postal_code_old->delete();
        }

        return ResponseFormatter::success(
            null,
            'Address Deleted Successfully'
        );
    }
}
