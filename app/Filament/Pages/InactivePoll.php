<?php

namespace App\Filament\Pages;

use App\Models\PollVote;
use Filament\Pages\Page;
use App\Models\PollComment;
use App\Models\Poll as PollModel;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class InactivePoll extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static string $view = 'filament.pages.inactive-poll';

    protected static ?string $navigationGroup = 'Voting';
    
    protected static ?string $navigationLabel = 'Expired Polls';

    protected static ?string $title = 'Expired Polls';

    protected static ?int $navigationSort = 2;

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
                    ->orWhere('expires_at', '<', now());
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