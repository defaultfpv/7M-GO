<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService) 
    {
        $this->authService = $authService;
    }


    public function register(Request $request) 
    {
        // Валидация входящих данных
        Log::info('Принял запрос на авторизацию:', $request->all());
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|min:1',
            'access_token' => 'required|string|min:16|max:512',
            'email' => 'required_without:messanger|email|max:255',
            'messanger' => 'required_without:email|in:telegram,max',
            'chat_id' => 'required_if:messanger,telegram,required_if:messanger,max|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->register($request->all());
        
        if ($result) {
            return response()->json(['success' => true], 200);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Registration failed'
        ], 403);
    }


    public function login(Request $request) 
    {
        // Валидация входящих данных
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|min:1',
            'access_token' => 'required|string|min:16|max:512',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->login($request->all());
        
        if ($result) {
            return response()->json(['success' => true], 200);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Authorization failed'
        ], 403);
    }





/**
 * @OA\Post(
 *     path="/auth/logout",
 *     tags={"Авторизация"},
 *     summary="Выход пользователя",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Успешный выход",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Не авторизован"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Ошибка выхода"
 *     )
 * )
 */
    public function logout(Request $request) 
    {
        $token = $request->bearerToken();
        $result = $this->authService->logout($token);
        
        if ($result) {
            return response()->json(['success' => true], 200);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'failed'
        ], 403);
    }




/**
 * @OA\Get(
 *     path="/auth/sessions",
 *     summary="Получить список активных сессий текущего пользователя",
 *     description="Возвращает список всех активных сессий (устройств), с которых пользователь авторизован. Требуется аутентификация через Bearer-токен.",
 *     tags={"Авторизация"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Response(
 *         response=200,
 *         description="Список активных сессий пользователя",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=14, description="ID сессии"),
 *                 @OA\Property(property="ip_address", type="string", example="127.0.0.1", description="IP-адрес, с которого была создана сессия"),
 *                 @OA\Property(property="user_agent", type="string", example="PostmanRuntime/7.49.1", description="User-Agent браузера/клиента"),
 *                 @OA\Property(property="device_type", type="string", example="bot", description="Тип устройства (desktop, mobile, bot, unknown и т.д.)"),
 *                 @OA\Property(property="browser", type="string", example="unknown", description="Браузер"),
 *                 @OA\Property(property="platform", type="string", example="unknown", description="Платформа/ОС"),
 *                 @OA\Property(
 *                     property="last_used_at",
 *                     type="string",
 *                     format="date-time",
 *                     example="2026-03-06T13:05:28.000000Z",
 *                     description="Время последнего использования сессии"
 *                 ),
 *                 @OA\Property(
 *                     property="created_at",
 *                     type="string",
 *                     format="date-time",
 *                     example="2026-03-06T08:37:57.000000Z",
 *                     description="Время создания сессии"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Не авторизован (токен отсутствует, недействителен или истёк)",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Доступ запрещён (например, токен не имеет нужных прав)",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Forbidden")
 *         )
 *     )
 * )
 */
    public function getSessions(Request $request) {
        $user = $request->user();
        $result = $this->authService->getSessions($user);
        return response()->json($result, 200);
    }





/**
 * @OA\Delete(
 *     path="/auth/sessions",
 *     summary="Удалить выбранные сессии текущего пользователя",
 *     description="Удаляет одну или несколько активных сессий (устройств) по их ID. Требуется аутентификация через Bearer-токен. После удаления сессии пользователь будет разлогинен на соответствующих устройствах.",
 *     tags={"Авторизация"},
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="session_ids[]",
 *         in="query",
 *         required=true,
 *         description="Массив ID сессий, которые нужно удалить (можно передать несколько значений через session_ids[]=11&session_ids[]=22)",
 *         @OA\Schema(
 *             type="array",
 *             @OA\Items(type="integer", example=11)
 *         ),
 *         example="session_ids[]=11&session_ids[]=13"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Сессии успешно удалены",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true,
 *                 description="Флаг успешного выполнения"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Не авторизован (токен отсутствует, недействителен или истёк)",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Доступ запрещён (например, токен не имеет прав или сессии не принадлежат пользователю)",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Forbidden")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Ошибка валидации (например, session_ids не передан или не является массивом)",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The session_ids field is required."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\AdditionalProperties(
 *                     type="array",
 *                     @OA\Items(type="string")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function deleteSessions(Request $request) {
        $validator = Validator::make($request->all(), [
            'session_ids' => 'required|array|',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $resoult = $this->authService->deleteSessions($request->user(), $request->get('session_ids'));
        return response()->json($resoult, 200);
    }

}