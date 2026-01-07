<?php

namespace App\Telegram\Handlers;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class DocumentHandler
{
    protected $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handle(Update $update)
    {
        if ($update->getMessage() && $update->getMessage()->getDocument()) {
            $this->processDocument($update);
        }
    }

    protected function processDocument(Update $update)
    {
        $message = $update->getMessage();
        $document = $message->getDocument();

        $fileId = $document->getFileId();
        $fileName = $document->getFileName();
        $fileSize = $document->getFileSize();

        // Process document here
    }
}
