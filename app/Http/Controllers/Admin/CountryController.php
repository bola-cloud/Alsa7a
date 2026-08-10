<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\ImageService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $countries = Country::latest()->paginate(20);
        return view('admin.countries.index', compact('countries'));
    }

    public function create()
    {
        return view('admin.countries.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:countries,code',
            'flag' => 'nullable|image',
            'is_active' => 'nullable|boolean',
            'subscription_monthly_price' => 'required|numeric|min:0',
            'subscription_annual_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
        ]);

        $countryData = [
            'name_en' => $data['name_en'],
            'name_ar' => $data['name_ar'],
            'code' => strtoupper($data['code']),
            'is_active' => $request->has('is_active'),
            'subscription_monthly_price' => $data['subscription_monthly_price'],
            'subscription_annual_price' => $data['subscription_annual_price'],
            'currency' => strtoupper($data['currency']),
        ];

        if ($request->hasFile('flag')) {
            $countryData['flag'] = $this->imageService->upload($request->file('flag'), 'countries');
        }

        Country::create($countryData);

        $this->flashSuccess(__('admin.messages.created'));
        return redirect()->route('admin.countries.index');
    }

    public function edit(Country $country)
    {
        return view('admin.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $data = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:countries,code,' . $country->id,
            'flag' => 'nullable|image',
            'is_active' => 'nullable|boolean',
            'subscription_monthly_price' => 'required|numeric|min:0',
            'subscription_annual_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
        ]);

        $countryData = [
            'name_en' => $data['name_en'],
            'name_ar' => $data['name_ar'],
            'code' => strtoupper($data['code']),
            'is_active' => $request->has('is_active'),
            'subscription_monthly_price' => $data['subscription_monthly_price'],
            'subscription_annual_price' => $data['subscription_annual_price'],
            'currency' => strtoupper($data['currency']),
        ];

        if ($request->hasFile('flag')) {
            $countryData['flag'] = $this->imageService->replace($request->file('flag'), 'countries', $country->flag);
        }

        $country->update($countryData);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.countries.index');
    }

    public function destroy(Country $country)
    {
        if ($country->flag) {
            $this->imageService->delete($country->flag);
        }
        $country->delete();

        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.countries.index');
    }
}
