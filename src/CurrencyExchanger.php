<?php

namespace Money;

use Money\CurrencyPair;

/**
 * Обменник валют
 *
 * @example
 * <code>
 * $currencyExchanger = CurrencyExchanger::getInstance();
 *
 * // Можно в цикле насыщать обменник парами,
 * // чтобы без проблем обменивать валюты туда-сюда
 *
 * $baseCurrency = new Currency('USD');
 * $counterCurrency = new Currency('RUR');
 * $ratio = floatval(62.6632);
 *
 * // Прямая пара
 * $pair = new CurrencyPair($baseCurrency, $counterCurrency, $ratio);
 * $currencyExchanger->addPair($pair);
 *
 * // Обратная пара
 * $reverseRatio = 1 / $ratio;
 * $reversePair = new CurrencyPair($counterCurrency, $baseCurrency, $reverseRatio);
 * $currencyExchanger->addPair($reversePair);
 *
 *
 * // Обмен
 * $price = Money::RUR(245435);
 *
 * $priceResultUsd = $currencyExchanger->exchange($price, 'USD');
 * $priceResultRur = $currencyExchanger->exchange($price, 'RUR');
 * </code>
 * @author Yury Shishkin
 * @see http://github.com/tttptd/money
 *
 */
class CurrencyExchanger
{

    /**
     * [$instance description]
     * @var [type]
     */
    private static $instance;


    /**
     * Пары валют
     * @var array of CurrencyPair
     */
    private static $pairs = array();


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


    /**
     * Добавляет пару в обменник
     * @param CurrencyPair $pair [description]
     * @return  self
     */
    public static function addPair(CurrencyPair $pair)
    {
        self::$pairs[strtoupper((string)$pair->getBaseCurrency())][strtoupper((string)$pair->getCounterCurrency())] = $pair;

        return self::$instance;
    }


    /**
     * Возвращает все пары
     * @return array
     */
    public static function getPairs()
    {
        return self::$pairs;
    }


    /**
     * Найти и вернуть валютную пару или false
     * @param  string $baseCurrencyCode    [description]
     * @param  string $counterCurrencyCode [description]
     * @return CurrencyPair | false
     */
    public static function getPair($baseCurrencyCode, $counterCurrencyCode)
    {
        $baseCurrencyCode = strtoupper($baseCurrencyCode);
        $counterCurrencyCode = strtoupper($counterCurrencyCode);

        return (array_key_exists($baseCurrencyCode, self::$pairs) &&
                array_key_exists($counterCurrencyCode, self::$pairs[$baseCurrencyCode]) ?
                    self::$pairs[$baseCurrencyCode][$counterCurrencyCode] :
                    false
                );
    }


    /**
     * Обменивает валюту $base на $counterCurrency
     * @param  Money              $base               [description]
     * @param  string|Currency    $counterCurrency    [description]
     * @return Money
     */
    public static function exchange(Money $base, $counterCurrency, $roundingMode = false) // $roundingMode = Money::ROUND_HALF_UP
    {
        if($counterCurrency instanceof Currency) {
            $counterCurrency = $counterCurrency->getCode();
        }
        else {
            $counterCurrency = strtoupper($counterCurrency);
        }

        // Если валюта пустая, вернем то что отдали
        if(!$base->getCurrency()->getCode()) {
            return $base;
        }
        // Если валюты одинаковые, ничего преобразовывать не нужно
        elseif($base->getCurrency()->getCode() === $counterCurrency) {
            return $base;
        }
        else {
            return self::getPair($base->getCurrency()->getCode(), $counterCurrency)
                                                            ->convert($base, $roundingMode);
        }

    }

 }
