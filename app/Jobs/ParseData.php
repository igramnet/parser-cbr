<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $startDate;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        (new \App\Services\ParserService)->GetDataOnDate($this->startDate);
    }

    public function failed(Exception $exception)
    {
        // usually would send new notification to admin/user
        info($exception);
    }
}
