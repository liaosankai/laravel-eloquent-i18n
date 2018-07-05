<?php

namespace Liaosankai\LaravelEloquentI18n\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class BaseTestCase extends TestCase
{
    /**
     * @var ConsoleOutput 終端器輸出器
     */
    protected $console;

    /**
     * @var \Faker\Factory 假資料產生器
     */
    protected $faker;

    /**
     * BaseTestCase constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->console = new ConsoleOutput();
        $this->faker = \Faker\Factory::create();
    }

    /**
     * 測試時模擬的 Package Providers 設定
     *
     *  ( 等同於原 laravel 設定 config/app.php 的 Autoloaded Service Providers )
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        // 這邊通常要把目前選擇套件的 Providers 放進去
        return [
            \Orchestra\Database\ConsoleServiceProvider::class,
            \Liaosankai\LaravelEloquentI18n\LaravelEloquentI18nServiceProvider::class,
        ];
    }

    /**
     * 測試時模擬的 Class Aliases 設定
     *
     * ( 等同於原 laravel 中設定 config/app.php 的 Class Aliases )
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [

        ];
    }

    /**
     * 測試時的模擬時區設定
     *
     * ( 等同於原 laravel 中設定 config/app.php 的 Application Timezone )
     *
     * @see http://php.net/manual/en/timezones.php
     * @param  \Illuminate\Foundation\Application $app
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return 'Asia/Taipei';
    }

    /**
     * 測試時模擬使用的 HTTP Kernel
     *
     * ( 等同於原 laravel 中 app/HTTP/kernel.php )
     * ( 若需要用自訂時，把 Orchestra\Testbench\Http\Kernel 改成自己的 )
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Http\Kernel',
            'Orchestra\Testbench\Http\Kernel'
        );
    }

    /**
     * 測試時使用的 Console Kernel
     *
     * ( 等同於原 laravel 中 app/Console/kernel.php )
     * ( 若需要用自訂時，把 Orchestra\Testbench\Console\Kernel 改成自己的 )
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Console\Kernel',
            'Orchestra\Testbench\Console\Kernel'
        );
    }

    /**
     * 測試環境設定
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // 若有環境變數檔案，嘗試著讀取使用
        if (file_exists(dirname(__DIR__) . '/.env')) {
            $dotEnv = new \Dotenv\Dotenv(dirname(__DIR__));
            $dotEnv->load();
        }

        // 定義測試時使用的資料庫(預設會使用 sqlite)
        $driver = env('TEST_DB_CONNECTION', 'sqlite');
        $app['config']->set('database.connections.testing', [
            'driver' => $driver,
            'host' => env('TEST_DB_HOST', 'localhost'),
            'database' => env('TEST_DB_DATABASE', ':memory:'),
            'port' => env('TEST_DB_PORT'),
            'username' => env('TEST_DB_USERNAME'),
            'password' => env('TEST_DB_PASSWORD'),
            'prefix' => env('TEST_DB_PREFIX'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
        $app['config']->set('database.default', 'testing');
    }

    /**
     * 測試初始設置
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /*
    |--------------------------------------------------------------------------
    | 選用的
    |--------------------------------------------------------------------------
    */
    /**
     * 初始化套件所必要資料庫 migrations 表以及 factories 等
     *
     * @throws /Exception
     */
    protected function loadPackageMigrations()
    {
        // 只有套件的 database/migrations 資料夾存在時
        // 才會載入套件的 migrations 檔案
        if (file_exists(__DIR__ . '/../database/migrations')) {
            // 避免 migration 的 string 類型欄位，在未定字串長度時
            // 在 MySQL 版本 5.7 以上，會產生錯誤的問題
            Schema::defaultStringLength(191);

            $this->loadMigrationsFrom([
                '--database' => 'testing',
                '--realpath' => realpath(__DIR__ . '/../database/migrations'),
            ]);
            // 執行 migrations (這樣才會將資料表建立至資料庫)
            $this->artisan('migrate');
        }
        // 只有套件的 database/factories 資料夾存在時
        // 才會載入輔助資料產生的工廠類別
        if (file_exists(__DIR__ . '/../database/factories')) {
            $this->withFactories(__DIR__ . '/../database/factories');
        }
    }

    /**
     * 初始化套件所必要資料庫 migrations 表以及 factories 等
     *
     * @throws /Exception
     */
    protected function loadMockMigrations()
    {
        // 只有模擬主專案的 database/migrations 資料夾存在時
        // 才會載入模擬主專案的 migrations 檔案
        if (file_exists(__DIR__ . '/../mocks/database/migrations')) {
            // 避免 migration 的 string 類型欄位，在未定字串長度時
            // 在 MySQL 版本 5.7 以上，會產生錯誤的問題
            Schema::defaultStringLength(191);

            $this->loadMigrationsFrom([
                '--database' => 'testing',
                '--realpath' => realpath(__DIR__ . '/../mocks/database/migrations'),
            ]);

            // 執行 migrations (這樣才會將資料表建立至資料庫)
            $this->artisan('migrate');
        }
        // 只有模擬主專案的 database/factories 資料夾存在時
        // 才會載入模擬主專案輔助資料產生的工廠類別
        if (file_exists(__DIR__ . '/../mocks/database/factories')) {
            $this->withFactories(__DIR__ . '/../mocks/database/factories');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 補助方法
    |--------------------------------------------------------------------------
    */
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return null|string|string[]
     */
    public function ddSql(\Illuminate\Database\Eloquent\Builder $builder)
    {
        $sql = $builder->toSql();
        foreach ($builder->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        dd($sql);
    }
}
