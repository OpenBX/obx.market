<?php
/***********************************************
 ** @product OBX:Market Bitrix Module         **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Market;

use OBX\Core\DBSimple\EntityStatic;

/**
 * Class CIBlockPropertyPrice
 * @package OBX\Market
 * @method static getFullPriceList($IBLOCK_ID = 0, $bResultDBResult = false)
 * @method static getFullPropList($IBLOCK_ID = 0, $bResultCDBResult = false)
 * @method static addIBlockPriceProperty($arFields)
 * @method static onIBlockPropertyDelete($ID)
 * @method static onIBlockDelete($ID)
 * @method static getValue($IBLOCK_ID, $PRICE_ID, $bFormat = true)
 * @method static registerModuleDependencies()
 * @method static unRegisterModuleDependencies()
 */
class CIBlockPropertyPrice extends EntityStatic {}
CIBlockPropertyPrice::__initEntity(CIBlockPropertyPriceDBS::getInstance());
