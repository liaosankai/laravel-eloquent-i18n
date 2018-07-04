<?php

namespace Liaosankai\LaravelEloquentI18n\Models;

use Illuminate\Support\Facades\App;

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
     */
    public function save(array $options = [])
    {
        parent::save($options);

        foreach ($this->localeAttributes as $locale => $keyVal) {
            foreach ($keyVal as $key => $val) {
                Translation::updateOrCreate(
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

    }

}