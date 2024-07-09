<?php

namespace App\Http\Controllers;

use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class User extends Controller
{
    /**
     * @OA\Post(
     *      path="/v1/getuserinfo",
     *      operationId="getuserinfo",
     *      tags={"user"},
     *      summary="取得會員資料",
     *      description="取得會員資料",
     *      security={
     *          {
     *              "Authorization": {}
     *          }
     *      },
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_name"},
     *             @OA\Property(
     *                 property="user_name",
     *                 type="string",
     *                 example="Bob",
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
    public function GetUserInfo(Request $request)
    {
        // 有帶 token 後才做後續驗證 管理員/會員功能
        // 1. 用 token 取得權限資料，檢查是否為會員
        // 2. 是會員 => 可查看會員資料，非會員 => 阻擋並回傳錯誤訊息
        // test token = Bearer 12|HeLwM4wvcKufWPDtw3hiQbXdswSpnwvwpxx21Buy40080ca3

        // 取得登入帳號資料
        $user = $request->user();
        var_dump($user);die();
    }

    /**
     * @OA\Post(
     *      path="/v1/newuser",
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
        // 1. get user input data
        // 2. vaildation input
        // 3. pwd hash
        // 4. write sql to db

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
        $newUserData['money'] = 0;

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
        // 1. 撈 user_info or admin_info 檢查帳號密碼是否正確
        // 2. 確認正確後，產生 token 存入 login_status，並同時寫入 user 等級
        // login_status:
        //      user_id
        //      token
        //      expire_time


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

    public function Logout(Request $request)
    {
        // 1. 檢查帶入 token 與 user_id 是否正確
        // 2. 若正確，清除 login_status 上的該筆資料，完成登出動作
    }
}
