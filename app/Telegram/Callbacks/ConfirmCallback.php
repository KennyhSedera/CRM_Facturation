<?php

namespace Telegram\Callbacks;

class ConfirmCallback
{
    public function handle($data)
    {
        // Handle the confirm callback logic here
        // You can access the data passed to the callback through the $data parameter

        // Example: Print the received data
        echo "Received data: " . json_encode($data) . "\n";

        // Example: Extract the 'confirm' field from the data
        $confirm = $data['confirm'] ?? null;

        // Example: Perform some logic based on the 'confirm' field
        if ($confirm === true) {
            // User confirmed
            echo "User confirmed\n";
        } else {
            // User did not confirm
            echo "User did not confirm\n";
        }
    }
}
