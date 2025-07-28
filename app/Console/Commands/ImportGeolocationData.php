<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportGeolocationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:geolocation-data {country_iso_code : The ISO 3166-1 alpha-2 code of the country (e.g., EC, US)} 
                                                   {--username= : Your GeoNames username (optional, defaults to preconfigured)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports provinces and cities for a given country from GeoNames API.';

    private string $geoNamesUsername;
    private const GEONAMES_API_BASE_URL = 'http://api.geonames.org/';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $countryIsoCode = strtoupper($this->argument('country_iso_code'));
        $this->geoNamesUsername = $this->option('username') ?: 'apaysamigeo'; // Use provided or default

        if (empty($this->geoNamesUsername)) {
            $this->error('GeoNames username is required. Please provide it via --username option or configure a default.');
            return Command::FAILURE;
        }

        $this->info("Starting geolocation data import for country: {$countryIsoCode} using GeoNames user: {$this->geoNamesUsername}");

        $country = Country::where('iso_code_2', $countryIsoCode)->first();

        if (!$country) {
            $this->error("Country with ISO code '{$countryIsoCode}' not found in the database.");
            return Command::FAILURE;
        }

        if (empty($country->geoname_id)) {
            $this->error("Country '{$country->name}' does not have a geoname_id set. Cannot fetch subdivisions.");
            // Optionally, try to fetch geoname_id for the country here
            // $this->fetchCountryGeoNameId($country);
            return Command::FAILURE;
        }

        $this->importProvinces($country);
        // Cities are imported within importProvinces, after each province is processed.

        $this->info("Geolocation data import finished for country: {$countryIsoCode}.");
        return Command::SUCCESS;
    }

    private function importProvinces(Country $country): void
    {
        $this->line("Fetching provinces for {$country->name} (GeoName ID: {$country->geoname_id})...");
        
        // Endpoint for administrative divisions (ADM1 for provinces/states)
        // http://api.geonames.org/childrenJSON?geonameId=3658394&username=apaysamigeo&lang=es&featureCode=ADM1
        $response = Http::get(self::GEONAMES_API_BASE_URL . 'childrenJSON', [
            'geonameId' => $country->geoname_id,
            'username' => $this->geoNamesUsername,
            'lang' => 'es', // Prefer Spanish names
            // 'featureCode' => 'ADM1', // To specifically get first-level administrative divisions
        ]);

        if ($response->failed()) {
            $this->error("Failed to fetch provinces for {$country->name}. Status: " . $response->status());
            Log::error("GeoNames API error for provinces of {$country->name}: " . $response->body());
            return;
        }

        $data = $response->json();

        if (!isset($data['geonames']) || empty($data['geonames'])) {
            $this->warn("No provinces found for {$country->name} or an issue with the response. Total results: " . ($data['totalResultsCount'] ?? 'N/A'));
            Log::warning("GeoNames: No provinces data for {$country->name}", ['response' => $data]);
            return;
        }

        $provincesCount = 0;
        foreach ($data['geonames'] as $provinceData) {
            // Filter by fcode ADM1 for provinces/states
            if (isset($provinceData['fcode']) && $provinceData['fcode'] === 'ADM1') {
                $province = Province::updateOrCreate(
                    ['geoname_id' => $provinceData['geonameId']],
                    [
                        'country_id' => $country->id,
                        'name' => $provinceData['name'],
                        'code' => $provinceData['adminCode1'] ?? null, // adminCode1 is often the province code
                        'latitude' => $provinceData['lat'] ?? null,
                        'longitude' => $provinceData['lng'] ?? null,
                        'is_active' => true,
                    ]
                );
                $this->line("  Processed province: {$province->name} (ID: {$province->id}, GeoName ID: {$province->geoname_id})");
                $provincesCount++;
                $this->importCities($province); // Import cities for this province
            }
        }
        $this->info("{$provincesCount} provinces processed for {$country->name}.");
    }

    private function importCities(Province $province): void
    {
        $this->line("  Fetching cities for province: {$province->name} (GeoName ID: {$province->geoname_id})...");

        // Using childrenJSON for cities of a province
        // http://api.geonames.org/childrenJSON?geonameId={province_geoname_id}&username=apaysamigeo&lang=es
        // Alternatively, searchJSON with adminCode1 and featureClass PPL (populated place)
        // http://api.geonames.org/searchJSON?country=EC&adminCode1=EC.18&featureClass=PPL&username=apaysamigeo&lang=es&maxRows=1000
        
        $response = Http::get(self::GEONAMES_API_BASE_URL . 'childrenJSON', [
            'geonameId' => $province->geoname_id,
            'username' => $this->geoNamesUsername,
            'lang' => 'es',
            // 'featureClass' => 'PPL', // For populated places, might be too broad or too narrow depending on childrenJSON behavior
            'maxRows' => 1000, // Max rows for cities
        ]);

        if ($response->failed()) {
            $this->error("  Failed to fetch cities for province {$province->name}. Status: " . $response->status());
            Log::error("GeoNames API error for cities of {$province->name}: " . $response->body());
            return;
        }

        $data = $response->json();

        if (!isset($data['geonames']) || empty($data['geonames'])) {
            $this->warn("  No cities found for province {$province->name}. Total results: " . ($data['totalResultsCount'] ?? 'N/A'));
            // Log::warning("GeoNames: No cities data for province {$province->name}", ['response' => $data]);
            return;
        }
        
        $citiesCount = 0;
        foreach ($data['geonames'] as $cityData) {
            // Filter for populated places (PPL, PPLA, PPLC etc.)
            if (isset($cityData['fcl']) && $cityData['fcl'] === 'P') {
                 City::updateOrCreate(
                    ['geoname_id' => $cityData['geonameId']],
                    [
                        'province_id' => $province->id,
                        'country_id' => $province->country_id, // Inherit country_id from province
                        'name' => $cityData['name'],
                        'latitude' => $cityData['lat'] ?? null,
                        'longitude' => $cityData['lng'] ?? null,
                        'is_active' => true,
                    ]
                );
                $citiesCount++;
            }
        }
        $this->line("    {$citiesCount} cities processed for province: {$province->name}.");
    }
}
