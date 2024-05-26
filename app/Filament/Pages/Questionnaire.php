<?php

namespace App\Filament\Pages;

use App\Models\Section;
use App\Models\Question;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Placeholder;
use App\Models\Progress as ModelProgress;
use Filament\Forms\Concerns\InteractsWithForms;

class Questionnaire extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.questionnaire';

    protected static ?int $navigationSort = 1;

    public Collection $questions;

    public ?array $form_data = [];
    
    public ?array $answers = [];
    
    public $score = 0;

    public $section_id = null;

    public function mount(): void
    {
        $this->questions = Question::where('section_id', Section::all()->random()->id)->get();
        $this->questions = $this->questions->shuffle();
    }

    public function form(Form $form): Form
    {
        $steps = $this->questions->map(function ($question, $key) {
                return Wizard\Step::make($question->id)
                    ->label(false)
                    ->schema([
                        Placeholder::make($question->id)
                            ->label(false)
                            ->content($question->title),
                        Placeholder::make($question->id)
                            ->label(false)
                            ->visible(fn () => $question->description != null)
                            ->view('question-description', ['desc' => $question->description]),
                        Radio::make($question->id)
                            ->label(false)
                            ->inline(false)
                            ->required()
                            ->inlineLabel(false)
                            ->options($this->getOptions($question->id))
                    ]);
        });

        return $form
        ->schema([
            Wizard::make($steps->toArray())
            ->submitAction(new HtmlString(Blade::render(<<<BLADE
            <x-filament::button
                type="submit"
                wire:click.prevent="save"
                size="md"
            >
                Submit
            </x-filament::button>
        BLADE)))
        ])
        ->statePath('form_data');
    }

    protected function getOptions($id): array
    {
        $options = [];
        $data = Question::select('section_id','options')->where('id', $id)->get()->toArray();
        $this->section_id = $data[0]['section_id'];
        $data = $data[0]['options'];
        foreach($data as $items){
            $options[$items['title']] = $items['title'];
            if($items['correct']){
                $this->answers[$id] = $items['title'];
            }
        }
        return $options;
    }

    public function save()
    {
        $data = $this->form->getState();
        $this->validateAnswers($data);
    }

    public function validateAnswers($state)
    {
        $score = count($state);

        foreach ($state as $key => $userAnswer) {
            if (!isset($userAnswer) || $userAnswer !== $this->answers[$key]) {
                $score = $score - 1;
            }
        }
        $this->score = $score;
        $this->submit($score);
    }

    public function submit($score)
    {
        ModelProgress::create([
            'section_id' => $this->section_id,
            'user_id' => auth()->user()->id,
            'score' => $score .'/'.count($this->answers)
        ]);
        $this->dispatch('open-modal', id: 'view-results');
    }
}