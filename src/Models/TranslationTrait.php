<?php

namespace Liaosankai\LaravelEloquentI18n\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

trait TranslationTrait
{
    /**
     * @var string
     */
    protected $useLocale;

    /**
     * @var array
     */
    protected $localeAttributes = [];

    /**
     * @var
     */
    protected $subQuerySql;

    /**
     * @return mixed
     */
    public function translations()
    {
        return $this->morphMany('Liaosankai\LaravelEloquentI18n\Models\Translation', 'translatable');
    }

    /**
     * @return array
     */
    public function i18nable()
    {
        return $this->i18nable;
    }

    /**
     * @param mixed $locale
     * @return mixed
     */
    public function i18n($locale = false)
    {
        foreach ($this->translations()->select(['key', 'value', 'locale'])->get() as $trans) {
            if (!array_has($this->localeAttributes, "{$trans->locale}.{$trans->key}")) {
                $this->localeAttributes[$trans->locale][$trans->key] = $trans->value;
            }
        }

        if (is_array($locale)) {
            $this->localeAttributes = array_replace_recursive($this->localeAttributes, $locale);
        } else {
            $this->useLocale = $locale;
        }

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if (is_string($this->useLocale)) {
            $localePackage = array_get($this->localeAttributes, $this->useLocale, []);
            $appLocalePackage = array_get($this->localeAttributes, App::getLocale(), []);

            $this->useLocale = null;

            return array_get(array_replace($appLocalePackage, $localePackage), $key, parent::__get($key));
        }

        if (is_bool($this->useLocale) && $this->useLocale === false) {
            $val = [];
            foreach ($this->localeAttributes as $locale => $attrs) {
                $val[$locale] = array_get($attrs, $key, parent::__get($key));
            }

            $this->useLocale = null;

            return $val;
        }

        return parent::__get($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        if (in_array($key, $this->i18nable())) {

            if (is_array($value)) {
                foreach ($value as $locale => $val) {
                    $this->localeAttributes[$locale][$key] = $val;
                }
            } else if (!empty($this->useLocale)) {
                $this->localeAttributes[$this->useLocale][$key] = $value;
                $this->useLocale = null;
            } else {
                parent::__set($key, $value);
            }

            if (!$this->$key) {
                $defaultVal = array_get($this->localeAttributes, App::getLocale() . "." . $key, '');
                parent::__set($key, $defaultVal);
            }

            return;
        }

        parent::__set($key, $value);
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function save(array $options = [])
    {
        $saved = parent::save($options);

        // TODO: 儲存語系檔案應該發出自訂語系資料完成設定事件

        if (is_array($this->localeAttributes)) {
            $updateOrCreate = false;
            $this->fireModelEvent('updating');
            foreach ($this->localeAttributes as $locale => $keyVal) {
                foreach ($keyVal as $key => $val) {
                    $updateOrCreate = Translation::updateOrCreate(
                        [
                            'locale' => $locale,
                            'key' => $key,
                            'translatable_id' => $this->id,
                        ],
                        [
                            'translatable_type' => __CLASS__,
                            'value' => $val,
                        ]
                    );
                }
            }
            if ($updateOrCreate) {
                $this->fireModelEvent('updated');
            }
        }


        return $saved;
    }


    /**
     * @param array $attrValue
     * @param null $locale
     * @return $this
     */
    public function scopeI18nLike($query, $params)
    {
        $tempColName = '__i18n__' . str_random(5);

        $query->withCount([
            "translations AS {$tempColName}" => function ($query) use ($params) {
                $attrValue = array_get($params, 'filter');
                $locale = array_get($params, 'locale');

                if ($locale) {
                    $query->where('locale', $locale);
                }
                foreach ($attrValue as $attr => $val) {
                    $query->where('key', $attr);
                    $query->where('value', 'LIKE', "%{$val}%");
                }
                $this->subQuerySql = str_replace(__CLASS__, addslashes(__CLASS__), $this->toSql($query));
            }
        ])->where(function ($query) {
            $query->where(DB::raw("({$this->subQuerySql})"), '>', 0);
        });
        $this->subQuerySql = '';
        return $query;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return null|string|string[]
     */
    private function toSql(\Illuminate\Database\Eloquent\Builder $builder)
    {
        $sql = $builder->toSql();
        foreach ($builder->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributesToArray = [];
        foreach ($this->attributesToArray() as $attr => $value) {
            if (!starts_with($attr, '__i18n__')) {
                $attributesToArray[$attr] = $value;
            }
        }

        return array_merge($attributesToArray, $this->relationsToArray());
    }
}