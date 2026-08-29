<?php

use App\Services\AtlasSandboxFlightSearch;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('maps a successful Atlas sandbox search into Rebound flight cards', function () {
    config([
        'services.atlas.enabled' => true,
        'services.atlas.base_url' => 'https://sandbox.atriptech.com',
        'services.atlas.access_key' => 'test-access-key',
        'services.atlas.secret_key' => 'test-secret-key',
    ]);

    Http::fake([
        'https://sandbox.atriptech.com/search.do' => Http::response([
            'status' => 0,
            'routings' => [[
                'routingIdentifier' => 'sandbox-routing-id',
                'currency' => 'USD',
                'adultPrice' => 100,
                'adultTax' => 20,
                'fromSegments' => [[
                    'flightNumber' => 'AT123',
                    'carrier' => 'AT',
                    'airlineName' => 'Atlas Test Air',
                    'airlineCode' => 'AT',
                    'depAirport' => 'CGK',
                    'arrAirport' => 'SIN',
                    'depTime' => '202608311000',
                    'arrTime' => '202608311230',
                    'aircraftCode' => '320',
                    'seatCount' => 6,
                    'duration' => 150,
                ]],
            ]],
        ], 200),
    ]);

    $result = app(AtlasSandboxFlightSearch::class)->search('CGK', 'SIN', Carbon::parse('2026-08-31'));

    expect($result['available'])->toBeTrue()
        ->and($result['flights'])->toHaveCount(1)
        ->and($result['flights'][0])->toMatchArray([
            'flightNumber' => 'AT123',
            'fromCode' => 'CGK',
            'toCode' => 'SIN',
            'fromCity' => 'Jakarta',
            'toCity' => 'Singapore',
            'depTime' => '10:00',
            'arrTime' => '12:30',
            'aircraft' => 'Airbus A320',
            'seatsAvailable' => 6,
            'gate' => null,
            'source' => 'atlas_sandbox',
            'atlasRoutingIdentifier' => 'sandbox-routing-id',
        ]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://sandbox.atriptech.com/search.do'
        && $request['fromCity'] === 'CGK'
        && $request['toCity'] === 'SIN'
        && $request['fromDate'] === '20260831');
});
