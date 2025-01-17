<x-filament::page>
    <div class="space-y-6">
        @if($polls->isEmpty())
            <x-filament::card class="text-center">
                <p class="text-gray-500 text-sm">No active polls found.</p>
            </x-filament::card>
        @endif
        {{-- Active Polls List --}}
        @foreach($polls as $poll)
            <x-filament::card>
                @php
                    $hasVoted = $poll->options->pluck('id')->intersect($votedPollIds)->isNotEmpty();
                    $totalVotes = $poll->options->sum(fn($option) => $option->votes->count());
                @endphp
                <div class="space-y-4">
                    @if(!$hasVoted)
                    <x-filament::section>
                        <x-slot name="heading">
                            {{ $poll->title }}
                        </x-slot>
                        <x-slot name="headerActions">
                            @if($hasVoted)
                            <span class="text-gray-500 text-xs">
                                Total votes: {{ $totalVotes }}
                            </span>
                            @endif
                        </x-slot>
                        <form wire:submit.prevent="vote" class="space-y-4">
                            <!-- Poll Options -->
                            <div class="space-y-3">
                                <p class="text-md">{{ $poll->description }}</p>
                                @foreach($poll->options as $option)
                                    <label class="flex items-center space-x-3">
                                        <!-- Radio Input -->
                                        <input
                                            type="radio"
                                            wire:click="vote"
                                            name="option_{{ $poll->id }}"
                                            wire:model="selectedOption"
                                            value="{{ $option->id }}"
                                            class="h-4 w-4 text-primary-600 focus:ring-primary-500"
                                        >
                                        <!-- Option Text -->
                                        <span class="text-sm font-medium text-gray-800" style="margin-left: 5px;">
                                            {{ $option->option_text }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </form>
                    </x-filament::section>
                    @else
                        <x-filament::section>
                            <x-slot name="heading">
                                {{ $poll->title }}
                            </x-slot>
                            <x-slot name="headerActions">
                                @if($hasVoted)
                                <span class="text-gray-500 text-md">
                                    Total votes: {{ $totalVotes }}
                                </span>
                                @endif
                            </x-slot>
                            <div class="space-y-3">
                                <p class="text-md">{{ $poll->description }}</p>
                                @foreach($poll->options as $option)
                                    @php
                                        $voteCount = $option->votes->count();
                                        $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span>&bullet;&nbsp;&nbsp;{{ $option->option_text }}</span>
                                            <span class="text-gray-500">{{ $percentage }}% ({{ $voteCount }} votes)</span>
                                        </div>
                                        <div class="mt-1 h-2 rounded-full">
                                            <div
                                                class="h-2 dark:bg-white rounded-full"
                                                style="width: {{ $percentage }}%"
                                            ></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-filament::section>
                        {{-- Comments Section --}}
                        <x-filament::section collapsible collapsed>
                            <x-slot name="heading">
                                Comments
                            </x-slot>
                            <x-slot name="headerActions">
                                <span class="text-gray-500 text-xs">
                                    {{ $poll->allComments()->count() ? $poll->allComments()->count() . ' comments' : 'No comments yet' }}
                                </span>
                            </x-slot>
                            <div>                              
                                {{-- Add Comment Form --}}
                                <div class="mb-4">
                                    <form wire:submit.prevent="addComment({{ $poll->id }})">
                                        {{ $this->form }}
                                        <x-filament::button
                                            size="xs"
                                            type="submit"
                                            class="mt-2"
                                        >
                                            Add Comment
                                        </x-filament::button>
                                    </form>
                                </div>
    
                                {{-- Comments List --}}
                                <div class="space-y-4">
                                    @foreach($poll->comments()->with(['user', 'replies.user'])->latest()->get() as $comment)
                                        <div class="rounded-lg p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <span class="text-xs font-medium">{{ $comment->user->name }}</span>
                                                    <span class="text-xs text-gray-500 ml-5">
                                                        {{ $comment->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                                
                                                @if($comment->user_id === auth()->id() || auth()->user()->isAdmin())
                                                    <x-filament::button
                                                        color="danger"
                                                        size="xs"
                                                        wire:click="deleteComment({{ $comment->id }})"
                                                    >
                                                        Delete
                                                    </x-filament::button>
                                                @endif
                                            </div>
                                            
                                            <p class="mt-2 text-xs">{{ $comment->comment }}</p>
                                            
                                            {{-- Reply Button --}}
                                            <div class="mt-2">
                                                <button
                                                    wire:click="startReply({{ $comment->id }})"
                                                    class="text-xs text-primary-600 hover:text-primary-800"
                                                >
                                                    Reply
                                                </button>
                                            </div>
    
                                            {{-- Reply Form --}}
                                            @if($replyingTo === $comment->id)
                                                <div class="mt-3 ml-6">
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input
                                                            wire:model="replyText"
                                                            placeholder="Write your reply..."
                                                        />
                                                    </x-filament::input.wrapper>
                                                    
                                                    <div class="mt-2 space-x-2">
                                                        <x-filament::button
                                                            wire:click="addReply({{ $comment->id }})"
                                                            size="xs"
                                                        >
                                                            Post Reply
                                                        </x-filament::button>
                                                        
                                                        <x-filament::button
                                                            wire:click="cancelReply"
                                                            color="secondary"
                                                            size="xs"
                                                        >
                                                            Cancel
                                                        </x-filament::button>
                                                    </div>
                                                </div>
                                            @endif
    
                                            {{-- Replies --}}
                                            @foreach($comment->replies as $reply)
                                                <div class="ml-6 mt-3 rounded-lg p-3">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <span class="text-xs font-medium">{{ $reply->user->name }}</span>
                                                            <span class="text-xs text-gray-500 ml-2">
                                                                {{ $reply->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                        
                                                        @if($reply->user_id === auth()->id() || auth()->user()->isAdmin())
                                                            <x-filament::button
                                                                color="danger"
                                                                size="xs"
                                                                wire:click="deleteComment({{ $reply->id }})"
                                                            >
                                                                Delete
                                                            </x-filament::button>
                                                        @endif
                                                    </div>
                                                    <p class="mt-2 text-xs">{{ $reply->comment }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </x-filament::section>
                    @endif

                    @if($poll->expires_at)
                    <div class="flex justify-between items-center mt-4">
                        <div class="text-xs text-gray-500">
                            Expires on {{ $poll->expires_at->format('M d, Y H:i') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Correct answer will be manually posted after the poll
                        </div>
                    </div>
                    @endif
                </div>
            </x-filament::card>
        @endforeach
    </div>
</x-filament::page>
