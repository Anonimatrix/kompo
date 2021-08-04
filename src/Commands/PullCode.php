<?php

namespace Kompo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PullCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kompo:pull {uuid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull a specific set of files from kompo.io';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $request = Http::post('http://kompoio.local/api/pull-bundle/'.$this->argument('uuid'));

        $pull = $request->json();

        $items = $pull['pull_items'];

        foreach ($items as $item) {
            $this->handleFile($item['item']);
        }

        dd($request->json());
    }

    protected function handleFile($file)
    {
        if (
            file_exists(base_path($file['path'])) 
            && !$this->confirm('Do you wish to overwrite '.$file['path'].'?')
        ) {
            return;
        }

        file_put_contents(base_path($file['path']), $file['file_contents']);

    }
}