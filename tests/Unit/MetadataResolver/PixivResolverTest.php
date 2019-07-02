<?php

namespace Tests\Unit\MetadataResolver;

use App\MetadataResolver\PixivResolver;
use Tests\TestCase;

class PixivResolverTest extends TestCase
{
    use CreateMockedResolver;

    public function setUp()
    {
        parent::setUp();

        if (!$this->shouldUseMock()) {
            sleep(1);
        }
    }

    public function testIllust()
    {
        $responseText = file_get_contents(__DIR__ . '/../../fixture/Pixiv/testIllust.json');

        $this->createResolver(PixivResolver::class, $responseText);

        $metadata = $this->resolver->resolve('https://www.pixiv.net/member_illust.php?mode=medium&illust_id=68188073');
        $this->assertEquals('coffee break', $metadata->title);
        $this->assertEquals('投稿者: 裕' . PHP_EOL, $metadata->description);
        $this->assertEquals('https://i.pixiv.cat/img-master/img/2018/04/12/00/01/28/68188073_p0_master1200.jpg', $metadata->image);
        $this->assertEquals(['オリジナル', 'カフェ', '眼鏡', 'イヤホン', 'ぱっつん', '艶ぼくろ', '眼鏡っ娘', 'オリジナル5000users入り'], $metadata->tags);
        if ($this->shouldUseMock()) {
            $this->assertSame('https://www.pixiv.net/ajax/illust/68188073', (string) $this->handler->getLastRequest()->getUri());
        }
    }

    public function testIllustMultiPages()
    {
        $responseText = file_get_contents(__DIR__ . '/../../fixture/Pixiv/testIllustMultiPages.json');

        $this->createResolver(PixivResolver::class, $responseText);

        $metadata = $this->resolver->resolve('https://www.pixiv.net/member_illust.php?mode=medium&illust_id=47220843');
        $this->assertEquals('がぶ飲みミルクティー', $metadata->title);
        $this->assertEquals('投稿者: きっぷる' . PHP_EOL . '劇中で度々お見かけするお姿がたまらなく愛おしいのです' . PHP_EOL . 'チラリズムでしょうか', $metadata->description);
        $this->assertEquals('https://i.pixiv.cat/img-master/img/2014/11/23/15/52/00/47220843_p0_master1200.jpg', $metadata->image);
        $this->assertEquals(['SHIROBAKO', '小笠原綸子', 'ゴスロリ様', '中出し', 'SHIRUPAKO', 'くわえたくしあげ', 'ずらし挿入', 'SHIROBAKO1000users入り', '破れストッキング'], $metadata->tags);
        if ($this->shouldUseMock()) {
            $this->assertSame('https://www.pixiv.net/ajax/illust/47220843', (string) $this->handler->getLastRequest()->getUri());
        }
    }

    public function testManga()
    {
        $responseText = file_get_contents(__DIR__ . '/../../fixture/Pixiv/testManga.json');

        $this->createResolver(PixivResolver::class, $responseText);

        $metadata = $this->resolver->resolve('https://www.pixiv.net/member_illust.php?mode=medium&illust_id=46713544');
        $this->assertEquals('冬の日ラブラブ', $metadata->title);
        $this->assertEquals('投稿者: Aza' . PHP_EOL . 'ラブラブエッチのらくがき' . PHP_EOL . PHP_EOL . '三万フォロワー感謝します～' . PHP_EOL . PHP_EOL . '最近忙しいので、自分の時間が少ない・・・', $metadata->description);
        $this->assertEquals('https://i.pixiv.cat/img-master/img/2014/10/25/00/06/58/46713544_p0_master1200.jpg', $metadata->image);
        $this->assertEquals(['落書き', 'おっぱい', 'オリジナル', 'パイズリ', '中出し', 'だいしゅきホールド', '愛のあるセックス', '黒髪ロング', 'オリジナル10000users入り'], $metadata->tags);
        if ($this->shouldUseMock()) {
            $this->assertSame('https://www.pixiv.net/ajax/illust/46713544', (string) $this->handler->getLastRequest()->getUri());
        }
    }
}
