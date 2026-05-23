<div>
    @if($movie)
        @include('livewire.vote-area-item', ['movie' => $movie])
    @else
        @foreach($roomMovies as $movie)
            @include('livewire.vote-area-item', ['movie' => $movie])
        @endforeach
    @endif
</div>
