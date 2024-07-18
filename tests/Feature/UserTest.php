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
     * 測試 API /v1/user/getuserinfo
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
        ])->getJson('/v1/user/getuserinfo');

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
     * 測試 API /v1/user/getuserinfo
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
        ])->getJson('/v1/user/getuserinfo');

        // assert status & response json
        $response->assertStatus(400);
        $response->assertJson([
            'result' => false,
            'error_code' => 400004,
            'error_msg' => 'The member has been temporarily suspended.'
        ]);
    }
}
