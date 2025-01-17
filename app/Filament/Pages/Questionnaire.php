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
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use App\Models\Progress as ModelProgress;
use Filament\Forms\Components\Placeholder;
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

    public ?array $question_options_set = [];

    public ?array $question_set = [];

    public ?array $to_save = [];
    
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
                        ->visible(fn () => $question && $question->description)
                        ->view('question-description', ['desc' => $question->description]),
                    ViewField::make('image_preview')
                        ->view('image-display', ['record' => $question])
                        ->visible(fn () => $question && $question->image),
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
        $options_set = [];
        $data = Question::select('section_id','title','options','image','description')->where('id', $id)->get()->toArray();
        $this->question_set[$id]['title'] = $data[0]['title'];
        $this->question_set[$id]['description'] = $data[0]['description'];
        $this->question_set[$id]['image'] = $data[0]['image'];
        $this->section_id = $data[0]['section_id'];
        $data = $data[0]['options'];
        foreach($data as $items){
            $options[$items['title']] = $items['title'];
            $options_set[] = $items['title']; 
            if($items['correct']){
                $this->answers[$id] = $items['title'];
            }
        }
        $this->question_options_set[$id] = $options_set;
        return $options;
    }

    public function save()
    {
        $data = $this->form->getState();

        $all = [];

        foreach($this->question_options_set as $key => $value){
            $all[$key]['title'] = $this->question_set[$key]['title'];
            $all[$key]['description'] = $this->question_set[$key]['description'] ?? null;
            $all[$key]['image'] = $this->question_set[$key]['image'] ?? null;
            for ($i=0; $i < count($value); $i++) {
                if($data[$key] == $value[$i]){
                    if($value[$i] == $this->answers[$key]){
                        $all[$key]['choices'][$i] = ['value' => $value[$i], 'selected' => true, 'correct' => true];
                    }
                    else{
                        $all[$key]['choices'][$i] = ['value' => $value[$i], 'selected' => true, 'correct' => false];
                    }
                }
                else{
                    if($value[$i] == $this->answers[$key]){
                        $all[$key]['choices'][$i] = ['value' => $value[$i], 'selected' => false, 'correct' => true];
                    }
                    else{
                        $all[$key]['choices'][$i] = ['value' => $value[$i], 'selected' => false, 'correct' => false];
                    }
                }
            }
        }
        $this->to_save[] = $all;
        
        $this->validateAnswers($data, $all);
    }

    public function validateAnswers($state, $all)
    {
        $score = count($state);

        foreach ($state as $key => $userAnswer) {
            if (!isset($userAnswer) || $userAnswer !== $this->answers[$key]) {
                $score = $score - 1;
            }
        }
        $this->score = $score;
        $this->submit($score, $all);
    }

    public function submit($score, $all)
    {
        ModelProgress::create([
            'section_id' => $this->section_id,
            'user_id' => auth()->user()->id,
            'score' => $score .'/'.count($this->answers),
            'response' => $all
        ]);

        $this->questions->shuffle();
        
        Notification::make()
            ->title('Score saved successfully')
            ->body('You scored '.$score.' out of '.count($this->answers).' correct answers')
            ->success()
            ->send();
        // $this->dispatch('open-modal', id: 'view-results');
    }
}