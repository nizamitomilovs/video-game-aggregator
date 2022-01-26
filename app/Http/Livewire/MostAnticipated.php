<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class MostAnticipated extends Component
{
    public $mostAnticipated = [];

    public function loadMostAnticipated()
    {
        $current = Carbon::now()->timestamp;
        $afterFourMonths = Carbon::now()->addMonths(4)->timestamp;

        $mostAnticipatedUnformatted = Http::withHeaders(config('services.igdb'))
            ->withBody(
                "fields name, cover, cover.url, first_release_date, total_rating_count,
                platforms.abbreviation, rating, slug;
                where platforms = (48,49,130,6)
				& (total_rating_count>1
				& first_release_date >= {$current});
                sort total_rating_count desc;
                limit 4;", "text/plain"
            )->post('https://api.igdb.com/v4/games')
            ->json();

        $this->mostAnticipated = $this->formatForView($mostAnticipatedUnformatted);
    }

    public function render()
    {
        return view('livewire.most-anticipated');
    }

    private function formatForView($games)
    {
        return collect($games)->map(function ($game){
            return collect($game)->merge([
                'coverImageUrl' => Str::replaceFirst('thumb', 'cover_small', $game['cover']['url']),
                'releaseDate' => Carbon::parse($game['first_release_date'])->format('M d, Y')
            ]);
        })->toArray();
    }
}
