<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSellerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class SellerProfileController extends Controller
{
    public function save(UpdateSellerRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($user->seller) {
            $user->seller->update($data);
        } else {
            $user->seller()->create($data);
        }

        return Redirect::route('profile.edit')->with('status', 'seller-updated');
    }
}
