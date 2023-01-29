<li class="directory dir-tree @if(in_array($directory->id, ExploreService::getSelectedDirectories())) dir-open @endif"
    act="file" href="{{route('admin.directory.show', $directory)}}"
    edit-href="{{route('admin.directory.edit', $directory)}}"
    data-file-type="App\Models\Directory">
    <div class="directory-content">
        <a href="#" class="directory-name">{{$directory->title}}</a>
    </div>
    @php $directories = $directory->directories @endphp
    @php $depth = ($depth ?? 0) + 1 @endphp
    @if(count(is_countable($directories)?$directories:[]) > 0 and $depth < 3)
        <ul class="sub-directories">
            @foreach($directories as $directory)
                @include('admin.templates.explore.directory', compact('directory', 'depth'))
            @endforeach
        </ul>
    @endif
</li>
