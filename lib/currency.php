<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */


namespace OBX\Market;

use OBX\Core\DBSimple\EntityStatic;

/**
 * Class Currency
 * @package OBX\Market
 * @method static bool setDefault($currency, &$bIsAlreadyDefault = false)
 * @method static string getDefault()
 * @method static array getDefaultArray()
 */
class Currency extends EntityStatic {}
Currency::__initEntity(CurrencyDBS::getInstance());
