### Requirements
+ PHP >= 7.0.0
+ Laravel >= 5.5.0

### Installation
You can install the package via composer:

    composer require liaosankai/laravel-eloquent-i18n

### Making a model translatable

    use Illuminate\Database\Eloquent\Model;
    use Liaosankai\LaravelEloquentI18n\Models\TranslationTrait;
    
    class Book extends Model
    {
        use TranslationTrait;
    
        public $i18nable = [
            'title',
            'content',
            'author',
        ];
    }

### Write translations data

Set raw attribute
    
    $book = new Book();
    $book->title = 'raw title';

Set single attribute of single locales 

    $book = new Book();
    $book->title = 'raw title';
    $book->i18n('zh-Hant')->title = '正體中文的標題';
    $book->i18n('zh-Hans')->title = '简体中文的标题';
    $book->save();
    
Set multiple locales of single attribute

    $book = new Book();
    $book->title = 'raw title';
    $book->title = [
        'zh-Hant' => '正體中文的標題',
        'zh-Hans' => '简体中文的标题',
        'en' => 'english title',
    ];
    $book->save();
    
Set multiple attribute of multiple locales  

    $book = new Book();
    $book->title = 'raw title';
    $book->i18n([
        'zh-Hant' => [
            'title' => '正體中文的標題',
            'content' => '正體中文的內容',
        ],
        'zh-Hans' => [
            'title' => '简体中文的标题',
            'content' => '简体中文的內容',
        ],
        'en' => [
            'title' => 'english title',
            'content' => 'english content',
        ],
    ])->save();

### Read translations data
Arrange presence translations data：

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
            'content' => '简体中文的內容',
        ],
        'en' => [
            'title' => 'english title',
            'content' => 'english content',
        ],
    ])->save();
    
i18n() use all locales array if unassigned any locale

    App::setLocale('zh-Hant');
    $book->i18n()->title;
    /* 
      [
         'zh-Hant' => '正體中文的標題',
         'zh-Hans' => '简体中文的标题',
         'en' => 'english title',
      ]
    */
       
i18n() use assign locale

    $book->i18n('zh-Hant')->title; // 正體中文的標題
    $book->i18n('zh-Hans')->title; // 简体中文的标题
    $book->i18n('zh-en')->title; // english title
    
i18n() use app locale if assign locale not found

    App::setLocale('zh-Hant');
    $book->i18n('ja')->title; // 正體中文的標題
   
i18n() use raw data if assign locale attribute not found
 
    App::setLocale('zh-Hant');
    $book->i18n('zh-Hant')->author; // raw author

Use raw data without i18n()   

    $book->title; // raw title
    $book->content; // raw content
    
### LICENSE
`laravel-eloquent-i18n` is released under the [MIT License]()