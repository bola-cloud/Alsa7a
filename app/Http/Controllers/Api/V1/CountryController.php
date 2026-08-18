<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Get all active countries for the dropdown.
     */
    public function index(Request $request)
    {
        $countries = Country::where('is_active', true)->ordered()->get();

        return response()->json([
            'status' => true,
            'data' => $countries,
            'message' => 'Countries retrieved successfully'
        ]);
    }
}
