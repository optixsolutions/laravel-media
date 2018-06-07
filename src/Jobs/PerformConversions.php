<?php

namespace Optix\Media\Jobs;

use Optix\Media\Media;
use Illuminate\Bus\Queueable;
use Optix\Media\FileManipulator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PerformConversions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mediaId;

    protected $conversions;

    /**
     * Create a new job instance.
     *
     * @param  int  $mediaId
     * @param  array  $conversions
     */
    public function __construct($mediaId, array $conversions)
    {
        $this->mediaId = $mediaId;

        $this->conversions = $conversions;
    }

    /**
     * Execute the job.
     *
     * @param  FileManipulator  $manipulator
     * @return void
     */
    public function handle(FileManipulator $manipulator)
    {
        $media = Media::find($this->mediaId);

        if ($media) {
            $manipulator->manipulate($media, $this->conversions);
        }
    }
}
