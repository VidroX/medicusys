<?php
/**
 * Created by PhpStorm.
 * User: VidroX
 * Date: 3/31/2019
 * Time: 11:30 AM
 */

namespace App\Utils;

class i18n {
    private $languageCode;
    private $config;

    /**
     * i18n constructor.
     *
     * @author VidroX
     * @param string $languageCode Language to be used in the app
     */
    public function __construct($languageCode = 'ru'){
        $this->config = include(__DIR__."/../../config/i18n.php");

        if(in_array($languageCode, $this->config['languages']['availableLanguages'])) {
            $this->languageCode = $languageCode;
        }else{
            $this->languageCode = $this->config['languages']['defaultLanguage'];
        }
    }

    /**
     * Get all available languages, that can be used within the app
     *
     * @author VidroX
     * @return array All available language codes in the app
     */
    public function getAvailableLanguages()
    {
        return $this->config['languages']['availableLanguages'];
    }

    /**
     * Check if language code is in available languages
     *
     * @author VidroX
     *
     * @param string $code Language code
     *
     * @return boolean Is language allowed
     */
    public function isLanguageCodeAllowed($code)
    {
        return in_array($code, $this->getAvailableLanguages());
    }

    /**
     * Get app's default language code
     *
     * @author VidroX
     * @return string App's default language code
     */
    public function getDefaultLanguageCode()
    {
        return $this->config['languages']['defaultLanguage'];
    }

    /**
     * Should default language code be hidden in the url?
     *
     * @author VidroX
     * @return bool true = should be hidden, false otherwise
     */
    public function isDefaultLanguageCodeHidden()
    {
        return $this->config['languages']['isDefaultHidden'];
    }

    /**
     * Get language code determined by web browser
     *
     * @author VidroX
     *
     * @param bool $formatted Should the locale be formatted?
     *
     * @return string Language code determined by web browser
     */
    public static function getBrowserLocale($formatted = true)
    {
        return $formatted ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }

    /**
     * Get language code that is being used in app
     *
     * @author VidroX
     * @return string App's current language code
     */
    public function getLanguageCode()
    {
        if(in_array($this->languageCode, $this->config['languages']['availableLanguages'])) {
            return $this->languageCode;
        }else{
            return $this->config['languages']['defaultLanguage'];
        }
    }

    /**
     * Get language code that is being used in app's url
     *
     * @author VidroX
     * @return string App's current language code
     */
    public function getLanguageCodeForUrl()
    {
        if(in_array($this->languageCode, $this->config['languages']['availableLanguages'])) {
            if($this->languageCode == $this->config['languages']['defaultLanguage']){
                return $this->isDefaultLanguageCodeHidden() ? "" : $this->languageCode;
            }else{
                return $this->languageCode;
            }
        }else{
            return $this->isDefaultLanguageCodeHidden() ? "" : $this->config['languages']['defaultLanguage'];
        }
    }

    /**
     * Set language to be used in app
     *
     * @author VidroX
     * @param string $languageCode
     */
    public function setLanguageCode($languageCode)
    {
        if(in_array($languageCode, $this->config['languages']['availableLanguages'])) {
            $this->languageCode = $languageCode;
        }else{
            $this->languageCode = $this->config['languages']['defaultLanguage'];
        }
    }

    /**
     * Get array with translation based on user's current language
     *
     * @author VidroX
     * @return array Language file
     */
    public function getTranslations(){
        return json_decode(file_get_contents(__DIR__."/../../assets/languages/".$this->languageCode.".json"), true);
    }

    /**
     * Get translated string based on user's current language
     *
     * @author VidroX
     * @param string $key Key by which translation is found
     * @return string Translated string
     */
    public function getTranslation($key){
        $translationArr = json_decode(file_get_contents(__DIR__."/../../assets/languages/".$this->languageCode.".json"), true);

        if(array_key_exists($key, $translationArr)) {
            $translation = $translationArr[$key];
            return $translation;
        }else{
            return 'Translation for key = '.$key.' not found!';
        }
    }
}