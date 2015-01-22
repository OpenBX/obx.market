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
 * Class OrderList
 * @method @static OrderDBS getInstance()
 */
class OrderList extends EntityStatic {
	static public function add($arFields = array()) {
		return parent::add($arFields);
	}
}
OrderList::__initEntity(OrderDBS::getInstance());