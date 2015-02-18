<?php

/**
 *
 * @author Yury Shishkin
 * 2015
 * http://github.com/tttptd/money
 *
 */

use Money\Currency;

namespace Money;

/**
 * Свойства валют
 *
 */

/**
 * Обменник валют
 */
class CurrenciesProperties
{

    /**
     * [$instance description]
     * @var [type]
     */
    private static $instance;


    final private function __construct() {}
    final private function __clone() {}
    final private function __wakeup() {}
    final public static function getInstance()
    {
        if(empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    private static $currencies = array(
        'RUR' => array(
            'symbol' => '₷',
            'ru' => array(
                'short' => 'руб.',
                'full' => array('рубль', 'рубля', 'рублей'),
            ),
            'en' => array(
                'short' => 'rub.',
                'full' => array('rouble', 'roubles'),
            ),
        ),
        'RUB' => array(
            'symbol' => '₷',
            'ru' => array(
                'short' => 'руб.',
                'full' => array('рубль', 'рубля', 'рублей'),
            ),
            'en' => array(
                'short' => 'rub.',
                'full' => array('rouble', 'roubles'),
            ),
        ),
        'USD' => array(
            'symbol' => '$',
            'ru' => array(
                'short' => 'долл.',
                'full' => array('доллар', 'доллара', 'долларов'),
            ),
            'en' => array(
                'short' => 'doll.',
                'full' => array('dollar', 'dollars'),
            ),
        ),
        'EUR' => array(
            'symbol' => '€',
            'ru' => array(
                'short' => 'евро',
                'full' => array('евро', 'евро', 'евро'),
            ),
            'ru' => array(
                'short' => 'eur.',
                'full' => array('euro', 'euros'),
            ),
        ),
    );


    /**
     * Возвращает свойства валюты
     * @param Currency $currency
     */
    public static function getProperties(Currency $currency)
    {
        $code = strtoupper((string)$currency->getCode());

        return (array_key_exists($code, self::$currencies) ? self::$currencies[$code] : false);
    }


    /**
     * Возвращает свойство валюты
     * @param  Currency $currency [description]
     * @param  string   $property [description]
     * @param  string   $lang     [description]
     * @return [type]             [description]
     */
    public static function getProperty(Currency $currency, $property, $lang = 'ru')
    {
        $value = false;
        $properties = self::getProperties($currency);

        if($properties) {
            if(in_array($property, array('symbol'))) {
                $value = $properties[$property];
            }
            elseif(in_array($property, array('short', 'full'))) {
                if($properties[$lang]) {
                    $value = $properties[$lang][$property];
                }
            }
        }

        return $value;
    }


 }
