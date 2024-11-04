<?php

namespace App\Filament\Pages;

use Closure;
use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

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
                Section::make('Personal Information')
                    ->description("Update your account's profile information and email address.")
                    ->schema([
                        TextInput::make('name'),
                        TextInput::make('email'),
                    ])
                    ->columns(2),
                Section::make('Update Password')
                    ->description('Ensure your account is using a long, random password to stay secure.')
                    ->schema([
                        TextInput::make('old_password')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->revealable()
                        ->rules([
                            fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                if (!Hash::check($value, auth()->user()->password)) {
                                    $fail('The :attribute is incorrect.');
                                }
                            },
                        ])
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state)),
                    TextInput::make('new_password')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if ($value !== $get('confirm_password')) {
                                    $fail('The :attribute confirmation does not match.');
                                }
                            },
                        ])
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state)),
                    TextInput::make('confirm_password')
                        ->label('Confirm Password')
                        ->password()
                        ->required()
                        ->minLength(8)
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state)),
                ])
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

        if (isset($data['new_password'])) {
            $user->password = $data['new_password'];
        }

        $user->save();
        
        // $this->reset(['data.old_password', 'data.new_password', 'data.confirm_password']);

        session()->put([
            'password_hash_'.auth()->getDefaultDriver() => $user->getAuthPassword(),
        ]);

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
    }
}