<?php

namespace Tests\Unit\MetadataResolver;

use App\MetadataResolver\NicoSeigaResolver;
use Tests\TestCase;

class NicoSeigaResolverTest extends TestCase
{
    use CreateMockedResolver;

    public function setUp()
    {
        parent::setUp();

        if (!$this->shouldUseMock()) {
            sleep(1);
        }
    }

    public function testSeiga()
    {
        $responseText = file_get_contents(__DIR__ . '/../../fixture/NicoSeiga/seiga.html');

        $this->createResolver(NicoSeigaResolver::class, $responseText);

        $metadata = $this->resolver->resolve('https://seiga.nicovideo.jp/seiga/im9623750');
        $this->assertSame('シャミ子 / まとけち さんのイラスト', $metadata->title);
        $this->assertSame('シャミ子が悪いんだよ・・・', $metadata->description);
        $this->assertSame('https://lohas.nicoseiga.jp/thumb/9623750l?', $metadata->image);
        $this->assertSame(['アニメ', 'まちカドまぞく', 'シャミ子', 'シャドウミストレス優子', '吉田優子', '危機管理フォーム', 'シャミ子が悪いんだよ', '赤面', 'シャミ子は悪くないよ'], $metadata->tags);
        if ($this->shouldUseMock()) {
            $this->assertSame('https://seiga.nicovideo.jp/seiga/im9623750', (string) $this->handler->getLastRequest()->getUri());
        }
    }

    public function testShunga()
    {
        $responseText = file_get_contents(__DIR__ . '/../../fixture/NicoSeiga/shunga.html');

        $this->createResolver(NicoSeigaResolver::class, $responseText);

        $metadata = $this->resolver->resolve('https://seiga.nicovideo.jp/seiga/im9232798');
        $this->assertSame('ベッドのゆかりさん / せゆーら/Se-U-Ra さんのイラスト', $metadata->title);
        $this->assertSame('待つ側の方がつよいってスマブラが伝えてきたので', $metadata->description);
        $this->assertSame('https://lohas.nicoseiga.jp/thumb/9232798l?', $metadata->image);
        $this->assertSame(['結月ゆかり', 'VOICEROID', '裸パーカー', '謎の光'], $metadata->tags);
        if ($this->shouldUseMock()) {
            $this->assertSame('https://seiga.nicovideo.jp/seiga/im9232798', (string) $this->handler->getLastRequest()->getUri());
        }
    }
}
