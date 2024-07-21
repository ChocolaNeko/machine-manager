<?php

namespace Tests\Feature;

use App\Http\Controllers\User;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class UserTest extends TestCase
{
//    public function setUp(): void
//    {
//        parent::setUp();
//        // 使用 Mockery 來 mock Sanctum 驗證
//        $this->mockSanctum();
//    }

    /**
     * 測試 API /v1/user/userinfo
     * 預設有帶入 auth key 情境、回傳會員資料
     */
    public function test_getUserInfo()
    {
        // Mock User
        $user = UserInfo::factory()->make([
            'user_id' => 12,
            'user_name' => "tester",
            'email' => "tester@gg.co",
            'status' => 1,
            'create_time' => 123456789,
            'balance' => 100
        ]);

        // Mock createToken for user
        $mockToken = (object) [
            'plainTextToken' => 'fake-token',
            'accessToken' => (object) ['abilities' => ['user']]
        ];

        $user = Mockery::mock($user);
        $user->shouldReceive('createToken')
            ->andReturn($mockToken);

        // Mock Sanctum::actingAs
        Sanctum::actingAs($user, ['user']);

        // call API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer fake-token',
        ])->getJson('/v1/user/userinfo');

        // assert status & response json
        $response->assertStatus(200);
        $response->assertJson([
            'result' => true,
            'data' => [
                'user_id' => 12,
                'user_name' => 'tester',
                'email' => 'tester@gg.co',
                'create_time' => 123456789,
                'balance' => 100
            ],
        ]);
    }

    /**
     * 測試 API /v1/user/userinfo
     * 預設有帶入 auth key 情境、但該會員為停權狀態
     */
    public function test_getUserInfo_suspended()
    {
        // Mock User
        $user = UserInfo::factory()->make([
            'user_id' => 12,
            'user_name' => "tester",
            'email' => "tester@gg.co",
            'status' => 0,
            'create_time' => 123456789,
            'balance' => 100
        ]);

        // Mock createToken for user
        $mockToken = (object) [
            'plainTextToken' => 'fake-token',
            'accessToken' => (object) ['abilities' => ['user']]
        ];

        $user = Mockery::mock($user);
        $user->shouldReceive('createToken')
            ->andReturn($mockToken);

        // Mock Sanctum::actingAs
        Sanctum::actingAs($user, ['user']);

        // call API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer fake-token',
        ])->getJson('/v1/user/userinfo');

        // assert status & response json
        $response->assertStatus(400);
        $response->assertJson([
            'result' => false,
            'error_code' => 400004,
            'error_msg' => '會員為停用狀態'
        ]);
    }

    /**
     * 測試 API /v1/user/payment
     * 預設有帶入 auth key 情境、成功付款
     */
    public function test_payment()
    {
        // Mock User
        $user = UserInfo::factory()->make([
            'user_id' => 12,
            'user_name' => "tester",
            'email' => "tester@gg.co",
            'status' => 1,
            'create_time' => 123456789,
            'balance' => 1000
        ]);

        // Mock createToken for user
        $mockToken = (object) [
            'plainTextToken' => 'fake-token',
            'accessToken' => (object) ['abilities' => ['user']]
        ];

        // Mock Sanctum::actingAs
        Sanctum::actingAs($user, ['user']);

        $user = Mockery::mock($user);
        $user->shouldReceive('createToken')
            ->andReturn($mockToken);

        // call API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer fake-token',
        ])->postJson('/v1/user/payment', [
            'machine_id' => 1,
            'amount' => 150
        ]);

        // 用 machine_id 檢查該設備是否存在且啟用，不存在或未啟用回傳 error
//        $machineMock = Mockery::mock('alias:App\Models\MachineInfo');
//        $machineMock->shouldReceive('where->exists')->andReturn(true);
//        $machineMock->shouldReceive('where->first')->andReturn((object)[
//            'machine_id ' => 1,
//            'machine_name' => 'Washing Machine',
//            'status' => 1,
//            'create_time' => 1721448307,
//            'update_time' => 1721448307
//        ]);

        // 以 auth token 取得 user_id，並檢查會員是否存在及停用狀態
        $user->shouldReceive('where->lockForUpdate->firstOrFail')->andReturn((object)[
            'id' => 1,
            'balance' => 1000,
            'save' => null,
            'balance' => 900, // 扣款後的餘額
        ]);

        // 模擬 UserPaymentRecord 模型
        $userPaymentRecordMock = Mockery::mock('alias:App\Models\UserPaymentRecord');
        $userPaymentRecordMock->shouldReceive('create')->andReturn((object)[
            'user_id' => 1,
            'machine_id' => 101,
            'transaction_amount' => 100,
            'transaction_type' => 'p',
            'after_transaction_balance' => 900,
            'transaction_time' => now()->toDateTimeString(),
        ]);

        // 模擬 MachinePaymentRecord 模型
        $machinePaymentRecordMock = Mockery::mock('alias:App\Models\MachinePaymentRecord');
        $machinePaymentRecordMock->shouldReceive('create')->andReturn((object)[
            'machine_id' => 101,
            'user_id' => 1,
            'transaction_amount' => 100,
            'transaction_type' => 'p',
            'transaction_time' => now()->toDateTimeString(),
        ]);

        // 斷言回應
//        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Transaction completed successfully',
            'user' => [
                'id' => 1,
                'balance' => 900,
            ]
        ]);
    }
}
