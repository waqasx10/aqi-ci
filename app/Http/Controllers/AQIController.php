<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Csv\Reader;
use League\Csv\Writer;
use Illuminate\Support\Facades\Http;
use App\Models\Aqi;


class AQIController extends Controller
{
      public function index()
    {
        return view('welcome');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv' => 'required|mimes:csv,txt',
        ]);

        try {
            $csv = Reader::createFromPath($request->file('csv')->getRealPath(), 'r');
            $csv->setHeaderOffset(0);

            // Validate headers case-insensitively and ignore extras
            $headers = array_map('strtolower', (array) $csv->getHeader());
            $required = ['name', 'city', 'phone'];
            $missing = array_values(array_diff($required, $headers));
            if (!empty($missing)) {
                $details = 'Missing required header(s): ' . implode(', ', $missing) . '. Expected headers: Name, City, Phone';
                return back()->with('error', 'Invalid CSV headers.')->with('error_details', $details);
            }

            $records = $csv->getRecords();
            $output = [];
            $rowNumber = 1; // header is row 1

            foreach ($records as $record) {
                $rowNumber++;

                // Normalize keys to lowercase to support any casing in headers
                $normalized = array_change_key_case($record, CASE_LOWER);
                $name = trim((string) ($normalized['name'] ?? ''));
                $city = trim((string) ($normalized['city'] ?? ''));
                $phone = trim((string) ($normalized['phone'] ?? ''));

                // Validate required fields per row
                if ($name === '' || $city === '' || $phone === '') {
                    $output[] = [
                        'name'    => $name,
                        'city'    => $city,
                        'phone'   => $phone,
                        'aqi'     => null,
                        'message' => " Missing value(s). Each row must include Name, City, and Phone.",
                    ];
                    continue;
                }

                try {
                    $response = Http::get("https://api.airvisual.com/v2/city", [
                        'city'    => $city,
                        'state'   => $this->getStateByCity($city),
                        'country' => 'Pakistan',
                        'key'     => env('IQAIR_API_KEY'),
                    ]);

                    if ($response->successful() && $response->json('status') === 'success') {
                        $aqi = $response->json('data.current.pollution.aqius');
                        $message = $this->getMessage($aqi, $name, $city);
                    } else {
                        $aqi = null;
                        $message = " City not found or API error.";
                    }
                } catch (\Throwable $e) {
                    $aqi = null;
                    $message = " Could not fetch AQI (" . $e->getMessage() . ").";
                }

                $output[] = [
                    'name'    => $name,
                    'city'    => $city,
                    'phone'   => $phone,
                    'aqi'     => $aqi,
                    'message' => $message,
                ];
            }

            if (empty($output)) {
                return back()->with('error', 'The CSV appears to be empty.')->with('error_details', 'Add at least one row under the headers: Name, City, Phone');
            }

            session(['aqi_results' => $output]);
            return back()->with('results', $output)->with('success', 'CSV processed successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not process the CSV file.')->with('error_details', $e->getMessage());
        }

    }


    private function getMessage($aqi, $name, $city)
    {
        if (is_null($aqi)) {
            return "Hi {$name}, we couldn’t retrieve air quality data for {$city}.";
        }

        // Load custom messages from DB
        $messages = Aqi::pluck('message','range')->toArray();
        // dd($messages);

        if ($aqi <= 50) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['good'] ?? "the air quality in {$city} is Good 😊 (AQI: {$aqi}). Enjoy your day!");
        } elseif ($aqi <= 100) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['moderate'] ?? "the air quality in {$city} is Moderate 🙂 (AQI: {$aqi}). It’s generally okay.");
        } elseif ($aqi <= 150) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['unhealthy_sensitive'] ?? "the air quality in {$city} is Unhealthy for Sensitive Groups 😷 (AQI: {$aqi}). Be careful if you have breathing issues.");
        } elseif ($aqi <= 200) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['unhealthy'] ?? "the air quality in {$city} is Unhealthy ❌ (AQI: {$aqi}). Try to limit outdoor activity.");
        } elseif ($aqi <= 300) {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['very_unhealthy'] ?? "the air quality in {$city} is Very Unhealthy ⚠️ (AQI: {$aqi}). Consider staying indoors.");
        } else {
            return "Hi {$name}! Today AQI in {$city} is {$aqi}. " . ($messages['hazardous'] ?? "the air quality in {$city} is Hazardous ☠️ (AQI: {$aqi}). Stay safe and avoid going outside.");
        }
    }


    private function getStateByCity($city)
    {
        // 🔥 IQAir requires "state" for each city
        $map = [
            'Karachi'     => 'Sindh',
            'Lahore'      => 'Punjab',
            'Islamabad'   => 'Islamabad',
            'Faisalabad'  => 'Punjab',
            'Multan'      => 'Punjab',
            'Rawalpindi'  => 'Punjab',
            'Quetta'      => 'Balochistan',
            'Peshawar'    => 'Khyber Pakhtunkhwa',
            'Hyderabad'   => 'Sindh',
            'Sialkot'     => 'Punjab',
        ];

        return $map[$city] ?? 'Punjab'; // fallback
    }

    public function download()
    {
        $output = session('aqi_results', []);

        if (empty($output)) {
            return redirect()->route('home')->with('error', 'No results available to download.');
        }

        $csv = Writer::createFromString('');
        $csv->insertOne(['name', 'city','phone','aqi', 'message']);
        $csv->insertAll($output);

        return response((string) $csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="aqi_results.csv"');
    }

    public function saveMessages(Request $request)
    {
        $data = $request->only([
            'good',
            'moderate',
            'unhealthy_sensitive',
            'unhealthy',
            'very_unhealthy',
            'hazardous',
        ]);

        foreach ($data as $range => $message) {
            if (!empty($message)) {
                Aqi::updateOrCreate(
                    ['range' => $range],
                    ['message' => $message]
                );
            }
        }

        return back()->with('success', 'Custom messages saved successfully!');
    }

}
