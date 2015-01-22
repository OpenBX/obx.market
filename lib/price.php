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
 * @method @static PriceDBS getInstance()
 * @method @static getOptimalProductPrice($productID, $userID = null, $langID = LANGUAGE_ID)
 * @method @static getProductPriceList($productID, $userID = null)
 * @method @static formatPrice($priceValue, $priceCode, $langID = null)
 * @method @static addGroup($priceID, $groupID = 0)
 * @method @static removeGroup($priceID, $groupID = 0)
 * @method @static setGroupList($priceID, $arGroupIDList)
 * @method @static getGroupList($priceID, $bReturnCDBResult = false)
 * @method @static getGroupListCached($priceID)
 * @method @static getAvailPriceForUser($userID, $bReturnCDBResult = false)
 */
class Price extends EntityStatic {}
Price::__initEntity(PriceDBS::getInstance());
