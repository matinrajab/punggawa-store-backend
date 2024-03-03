<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\AddressCategory;
use Illuminate\Http\Request;

class AddressCategoryController extends Controller
{
    public function all()
    {
        $category = AddressCategory::all();

        return ResponseFormatter::success(
            $category,
            'Data list kategori address berhasil diambil'
        );
    }
}
