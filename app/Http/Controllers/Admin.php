<?php

namespace App\Http\Controllers;

use App\Models\AdminInfo;
use App\Models\MachineInfo;
use App\Models\UserInfo;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Admin extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1/admin/userlist",
     *      operationId="userlist",
     *      tags={"admin"},
     *      summary="取得會員列表",
     *      description="取得會員列表",
     *      security={
     *          {
     *              "Authorization": {}
     *          }
     *      },
     *      @OA\Parameter(
     *          name="is_enable",
     *          description="帳號是否啟用(1 or 0)",
     *          required=false,
     *          in="query",
     *          example="1",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="create_time",
     *          description="帳號創立時間",
     *          required=false,
     *          in="query",
     *          example="123456789",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="create_time_operator",
     *          description="帳號創立時間 運算子",
     *          required=false,
     *          in="query",
     *          example=">",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="amount",
     *          description="餘額",
     *          required=false,
     *          in="query",
     *          example="250",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="amount_operator",
     *          description="餘額 運算子",
     *          required=false,
     *          in="query",
     *          example="<",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          description="會員 ID",
     *          required=false,
     *          in="query",
     *          example="2545566",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="user_name",
     *          description="會員名稱",
     *          required=false,
     *          in="query",
     *          example="tester",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="第幾頁",
     *          required=false,
     *          in="query",
     *          example="1",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          description="每頁幾筆",
     *          required=false,
     *          in="query",
     *          example="50",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          description="排序欄位",
     *          required=false,
     *          in="query",
     *          example="user_id",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          description="排序方式",
     *          required=false,
     *          in="query",
     *          example="asc",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="result",
     *                     type="bool"
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     @OA\Property(
     *                         property="user_list",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="user_id", type="integer", example=1),
     *                             @OA\Property(property="user_name", type="string", example="test"),
     *                             @OA\Property(property="email", type="string", example="test@gg.tt"),
     *                             @OA\Property(property="status", type="integer", example=1),
     *                             @OA\Property(property="create_time", type="integer", example=1721202502),
     *                             @OA\Property(property="balance", type="integer", example=100)
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="total_user_count",
     *                         type="integer",
     *                         example=75
     *                     ),
     *                     @OA\Property(
     *                         property="page",
     *                         type="integer",
     *                         example=1
     *                     )
     *                 ),
     *             )
     *         )
     *       ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="result",
     *                     type="bool",
     *                     example=false
     *                 ),
     *                 @OA\Property(
     *                     property="error_code",
     *                     type="int",
     *                     example=401001
     *                 ),
     *                 @OA\Property(
     *                     property="error_msg",
     *                     type="string",
     *                     example="Unauthenticated."
     *                 ),
     *                 example={"result": false, "error_code": 401001, "error_msg": "Unauthenticated."}
     *             )
     *         )
     *       ),
     *     )
     */
    public function GetUserList(Request $request)
    {
        $res = [];
        if ($request->query()) {
            // 有帶參數 => 檢查參數
            $validRule = [
                'is_enable' => 'nullable|integer|in:0,1',
                'create_time' => 'nullable|integer',
                'create_time_operator' => 'nullable|string|in:>,<,=,>=,<=',
                'amount' => 'nullable|integer',
                'amount_operator' => 'nullable|string|in:>,<,=,>=,<=',
                'user_id' => 'nullable|integer',
                'user_name' => 'nullable|string|max:255',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1',
                'sort' => 'nullable|string|in:user_id,user_name,email,create_time,amount',
                'order' => 'nullable|string|in:ASC,DESC,asc,desc'
            ];

            $errMsg = [
                'string' => '必須為字串',
                'integer' => '必須為整數',
                'max' => '超出最大值(:max)',
                'min' => '低於最小值(:min)',
                'in' => '欄位非指定值(:values)',
            ];

            $validator = Validator::make($request->all(), $validRule, $errMsg);
            // 資料驗證有誤，回傳 error msg
            $resultMsg = "";
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $k => $v) {
                    $resultMsg = $resultMsg . $k . " ";
                    foreach ($v as $k2 => $v2) {
                        $resultMsg = $resultMsg . $v2 . ", ";
                    }
                }
                $resultMsg = rtrim($resultMsg, ", ");

                return response()->json([
                    'result' => false,
                    'error_code' => 400001, // API 驗證錯誤
                    'error_msg' => $resultMsg,
                ], 400);
            }

            $isEnable = $request->query('is_enable');
            $createTime = $request->query('create_time');
            $createTimeOperator = $request->query('create_time_operator');
            $amount = $request->query('amount');
            $amountOperator = $request->query('amount_operator');
            $userId = $request->query('user_id');
            $userName = $request->query('user_name');
            $page = $request->query('page');
            $limit = $request->query('limit');
            $sort = $request->query('sort');
            $order = $request->query('order');

            $query = UserInfo::select('user_id', 'user_name', 'email', 'status', 'create_time', 'balance');

            if (!is_null($isEnable)) {
                $query->where('status', '=', $isEnable);
            }
            if ($createTime && $createTimeOperator) {
                $query->where('create_time', $createTimeOperator, $createTime);
            }
            if ($amount && $amountOperator) {
                $query->where('balance', $amountOperator, $amount);
            }
            if ($userId) {
                $query->where('user_id', $userId);
            }
            if ($userName) {
                $query->where('user_name', 'like', '%' . $userName . '%');
            }
            if ($sort && $order) {
                $query->orderBy($sort, $order);
            } else {
                $query->orderBy('user_id', 'asc');
            }
            if ($page && $limit) {
                $query->paginate($limit, ['*'], 'page', $page);
            } else {
                $query->paginate(50, ['*'], 'page', 1);
            }
            $data = $query->get();

            // 整理資料為 json
            $userList = [];
            foreach ($data as $d) {
                $user = [
                    'user_id' => $d->user_id,
                    'user_name' => $d->user_name,
                    'email' => $d->email,
                    'status' => $d->status,
                    'create_time' => $d->create_time,
                    'amount' => $d->balance
                ];
                $userList[] = $user;
            }

            $res['result'] = true;
            $res['data'] = [
                'user_list' => $userList,
                'total_user_count' => $query->count(),
                'page' => is_null($page) ? 1:$page
            ];
        } else {
            // 沒帶參數 => 預設查詢第一頁，每頁50筆資料，以 user_id ASC 排序
            $data = UserInfo::select('user_id', 'user_name', 'email', 'status', 'create_time', 'balance')
                ->orderBy('user_id', 'asc')
                ->paginate(50, ['*'], 'page', 1);

            $userList = [];
            foreach ($data as $d) {
                $user = [
                    'user_id' => $d->user_id,
                    'user_name' => $d->user_name,
                    'email' => $d->email,
                    'status' => $d->status,
                    'create_time' => $d->create_time,
                    'balance' => $d->balance
                ];
                $userList[] = $user;
            }

            $res['result'] = true;
            $res['data'] = [
                'user_list' => $userList,
                'total_user_count' => $data->total(),
                'page' => 1
            ];
        }

        return response()->json($res);
    }

    /**
     * @OA\Post(
     *     path="/v1/admin/new-machine",
     *     operationId="new-machine",
     *     tags={"admin"},
     *     summary="新增設備",
     *     description="新增設備",
     *     security={
     *           {
     *               "Authorization": {}
     *           }
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"machine_name", "status"},
     *             @OA\Property(
     *                 property="machine_name",
     *                 type="string",
     *                 example="Washing Machine",
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="int",
     *                 example="1",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="result",
     *                     type="bool"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string"
     *                 ),
     *                 example={"result": true, "message": "success"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="result",
     *                     type="bool"
     *                 ),
     *                 @OA\Property(
     *                     property="error_code",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="error_msg",
     *                     type="string"
     *                 ),
     *                 example={"result": false, "error_code": 400001, "error_msg": "The JSON payload is not in the correct format."}
     *             )
     *         )
     *     )
     * )
     */
    public function NewMachine(Request $request)
    {
        $validRule = [
            'machine_name' => 'required|string|max:255',
            'status' => 'required|integer|in:0,1'
        ];

        $errMsg = [
            'required' => '為必填欄位',
            'string' => '必須為字串',
            'integer' => '必須為 0 或 1',
            'max' => '超出最大值(:max)',
            'in' => '欄位非指定值(:values)'
        ];

        $resultMsg = "";

        $validator = Validator::make($request->json()->all(), $validRule, $errMsg);
        // 資料驗證有誤，回傳 error msg
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $k => $v) {
                $resultMsg = $resultMsg . $k . " ";
                foreach ($v as $k2 => $v2) {
                    $resultMsg = $resultMsg . $v2 . ", ";
                }
            }
            $resultMsg = rtrim($resultMsg, ", ");

            return response()->json([
                'result' => false,
                'error_code' => 400001, // API 驗證錯誤
                'error_msg' => $resultMsg,
            ], 400);
        }

        // 驗證通過，寫入 machine_info
        $newMachine['machine_name'] = $request->json('machine_name');
        $newMachine['status'] = $request->json('status');
        date_default_timezone_set('Asia/Taipei');
        $newMachine['create_time'] = time();
        $newMachine['update_time'] = time();

        try {
            $createAdmin = MachineInfo::create($newMachine);
            if ($createAdmin) {
                return response()->json([
                    'result' => 'true',
                    'message' => '新增設備成功',
                ]);
            }
        } catch (QueryException $queryException) {
            // 寫入 DB 異常
            Log::error('資料寫入錯誤', ['exception' => $queryException->getMessage()]);
            return response()->json([
                'result' => false,
                'error_code' => 400101, // 寫入 DB 異常
                'error_msg' => $queryException->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            // 其他異常
            Log::error('資料寫入錯誤', ['exception' => $e->getMessage()]);
            return response()->json([
                'result' => false,
                'error_code' => 400002, // 其他異常
                'error_msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *      path="/v1/admin/newadmin",
     *      operationId="newadmin",
     *      tags={"admin"},
     *      summary="新增管理員",
     *      description="新管理員註冊",
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"admin_name", "password", "email"},
     *             @OA\Property(
     *                 property="admin_name",
     *                 type="string",
     *                 example="Bob",
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="579f2cfcfa83",
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 example="eva.lin321@tems.edu",
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="result",
     *                     type="bool"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string"
     *                 ),
     *                 example={"result": true, "message": "success"}
     *             )
     *         )
     *       ),
     *      @OA\Response(
     *         response=400,
     *         description="Error",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="result",
     *                     type="bool"
     *                 ),
     *                 @OA\Property(
     *                     property="error_code",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="error_msg",
     *                     type="string"
     *                 ),
     *                 example={"result": false, "error_code": 400001, "error_msg": "The JSON payload is not in the correct format."}
     *             )
     *         )
     *       ),
     *     )
     */
    public function NewAdmin(Request $request)
    {
        $validRule = [
            'admin_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9]+$/',
            'password' => 'required|string|min:10|max:255|regex:/^\S+$/',
            'email' => 'required|string|email|max:500',
        ];

        $errMsg = [
            'required' => '為必填欄位',
            'string' => '必須為字串',
            'max' => '超出最大值(:max)',
            'min' => '低於最小值(:min)',
            'email' => '非正確格式',
            'regex' => '格式不符(admin_name 不能有特殊符號, password 不能有空格)',
        ];

        $resultMsg = "";

        $validator = Validator::make($request->json()->all(), $validRule, $errMsg);
        // 資料驗證有誤，回傳 error msg
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $k => $v) {
                $resultMsg = $resultMsg . $k . " ";
                foreach ($v as $k2 => $v2) {
                    $resultMsg = $resultMsg . $v2 . ", ";
                }
            }
            $resultMsg = rtrim($resultMsg, ", ");

            return response()->json([
                'result' => false,
                'error_code' => 400001, // API 驗證錯誤
                'error_msg' => $resultMsg,
            ], 400);
        }

        // 驗證正確，將管理員資料寫入DB (hash pwd, get now timestamp)

        $newAdminData['admin_name'] = $request->json('admin_name');
        $newAdminData['admin_hash'] = Hash::make($request->json('password'));
        $newAdminData['email'] = $request->json('email');
        $newAdminData['status'] = 1;
        date_default_timezone_set('Asia/Taipei');
        $newAdminData['create_time'] = time();

        try {
            $createAdmin = AdminInfo::create($newAdminData);
            if ($createAdmin) {
                return response()->json([
                    'result' => 'true',
                    'message' => '新增管理員成功',
                ]);
            }
        } catch (QueryException $queryException) {
            // 寫入 DB 異常
            Log::error('資料寫入錯誤', ['exception' => $queryException->getMessage()]);
            return response()->json([
                'result' => false,
                'error_code' => 400101, // 寫入 DB 異常
                'error_msg' => $queryException->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            // 其他異常
            Log::error('資料寫入錯誤', ['exception' => $e->getMessage()]);
            return response()->json([
                'result' => false,
                'error_code' => 400002, // 其他異常
                'error_msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *      path="/v1/admin/login",
     *      operationId="admin login",
     *      tags={"admin"},
     *      summary="管理員登入",
     *      description="管理員登入",
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 example="eva.lin321@tems.edu"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="579f2cfcfa83",
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="登入成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="result",
     *                     type="bool",
     *                     example=true
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     @OA\Property(
     *                         property="token",
     *                         type="string",
     *                         example="e3a45282d0621f0a6fc83eeb061247f0e6d94f0d3f6c29f98f"
     *                     )
     *                 ),
     *                 example={"result":true,"data":{"token":"e3a45282d0621f0a6fc83eeb061247f0e6d94f0d3f6c29f98f"}}
     *             )
     *         )
     *       )
     *     )
     */
    public function Login(Request $request)
    {
        $email = $request->json('email');
        $password = $request->json('password');
        $admin = AdminInfo::where('email', $email)->first();

        if (!$admin || !Hash::check($password, $admin->admin_hash)) {
            return response()->json([
                'result' => false,
                'error_code' => 400003, // 登入失敗(email 或密碼錯誤)
                'error_msg' => 'Email not found or error password.',
            ], 400);
        }
        // 刪除 personal_access_tokens 內的資料
        $admin->tokens()->delete();
        // 新增資料到 personal_access_tokens： tokenable_id = admin_id, name = $admin->admin_name, abilities = ["admin"], expires_at = 一天後
        $token = $admin->createToken($admin->admin_name, ['admin'], now()->addDay())->plainTextToken;

        return response()->json([
            'result' => 'true',
            'data' => ['token' => $token],
        ]);
    }

    /**
     * @OA\Post(
     *      path="/v1/admin/logout",
     *      operationId="admin logout",
     *      tags={"admin"},
     *      summary="管理員登出",
     *      description="管理員登出",
     *      security={
     *          {
     *              "Authorization": {}
     *          }
     *      },
     *      @OA\Response(
     *         response=200,
     *         description="登出成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="result",
     *                     type="bool",
     *                     example=true
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Logged out successfully"
     *                 )
     *             )
     *         )
     *       )
     *     )
     */
    public function Logout(Request $request)
    {
        // 以 token 取得目前 admin 資訊
        $adminInfo = $request->user();

        // 清除 token
        $adminInfo->tokens()->delete();

        return response()->json([
            'result' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
