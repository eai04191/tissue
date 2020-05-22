<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;

class CheckinCsvExporter
{
    /** @var User Target user */
    private $user;
    /** @var string Output filename */
    private $filename;
    /** @var string Output charset */
    private $charset;

    public function __construct(User $user, string $filename, string $charset)
    {
        $this->user = $user;
        $this->filename = $filename;
        $this->charset = $charset;
    }

    public function execute()
    {
        $csv = Writer::createFromPath($this->filename, 'wb');
        $csv->setNewline("\r\n");
        if ($this->charset === 'SJIS-win') {
            $csv->addStreamFilter('convert.mbstring.encoding.UTF-8:SJIS-win');
        }

        $header = ['日時', 'ノート', 'オカズリンク'];
        for ($i = 1; $i <= 32; $i++) {
            $header[] = "タグ{$i}";
        }
        $csv->insertOne($header);

        DB::transaction(function () use ($csv) {
            // TODO: そんなに読み取り整合性を保つ努力はしていないのと、chunkの件数これでいいか分からない
            $this->user->ejaculations()->with('tags')->orderBy('ejaculated_date')
                ->chunk(1000, function ($ejaculations) use ($csv) {
                    foreach ($ejaculations as $ejaculation) {
                        $record = [
                            $ejaculation->ejaculated_date->format('Y/m/d H:i'),
                            $ejaculation->note,
                            $ejaculation->link,
                        ];
                        foreach ($ejaculation->tags->take(32) as $tag) {
                            $record[] = $tag->name;
                        }
                        $csv->insertOne($record);
                    }
                });
        });
    }
}