<?php

namespace Optix\Media\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Optix\Media\ImageManipulator;
use Optix\Media\Models\Media;

class PerformConversions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Media */
    protected $media;

    /** @var array */
    protected $conversions;

    /**
     * Create a new job instance.
     *
     * @param Media $media
     * @param array $conversions
     * @return void
     */
    public function __construct(Media $media, array $conversions)
    {
        $this->media = $media;

        $this->conversions = $conversions;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(ImageManipulator::class)->manipulate(
            $this->media, $this->conversions
        );
    }

    /** @return Media */
    public function getMedia()
    {
        return $this->media;
    }

    /** @return array */
    public function getConversions()
    {
        return $this->conversions;
    }
}
