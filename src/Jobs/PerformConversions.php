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

    /**
     * @var Media
     */
    protected $media;

    /**
     * @var array
     */
    protected $conversions;

    /**
     * Create a new PerformConversions instance.
     *
     * @param  Media  $media
     * @param  array  $conversions
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

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return array
     */
    public function getConversions()
    {
        return $this->conversions;
    }
}
