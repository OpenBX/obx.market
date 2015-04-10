<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */

use OBX\Market\Basket;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
IncludeModuleLangFile(__FILE__);
/** @global \CMain $APPLICATION */
$APPLICATION->RestartBuffer();

//Заголовки для предотвращения кеширования и указания типа данных JSON
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json; charset: UTF-8');


$arJSON = array(
	'messages' => array()
);

if (!CModule::IncludeModule('obx.market')) {
	$arJSON['messages'][] = array(
		'TYPE' => 'E',
		'TEXT' => GetMessage('OBX_MARKET_MODULE_NOT_INSTALLED'),
		'CODE' => 1
	);
} else {

	$Basket = Basket::getCurrent();

	if (is_array($_REQUEST['add']) && count($_REQUEST['add']) > 0) {
		foreach ($_REQUEST['add'] as $productID => $quantity) {
			$productID = intval($productID);
			$quantity = intval($quantity);

			if ($Basket->isEmpty($productID)) {
				$bSuccess = $Basket->addProduct($productID, $quantity);
			} else {
				$newProductQuantity = $Basket->setProductQuantity($productID, $quantity);
				$bSuccess = ($newProductQuantity>0)?true:false;
			}
			if (!$bSuccess<=0) {
				$arJSON['messages'][] = $Basket->popLastError('ARRAY');
			}
		}
	}
	if (isset($_REQUEST['update'])
			&& isset($_REQUEST['update']['id'])
			&& isset($_REQUEST['update']['qty'])
	) {
		$productID = intval($_REQUEST['update']['id']);
		$quantity = intval($_REQUEST['update']['qty']);
		if ($productID > 0) {
			if ($Basket->isEmpty($productID)) {
				$bSuccess = $Basket->addProduct($productID, $quantity);
			} else {
				$bSuccess = $Basket->setProductQuantity($productID, $quantity);
			}
			if (!$bSuccess) {
				$arJSON['messages'][] = $Basket->popLastError('ARRAY');
			}
		}
	}
	if (isset($_REQUEST['remove'])) {
		$bSuccess = $Basket->removeProduct(intval($_REQUEST['remove']));
		if (!$bSuccess) {
			$arJSON['messages'][] = $Basket->popLastError('ARRAY');
		}
	}

	if (isset($_REQUEST['make_order']) && $_REQUEST['make_order'] = "Y") {
		//$basketID = $Basket->getFields('ID');
		//$bMoveBasketSuccess = BasketList::update(array('ID' => $basketIDt, 'ORDER_ID' => $orderID));

	}
	$arJSON['basket_cost'] = $Basket->getCost();
	$arJSON['products_count'] = $Basket->getProductsCount();
	$arJSON['products_list'] = array();
	$arJSON['items_list'] = $Basket->getQuantityList();

	$arProductList = $Basket->getProductsList(true);
	foreach ($arProductList as &$arBasketItem) {

		$arProperties = $Basket->getProductIBlockPropertyValues($arBasketItem['PRODUCT_ID']);
		$arJsonProduct = array(
			'id' => $arBasketItem['PRODUCT_ID'],
			'href' => $arBasketItem['IB_ELEMENT']['DETAIL_PAGE_URL'],
			'name' => $arBasketItem['IB_ELEMENT']['NAME'],
			'price_id' => $arBasketItem['PRICE_ID'],
			'price' => $arBasketItem['PRICE']['VALUE'],
			'section_id' => $arBasketItem['IB_ELEMENT']['SECTION_ID']
		);
		foreach ($arProperties as &$arProperty) {
			$arJsonProduct['prop_' . $arProperty['ID']] = $arProperty['VALUE'];
		}
		$arJSON['products_list'][] = $arJsonProduct;
	}
}


//rint_r($arJSON);
if(!defined('BX_UTF') || BX_UTF !== true) {
	$arJSON = $APPLICATION->ConvertCharsetArray($arJSON, LANG_CHARSET, 'UTF-8');
}
echo json_encode($arJSON);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
