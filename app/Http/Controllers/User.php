<?php

namespace App\Http\Controllers;

use App\Models\MachineInfo;
use App\Models\MachinePaymentRecord;
use App\Models\UserInfo;
use App\Models\UserPaymentRecord;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
class User extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1/user/userinfo",
     *      operationId="userinfo",
     *      tags={"user"},
     *      summary="取得會員資料",
     *      description="取得會員資料",
     *      security={
     *          {
     *              "Authorization": {}
     *          }
     *      },
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
     *                     @OA\Property(property="user_id", type="integer", example=12),
     *                     @OA\Property(property="user_name", type="string", example="paul"),
     *                     @OA\Property(property="email", type="string", example="paul@test.net"),
     *                     @OA\Property(property="create_time", type="integer", example=1721202502),
     *                     @OA\Property(property="balance", type="integer", example=200)
     *                 )
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
    public function UserInfo(Request $request)
    {
        // 以 token 取得登入帳號資料
        $userInfo = $request->user();
        $res = [];
        if (!is_null($userInfo->user_id)) {
            if ($userInfo->status == 0) {
                $res['result'] = false;
                $res['error_code'] = 400004; // 該會員為停用狀態
                $res['error_msg'] = "會員為停用狀態";
                return response()->json($res, 400);
            } else {
                $res['result'] = true;
                $res['data'] = [
                    'user_id' => $userInfo->user_id,
                    'user_name' => $userInfo->user_name,
                    'email' => $userInfo->email,
                    'create_time' => $userInfo->create_time,
                    'balance' => $userInfo->balance,
                ];
            }
        }
        return response()->json($res);
    }

    /**
     * @OA\Post(
     *      path="/v1/user/payment",
     *      operationId="payment",
     *      tags={"user"},
     *      summary="會員付款",
     *      description="會員付款",
     *      security={
     *           {
     *               "Authorization": {}
     *           }
     *       },
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"machine_id", "user_id", "amount"},
     *             @OA\Property(
     *                 property="machine_id",
     *                 type="int",
     *                 example="12",
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="int",
     *                 example="150",
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
     *                     property="data",
     *                     type="object",
     *                     @OA\Property(property="user_id", type="integer", example=21),
     *                     @OA\Property(property="machine_id", type="integer", example=1),
     *                     @OA\Property(property="transaction_amount", type="integer", example=50),
     *                     @OA\Property(property="after_transaction_balance", type="integer", example=750),
     *                     @OA\Property(property="transaction_time", type="integer", example=1721540702)
     *                 )
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
    public function Payment(Request $request)
    {
        $validRule = [
            'machine_id' => 'required|integer',
            'amount' => 'required|integer'
        ];

        $errMsg = [
            'required' => '為必填欄位',
            'integer' => '必須為整數'
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

        $machineId = $request->json('machine_id');
        $amount = $request->json('amount');

        // 用 machine_id 檢查該設備是否存在且啟用，不存在或未啟用回傳 error
        $machine = MachineInfo::where('machine_id', $machineId);
        if (!$machine->exists()) {
            return response()->json([
                'result' => false,
                'error_code' => 400004, // 資料不存在
                'error_msg' => "該設備不存在",
            ], 400);
        }

        $mData = $machine->first();
        if ($mData->status == 0) {
            return response()->json([
                'result' => false,
                'error_code' => 400005, // 設備為停用狀態
                'error_msg' => "該設備已停用",
            ], 400);
        }

        // 設備為啟用狀態，以 auth token 取得 user_id，並檢查會員是否存在及停用狀態
        $userInfo = $request->user();
        if (!is_null($userInfo->user_id)) {
            if ($userInfo->status == 0) {
                return response()->json([
                    'result' => false,
                    'error_code' => 400004, // 該會員為停用狀態
                    'error_msg' => "會員為停用狀態",
                ], 400);
            }
        } else {
            return response()->json([
                'result' => false,
                'error_code' => 400005, // 該會員不存在
                'error_msg' => "該會員不存在",
            ], 400);
        }

        // 3. 以 user_id 查 user_info
        // 4. 取 user_info balance(餘額) 與 amount(消費金額) 比較消費金額是否高於餘額，高於餘額回傳 error
        if ($userInfo->balance < $amount) {
            return response()->json([
                'result' => false,
                'error_code' => 400006, // 會員餘額不足
                'error_msg' => "會員餘額不足",
            ], 400);
        }
        // 確認消費金額小於餘額，開始交易機制
        DB::beginTransaction();
        // 鎖定該筆 user_info，扣款前再次檢查餘額是否足夠，再對 user_info.balance 扣款
        try {
            $user = UserInfo::where('user_id', $userInfo->user_id)->lockForUpdate()->firstOrFail();
            if ($user->balance < $amount) {
                throw new \Exception('會員餘額不足', 400006);
            }

            $user->balance = $user->balance - $amount;
            $user->save();

            // 將交易紀錄寫入 會員交易紀錄
            $afterBalance = $user->balance;
            $transactionTime = time();
            UserPaymentRecord::Create([
                'user_id' => $userInfo->user_id,
                'machine_id' => $machineId,
                'transaction_amount' => $amount,
                'transaction_type' => 'p', // 扣款代碼為 'p'
                'after_transaction_balance' => $afterBalance,
                'transaction_time' => $transactionTime,
                'note' => null
            ]);

            // 將交易紀錄寫入 設備交易紀錄
            MachinePaymentRecord::create([
                'machine_id' => $machineId,
                'user_id' => $userInfo->user_id,
                'transaction_amount' => $amount,
                'transaction_type' => 'p', // 扣款代碼為 'p'
                'transaction_time' => $transactionTime,
                'note' => null
            ]);

            // 三張表都更新/寫入資料，結束交易機制
            DB::commit();

            // 回傳交易成功訊息與會員交易後餘額
            return response()->json([
                'result' => true,
                'data' => [
                    'user_id' => $userInfo->user_id,
                    'machine_id' => $machineId,
                    'transaction_amount' => $amount,
                    'after_transaction_balance' => $afterBalance,
                    'transaction_time' => $transactionTime
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'error_code' => $e->getCode(),
                'error_msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/user/payment-records",
     *      operationId="payment-records",
     *      tags={"user"},
     *      summary="查詢交易紀錄",
     *      description="查詢交易紀錄",
     *      security={
     *          {
     *              "Authorization": {}
     *          }
     *      },
     *      @OA\Parameter(
     *          name="transaction_time",
     *          description="交易時間",
     *          required=false,
     *          in="query",
     *          example="1721547894",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="transaction_time_operator",
     *          description="交易時間 運算子",
     *          required=false,
     *          in="query",
     *          example=">",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="transaction_amount",
     *          description="交易金額",
     *          required=false,
     *          in="query",
     *          example="120",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="transaction_amount_operator",
     *          description="交易金額 運算子",
     *          required=false,
     *          in="query",
     *          example=">",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="machine_id",
     *          description="設備 ID",
     *          required=false,
     *          in="query",
     *          example="1",
     *          @OA\Schema(
     *              type="int"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="transaction_type",
     *          description="交易類型(p:扣款/c:儲值/o:其他)",
     *          required=false,
     *          in="query",
     *          example="p",
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
     *          example="machine_id",
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
     *                         property="record_list",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="machine_id", type="integer", example=1),
     *                             @OA\Property(property="machine_name", type="string", example="Washing Machine"),
     *                             @OA\Property(property="transaction_amount", type="integer", example=120),
     *                             @OA\Property(property="after_transaction_balance", type="integer", example=750),
     *                             @OA\Property(property="transaction_type", type="string", example="p"),
     *                             @OA\Property(property="transaction_time", type="integer", example=1721202502),
     *                             @OA\Property(property="note", type="string", example="test payment")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="total_record_count",
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
    public function PaymentRecord(Request $request)
    {
        $res = [];
        if ($request->query()) {
            $validRule = [
                'transaction_time' => 'nullable|integer',
                'transaction_time_operator' => 'nullable|string|in:>,<,=,>=,<=',
                'transaction_amount' => 'nullable|integer',
                'transaction_amount_operator' => 'nullable|string|in:>,<,=,>=,<=',
                'machine_id' => 'nullable|integer',
                'transaction_type' => 'nullable|string|max:10|in:p,c,o',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1',
                'sort' => 'nullable|string|in:transaction_amount,transaction_time,machine_id',
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

            $transactionTime = $request->query('transaction_time');
            $transactionTimeOperator = $request->query('transaction_time_operator');
            $transactionAmount = $request->query('transaction_amount');
            $transactionAmountOperator = $request->query('transaction_amount_operator');
            $machineId = $request->query('machine_id');
            $transactionType = $request->query('transaction_type');
            $page = $request->query('page');
            $limit = $request->query('limit');
            $sort = $request->query('sort');
            $order = $request->query('order');

            $query = DB::table('user_payment_record as upr')
                ->leftJoin('machine_info as mi', 'upr.machine_id', '=', 'mi.machine_id')
                ->select(
                    'upr.user_id', 'upr.machine_id', 'mi.machine_name', 'upr.transaction_amount',
                    'upr.transaction_type', 'upr.after_transaction_balance', 'upr.transaction_time', 'upr.note');

            if ($transactionTime && $transactionTimeOperator) {
                $query->where('transaction_time', $transactionTimeOperator, $transactionTime);
            }
            if ($transactionAmount && $transactionAmountOperator) {
                $query->where('transaction_amount', $transactionAmountOperator, $transactionAmount);
            }
            if ($machineId) {
                $query->where('machine_id', $machineId);
            }
            if ($transactionType) {
                $query->where('transaction_type', '=', $transactionType);
            }
            if ($sort && $order) {
                $query->orderBy($sort, $order);
            } else {
                $query->orderBy('transaction_time', 'asc');
            }
            if ($page && $limit) {
                $query->paginate($limit, ['*'], 'page', $page);
            } else {
                $query->paginate(50, ['*'], 'page', 1);
            }
            $data = $query->get();

            $recordList = [];
            foreach ($data as $d) {
                $record = [
                    'machine_id' => $d->machine_id,
                    'machine_name' => $d->machine_name,
                    'transaction_amount' => $d->transaction_amount,
                    'after_transaction_balance' => $d->after_transaction_balance,
                    'transaction_type' => $d->transaction_type,
                    'transaction_time' => $d->transaction_time,
                    'note' => is_null($d->note) ? '' : $d->note
                ];
                $recordList[] = $record;
            }

            $res['result'] = true;
            $res['data'] = [
                'record_list' => $recordList,
                'total_record_count' => $data->count(),
                'page' => is_null($page) ? 1:$page
            ];
        } else {
            // 沒帶參數 => 預設查詢第一頁，每頁50筆資料，以 transaction_time ASC 排序
            $data = DB::table('user_payment_record as upr')
                ->leftJoin('machine_info as mi', 'upr.machine_id', '=', 'mi.machine_id')
                ->select(
                    'upr.user_id', 'upr.machine_id', 'mi.machine_name', 'upr.transaction_amount',
                    'upr.transaction_type', 'upr.after_transaction_balance', 'upr.transaction_time', 'upr.note')
                ->orderBy('transaction_time', 'asc')
                ->paginate(50, ['*'], 'page', 1);

            $recordList = [];
            foreach ($data as $d) {
                $record = [
                    'machine_id' => $d->machine_id,
                    'machine_name' => $d->machine_name,
                    'transaction_amount' => $d->transaction_amount,
                    'after_transaction_balance' => $d->after_transaction_balance,
                    'transaction_type' => $d->transaction_type,
                    'transaction_time' => $d->transaction_time,
                    'note' => is_null($d->note) ? '' : $d->note
                ];
                $recordList[] = $record;
            }

            $res['result'] = true;
            $res['data'] = [
                'record_list' => $recordList,
                'total_record_count' => $data->total(),
                'page' => 1
            ];
        }

        return response()->json($res);
    }

    /**
     * @OA\Post(
     *      path="/v1/user/newuser",
     *      operationId="newuser",
     *      tags={"user"},
     *      summary="新增會員",
     *      description="新會員註冊",
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_name", "password", "email"},
     *             @OA\Property(
     *                 property="user_name",
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
    public function NewUser(Request $request)
    {
        $validRule = [
            'user_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9]+$/',
            'password' => 'required|string|min:10|max:255|regex:/^\S+$/',
            'email' => 'required|string|email|max:500',
        ];

        $errMsg = [
            'required' => '為必填欄位',
            'string' => '必須為字串',
            'max' => '超出最大值(:max)',
            'min' => '低於最小值(:min)',
            'email' => '非正確格式',
            'regex' => '格式不符(user_name 不能有特殊符號, password 不能有空格)',
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

        // 驗證正確，將會員資料寫入DB (hash pwd, get now timestamp)

        $newUserData['user_name'] = $request->json('user_name');
        $newUserData['user_hash'] = Hash::make($request->json('password'));
        $newUserData['email'] = $request->json('email');
        $newUserData['status'] = 1;
        date_default_timezone_set('Asia/Taipei');
        $newUserData['create_time'] = time();
        $newUserData['balance'] = 0;

        try {
            $createUser = UserInfo::create($newUserData);
            if ($createUser) {
                return response()->json([
                    'result' => 'true',
                    'message' => '新增會員成功',
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
     *      path="/v1/user/login",
     *      operationId="user login",
     *      tags={"user"},
     *      summary="會員登入",
     *      description="會員登入",
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
        $user = UserInfo::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->user_hash)) {
            return response()->json([
                'result' => false,
                'error_code' => 400003, // 登入失敗(email 或密碼錯誤)
                'error_msg' => 'Email not found or error password.',
            ], 400);
        }
        // 刪除 personal_access_tokens 內的資料
        $user->tokens()->delete();
        // 新增資料到 personal_access_tokens： tokenable_id = user_id, name = $user->user_name, abilities = ["user"], expires_at = 一天後
        $token = $user->createToken($user->user_name, ['user'], now()->addDay())->plainTextToken;

        return response()->json([
            'result' => 'true',
            'data' => ['token' => $token],
        ]);
    }

    /**
     * @OA\Post(
     *      path="/v1/user/logout",
     *      operationId="user logout",
     *      tags={"user"},
     *      summary="會員登出",
     *      description="會員登出",
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
        // 以 token 取得目前 user 資訊
        $userInfo = $request->user();

        // 清除 token
        $userInfo->tokens()->delete();

        return response()->json([
            'result' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
