<?php
/*******************************************
 ** @product OBX:Market Bitrix Module     **
 ** @authors                              **
 **         Maksim S. Makarov aka pr0n1x  **
 **         Morozov P. Artem aka tashiro  **
 ** @license Affero GPLv3                 **
 ** @mailto rootfavell@gmail.com          **
 ** @mailto tashiro@yandex.ru             **
 ** @copyright 2013 DevTop                **
 *******************************************/

use OBX\Market\Price;
use OBX\Market\Basket;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponent $this
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @var string $componentPath
 * @var string $componentName
 * @var string $componentTemplate
 *
 * @global CDatabase $DB
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CCacheManager $CACHE_MANAGER
 */
global $DB, $USER, $APPLICATION, $CACHE_MANAGER;

if (!CModule::IncludeModule('obx.market')) {
    ShowError(GetMessage('OBX_MARKET_NOT_INSTALLED'));
    return false;
}
if (!CModule::IncludeModule('iblock')) {
    ShowError(GetMessage('OBX_MARKET_NOT_INSTALLED'));
    return false;
}

///////////////////////////////////////////////////////////////////////////
// Processing of received parameters
///////////////////////////////////////////////////////////////////////////

$arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
$arParams['ID'] = intval($arParams['ID']);
$arParams['ACTION_VARIABLE'] = trim($arParams['ACTION_VARIABLE']);
$arParams['PRODUCT_ID_VARIABLE'] = trim($arParams['PRODUCT_ID_VARIABLE']);
$arParams['USE_QUANTITY_VARIABLE'] = $arParams['USE_QUANTITY_VARIABLE'] == 'Y' ? 'Y' : 'N';
$arParams['QUANTITY_VARIABLE'] = trim($arParams['QUANTITY_VARIABLE']);
$arParams['PATH_TO_BASKET'] = trim($arParams['PATH_TO_BASKET']);
$arParams['FILTER_NAME'] = trim($arParams['FILTER_NAME']);

$arParams['AJAX_BUY'] = $arParams['AJAX_BUY'] == 'Y' ? 'Y' : 'N';

$arParams['ELEMENT_ID'] = intval($arParams['~ELEMENT_ID']);
if($arParams['ELEMENT_ID'] > 0 && $arParams['ELEMENT_ID'].'' != $arParams['~ELEMENT_ID'])
{
	ShowError(GetMessage('PRODUCT_ELEMENT_NOT_FOUND'));
	@define('ERROR_404', 'Y');
	if($arParams['SET_STATUS_404']==='Y')
		CHTTP::SetStatus('404 Not Found');
	return;
}

///////////////////////////////////////////////////////////////////////////
// Processing of the Buy link
///////////////////////////////////////////////////////////////////////////

$strError = '';
if (array_key_exists($arParams['ACTION_VARIABLE'], $_REQUEST) && array_key_exists($arParams['PRODUCT_ID_VARIABLE'], $_REQUEST)) {
		$Basket = Basket::getCurrent();
		$q = 1;
		if ($arParams['USE_QUANTITY_VARIABLE'] == 'Y' && array_key_exists($arParams['QUANTITY_VARIABLE'], $_REQUEST)) {
			$rQ = intval($_REQUEST[$arParams['QUANTITY_VARIABLE']]);
			$q = ($rQ > 0) ? $rQ : 1;
		}

		switch ($_REQUEST[$arParams['ACTION_VARIABLE']]) {
			case 'ADD' :
				$Basket->addProduct($_REQUEST[$arParams['PRODUCT_ID_VARIABLE']], $q);
				break;
			case 'BUY' :
				$Basket->addProduct($_REQUEST[$arParams['PRODUCT_ID_VARIABLE']], $q);
				LocalRedirect($arParams['PATH_TO_BASKET']);
				break;
			case 'DEL' :
				$Basket->removeProduct($_REQUEST[$arParams['PRODUCT_ID_VARIABLE']]);
				break;
			default :
				break;
		}
		LocalRedirect($APPLICATION->GetCurPageParam('', array($arParams['PRODUCT_ID_VARIABLE'], $arParams['ACTION_VARIABLE'], $arParams['QUANTITY_VARIABLE'])));
}

