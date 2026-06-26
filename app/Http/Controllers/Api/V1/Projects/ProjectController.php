<?php

namespace App\Http\Controllers\Api\V1\Projects;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\User;
use App\Services\Telegram\TelegramBotService;
use App\Services\Users\UserService;

class ProjectController extends Controller
{
    private $botToken;
    private $apiUrl;
    protected $telegramBotService;

    public function __construct(TelegramBotService $telegramBotService, UserService $userService)
    {
        $this->telegramBotService = $telegramBotService;
    }

}