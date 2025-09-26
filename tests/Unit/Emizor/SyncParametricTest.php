<?php

use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Repositories\ParametricRepository;
use Emizor\SDK\Services\ParametricService;
use Mockery\MockInterface;

test("mock ParametricContract", function () {
    // Creamos el mock
    $this->mock(ParametricContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('setHost')
            ->andReturnSelf();  // Retorna el mock mismo para encadenamiento

        $mock->shouldReceive('setToken')
            ->andReturnSelf();  // Retorna el mock mismo

        $mock->shouldReceive('sync')
            ->andReturn([]);    // Devuelve un array vacío como ejemplo

        $mock->shouldReceive('get')
            ->andReturn(['param1', 'param2']); // Puedes definir lo que quieras
    });

    // Recuperamos el mock del contenedor
    $parametric = app(ParametricContract::class);

    // Probamos el encadenamiento
    $parametric->setHost('https://api.test')
        ->setToken('12345')
        ->sync('TYPE', 'accountId');


    $getResult = $parametric->get('TYPE', 'accountId');
    expect($getResult)->toBeArray()->toHaveCount(2); // get devuelve ['param1','param2']
});


test("sync parametric stores data in bei_specific_parametrics table", function () {
    // 1️⃣ Mock del cliente HTTP que devuelve datos controlados
    $httpMock = Mockery::mock(HttpClientInterface::class);
    $httpMock->shouldReceive('get')
        ->with('/api/v1/parametricas/actividades')
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

    // 2️⃣ Repositorio real
    $repository = new ParametricRepository();

    // 3️⃣ Servicio con HTTP mock y repositorio real
    $service = new ParametricService($httpMock, $repository);

    // 4️⃣ Ejecutamos sync
    $service->sync('actividades', 'account-123');

    // 5️⃣ Verificamos que los datos se guardaron en la tabla real
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
