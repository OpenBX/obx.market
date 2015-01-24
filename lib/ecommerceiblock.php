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

/**
 * Class ECommerceIBlock
 * @package OBX\Market
 * @method static \CDBResult getFullList($bResultCDBResult = false)
 * @method static void clearCachedList()
 * @method static array getCachedList()
 * @method static void onIBlockDelete($ID)
 * @method static void registerModuleDependencies()
 * @method static void unRegisterModuleDependencies()
 */
class ECommerceIBlock extends EntityStatic {}
ECommerceIBlock::__initEntity(ECommerceIBlockDBS::getInstance());
