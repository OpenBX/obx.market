<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */

use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

if(!CModule::IncludeModule('iblock')){
    return false;
}

$arEventList = GetModuleEvents('obx.market', 'onBeforeModuleInclude', true);
foreach($arEventList as $arEvent) {
	ExecuteModuleEventEx($arEvent, array());
}

if(!CModule::IncludeModule('obx.core')) {
	$obxCorePath = $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/obx.core/install/index.php';
	if(!file_exists($obxCorePath) ) {
		return false;
	}
	require_once $obxCorePath;
	$obxCore = new obx_core();
	$obxCore->DoInstall();
	if(!CModule::IncludeModule('obx.core')) {
		return false;
	}
}
Loader::registerAutoLoadClasses('obx.market', array(
	'OBX_Market_BXMainEventsHandlers' => 'lib/bxmaineventshandlers.php'
));

$arEventList = GetModuleEvents('obx.market', 'onAfterModuleInclude', true);
foreach($arEventList as $arEvent) {
	ExecuteModuleEventEx($arEvent, array());
}

