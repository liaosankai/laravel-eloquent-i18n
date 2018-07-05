# Changelog
All notable changes to laravel-eloquent-i18n will be documented in this file

## 1.2.0 (2018-07-05)
+ add i18nLike() model scope
```
    $book->i18nLike([
        'filter' => [
            'title' => 'keywords for title',
            'content' => 'keywords for content'.
        ],
        'locale' => 'zh-Hant',
    ])->get();
```

## 1.1.1 (2018-07-04)
+ Fix forget to set `$this->userLocale = null;` after __get()

## 1.1.0 (2018-07-04)
+ i18n() without assign any locale will return locales attribute array.
```
    # v1.0.0
    $book->i18n()->title; // english title
    
    # v1.1.0
    $book->i18n()->title; 
    /*
      [
        'en' => 'english title',
        'zh-Hant' => '正體中文標題',
      ]
    */
```
    
## 1.0.0 (2018-07-03)
+ Initial version