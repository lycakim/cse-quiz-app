<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class AccountProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.pages.account-profile';

    protected static ?int $navigationSort = 7;

    public ?array $data = [];

    public function mount(): void
    {
        $data = User::where('id', auth()->user()->id)->first();

        $this->form->fill([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Information')
                    ->schema([
                        TextInput::make('name'),
                        TextInput::make('email'),
                        TextInput::make('password')
                            ->password()
                            ->minLength(8)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->afterStateUpdated(fn ($state, $set) => $set('password', '')),
                    ])
                    ->columns(2),
            ])
            ->statePath('data')
            ->model(User::class);
    }

    public function save()
    {
        $data = $this->form->getState();

        $user = User::find(auth()->user()->id);

        $user->name = $data['name'];

        $user->email = $data['email'];

        if (isset($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        session()->put([
            'password_hash_'.auth()->getDefaultDriver() => $user->getAuthPassword(),
        ]);

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
    }
}