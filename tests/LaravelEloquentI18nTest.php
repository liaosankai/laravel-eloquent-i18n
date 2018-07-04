<?php

namespace Liaosankai\LaravelEloquentI18n\Tests;

use Illuminate\Support\Facades\App;
use Liaosankai\LaravelEloquentI18n\Mocks\Models\Article;
use Liaosankai\LaravelEloquentI18n\Mocks\Models\Book;
use Liaosankai\LaravelEloquentI18n\Models\Translation;

/**
 * Class LaravelRadioTest
 *
 * @package SuperPlatform\CasinoFaq\Tests\Http\Controllers
 */
class LaravelEloquentI18nTest extends BaseTestCase
{


    public function setUp()
    {
        parent::setUp();

        $this->loadPackageMigrations();
        $this->loadMockMigrations();
    }

    /**
     * 測試 Primary Key 使用 Uuid 字串類型的 Model
     *
     * @test
     */
    public function testModelWithUuidPrimaryKey()
    {
        $article = Article::create([
            'title' => '雙城記',
            'content' => '我是內容'
        ]);

        $article->translations()->saveMany([
            new Translation([
                'key' => 'title',
                'value' => '標題啦',
                'locale' => 'zh-Hant'
            ]),
            new Translation([
                'key' => 'title',
                'value' => '标题啦',
                'locale' => 'zh-Hans'
            ]),
            new Translation([
                'key' => 'title',
                'value' => 'title',
                'locale' => 'en'
            ]),
        ]);

        $this->assertEquals(3, $article->translations->count());

    }

    /**
     * 測試 Primary Key 使用 int 數字類型的 Model
     *
     * @test
     */
    public function testModelWithIntegerPrimaryKey()
    {
        $book = Book::create([
            'title' => 'english-la-la-la',
            'content' => '我是內容'
        ]);

        $book->translations()->saveMany([
            new Translation([
                'key' => 'title',
                'value' => '標題啦',
                'locale' => 'zh-Hant'
            ]),
            new Translation([
                'key' => 'title',
                'value' => '标题啦',
                'locale' => 'zh-Hans'
            ]),
            new Translation([
                'key' => 'title',
                'value' => 'title',
                'locale' => 'en'
            ]),
        ]);

        $this->assertEquals(3, $book->translations->count());

    }

    /**
     * 讀取測試
     *
     * @test
     */
    public function testRead()
    {
        // === ARRANGE ===
        $book = Book::create([
            'title' => 'raw title',
            'content' => 'raw content',
            'author' => 'raw author',
        ]);
        $book->translations()->saveMany([
            new Translation([
                'key' => 'title',
                'value' => '正體中文的標題',
                'locale' => 'zh-Hant'
            ]),
            new Translation([
                'key' => 'content',
                'value' => '正體中文的內容',
                'locale' => 'zh-Hant'
            ]),
            new Translation([
                'key' => 'title',
                'value' => '简体中文的标题',
                'locale' => 'zh-Hans'
            ]),
            new Translation([
                'key' => 'title',
                'value' => 'english title',
                'locale' => 'en'
            ]),
            new Translation([
                'key' => 'content',
                'value' => 'english content',
                'locale' => 'en'
            ]),
        ]);

        // === ASSERT ===
        // i18n() return all locales array if unassigned any locale
        // 如果未指任何語系，就回傳所有語系的屬性陣列
        $this->assertArraySubset([
            'zh-Hant' => '正體中文的標題',
            'zh-Hans' => '简体中文的标题',
            'en' => 'english title',
        ], $book->i18n()->title);
        $this->assertArraySubset([
            'zh-Hant' => '正體中文的內容',
            'zh-Hans' => 'raw content',
            'en' => 'english content',
        ], $book->i18n()->content);

        // i18n() use assign locale
        // 使用指定的語系
        App::setLocale('zh-Hant');
        $this->assertEquals('正體中文的標題', $book->i18n('zh-Hant')->title);
        $this->assertEquals('简体中文的标题', $book->i18n('zh-Hans')->title);
        $this->assertEquals('english title', $book->i18n('en')->title);

        // i18n() use app locale if assign locale not found
        // 如果指定語系不存在，就使用 APP 預設語系
        App::setLocale('zh-Hant');
        $this->assertEquals('正體中文的標題', $book->i18n('ja')->title);

        // i18n() use raw data if assign locale attribute not found
        // 如果語系的屬性不存在，使用原始屬性資料
        App::setLocale('zh-Hant');
        $this->assertEquals('raw author', $book->i18n('zh-Hant')->author);

        // Use raw data without i18n()
        // 如果沒使用 i18n()，使用原始資料
        $this->assertEquals('raw title', $book->title);
        $this->assertEquals('raw content', $book->content);

    }

