<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pharmacy;

//Classes to be able to use the new Provider DistanceMatrixServiceProvider
use TeamPickr\DistanceMatrix\DistanceMatrix;
use TeamPickr\DistanceMatrix\Licenses\StandardLicense;

class PharmaciesController extends Controller
{

    public function find_pharmacy(Request $request){
        //Make sure that we are receiving a latitude/longitude. If not, provide one.
        if(isset($request->latitude) && isset($request->longitude)){
            $origin = $request->latitude . ',' . $request->longitude;
        }else{
            $origin = '39.001423,-95.68695';
        }

        //Distance Matrix configure key
        $key = new StandardLicense(env('GOOGLE_MAPS_KEY'));
        $request = new DistanceMatrix($key);

        //Obtaining latitude and longitude of the pharmacies store.
        $pharmacies = Pharmacy::select('latitude', 'longitude')->get();
        //Joining latitude and longitude as a string
        $string_pharmacies = $pharmacies->map(function($pharmacy){
            return $pharmacy->latitude . ',' . $pharmacy->longitude;
        });
        //Joining every posible destination to parse this values to the Distance Matrix 
        $destinations = implode('||', $string_pharmacies->toArray());

        //Requesting estimate time for each destinations declared
        $response = DistanceMatrix::license($key)
        ->addOrigin($origin) //Origin (user location)
        ->addDestination($destinations) //Destinations (pharmacies)
        ->setMode('DRIVING') //Transportation mode
        ->useImperialUnits() //Set distance unit to miles
        ->request();

        //From all the information coming from the Distance Matrix, select only the distance information for each pharmacy
        $coming_distances = $response->json['rows'][0]['elements'];

        //Call the function GetCloserPharmacy() to sort the destinations by their distance.
        $closest_pharmacy = $this->GetCloserPharmacy($pharmacies->toArray(), $coming_distances);

        //Having the closest pharmacy, look for his name and address
        $closest_pharmacy = array_merge($closest_pharmacy, Pharmacy::select('name', 'address')
            ->where('latitude', $closest_pharmacy['latitude'])
            ->where('longitude', $closest_pharmacy['longitude'])
            ->first()->toArray());

        //return the closest location to the API request
        return $closest_pharmacy;
    }

    public function GetCloserPharmacy($pharmacies, $coming_distances){
        $responses = [];

        //Go through each destination results and merge that information with their latitude and longitude
        for ($i=0; $i < count($coming_distances); $i++) { 
            $single_pharmacy = [
                'distance_string' => $coming_distances[$i]['distance']['text'],
                'distance_int' => $coming_distances[$i]['distance']['value'],
                'duration_string' => $coming_distances[$i]['duration']['text'],
                'duration_int' => $coming_distances[$i]['duration']['value'],
            ];
            
            array_push($responses, array_merge($single_pharmacy, $pharmacies[$i]));
        }

        //Sort our array of destination taking the distance value
        usort($responses, function ($a, $b){
            return strcmp($a['distance_int'], $b['distance_int']);
        });
        
        //Return the first destination that after being sort should be the closest one
        return $responses[0];
    }
}
