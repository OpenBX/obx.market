<?php
/***********************************************
 ** @product OBX:Market Bitrix Module         **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Market;

use OBX\Core\DBSimple\EntityStatic;

IncludeModuleLangFile(__FILE__);

/**
 * Class CurrencyFormat
 * @package OBX\Market
 * @static @method CurrencyFormatDBS getInstance()
 */
class CurrencyFormat extends EntityStatic {
	static public function getListGroupedByLang($arSort = null) {
		return self::getInstance()->getListGroupedByLang($arSort);
	}
	static public function formatPrice($priceValue, $currencyCode = null, $langID = LANGUAGE_ID, $arFormat = null) {
		return self::getInstance()->formatPrice($priceValue, $currencyCode, $langID, $arFormat);
	}
}
CurrencyFormat::__initEntity(CurrencyFormatDBS::getInstance());
