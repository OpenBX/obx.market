<?php
/******************************************
 ** @product OpenBX:Market Bitrix Module **
 ** @authors                             **
 **         Maksim S. Makarov            **
 ** @license Affero GPLv3                **
 ** @mailto rootfavell@gmail.com         **
 ** @copyright 2013 DevTop               **
 ******************************************/


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if(!CModule::IncludeModule("obx.market"))return;

// Доступ
//if (!$USER->CanDoOperation('edit_orders'))
//	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
//$isAdmin = $USER->CanDoOperation('edit_orders');
if (!$USER->IsAdmin()) {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//echo "Редактирование платежных систем";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>