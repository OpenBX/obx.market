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

class CIBlockPropertyPrice extends EntityStatic {
	static public function delete($ID, $bDeleteIBlockProp = false) {
		return self::getInstance()->delete($ID, $bDeleteIBlockProp);
	}
	static public function getFullPriceList($IBLOCK_ID = 0, $bResultDBResult = false) {
		return self::getInstance()->getFullPriceList($IBLOCK_ID, $bResultDBResult);
	}
	static public function getFullPropList($IBLOCK_ID = 0, $bResultCDBResult = false) {
		return self::getInstance()->getFullPropList($IBLOCK_ID, $bResultCDBResult);
	}
	static public function addIBlockPriceProperty($arFields) {
		return self::getInstance()->addIBlockPriceProperty($arFields);
	}

	public function onIBlockPropertyDelete($ID) {
		return self::getInstance()->onIBlockPropertyDelete($ID);
	}
	static public function onIBlockDelete($ID) {
		return self::getInstance()->onIBlockDelete($ID);
	}
	public function getValue($IBLOCK_ID, $PRICE_ID, $bFormat = true) {
		return self::getInstance()->getValue($IBLOCK_ID, $PRICE_ID, $bFormat);
	}

	static public function registerModuleDependencies() {
		return self::getInstance()->registerModuleDependencies();
	}
	static public function unRegisterModuleDependencies() {
		return self::getInstance()->unRegisterModuleDependencies();
	}
}
CIBlockPropertyPrice::__initEntity(CIBlockPropertyPriceDBS::getInstance());