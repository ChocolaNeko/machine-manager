<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AdminTest extends TestCase
{

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * 測試 API /v1/getuserlist
     * 預設有帶入 auth key 情境、無帶入參數、無回傳資料
     */
    public function test_getUserList_noparam_nodata()
    {
        // skip middleware (sanctum) test
        $this->withoutMiddleware();

        // mock users data
        $usersData = collect([]);

        // mock paginate result
        $paginator = new LengthAwarePaginator(
            $usersData,
            0, // 總資料數量 ($data->total())
            50, // 每頁資料數量
            1, // 當前頁數
            ['path' => url('/v1/getuserlist')] // API path
        );

        // mock query builder
        $userInfoMock = Mockery::mock('alias:App\Models\UserInfo');
        $userInfoMock->shouldReceive('select')
            ->with('user_id', 'user_name', 'email', 'status', 'create_time', 'balance')
            ->andReturnSelf();
        $userInfoMock->shouldReceive('orderBy')
            ->with('user_id', 'asc')
            ->andReturnSelf();
        $userInfoMock->shouldReceive('paginate')
            ->with(50, ['*'], 'page', 1)
            ->andReturn($paginator);

        // call API
        $response = $this->getJson('/v1/getuserlist');

        // assert status & response json
        $response->assertStatus(200);
        $response->assertJson([
            'result' => true,
            'data' => [
                'user_list' => [],
                'total_user_count' => 0,
                'page' => 1
            ],
        ]);
    }

    /**
     * 測試 API /v1/getuserlist
     * 預設有帶入 auth key 情境、無帶入參數、有回傳資料
     */
    public function test_getUserList_noparam_havedata()
    {
        // skip middleware (sanctum) test
        $this->withoutMiddleware();

        // mock users data
        $usersData = collect([
            (object)[
                'user_id' => 1,
                'user_name' => 'John Doe',
                'email' => 'john@example.com',
                'status' => 1,
                'create_time' => 1721202502,
                'balance' => 0
            ],
            (object)[
                'user_id' => 2,
                'user_name' => 'Paul',
                'email' => 'paul@example.com',
                'status' => 0,
                'create_time' => 1721217524,
                'balance' => 1050
            ]
        ]);

        // mock paginate result
        $paginator = new LengthAwarePaginator(
            $usersData,
            2, // 總資料數量 ($data->total())
            50, // 每頁資料數量
            1, // 當前頁數
            ['path' => url('/v1/getuserlist')] // API path
        );

        // mock query builder
        $userInfoMock = Mockery::mock('alias:App\Models\UserInfo');
        $userInfoMock->shouldReceive('select')
            ->with('user_id', 'user_name', 'email', 'status', 'create_time', 'balance')
            ->andReturnSelf();
        $userInfoMock->shouldReceive('orderBy')
            ->with('user_id', 'asc')
            ->andReturnSelf();
        $userInfoMock->shouldReceive('paginate')
            ->with(50, ['*'], 'page', 1)
            ->andReturn($paginator);

        // call API
        $response = $this->getJson('/v1/getuserlist');
        $response->assertStatus(200);
        $response->assertJson([
            'result' => true,
            'data' => [
                'user_list' => [
                    [
                        'user_id' => 1,
                        'user_name' => 'John Doe',
                        'email' => 'john@example.com',
                        'status' => 1,
                        'create_time' => 1721202502,
                        'balance' => 0
                    ],
                    [
                        'user_id' => 2,
                        'user_name' => 'Paul',
                        'email' => 'paul@example.com',
                        'status' => 0,
                        'create_time' => 1721217524,
                        'balance' => 1050
                    ]
                ],
                'total_user_count' => 2,
                'page' => 1
            ],
        ]);
    }

    /**
     * 測試 API /v1/getuserlist
     * 預設有帶入 auth key 情境、有帶入參數(is_enable, user_name)、無回傳資料
     */
    public function test_getUserList_haveparam_nodata()
    {
        // skip middleware (sanctum) test
        $this->withoutMiddleware();

        // mock query builder
        $userInfoMock = Mockery::mock('alias:App\Models\UserInfo');
        $userInfoMock->shouldReceive('select')
            ->with('user_id', 'user_name', 'email', 'status', 'create_time', 'balance')
            ->andReturnSelf();
        $userInfoMock->shouldReceive('where')
            ->with('status', '=', 1);
        $userInfoMock->shouldReceive('where')
            ->with('user_name', 'like', '%tempuser%');
        $userInfoMock->shouldReceive('orderBy')
            ->with('user_id', 'asc')
            ->andReturnSelf();
        $userInfoMock->shouldReceive('paginate')
            ->with(50, ['*'], 'page', 1)
            ->andReturnSelf();
        $userInfoMock->shouldReceive('get')
            ->andReturn(collect([]));
        $userInfoMock->shouldReceive('count')
            ->andReturn(0);

        // call API
        $response = $this->getJson('/v1/getuserlist?is_enable=1&user_name=tempuser');

        // assert status & response json
        $response->assertStatus(200);
        $response->assertJson([
            'result' => 'true',
            'data' => [
                'user_list' => [],
                'total_user_count' => 0,
                'page' => 1
            ],
        ]);
    }

    /**
     * 測試 API /v1/getuserlist
     * 預設有帶入 auth key 情境、有帶入參數(create_time, create_time_operator, amount, amount_operator)、有回傳資料
     */
    public function test_getUserList_haveparam_havedata()
    {
        // skip middleware (sanctum) test
        $this->withoutMiddleware();

        // mock query builder
        $userInfoMock = Mockery::mock('alias:App\Models\UserInfo');
        $userInfoMock->shouldReceive('select')
            ->with('user_id', 'user_name', 'email', 'status', 'create_time', 'balance')
            ->andReturnSelf();
        $userInfoMock->shouldReceive('where')
            ->with('create_time', '<', 1721202502);
        $userInfoMock->shouldReceive('where')
            ->with('balance', '>=', 500);
        $userInfoMock->shouldReceive('orderBy')
            ->with('user_id', 'asc')
            ->andReturnSelf();
        $userInfoMock->shouldReceive('paginate')
            ->with(50, ['*'], 'page', 1)
            ->andReturnSelf();
        $userInfoMock->shouldReceive('get')
            ->andReturn(collect([
                (object)[
                    'user_id' => 2,
                    'user_name' => 'John',
                    'email' => 'John@example.com',
                    'status' => 1,
                    'create_time' => 1721201256,
                    'balance' => 1050
                ]
            ]));
        $userInfoMock->shouldReceive('count')
            ->andReturn(1);

        // call API
        $response = $this->getJson('/v1/getuserlist?create_time=1721202502&create_time_operator=<&amount=500&amount_operator=>=');

        // assert status & response json
        $response->assertStatus(200);
        $response->assertJson([
            'result' => true,
            'data' => [
                'user_list' => [
                    [
                        'user_id' => 2,
                        'user_name' => 'John',
                        'email' => 'John@example.com',
                        'status' => 1,
                        'create_time' => 1721201256,
                        'amount' => 1050
                    ]
                ],
                'total_user_count' => 1,
                'page' => 1
            ],
        ]);
    }

    /**
     * 測試 API /v1/getuserlist
     * 未帶入 auth key
     */
    public function test_getUserList_unauthenticated()
    {
        // call API without skip middleware
        $response = $this->getJson('/v1/getuserlist');
        $response->assertStatus(401);
        $response->assertJson([
            'result' => false,
            'error_code' => 401001,
            'error_msg' => 'you need token'
        ]);
    }

    /**
     * 測試 API /v1/newadmin
     * 測試 password 長度小於 10
     */
    public function test_NewAdmin_validRule()
    {
        // call api
        $response = $this->postJson('/v1/newadmin', [
            'admin_name' => 'Bob',
            'password' => 'asdfgh',
            'email' => 'bob01234@gmail.com',
        ]);

        // check response
        $response->assertStatus(400)
            ->assertJson([
                'result' => false,
                'error_code' => 400001,
                'error_msg' => 'password 低於最小值(10)',
            ]);
    }

    /**
     * 測試 API /v1/newadmin
     * 測試輸入資料完全符合格式
     */
    public function test_NewAdmin_success()
    {
        // mock insert
        $adminMock = Mockery::mock('alias:App\Models\AdminInfo');
        $adminMock->shouldReceive('create')
                ->once()
                ->with(Mockery::on(function ($arg) {
                    return $arg['admin_name'] === 'Bob'
                        && Hash::check('qwertyasdfgh', $arg['admin_hash'])
                        && $arg['email'] === 'bob01234@gmail.com';
                }))
                ->andReturn((object) [
                    'id' => 1,
                    'admin_name' => 'Bob',
                    'email' => 'bob01234@gmail.com'
                ]);

        // call api
        $response = $this->postJson('/v1/newadmin', [
            'admin_name' => 'Bob',
            'password' => 'qwertyasdfgh',
            'email' => 'bob01234@gmail.com'
        ]);

        // check response
        $response->assertJson([
            'result' => 'true',
            'message' => '新增管理員成功',
        ]);
    }
}
