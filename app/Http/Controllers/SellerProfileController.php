<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSellerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class SellerProfileController extends Controller
{
    private const SELLER_FIELDS = [
        'name', 'description', 'email', 'phone',
        'website', 'instagram', 'youtube', 'facebook', 'morph_market',
    ];

    private const ADDRESS_FIELDS = [
        'street_address', 'unit_number', 'city', 'state', 'postal_code', 'country',
    ];

    public function save(UpdateSellerRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $sellerData  = array_intersect_key($data, array_flip(self::SELLER_FIELDS));
        $addressData = array_filter(
            array_intersect_key($data, array_flip(self::ADDRESS_FIELDS)),
            fn ($v) => $v !== null
        );

        if ($user->seller) {
            $user->seller->update($sellerData);
        } else {
            $user->seller()->create($sellerData);
        }

        if (! empty($addressData)) {
            $addressData['country'] ??= 'US';
            $user->seller->address()->updateOrCreate([], $addressData);
        }

        return Redirect::route('profile.edit')->with('status', 'seller-updated');
    }
}
