<?php
/**
 * This file is part of the Verraes\Money library
 *
 * Copyright (c) 2011 Mathias Verraes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'bootstrap.php';

use Verraes\Money\Money;
use Verraes\Money\Currency;
use Verraes\Money\CurrencyPair;

class CurrencyPairTest extends PHPUnit_Framework_TestCase
{
	/** @test */
	public function ConvertsEurToUsdAndBack()
	{
		$eur = Money::EUR(100);

		$pair = new CurrencyPair(new Currency('EUR'), new Currency('USD'), 1.2500);
		$usd = $pair->convert($eur);
		$this->assertEquals(Money::USD(125), $usd);

		$pair = new CurrencyPair(new Currency('USD'), new Currency('EUR'), 0.8000);
		$eur = $pair->convert($usd);
		$this->assertEquals(Money::EUR(100), $eur);
	}


}