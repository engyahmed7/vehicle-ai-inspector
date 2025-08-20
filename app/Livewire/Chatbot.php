<?php

namespace App\Livewire;

use Prism\Prism\Prism;
use Livewire\Component;
use App\Models\ChatInteraction;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

class Chatbot extends Component
{
    public $messages = [];

    public $question;

    public $answer;

    public function mount()
    {
        $this->messages = ChatInteraction::where('user_id', auth()->id() ?? session()->getId())
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function askQuestion()
    {
        $conversation = [];

        foreach ($this->messages as $message) {
            $conversation[] = new UserMessage($message->question);
            $conversation[] = new AssistantMessage($message->answer);
        }

        $conversation[] = new UserMessage($this->question);

        $response = Prism::text()
            ->using(Provider::Groq, 'llama-3.1-8b-instant')
            ->withSystemPrompt('You are a helpful FAQ assistant.')
            ->withMessages($conversation)
            ->asText();

        $this->answer = $response->text;

        $interaction = ChatInteraction::create([
            'question' => $this->question,
            'answer' => $this->answer,
            'user_id' => auth()->id() ?? session()->getId(),
        ]);

        $this->messages->push($interaction);
        $this->question = '';
    }

    public function render()
    {
        return view('livewire.chatbot');
    }
}
