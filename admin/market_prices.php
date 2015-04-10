<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */

/**
 * @global \CMain $APPLICATION
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

// Заголовок ////////////////////////////////////////////////////////////////
$APPLICATION->SetTitle(GetMessage('OBX_MARKET_PRICES_TITLE'));
require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
if( !\CModule::IncludeModule('obx.core') ) die('Module obx.core is not installed');
if( !\CModule::IncludeModule('obx.market') ) die('Module obx.market is not installed');



// Контент //////////////////////////////////////////////////////////////////

// Доступ
if (!$USER->CanDoOperation('obx_market_admin_module')) {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->AddHeadScript("/bitrix/js/obx.market/jquery-1.9.1.min.js");
$APPLICATION->AddHeadScript("/bitrix/js/obx.market/tools.js");
$APPLICATION->AddHeadScript("/bitrix/js/obx.market/settings.js");
$APPLICATION->AddHeadScript("/bitrix/js/obx.market/jquery-cookie.js");
$arTabsList = array(
	array(
		"DIV" => "obx_market_settings_currency",
		"TAB" => GetMessage("OBX_MARKET_SETTINGS_TAB_CURRENCY"),
		"ICON" => "settings_currency",
		"TITLE" => GetMessage("OBX_MARKET_SETTINGS_TITLE_CURRENCY"),
		"CONTROLLER" => "Currency"
	),
	array(
		"DIV" => "obx_market_settings_price",
		"TAB" => GEtMessage("OBX_MARKET_SETTINGS_TAB_PRICE"),
		"ICON" => "settings_price",
		"TITLE" => GEtMessage("OBX_MARKET_SETTINGS_TITLE_PRICE"),
		"CONTROLLER" => "Price"
	),
	array(
		"DIV" => "obx_market_settings_catalog",
		"TAB" => GEtMessage("OBX_MARKET_SETTINGS_TAB_CATALOG"),
		"ICON" => "settings_catalog",
		"TITLE" => GEtMessage("OBX_MARKET_SETTINGS_TITLE_CATALOG"),
		"CONTROLLER" => "Catalog"
	),
);
$TabControl = new CAdminTabControl("tabSettings", $arTabsList);

?>
<div id="obx_market_settings">
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>&amp;lang=<?echo LANGUAGE_ID?>">
	<?
	$TabControl->Begin();
	foreach($arTabsList as &$arTab) {
		$TabControl->BeginNextTab();
		if( !array_key_exists("CONTROLLER", $arTab) || empty($arTab["CONTROLLER"]) ) {
			continue;
		}
		$MarketTab = OBX\Market\Settings::getController($arTab["CONTROLLER"]);
		if( !($MarketTab instanceof OBX\Market\Settings) ) {
			continue;
		}
		$MarketTab->saveTabData();
		$MarketTab->showMessages();
		$MarketTab->showErrors();
		$MarketTab->showTabContent();
	}
	$TabControl->End();
	?>
</form>
</div><!-- #obx_market_prices -->
<?
foreach($arTabsList as &$arTab) {
	if( !array_key_exists("CONTROLLER", $arTab) || empty($arTab["CONTROLLER"]) ) {
		continue;
	}
	$MarketTab = OBX\Market\Settings::getController($arTab["CONTROLLER"]);
	if( !($MarketTab instanceof OBX\Market\Settings) ) {
		continue;
	}
	?><div id="<?=$arTab["DIV"]."_scripts"?>"><?
	$MarketTab->showTabScripts();
	?></div><?
}


// Завершение страницы //////////////////////////////////////////////////////
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');