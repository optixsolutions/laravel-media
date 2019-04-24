<?php

namespace Optix\Media\Jobs;

use Illuminate\Bus\Queueable;
use Optix\Media\Models\Media;
use Optix\Media\ImageManipulator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PerformConversions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $media;

    protected $conversions;

    public function __construct(Media $media, array $conversions)
    {
        $this->media = $media;

        $this->conversions = $conversions;
    }

    public function handle()
    {
        app(ImageManipulator::class)->manipulate(
            $this->media, $this->conversions
        );
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function getConversions()
    {
        return $this->conversions;
    }
}
