<?php

namespace App\DB;

use App\Models\Address;
use App\Models\City;
use App\Models\Technician;
use App\Models\Town;
use Illuminate\Support\Facades\Log;

class AddressRepo
{
    // fetch all city
    public function cities()
    {
        return City::all('title', 'id');
    }

    // fetch towns of a city
    public function towns($city_id)
    {
        return Town::select('title', 'id')->where('city_id', $city_id)->get();
    }

    // fetch all towns
    public function allTowns($technician_id)
    {
        $technicianRepo = resolve(TechnicianRepo::class);

        $technician = $technicianRepo->fetch($technician_id);

        foreach ($technician as $t) {
            return Town::select('title', 'id')
                ->where('city_id', $t->city_id)
                ->get();
        }
    }

    // fetch all addresses of a client
    public function addresses($client_id)
    {
        return Address::select('description', 'id', 'city_id', 'town_id')->with('town:id,title', 'city:id,title')->where('client_id', $client_id)->get();
    }

    // fetch a town
    public function getTown($town_id)
    {
        return Town::findOrFail($town_id);
    }

    // fetch a city
    public function getCity($city_id)
    {
        return City::findOrFail($city_id);
    }


    // save a new address
    public function save($client_id, $city_id, $town_id, $description, $long, $lat)
    {

        $address = resolve(Address::class);

        if ($city_id != null) {
            $address->city()->associate($this->getCity($city_id));
        }
        if ($town_id != null) {
            $address->town()->associate($this->getTown($town_id));
        }

        if ($client_id != null) {
            $client = resolve(ClientRepo::class);

            $address->client()->associate($client->getClient($client_id));
        }

        $address->description = $description;
        $address->long = $long;
        $address->lat = $lat;

        $address->save();
    }

    public function delete($id, $client_id)
    {
        $address = Address::find($id);

        if ($client_id == $address->client_id) {

            $address->delete();
        }
    }
}
