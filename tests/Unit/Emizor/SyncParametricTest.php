<?php

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Repositories\ParametricRepository;
use Emizor\SDK\Services\ParametricService;
use Mockery\MockInterface;

test("mock ParametricContract", function () {
    // Create the mock
    $this->mock(ParametricContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('sync')
            ->with('https://api.test', '12345', 'TYPE', 'accountId')
            ->andReturn([]);    // Return an empty array as example

        $mock->shouldReceive('get')
            ->andReturn(['param1', 'param2']); // You can define whatever you want
    });

    // Retrieve the mock from the container
    $parametric = app(ParametricContract::class);

    // Test the sync call
    $parametric->sync('https://api.test', '12345', 'TYPE', 'accountId');

    $getResult = $parametric->get('TYPE', 'accountId');
    expect($getResult)->toBeArray()->toHaveCount(2); // get returns ['param1','param2']
});


test("sync parametric stores data in bei_specific_parametrics table", function () {
    // 1. Mock EmizorApiHttpContract that returns controlled data
    $apiMock = Mockery::mock(EmizorApiHttpContract::class);
    $apiMock->shouldReceive('getParametrics')
        ->with('https://test.host', 'test-token', 'actividades')
        ->once()
        ->andReturn([
            'data' => [
                [
                    'codigo' => "001",
                    'descripcion' => "Param1"
                ],
                [
                    'codigo' => "002",
                    'descripcion' => "Param2"
                ]
            ]
        ]);

    // 2. Real repository
    $repository = new ParametricRepository();

    // 3. Service with API mock and real repository
    $service = new ParametricService($apiMock, $repository);

    // 4. Execute sync
    $service->sync('https://test.host', 'test-token', 'actividades', 'account-123');

    // 5. Verify data was saved in the real table
    $this->assertDatabaseHas('bei_specific_parametrics', [
        'bei_account_id' => 'account-123',
        'bei_type' => 'actividades',
        'bei_code' => '001',
        'bei_description' => 'Param1'
    ]);

    $this->assertDatabaseHas('bei_specific_parametrics', [
        'bei_account_id' => 'account-123',
        'bei_type' => 'actividades',
        'bei_code' => '002',
        'bei_description' => 'Param2'
    ]);
});
