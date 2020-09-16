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
        
        if(isset($request->latitude) && isset($request->longitude)){
            $origin = $request->latitude . ',' . $request->longitude;
        }else{
            $origin = '39.001423,-95.68695';
        }
        $key = new StandardLicense(env('GOOGLE_MAPS_KEY'));
        $request = new DistanceMatrix($key);

        $pharmacies = Pharmacy::select('latitude', 'longitude')->get();
        $string_pharmacies = $pharmacies->map(function($pharmacy){
            return $pharmacy->latitude . ',' . $pharmacy->longitude;
        });
        $destinations = implode('||', $string_pharmacies->toArray());

        $response = DistanceMatrix::license($key)
        ->addOrigin($origin)
        ->addDestination($destinations)
        ->setMode('DRIVING')
        ->useImperialUnits()
        ->request();

        $coming_distances = $response->json['rows'][0]['elements'];

        $closest_pharmacy = $this->GetCloserPharmacy($pharmacies->toArray(), $coming_distances);
        $closest_pharmacy = array_merge($closest_pharmacy, Pharmacy::select('name', 'address')
            ->where('latitude', $closest_pharmacy['latitude'])
            ->where('longitude', $closest_pharmacy['longitude'])
            ->first()->toArray());

        return $closest_pharmacy;
    }

    public function GetCloserPharmacy($pharmacies, $coming_distances){
        $responses = [];

        for ($i=0; $i < count($coming_distances); $i++) { 
            $single_pharmacy = [
                'distance_string' => $coming_distances[$i]['distance']['text'],
                'distance_int' => $coming_distances[$i]['distance']['value'],
                'duration_string' => $coming_distances[$i]['duration']['text'],
                'duration_int' => $coming_distances[$i]['duration']['value'],
            ];
            
            array_push($responses, array_merge($single_pharmacy, $pharmacies[$i]));
        }

        function sorting($a, $b){
            return strcmp($a['distance_int'], $b['distance_int']);
        }

        usort($responses, function ($a, $b){
            return strcmp($a['distance_int'], $b['distance_int']);
        });
        

        return $responses[0];
    }
}
