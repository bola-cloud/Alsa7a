@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('admin.countries.edit') }}</h4>
            </div>
            <div class="card-content collapse show">
                <div class="card-body">
                    <form action="{{ route('admin.countries.update', $country->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_en">{{ __('admin.countries.name_en') }}</label>
                                        <input type="text" id="name_en" class="form-control round" name="name_en" value="{{ $country->name_en }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_ar">{{ __('admin.countries.name_ar') }}</label>
                                        <input type="text" id="name_ar" class="form-control round" name="name_ar" value="{{ $country->name_ar }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code">{{ __('admin.countries.code') }}</label>
                                        <input type="text" id="code" class="form-control round" name="code" value="{{ $country->code }}" required placeholder="e.g. EG, SA, AE">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="flag">{{ __('admin.countries.flag') }}</label>
                                        <input type="file" id="flag" class="form-control-file" name="flag">
                                        @if($country->flag)
                                            <div class="mt-2">
                                                <img src="{{ $country->flag_url }}" alt="Flag" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subscription_monthly_price">السعر الشهري للاشتراك (Monthly Price)</label>
                                        <input type="number" step="0.001" min="0" id="subscription_monthly_price" class="form-control round" name="subscription_monthly_price" value="{{ old('subscription_monthly_price', $country->subscription_monthly_price) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subscription_annual_price">السعر السنوي للاشتراك (Annual Price)</label>
                                        <input type="number" step="0.001" min="0" id="subscription_annual_price" class="form-control round" name="subscription_annual_price" value="{{ old('subscription_annual_price', $country->subscription_annual_price) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="currency">العملة (Currency)</label>
                                        <input type="text" id="currency" class="form-control round" name="currency" value="{{ old('currency', $country->currency) }}" required placeholder="e.g. EGP, SAR, OMR">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <fieldset>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="is_active" id="is_active" value="1" {{ $country->is_active ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">{{ __('admin.countries.is_active') }}</label>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions text-right">
                            <a href="{{ route('admin.countries.index') }}" class="btn btn-warning mr-1 rounded-pill">
                                <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill">
                                <i class="la la-check-square-o"></i> {{ __('admin.buttons.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
