<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AtlasSandboxFlightSearch
{
    /**
     * Search Atlas ATRIP's sandbox inventory. This class never creates orders,
     * takes payment, or issues tickets.
     */
    public function search(string $from, string $to, CarbonInterface $departureDate): array
    {
        if (! config('services.atlas.enabled') || ! $this->isConfigured()) {
            return ['available' => false, 'flights' => []];
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'Accept-Encoding' => 'gzip',
                    'x-atlas-client-id' => config('services.atlas.access_key'),
                    'x-atlas-client-secret' => config('services.atlas.secret_key'),
                ])
                ->timeout(12)
                ->post(rtrim(config('services.atlas.base_url'), '/') . '/search.do', [
                    'tripType' => '1',
                    'adultNum' => 1,
                    'childNum' => 0,
                    'infantNum' => 0,
                    'fromCity' => strtoupper($from),
                    'fromAirport' => '',
                    'toCity' => strtoupper($to),
                    'toAirport' => '',
                    'fromDate' => $departureDate->format('Ymd'),
                    'retDate' => null,
                    'airlines' => null,
                    'fromFlightNumbers' => null,
                    'retFlightNumbers' => null,
                    'includeMultipleFareFamily' => false,
                    'currency' => null,
                    'displayCurrency' => 'IDR',
                    'requestSource' => 'ReboundSandboxDemo',
                ]);

            $payload = $response->json();

            if (! $response->successful() || data_get($payload, 'status') !== 0) {
                Log::warning('Atlas sandbox flight search was rejected.', [
                    'http_status' => $response->status(),
                    'atlas_status' => data_get($payload, 'status'),
                ]);

                return ['available' => false, 'flights' => []];
            }

            $flights = collect(data_get($payload, 'routings', []))
                ->map(fn (array $routing) => $this->toFlightCard($routing, $from, $to))
                ->filter()
                ->take(4)
                ->values()
                ->all();

            return ['available' => count($flights) > 0, 'flights' => $flights];
        } catch (\Throwable $exception) {
            Log::warning('Atlas sandbox flight search failed; using demo fallback.', [
                'exception' => get_class($exception),
            ]);

            return ['available' => false, 'flights' => []];
        }
    }

    private function isConfigured(): bool
    {
        return filled(config('services.atlas.access_key'))
            && filled(config('services.atlas.secret_key'));
    }

    private function toFlightCard(array $routing, string $from, string $to): ?array
    {
        $segments = data_get($routing, 'fromSegments', []);
        $first = $segments[0] ?? null;
        $last = count($segments) ? $segments[array_key_last($segments)] : null;

        if (! is_array($first) || ! is_array($last)) {
            return null;
        }

        $fromCode = data_get($first, 'depAirport', data_get($first, 'departureAirport', strtoupper($from)));
        $toCode = data_get($last, 'arrAirport', data_get($last, 'arrivalAirport', strtoupper($to)));
        $departure = data_get($first, 'depTime', data_get($first, 'departureTime'));
        $arrival = data_get($last, 'arrTime', data_get($last, 'arrivalTime'));
        $flightNumber = data_get($first, 'flightNumber', 'Atlas option');
        $airlineCode = data_get($first, 'carrier', data_get($first, 'airlineCode', substr($flightNumber, 0, 2)));
        $airline = data_get($first, 'airlineName', $this->airlineName($airlineCode));
        $aircraft = $this->aircraftName(data_get($first, 'aircraftCode', data_get($first, 'aircraft')));
        $displayFare = data_get($routing, 'displayFare', []);
        $fare = (float) data_get($displayFare, 'adultPrice', data_get($routing, 'adultPrice', 0));
        $tax = (float) data_get($displayFare, 'adultTax', data_get($routing, 'adultTax', 0));

        return [
            'flightNumber' => $flightNumber,
            'airline' => $airline,
            'airlineCode' => $airlineCode,
            'aircraft' => $aircraft,
            // id: GDS tidak mengirim nomor gate pada hasil pencarian; jangan tampilkan placeholder "TBC".
            // en: The GDS does not return a gate number on search results; never show a "TBC" placeholder.
            'gate' => data_get($first, 'depGate') ?: null,
            'fromCode' => $fromCode,
            'fromCity' => data_get($first, 'departureCity', $this->cityName($fromCode)),
            'fromCityEn' => data_get($first, 'departureCity', $this->cityName($fromCode)),
            'toCode' => $toCode,
            'toCity' => data_get($last, 'arrivalCity', $this->cityName($toCode)),
            'toCityEn' => data_get($last, 'arrivalCity', $this->cityName($toCode)),
            'depTime' => $this->displayTime($departure),
            'arrTime' => $this->displayTime($arrival),
            'duration' => data_get($routing, 'duration', $this->duration($departure, $arrival)),
            'durationEn' => data_get($routing, 'duration', $this->duration($departure, $arrival)),
            'seatsAvailable' => data_get($first, 'seatCount', data_get($routing, 'seatCount')),
            'waiverStatus' => 'Eligibility checked by Rebound policy engine',
            'feeAmount' => 0,
            'isRecommended' => false,
            'source' => 'atlas_sandbox',
            'atlasRoutingIdentifier' => data_get($routing, 'routingIdentifier'),
            'price' => [
                'amount' => $fare + $tax,
                'currency' => data_get($displayFare, 'currency', data_get($routing, 'currency')),
            ],
        ];
    }

    private function displayTime(?string $value): string
    {
        $dateTime = $this->atlasDateTime($value);

        return $dateTime?->format('H:i') ?? 'TBC';
    }

    private function duration(?string $departure, ?string $arrival): string
    {
        $from = $this->atlasDateTime($departure);
        $to = $this->atlasDateTime($arrival);

        if (! $from || ! $to || $to->lessThanOrEqualTo($from)) {
            return 'TBC';
        }

        $minutes = $from->diffInMinutes($to);

        return intdiv($minutes, 60) . 'j ' . ($minutes % 60) . 'm';
    }

    private function atlasDateTime(?string $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{12}$/', $value)) {
            return null;
        }

        return Carbon::createFromFormat('YmdHi', $value);
    }

    private function cityName(string $airport): string
    {
        return [
            'CGK' => 'Jakarta',
            'SIN' => 'Singapore',
            'KUL' => 'Kuala Lumpur',
            'DPS' => 'Denpasar',
        ][strtoupper($airport)] ?? strtoupper($airport);
    }

    private function airlineName(string $code): string
    {
        return [
            '8B' => 'TransNusa',
            'GA' => 'Garuda Indonesia',
            'SQ' => 'Singapore Airlines',
            'QG' => 'Citilink',
            'ID' => 'Batik Air',
            'JT' => 'Lion Air',
            'QZ' => 'Indonesia AirAsia',
            'AK' => 'AirAsia',
            'MH' => 'Malaysia Airlines',
            'OD' => 'Batik Air Malaysia',
            'IU' => 'Super Air Jet',
            'TR' => 'Scoot',
            '3K' => 'Jetstar Asia',
        ][strtoupper($code)] ?? 'Atlas Sandbox';
    }

    // id: Petakan kode pesawat IATA ke nama ramah-penumpang; kode tak dikenal tetap ditampilkan apa adanya
    //     agar kartu tidak pernah kosong / "—".
    // en: Map IATA aircraft codes to passenger-friendly names; unknown codes are shown as-is so the
    //     card is never empty / "—".
    private function aircraftName(?string $code): ?string
    {
        if (! is_string($code) || trim($code) === '') {
            return null;
        }

        $code = strtoupper(trim($code));

        return [
            '320' => 'Airbus A320',
            '32N' => 'Airbus A320neo',
            '321' => 'Airbus A321',
            '32Q' => 'Airbus A321neo',
            '333' => 'Airbus A330-300',
            '339' => 'Airbus A330-900neo',
            '359' => 'Airbus A350-900',
            '388' => 'Airbus A380-800',
            '738' => 'Boeing 737-800',
            '739' => 'Boeing 737-900ER',
            '7M8' => 'Boeing 737 MAX 8',
            '77W' => 'Boeing 777-300ER',
            '788' => 'Boeing 787-8',
            '789' => 'Boeing 787-9',
            'AT7' => 'ATR 72-600',
            'E90' => 'Embraer E190',
            'DH4' => 'DHC-8 Dash 400',
        ][$code] ?? $code;
    }
}
