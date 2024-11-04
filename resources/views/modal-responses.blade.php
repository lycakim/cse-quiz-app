<div>
    <!-- <h2 class="text-md font-bold dark:text-gray-900">Post Details</h2> -->
    <p class="mt-1 text-md dark:text-gray-600 mb-4">
        Here list down all of {{ $record->user['id'] == auth()->user()->id ? 'your' : $record->user['name']."'s" }}
        answers.
    </p>
    <div class="flex flex-col dark:bg-gray-100">
        @foreach($record->response as $key => $value)
        <div class="border p-6 rounded-lg shadow-md w-80 my-2" style="border-color: #2a2a2c;">
            <h2 class="text-md font-bold dark:text-gray-900"> {{ $value['title'] }}</h2>
            @if(isset($value['description']))
            <pre class="py-2">{{ $value['description'] }}</pre>
            @endif
            @if(isset($value['image']))
            <div class="my-4">
                <img src="{{ Storage::url($value['image']) }}" alt="Image Preview"
                    style="max-width: 50%; width: 150px; height: auto;">
            </div>
            @endif
            @foreach($value['choices'] as $key2 => $rec)
            <div class="my-2">
                <input type="radio" id="{{ $rec['value'] }}" name="{{ $value['title'] }}" value="{{ $rec['value'] }}"
                    :checked="{{ $rec['selected'] }}" disabled>
                <label for="{{ $rec['value'] }}" style="padding-left: 5px;">{{ $rec['value'] }}</label>
                <span
                    style="color: #808080; font-size: 12px; margin-left: 5px;">{{ $rec['correct'] ? 'Right Answer': '' }}</span>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>