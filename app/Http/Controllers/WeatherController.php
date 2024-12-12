<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Weather;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Artisan;

class WeatherController extends Controller
{
    //
     public function postScrape(Request $request,$city)
     {
       $request->merge(['city' => $city]);

        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            response()->json(['error' => " input only string and if city have two words so use - ",
        'status' => 401]);
        }

        $url = 'https://www.timeanddate.com/weather/usa/' . strtolower($city);

        $response = Http::get($url);
        if ($response->failed()) {
            return response()->json(['error' => "Failed to fetch data from $url",
        'status' => 500]);
        }

        $html = $response->body();

        $crawler = new Crawler($html);

        $cityName = ucwords(str_replace('-', ' ', $city));

        $temperature = $crawler->filter('#qlook .h2')->count() ? $crawler->filter('#qlook .h2')->text() : null;

        $tds = $crawler->filter('.bk-focus__info table tbody tr td');
        $humidity = $tds->count() > 5 ? $tds->eq(5)->text() : null;

        Weather::updateOrCreate(
            ['city' => $cityName],
            [
                'temperature' => $temperature,
                'humidity' => $humidity
            ]
        );

        return response()->json(
            [
        'data' => [
              'city' => $cityName,
              'temperature' => $temperature,
              'humidity' => $humidity
        ],
        'success' => "Weather data for {$cityName} scraped and stored successfully.",
        'status' => 200
    ]);


     }
     public function scrape($city)
    {
        $cityName = ucwords(str_replace('-', ' ', $city));
        $weather = Weather::where('city', $cityName)->first();

        if (!$weather) {
            return response()->json(['error' => 'No data found'], 404);
        }

        // Return JSON response
        return response()->json([
            'city' => $weather->city,
            'temperature' => $weather->temperature,
            'humidity' => $weather->humidity,
            'updated_at' => $weather->updated_at
        ]);
    }
}