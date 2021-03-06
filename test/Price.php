<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */

use OBX\Market\Price;
use OBX\Market\PriceDBS;

OBX_Market_TestCase::includeLang(__FILE__);

class OBX_Test_Price extends OBX_Market_TestCase
{
	public function testAddNewPrice() {
		$newPriceID = Price::add(array(
			'CODE' => self::OBX_TEST_PRICE_CODE,
			'NAME' => GetMessage('OBX_MARKET_TEST_PRICE_1'),
			'CURRENCY' => 'RUB',
			''
		));
	}
}
