<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\AdminInfo;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class AdminTest extends TestCase
{
    // skip middleware test
//    use WithoutMiddleware;

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/v1/getuserlist');

        $response->assertStatus(500);
    }

    public function test_getUserList_nodata()
    {
        // skip middleware (sanctum) test
        $this->withoutMiddleware();

        // call API
        $response = $this->getJson('/v1/getuserlist');

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

    public function test_getUserList_havedata()
    {
        // skip middleware (sanctum) test
        $this->withoutMiddleware();

        // call API
        $response = $this->getJson('/v1/getuserlist');
    }

    public function test_getUserList_unauthenticated()
    {
        // call API without skip middleware
        $response = $this->getJson('/v1/getuserlist');
        $response->assertStatus(401);
    }

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
                'error_code' => 400001, // API 驗證錯誤
                'error_msg' => 'password 低於最小值(10)',
            ]);
    }

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
