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
 * @method static getListGroupedByLang($arSort = null)
 * @method static formatPrice($priceValue, $currencyCode = null, $langID = LANGUAGE_ID, $arFormat = null)
 */
class CurrencyFormat extends EntityStatic {}
CurrencyFormat::__initEntity(CurrencyFormatDBS::getInstance());
