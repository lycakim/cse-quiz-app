<x-filament-panels::page>
    <div style="margin-top: -75px">
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
</x-filament-panels::page>