    /**
     * 寫入測試 (設定多個語系的多個屬性)
     *
     * @test
     */
    public function testWriteMultiLangMultiAttr()
    {
        // ARRANGE
        $book = new Book();
        $book->title = 'raw title';
        $book->content = 'raw content';
        $book->author = 'raw author';
        $book->i18n([
            'zh-Hant' => [
                'title' => '正體中文的標題',
                'content' => '正體中文的內容',
            ],
            'zh-Hans' => [
                'title' => '简体中文的标题',
            ],
            'en' => [
                'title' => 'english title',
                'content' => 'english content',
            ],
        ])->save();

        // === ASSERT ===
        // i18n() return all locales array if unassigned any locale
        // 如果未指任何語系，就回傳所有語系的屬性陣列
        $this->assertArraySubset([
            'zh-Hant' => '正體中文的標題',
            'zh-Hans' => '简体中文的标题',
            'en' => 'english title',
        ], $book->i18n()->title);
        $this->assertArraySubset([
            'zh-Hant' => '正體中文的內容',
            'zh-Hans' => 'raw content',
            'en' => 'english content',
        ], $book->i18n()->content);

        // i18n() use assign locale
        // 使用指定的語系
        App::setLocale('zh-Hant');
        $this->assertEquals('正體中文的標題', $book->i18n('zh-Hant')->title);
        $this->assertEquals('简体中文的标题', $book->i18n('zh-Hans')->title);
        $this->assertEquals('english title', $book->i18n('en')->title);

        // i18n() use app locale if assign locale not found
        // 如果指定語系不存在，就使用 APP 預設語系
        App::setLocale('zh-Hant');
        $this->assertEquals('正體中文的標題', $book->i18n('ja')->title);

        // i18n() use raw data if assign locale attribute not found
        // 如果語系的屬性不存在，使用原始屬性資料
        App::setLocale('zh-Hant');
        $this->assertEquals('raw author', $book->i18n('zh-Hant')->author);

        // Use raw data without i18n()
        // 如果沒使用 i18n()，使用原始資料
        $this->assertEquals('raw title', $book->title);
        $this->assertEquals('raw content', $book->content);

    }

    /**
     * 寫入測試 (設定單一屬性的多個語系)
     *
     * @test
     */
    public function testWriteSingleAttrMultiLang()
    {
        // ARRANGE
        $book = new Book();
        $book->title = 'raw title';
        $book->content = 'raw content';
        $book->author = 'raw author';
        $book->title = [
            'zh-Hant' => '正體中文的標題',
            'zh-Hans' => '简体中文的标题',
            'en' => 'english title',
        ];
        $book->content = [
            'zh-Hant' => '正體中文的內容',
            'en' => 'english content',
        ];
        $book->save();

        // === ASSERT ===
        // i18n() return all locales array if unassigned any locale
        // 如果未指任何語系，就回傳所有語系的屬性陣列
        $this->assertArraySubset([
            'zh-Hant' => '正體中文的標題',
            'zh-Hans' => '简体中文的标题',
            'en' => 'english title',
        ], $book->i18n()->title);
        $this->assertArraySubset([
            'zh-Hant' => '正體中文的內容',
            'zh-Hans' => 'raw content',
            'en' => 'english content',
        ], $book->i18n()->content);

        // i18n() use assign locale
        // 使用指定的語系
        App::setLocale('zh-Hant');
        $this->assertEquals('正體中文的標題', $book->i18n('zh-Hant')->title);
        $this->assertEquals('简体中文的标题', $book->i18n('zh-Hans')->title);
        $this->assertEquals('english title', $book->i18n('en')->title);

        // i18n() use app locale if assign locale not found
        // 如果指定語系不存在，就使用 APP 預設語系
        App::setLocale('zh-Hant');
        $this->assertEquals('正體中文的標題', $book->i18n('ja')->title);

        // i18n() use raw data if assign locale attribute not found
        // 如果語系的屬性不存在，使用原始屬性資料
        App::setLocale('zh-Hant');
        $this->assertEquals('raw author', $book->i18n('zh-Hant')->author);

        // Use raw data without i18n()
        // 如果沒使用 i18n()，使用原始資料
        $this->assertEquals('raw title', $book->title);
        $this->assertEquals('raw content', $book->content);

        // 最後語系資料檔應該有 5 筆
        $this->assertEquals(5, Translation::count());
    }

    /**
     * 寫入測試 (設定單一語系的單一屬性)
     *
     * @test
     */
    public function testWriteOneLangOneAttr()
    {
        // ARRANGE
        $book = new Book();
        $book->title = 'raw title';
        $book->content = 'raw content';
        $book->author = 'raw author';
        $book->i18n('zh-Hant')->title = '正體中文的標題';
        $book->i18n('zh-Hans')->title = '简体中文的标题';
        $book->i18n('en')->title = 'english title';
        $book->i18n('zh-Hant')->content = '正體中文的內容';
        $book->i18n('en')->content = 'english content';
        $book->save();

        // === ASSERT ===
        // i18n() return all locales array if unassigned any locale
        // 如果未指任何語系，就回傳所有語系的屬性陣列
        $this->assertArraySubset([
            'zh-Hant' => '正體中文的標題',
            'zh-Hans' => '简体中文的标题',
            'en' => 'english title',
        ], $book->i18n()->title);
        $this->assertArraySubset([
            'zh-Hant' => '正體中文的內容',
            'zh-Hans' => 'raw content',
            'en' => 'english content',
        ], $book->i18n()->content);

        // i18n() use assign locale
        // 使用指定的語系
        App::setLocale('zh-Hant');
        $this->assertEquals('正體中文的標題', $book->i18n('zh-Hant')->title);
        $this->assertEquals('简体中文的标题', $book->i18n('zh-Hans')->title);
        $this->assertEquals('english title', $book->i18n('en')->title);

        // i18n() use app locale if assign locale not found
        // 如果指定語系不存在，就使用 APP 預設語系
        App::setLocale('zh-Hant');
        $this->assertEquals('正體中文的標題', $book->i18n('ja')->title);

        // i18n() use raw data if assign locale attribute not found
        // 如果語系的屬性不存在，使用原始屬性資料
        App::setLocale('zh-Hant');
        $this->assertEquals('raw author', $book->i18n('zh-Hant')->author);

        // Use raw data without i18n()
        // 如果沒使用 i18n()，使用原始資料
        $this->assertEquals('raw title', $book->title);
        $this->assertEquals('raw content', $book->content);

        // 最後語系資料檔應該有 5 筆
        $this->assertEquals(5, Translation::count());
    }
}
