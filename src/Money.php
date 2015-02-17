<?php

/**
 * This file is part of the Money library.
 *
 * Copyright (c) 2011-2014 Mathias Verraes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Money;

use InvalidArgumentException;
use OverflowException;
use UnderflowException;
use UnexpectedValueException;

/**
 * Money Value Object
 *
 * @author Mathias Verraes
 */
class Money
{
    const ROUND_HALF_UP   = PHP_ROUND_HALF_UP;
    const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
    const ROUND_HALF_EVEN = PHP_ROUND_HALF_EVEN;
    const ROUND_HALF_ODD  = PHP_ROUND_HALF_ODD;


    /**
     * Internal value
     *
     * @var int
     */
    private $amount;


    /**
     * @var Currency
     */
    private $currency;


    /**
     * @param int  $amount   Amount, expressed in the smallest units of $currency (eg cents)
     * @param Currency $currency
     *
     * @throws InvalidArgumentException If amount is not integer
     */
    public function __construct($amount, Currency $currency)
    {
        if(!is_int($amount)) {
            throw new InvalidArgumentException('Amount must be an integer');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }


    /**
     * Convenience factory method for a Money object
     *
     * <code>
     * $fiveDollar = Money::USD(500);
     * </code>
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return Money
     */
    public static function __callStatic($method, $arguments)
    {
        return new Money($arguments[0], new Currency($method));
    }





    /**
     * Checkers area
     *
     */


    /**
     * Checks whether a Money has the same Currency as this
     *
     * @param Money $other
     *
     * @return boolean
     */
    public function isSameCurrency(Money $other)
    {
        return $this->currency->equals($other->currency);
    }


    /**
     * Checks if the value represented by this object is zero
     *
     * @return boolean
     */
    public function isZero()
    {
        return $this->amount === 0;
    }


    /**
     * Checks if the value represented by this object is positive
     *
     * @return boolean
     */
    public function isPositive()
    {
        return $this->amount > 0;
    }


    /**
     * Checks if the value represented by this object is negative
     *
     * @return boolean
     */
    public function isNegative()
    {
        return $this->amount < 0;
    }


    /**
     * Checks whether the value represented by this object equals to the other
     *
     * @param Money $other
     *
     * @return boolean
     */
    public function equals(Money $other)
    {
        return $this->isSameCurrency($other) && $this->amount == $other->amount;
    }


    /**
     * Checks whether the value represented by this object is greater than the other
     *
     * @param Money $other
     *
     * @return boolean
     */
    public function greaterThan(Money $other)
    {
        return 1 == $this->compare($other);
    }


    /**
     * @param \Money\Money $other
     * @return bool
     */
    public function greaterThanOrEqual(Money $other)
    {
        return 0 >= $this->compare($other);
    }


    /**
     * Checks whether the value represented by this object is less than the other
     *
     * @param Money $other
     *
     * @return boolean
     */
    public function lessThan(Money $other)
    {
        return -1 == $this->compare($other);
    }


    /**
     * @param \Money\Money $other
     * @return bool
     */
    public function lessThanOrEqual(Money $other)
    {
        return 0 <= $this->compare($other);
    }


    /**
     * Returns an integer less than, equal to, or greater than zero
     * if the value of this object is considered to be respectively
     * less than, equal to, or greater than the other
     *
     * @param Money $other
     *
     * @return int
     */
    public function compare(Money $other)
    {
        $this->assertSameCurrency($other);

        if($this->amount < $other->amount) {
            return -1;
        } elseif($this->amount == $other->amount) {
            return 0;
        } else {
            return 1;
        }
    }





    /**
     * Getters area
     *
     */


    /**
     * Returns the value represented by this object
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }


    /**
     * Возвращает значение getAmount() / 100
     *
     * @param  mixed $roundingMode [false | Money::ROUND_HALF_DOWN | Money::ROUND_HALF_EVEN | Money::ROUND_HALF_ODD | Money::ROUND_HALF_UP]
     *                             Смотри в ман по PHP, функция round, для информации о ключах
     *                             Если false, то без округления
     * @return int|float
     */
    public function getAmountNormal($roundingMode = self::ROUND_HALF_UP)
    {
        return ($roundingMode ? round($this->amount / 100, 0, $roundingMode) : $this->amount / 100);
    }


    /**
     * Returns the currency of this object
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }


    /**
     * Возвращает "человеческое" представление: 34 105,23 руб
     * Для удобного визуального тестирования
     *
     * @param  integer $decimals Кол-во знаков после запятой
     *
     * @return String
     */
    public function getHuman($decimals = 2)
    {
        $currency = (string)$this->getCurrency();

        return number_format($this->getAmount() / 100, $decimals, ',', ' ') . (!empty($currency) ? ' '. $currency : '' );
    }





    /**
     * Operations area
     *
     */


    /**
     * Returns a new Money object that represents
     * the sum of this and an other Money object
     *
     * @param Money $addend
     *
     * @return Money
     */
    public function add(Money $addend)
    {
        $this->assertSameCurrency($addend);

        $amount = $this->amount + $addend->amount;

        $this->assertInteger($amount);

        return $this->newInstance($amount);
    }


    /**
     * Returns a new Money object that represents
     * the difference of this and an other Money object
     *
     * @param Money $subtrahend
     *
     * @return Money
     */
    public function subtract(Money $subtrahend)
    {
        $this->assertSameCurrency($subtrahend);

        $amount = $this->amount - $subtrahend->amount;

        $this->assertInteger($amount);

        return $this->newInstance($amount);
    }


    /**
     * Returns a new Money object that represents
     * the multiplied value by the given factor
     *
     * @param numeric $multiplier
     * @param int $roundingMode
     *
     * @return Money
     */
    public function multiply($multiplier, $roundingMode = self::ROUND_HALF_UP)
    {
        $this->assertOperand($multiplier);

        $this->assertRoundingMode($roundingMode);

        $product = round($this->amount * $multiplier, 0, $roundingMode);

        $product = $this->castInteger($product);

        return $this->newInstance($product);
    }


    /**
     * Returns a new Money object that represents
     * the divided value by the given factor
     *
     * @param numeric $divisor
     * @param int $roundingMode
     *
     * @return Money
     */
    public function divide($divisor, $roundingMode = self::ROUND_HALF_UP)
    {
        $this->assertOperand($divisor);

        $this->assertRoundingMode($roundingMode);

        $quotient = round($this->amount / $divisor, 0, $roundingMode);

        $quotient = $this->castInteger($quotient);

        return $this->newInstance($quotient);
    }


    /**
     * Конвертирует текущую валюту в $targetCurrency по курсу $conversionRate
     * @param Currency $targetCurrency
     * @param float|int $conversionRate
     * @param int $roundingMode
     * @return Money
     */
    public function convert(Currency $targetCurrency, $conversionRate, $roundingMode = Money::ROUND_HALF_UP)
    {
        $this->assertRoundingMode($roundingMode);
        $amount = round($this->amount * $conversionRate, 0, $roundingMode);
        $amount = $this->castInteger($amount);
        return new Money($amount, $targetCurrency);
    }


    /**
     * Распределить деньги по долям
     * Usage:
     *     list($my_cut, $investors_cut) = Money::EUR(5)->allocate(70, 30);
     *
     * @param array $ratios
     *
     * @return Money[]
     */
    public function allocate(array $ratios)
    {
        $remainder = $this->amount;
        $results = array();
        $total = array_sum($ratios);

        foreach ($ratios as $ratio) {
            $share = $this->castInteger($this->amount * $ratio / $total);
            $results[] = $this->newInstance($share);
            $remainder -= $share;
        }

        for ($i = 0; $remainder > 0; $i++) {
            $results[$i]->amount++;
            $remainder--;
        }

        return $results;
    }


    /**
     * Распределить деньги поровну на N частей
     *
     * @param int $n
     *
     * @return Money[]
     *
     * @throws InvalidArgumentException If number of targets is not an integer
     */
    public function allocateTo($n)
    {
        if(!is_int($n)) {
            throw new InvalidArgumentException('Number of targets must be an integer');
        }

        $amount = intval($this->amount / $n);
        $results = array();

        for ($i = 0; $i < $n; $i++) {
            $results[$i] = $this->newInstance($amount);
        }

        for ($i = 0; $i < $this->amount % $n; $i++) {
            $results[$i]->amount++;
        }

        return $results;
    }


    /**
     * Creates amounts from string
     *
     * @param string $string Something like "2939.00"
     *
     * @return int
     *
     * @throws InvalidArgumentException If $string cannot be parsed
     */
    public static function stringToAmount($string)
    {
        $sign = "(?P<sign>[-\+])?";
        $digits = "(?P<digits>\d*)";
        $separator = "(?P<separator>[.,])?";
        $decimals = "(?P<decimal1>\d)?(?P<decimal2>\d)?";
        $pattern = "/^".$sign.$digits.$separator.$decimals."$/";

        if(!preg_match($pattern, trim($string), $matches)) {
            throw new InvalidArgumentException("The value could not be parsed as money");
        }

        $units = $matches['sign'] == "-" ? "-" : "";
        $units .= $matches['digits'];
        $units .= isset($matches['decimal1']) ? $matches['decimal1'] : "0";
        $units .= isset($matches['decimal2']) ? $matches['decimal2'] : "0";

        return (int) $units;
    }






    /**
     * Private area
     *
     */


    /**
     * Returns a new Money instance based on the current one using the Currency
     *
     * @param int $amount
     *
     * @return Money
     */
    private function newInstance($amount)
    {
        return new Money($amount, $this->currency);
    }


    /**
     * Asserts that a Money has the same currency as this
     *
     * @throws InvalidArgumentException If $other has a different currency
     */
    private function assertSameCurrency(Money $other)
    {
        if(!$this->isSameCurrency($other)) {
            throw new InvalidArgumentException('Currencies must be identical');
        }
    }


    /**
     * Asserts that integer remains integer after arithmetic operations
     *
     * @param  numeric $amount
     */
    private function assertInteger($amount)
    {
        if(!is_int($amount)) {
            throw new UnexpectedValueException('The result of arithmetic operation is not an integer');
        }
    }


    /**
     * Asserts that the operand is integer or float
     *
     * @throws InvalidArgumentException If $operand is neither integer nor float
     */
    private function assertOperand($operand)
    {
        if(!is_int($operand) && !is_float($operand)) {
            throw new InvalidArgumentException('Operand should be an integer or a float');
        }
    }


    /**
     * Asserts that an integer value didn't become something else
     * (after some arithmetic operation)
     *
     * @param numeric $amount
     *
     * @throws OverflowException If integer overflow occured
     * @throws UnderflowException If integer underflow occured
     */
    private function assertIntegerBounds($amount)
    {
        if($amount > PHP_INT_MAX) {
            throw new OverflowException;
        } elseif($amount < ~PHP_INT_MAX) {
            throw new UnderflowException;
        }
    }


    /**
     * Casts an amount to integer ensuring that an overflow/underflow did not occur
     *
     * @param numeric $amount
     *
     * @return int
     */
    private function castInteger($amount)
    {
        $this->assertIntegerBounds($amount);

        return intval($amount);
    }


    /**
     * Asserts that rounding mode is a valid integer value
     *
     * @param int $roundingMode
     *
     * @throws InvalidArgumentException If $roundingMode is not valid
     */
    private function assertRoundingMode($roundingMode)
    {
        if(!in_array(
            $roundingMode,
            array(self::ROUND_HALF_DOWN, self::ROUND_HALF_EVEN, self::ROUND_HALF_ODD, self::ROUND_HALF_UP)
        )) {
            throw new InvalidArgumentException(
                'Rounding mode should be Money::ROUND_HALF_DOWN | ' .
                'Money::ROUND_HALF_EVEN | Money::ROUND_HALF_ODD | ' .
                'Money::ROUND_HALF_UP'
            );
        }
    }

}
