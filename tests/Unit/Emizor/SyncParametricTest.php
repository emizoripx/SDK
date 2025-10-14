<?php

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Repositories\ParametricRepository;
use Emizor\SDK\Services\ParametricService;
use Mockery\MockInterface;

test("mock ParametricContract", function () {
    // Create a mock account
    $account = \Emizor\SDK\Models\BeiAccount::factory()->create();

    // Create the mock
    $this->mock(ParametricContract::class, function (MockInterface $mock) use ($account) {
        $mock->shouldReceive('syncAndSaveParametric')
            ->with($account, 'TYPE')
            ->andReturn();    // Return void

        $mock->shouldReceive('getParametric')
            ->with($account, 'TYPE')
            ->andReturn(['param1', 'param2']); // You can define whatever you want
    });

    // Retrieve the mock from the container
    $parametric = app(ParametricContract::class);

    // Test the sync call
    $parametric->syncAndSaveParametric($account, 'TYPE');

    $getResult = $parametric->getParametric($account, 'TYPE');
    expect($getResult)->toBeArray()->toHaveCount(2); // get returns ['param1','param2']
});


test("sync parametric stores data in bei_specific_parametrics table", function () {
    // 1. Create a test account
    $account = \Emizor\SDK\Models\BeiAccount::factory()->create([
        'bei_host' => 'https://test.host',
        'bei_token' => 'test-token',
    ]);

    // 2. Mock EmizorApiHttpContract that returns controlled data
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

    // 3. Real repository
    $repository = new ParametricRepository();

    // 4. Service with API mock and real repository
    $service = new ParametricService($apiMock, $repository);

    // 5. Execute sync
    $service->syncAndSaveParametric($account, 'actividades');

    // 6. Verify data was saved in the real table
    $this->assertDatabaseHas('bei_specific_parametrics', [
        'bei_account_id' => $account->id,
        'bei_type' => 'actividades',
        'bei_code' => '001',
        'bei_description' => 'Param1'
    ]);

    $this->assertDatabaseHas('bei_specific_parametrics', [
        'bei_account_id' => $account->id,
        'bei_type' => 'actividades',
        'bei_code' => '002',
        'bei_description' => 'Param2'
    ]);
});
