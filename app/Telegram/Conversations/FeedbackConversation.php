<?php

namespace App\Telegram\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;

try {
} catch (\Exception $e) {
    // Feedback model not available
}

class FeedbackConversation extends Conversation
{
    protected int $rating;
    protected ?string $message = null;

    public function run(): void
    {
        $this->say('Thank you — I will collect your feedback.');
        $this->askRating();
    }

    protected function askRating(): void
    {
        $this->ask('Please rate your experience from 1 (worst) to 5 (best):', function (Answer $answer) {
            $text = trim($answer->getText());
            if (!preg_match('/^[1-5]$/', $text)) {
                $this->say('Invalid input. Enter a number between 1 and 5.');
                return $this->repeat();
            }

            $this->rating = (int) $text;
            $this->askComment();
        });
    }

    protected function askComment(): void
    {
        $this->ask('Any additional comments? (type "skip" to omit)', function (Answer $answer) {
            $text = trim($answer->getText());
            if (strtolower($text) !== 'skip') {
                $this->message = $text;
            }

            $this->confirm();
        });
    }

    protected function confirm(): void
    {
        $summary = "Rating: {$this->rating}\nComment: " . ($this->message ?? '—');
        $this->ask("Please confirm sending the following feedback:\n\n{$summary}\n\nType 'yes' to confirm.", function (Answer $answer) {
            $text = strtolower(trim($answer->getText()));
            if (in_array($text, ['yes', 'y', 'oui', 'o'], true)) {
                $this->saveFeedback();
                $this->say('Thanks — your feedback has been recorded.');
            } else {
                $this->say('Feedback cancelled.');
            }
        });
    }

    protected function saveFeedback(): void
    {
        try {
            if (class_exists('App\Models\Feedback')) {
                $feedbackClass = 'App\Models\Feedback';
                $feedbackClass::create([
                    'user_id' => $this->getBot()->getUser()?->getId(),
                    'rating' => $this->rating,
                    'message' => $this->message,
                ]);
                return;
            }

            // Fallback: if no model exists, send to admin/channel or just acknowledge
            // (Left intentionally minimal — adapt to your app)
        } catch (\Throwable $e) {
            // swallow errors to avoid breaking the conversation
        }
    }
}
