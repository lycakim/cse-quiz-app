@if ($record->image)
<div>
    <img src="{{ Storage::url($record->image) }}" alt="Image Preview"
        style="max-width: 50%; width: 200px; height: auto;">
</div>
@endif