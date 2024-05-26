<x-filament-panels::page>
    <div style="margin-top: -50px">
        <div class="flex justify-between gap-2 mb-2">
            <div> </div>
            <div class="flex items-center mb-5 gap-2">
                <x-filament::button icon="heroicon-o-arrow-path" x-on:click.prevent="window.location.reload()" outlined>
                    <div class="flex items-center">
                        <p>Refresh Questions</p>
                    </div>
                </x-filament::button>
            </div>
        </div>

        <div>
            {{ $this->form }}
        </div>
    </div>

    {{-- view results --}}
    <x-filament::modal id="view-results" width="5xl" :close-by-clicking-away="false" :close-button="false">

        <x-slot name="heading">
            Results
        </x-slot>

        <x-slot name="description">
            You've got {{ $score }} correct answers out of {{ count($answers) }}!
        </x-slot>

        <div class="fi-modal-content overflow-y-auto">
            @foreach($questions as $key => $question)
            <div class="py-2">
                <h3>Question # {{ $key + 1 }}</h3>
                <h1>{{ $question->title }}</h1>
                <ul style="list-style-type: disc; padding-left: 20px;">
                    @foreach($this->getOptions($question->id) as $option)
                    <li>{{ $option }}
                        <span
                            style="color: #808080; font-size: 12px; margin-left: 5px;">{{ $this->answers[$question->id] == $option ? 'Right Answer': '' }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        <div
            class="fi-modal-footer-actions gap-3 flex flex-col-reverse sm:grid sm:grid-cols-[repeat(auto-fit,minmax(0,1fr))]">
            <x-filament::button color="gray" outlined size="lg"
                x-on:click.prevent="$dispatch('close-modal', {id: 'view-results'}); window.location.reload()">
                Cancel
            </x-filament::button>
            <x-filament::button size="lg" x-on:click.prevent="window.location.reload()">
                Take another quiz
            </x-filament::button>
        </div>
    </x-filament::modal>
</x-filament-panels::page>

<script>
function reloadPage() {
    window.location.reload();
}
</script>