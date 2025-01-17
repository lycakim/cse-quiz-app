<?php

namespace App\Filament\Pages;

use App\Models\PollVote;
use Filament\Pages\Page;
use App\Models\PollOption;
use App\Models\PollComment;
use App\Models\Poll as PollModel;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class Poll extends Page implements HasForms
{    
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Voting';
    
    protected static ?string $navigationLabel = 'Vote in Active Polls';

    protected static ?string $title = 'Active Polls';
    
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.poll';

    public $selectedPoll = null;
    
    public $selectedOption = null;

    public $commentText = '';
    
    public $replyText = '';
    
    public $replyingTo = null;

    protected function getViewData(): array
    {
        $polls = PollModel::where('status', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['options.votes'])
            ->get();

        // Get polls user has already voted in
        $votedPollIds = PollVote::where('user_id', Auth::id())
            ->whereHas('option.poll', function($query) {
                $query->where('status', true);
            })
            ->pluck('poll_option_id')
            ->toArray();

        return [
            'polls' => $polls,
            'votedPollIds' => $votedPollIds,
        ];
    }

    public function vote()
    {
        // Validate vote
        if (! $this->selectedOption) {
            Notification::make()
                ->title('Please select an option')
                ->danger()
                ->send();
            return;
        }
        else{
            $this->selectedPoll = PollOption::find($this->selectedOption)->poll_id;
        }

        // Check if user already voted
        $existingVote = PollVote::where('user_id', Auth::id())
            ->whereHas('option.poll', function($query) {
                $query->where('id', $this->selectedPoll);
            })
            ->exists();

        if ($existingVote) {
            Notification::make()
                ->title('You have already voted in this poll')
                ->danger()
                ->send();
            return;
        }

        // Create vote
        PollVote::create([
            'poll_option_id' => $this->selectedOption,
            'user_id' => Auth::id()
        ]);

        $this->selectedPoll = null;
        $this->selectedOption = null;

        Notification::make()
            ->title('Vote recorded successfully!')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('commentText')
                ->label('Add a comment')
                ->rows(2)
                ->maxLength(500),
        ];
    }

    public function addComment($pollId)
    {
        $this->validate([
            'commentText' => 'required|min:2|max:500',
        ]);

        PollComment::create([
            'poll_id' => $pollId,
            'user_id' => auth()->id(),
            'comment' => $this->commentText,
        ]);

        $this->commentText = '';

        Notification::make()
            ->title('Comment added successfully!')
            ->success()
            ->send();
    }

    public function startReply($commentId)
    {
        $this->replyingTo = $commentId;
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
        $this->replyText = '';
    }

    public function addReply($commentId)
    {
        $this->validate([
            'replyText' => 'required|min:2|max:500',
        ]);

        PollComment::create([
            'poll_id' => PollComment::find($commentId)->poll_id,
            'user_id' => auth()->id(),
            'comment' => $this->replyText,
            'parent_id' => $commentId,
        ]);

        $this->replyText = '';
        $this->replyingTo = null;

        Notification::make()
            ->title('Reply added successfully!')
            ->success()
            ->send();
    }

    public function deleteComment($commentId)
    {
        $comment = PollComment::find($commentId);
        
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return;
        }

        $comment->delete();

        Notification::make()
            ->title('Comment deleted successfully!')
            ->success()
            ->send();
    }
}