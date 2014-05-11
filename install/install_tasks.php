<?php
/******************************************
 ** @product OpenBX:Market Bitrix Module **
 ** @authors                             **
 **         Maksim S. Makarov            **
 ** @license Affero GPLv3                **
 ** @mailto rootfavell@gmail.com         **
 ** @copyright 2013 DevTop               **
 ******************************************/

/**
 * @var $this \CModule
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */
global $APPLICATION;
global $USER;
global $DB;

IncludeModuleLangFile(__FILE__);


/** @noinspection PhpDynamicAsStaticMethodCallInspection */
$arTasksListRaw = \CTask::GetTasksInModules(false, $this->MODULE_ID);
if(empty($arTasksListRaw)) {
	return false;
}
$arTasksListRaw = $arTasksListRaw[$this->MODULE_ID];
$arTasksList = array();
foreach($arTasksListRaw as &$arTask) {
	$arTasksList[$arTask['LETTER']] = $arTask;
}
unset($arTasksListRaw);


$CGroup = new \CGroup();
$moduleAdminRoleGroupID = $CGroup->Add(array(
	'ACTIVE' => 'Y',
	'NAME' => GetMessage('OBX_MARKET_ROLE_MODULE_ADMIN_NAME'),
	'DESCRIPTION' => GetMessage('OBX_MARKET_ROLE_MODULE_ADMIN_DESCRIPTION'),
));
$ordersOperatorRoleGroupID = $CGroup->Add(array(
	'ACTIVE' => 'Y',
	'NAME' => GetMessage('OBX_MARKET_ROLE_ORDERS_OPERATOR_NAME'),
	'DESCRIPTION' => GetMessage('OBX_MARKET_ROLE_ORDERS_OPERATOR_DESCRIPTION'),
));
$arTasksInModule = array();
if( $moduleAdminRoleGroupID > 2 ) {
	$arTasksInModule[$moduleAdminRoleGroupID] = array('ID' => $arTasksList['W']['ID']);
	$APPLICATION->SetGroupRight($this->MODULE_ID, $moduleAdminRoleGroupID, 'W');
}
if( $ordersOperatorRoleGroupID > 2) {
	$arTasksInModule[$ordersOperatorRoleGroupID] = array('ID' => $arTasksList['P']['ID']);
	$APPLICATION->SetGroupRight($this->MODULE_ID, $ordersOperatorRoleGroupID, 'P');
}
if(!empty($arTasksInModule)) {
	$CGroup->SetTasksForModule($this->MODULE_ID, $arTasksInModule);
}
