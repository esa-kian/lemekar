<?php

namespace App\Http\Controllers;

use App\DB\AddressRepo;
use App\DB\ClientRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AddressController extends Controller
{
    public function clientId()
    {
        $clientRepo = resolve(ClientRepo::class);

        return $clientRepo->getClientId(auth()->guard('api')->id());
    }

    public function fetchCities(AddressRepo $addressRepo)
    {
        return response(['cities' => $addressRepo->cities()]);
    }

    public function fetchTowns($id)
    {
        $addressRepo = resolve(AddressRepo::class);

        return response(['towns' => $addressRepo->towns($id)]);
    }

    public function allTowns(AddressRepo $addressRepo)
    {
        return response(['towns' => $addressRepo->allTowns(auth()->guard('api')->user()->technician_id)]);
    }

    public function fetchAddresses(AddressRepo $addressRepo)
    {
        return response(['addresses' => $addressRepo->addresses($this->clientId()->client_id)]);
    }

    public function newAddress(Request $request, AddressRepo $addressRepo)
    {
        try {

            $addressRepo->save(
                $this->clientId()->client_id,
                $request->city_id,
                $request->town_id,
                $request->description,
                $request->long,
                $request->lat
            );

            return response(['message' => 'آدرس با موفقیت ذخیره شد'], 200);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. آدرس ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function destroy($id, AddressRepo $addressRepo)
    {
        try {

            $addressRepo->delete($id, $this->clientId()->client_id);

            return response(['message' => 'آدرس با موفقیت حذف شد'], 200);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. آدرس حذف نشد', 'error' => $e], 500);
        }
    }
}