///////////////////////////////////////////////////////////////////////////
// Work with cache
///////////////////////////////////////////////////////////////////////////
if( $this->StartResultCache(false, ($arParams['CACHE_GROUPS']==='N'? false: $USER->GetGroups())) ) {

	if (!\Bitrix\Main\Loader::includeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return 0;
	}

	//Handle case when ELEMENT_CODE used
	if($arParams['ELEMENT_ID'] <= 0)
		$arParams['ELEMENT_ID'] = CIBlockFindTools::GetElementID(
			$arParams['ELEMENT_ID'],
			$arParams['ELEMENT_CODE'],
			false,
			false,
			array(
				'IBLOCK_ID' => $arParams['IBLOCK_ID'],
				'IBLOCK_LID' => SITE_ID,
				'IBLOCK_ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'ACTIVE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
			)
		);
	if($arParams["ELEMENT_ID"] > 0)
	{
		$arElementSelect = array(
			 'ID'
			,'NAME'
			,'DATE_CREATE'
			,'DATE_CREATE_UNIX'
			,'IBLOCK_ID'
			,'IBLOCK_SECTION_ID'
			,'PREVIEW_PICTURE'
			,'PREVIEW_TEXT'
			,'PREVIEW_TEXT_TYPE'
			,'DETAIL_PICTURE'
			,'DETAIL_TEXT'
			,'DETAIL_TEXT_TYPE'
			,'SEARCHABLE_CONTENT'
			,'CODE'
			,'TAGS'
			,'IBLOCK_TYPE_ID'
			,'IBLOCK_CODE'
			,'IBLOCK_NAME'
			,'DETAIL_PAGE_URL'
			,'LIST_PAGE_URL'
		);
		$arElementFilter = array(
			'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
			'IBLOCK_ID' => $arParams['IBLOCK_ID'],
			'ACTIVE' => 'Y'
		);

		$arNavStartParams = array();

		/** @var \CIBlockResult $dbElement */
		$dbElement = \CIBlockElement::GetByID($arParams['ELEMENT_ID']);

		$bPriceFound = true;

		foreach ($arParams['PROPERTY_CODE'] as $propKey => $propCode) {
			if (empty($propCode)) unset($arParams['PROPERTY_CODE'][$propKey]);
		}
		$obElement = $dbElement->GetNextElement();

		if (!empty($obElement)) {
			/** @var \_CIBElement $obElement */

			$arResult = $obElement->GetFields();
			$arResult['PROPERTIES'] = $obElement->GetProperties();
			$arResult['DISPLAY_PROPERTIES'] = array();
			foreach ($arParams['PROPERTY_CODE'] as $pid) {
				$prop = & $arResult['PROPERTIES'][$pid];
				if ((is_array($prop['VALUE']) && count($prop['VALUE']) > 0)
					|| (!is_array($prop['VALUE']) && strlen($prop['VALUE']) > 0)
				) {
					$arResult['DISPLAY_PROPERTIES'][$pid] = CIBlockFormatProperties::GetDisplayValue($arResult, $prop, 'catalog_out');
				}
			}
			$arButtons = CIBlock::GetPanelButtons(
				$arResult['IBLOCK_ID'],
				$arResult['ID'],
				0,
				array('SECTION_BUTTONS' => false, 'SESSID' => false)
			);

			$arResult['EDIT_LINK'] = $arButtons['edit']['edit_element']['ACTION_URL'];
			$arResult['DELETE_LINK'] = $arButtons['edit']['delete_element']['ACTION_URL'];

			$arResult['PREVIEW_PICTURE'] = CFile::GetFileArray($arResult['PREVIEW_PICTURE']);
			$arResult['DETAIL_PICTURE'] = CFile::GetFileArray($arResult['DETAIL_PICTURE']);

			/*
			 * Mapping arPrices from Bitrix:CIBlockPriceTools::GetItemPrices();
			 */
			/*
			$arResult['PRICES'] = array(
				'VALUE_NOVAT', // цена без налога
				'PRINT_VALUE_NOVAT', // цена без налога для вывода

				'VALUE_VAT', // цена с налогом
				'PRINT_VALUE_VAT', // цена с налогом для вывода

				'VATRATE_VALUE', // процент налога
				'PRINT_VATRATE_VALUE', // процент налога для вывода

				'DISCOUNT_VALUE_NOVAT', // сумма скидки без налога
				'PRINT_DISCOUNT_VALUE_NOVAT', // сумма скидки без налога для вывода

				'DISCOUNT_VALUE_VAT', // сумма скидки с налогом
				'PRINT_DISCOUNT_VALUE_VAT', // сумма скидки с налогом для вывода

				'DISCOUNT_VATRATE_VALUE', // процент налога для суммы скидки
				'PRINT_DISCOUNT_VATRATE_VALUE', // процент налога для суммы скидки для вывода

				'CURRENCY', // код валюты
				'ID', // ID ценового предложения
				'CAN_ACCESS', // возможность просмотра - Y/N
				'CAN_BUY', // возможность купить - Y/N
				'VALUE', // цена
				'PRINT_VALUE', // отформатированная цена для вывода
				'DISCOUNT_VALUE', // цена со скидкой
				'PRINT_DISCOUNT_VALUE', // отформатированная цена со скидкой
			);
			*/
			$arResult['PRICE'] = null; // Нужная цена
			$arSupportData = array();

			$arResultPrices = Price::getProductPriceList($arResult['ID']);
			foreach ($arResultPrices as &$arPrice) {
				if ($arPrice['IS_OPTIMAL'] == 'Y' && $arPrice['AVAILABLE'] == 'Y') { // IS_OPTIMAL может быть == Y только 1 раз
					$arResult['PRICE'] = $arPrice;
					$arResult['CAN_BUY'] = 'Y';
					$arSupportData['WEIGHT']['ID'] = $arPrice['WEIGHT_VAL_PROP_ID'];
					$arSupportData['DISCOUNT']['ID'] = $arPrice['DISCOUNT_VAL_PROP_ID'];
				}
				$arResult['PRICES'][$arPrice['PRICE_CODE']] = array(
					'VALUE_NOVAT' => $arPrice['TOTAL_VALUE'],
					'PRINT_VALUE_NOVAT' => $arPrice['TOTAL_VALUE_FORMATTED'],

					'VALUE_VAT' => $arPrice['TOTAL_VALUE'],
					'PRINT_VALUE_VAT' => $arPrice['TOTAL_VALUE_FORMATTED'],

					'VATRATE_VALUE' => 'NULL',
					'PRINT_VATRATE_VALUE' => '0%',

					'DISCOUNT_VALUE_NOVAT' => $arPrice['DISCOUNT_VALUE'],
					'PRINT_DISCOUNT_VALUE_NOVAT' => $arPrice['DISCOUNT_VALUE_FORMATTED'],

					'DISCOUNT_VALUE_VAT' => $arPrice['DISCOUNT_VALUE'],
					'PRINT_DISCOUNT_VALUE_VAT' => $arPrice['DISCOUNT_VALUE_FORMATTED'],

					'DISCOUNT_VATRATE_VALUE' => 'NULL',
					'PRINT_DISCOUNT_VATRATE_VALUE' => '0%',

					'CURRENCY' => $arPrice['PRICE_CURRENCY'],
					'ID' => $arPrice['PRICE_ID'],
					'CAN_ACCESS' => $arPrice['AVAILABLE'],
					'CAN_BUY' => $arPrice['AVAILABLE'],
					'VALUE' => $arPrice['VALUE'],
					'PRINT_VALUE' => $arPrice['VALUE_FORMATTED'],
					'DISCOUNT_VALUE' => $arPrice['DISCOUNT_VALUE'],
					'PRINT_DISCOUNT_VALUE' => $arPrice['DISCOUNT_VALUE_FORMATTED'],
				);
			}
			unset($arPrice);

			$arResult['BUY_URL'] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams['ACTION_VARIABLE'] . '=BUY&' . $arParams['PRODUCT_ID_VARIABLE'] . '=' . $arResult['ID'], array($arParams['PRODUCT_ID_VARIABLE'], $arParams['ACTION_VARIABLE'])));
			$arResult['ADD_URL'] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams['ACTION_VARIABLE'] . '=ADD&' . $arParams['PRODUCT_ID_VARIABLE'] . '=' . $arResult['ID'], array($arParams['PRODUCT_ID_VARIABLE'], $arParams['ACTION_VARIABLE'])));
			$arResult['DEL_URL'] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams['ACTION_VARIABLE'] . '=DEL&' . $arParams['PRODUCT_ID_VARIABLE'] . '=' . $arResult['ID'], array($arParams['PRODUCT_ID_VARIABLE'], $arParams['ACTION_VARIABLE'])));

			$arResult["IN_BASKET"] = false;
			$Basket = Basket::getCurrent();
			$arBasketItems = $Basket->getProductsList();
			foreach( $arBasketItems as &$arBasketItem ){
				if($arResult["ID"] == $arBasketItem["PRODUCT_ID"])
					$arResult["IN_BASKET"] = true;
			} unset($arBasketItem);

			if (empty($arResult['PRICE'])) {
				$bPriceFound = false;
			}

			if (!empty ($arSupportData['WEIGHT']['ID'])) {
				$resWeight = CIBlockProperty::GetByID(
					$arSupportData['WEIGHT']['ID'],
					$arParams['IBLOCK_ID']
				);
				$arWeight = $resWeight->GetNext();
				unset ($resWeight);
				if (!empty($arWeight['CODE'])) {
					$arSupportData['WEIGHT']['CODE'] = $arWeight['CODE'];
				}
				unset ($arWeight);
			}
			if (!empty ($arSupportData['DISCOUNT']['ID'])) {
				$resDiscount = CIBlockProperty::GetByID(
					$arSupportData['DISCOUNT']['ID'],
					$arParams['IBLOCK_ID']
				);
				$arDiscount = $resDiscount->GetNext();
				unset ($resDiscount);
				if (!empty($arDiscount['CODE'])) {
					$arSupportData['DISCOUNT']['CODE'] = $arDiscount['CODE'];
				}
				unset ($arDiscount);
			}

			// end Item
			if (!$bPriceFound) {
				$this->AbortResultCache();
				$arResult['ERROR'] = GetMessage('OBX_MARKET_CMP_CAN_NOT_FIND_PRICE');
			} else {
				$arResult['ERROR'] = null;
			}
			$arResult['SUPPORT_DATA'] = $arSupportData;
			unset ($arResults);
			unset ($arSections);
			unset ($arSupportData);

			$this->SetResultCacheKeys(array(
				"IBLOCK_ID",
				"ID",
				"IBLOCK_SECTION_ID",
				"NAME",
				"LIST_PAGE_URL",
				"DISPLAY_PROPERTIES",
				"SECTION",
				"PRICES",
			));
			$this->IncludeComponentTemplate();

		}
		else {
			$this->AbortResultCache();
			ShowError(GetMessage("PRODUCT_ELEMENT_NOT_FOUND"));
			@define("ERROR_404", "Y");
			if($arParams["SET_STATUS_404"]==="Y") {
				CHTTP::SetStatus("404 Not Found");
			}
			$arResult = array();
		}
	}
	else
	{
		$this->AbortResultCache();
		ShowError(GetMessage("PRODUCT_ELEMENT_NOT_FOUND"));
		@define("ERROR_404", "Y");
		if($arParams["SET_STATUS_404"]==="Y") {
			CHTTP::SetStatus("404 Not Found");
		}
	}
}

if(isset($arResult['ID'])) {
	return $arResult['ID'];
}
return 0;
