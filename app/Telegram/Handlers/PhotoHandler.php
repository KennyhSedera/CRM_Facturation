<?php

namespace App\Telegram\Handlers;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class PhotoHandler
{
    protected $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handle(Update $update)
    {
        $message = $update->getMessage();
        $photo = $message->getPhoto();

        if (!$photo) {
            return $this->sendError($message->getChat()->getId());
        }

        $photoId = end($photo)->getFileId();
        $filePath = $this->downloadPhoto($photoId);

        $this->processPhoto($filePath, $message);
        $this->sendConfirmation($message->getChat()->getId());
    }

    private function downloadPhoto($fileId)
    {
        $file = $this->telegram->getFile(['file_id' => $fileId]);
        return $file->getFilePath();
    }

    private function processPhoto($filePath, $message)
    {
        // Add your photo processing logic here
        // Example: save to database, resize, etc.
    }

    private function sendConfirmation($chatId)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Photo received and processed successfully!'
        ]);
    }

    private function sendError($chatId)
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Error: Please send a valid photo.'
        ]);
    }
}
