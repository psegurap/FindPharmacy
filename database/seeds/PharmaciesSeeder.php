<?php

use Illuminate\Database\Seeder;
use App\Pharmacy;
class PharmaciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Bring the .json file that is store to be able to seed the database with that information
        $file = base_path() . '/public/csv/pharmacies.json';
        $pharmacies = json_decode(file_get_contents($file));

        //Run through each pharmacy information to store on the table.
        foreach ($pharmacies as $pharmacy) {
            $pharmacy_info = [
                'name' => $pharmacy->name,
                'address' => $pharmacy->address,
                'city' => $pharmacy->city,
                'state' => $pharmacy->state,
                'zip' => $pharmacy->zip,
                'latitude' => $pharmacy->latitude,
                'longitude' => $pharmacy->longitude,
            ];
            Pharmacy::create($pharmacy_info);
        }

    }
}
