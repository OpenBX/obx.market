<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
require $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/obx.market/includes/'.LANGUAGE_ID.'/cmp_lang_desc.php';
$arComponentDescription = array(
	'NAME' => 'Order cancel',
	'DESCRIPTION' => 'Order cancel',
	'PATH' => array(
		'ID' => 'obx_market',
		'NAME' => GetMessage('OBX_MARKET_CMP_PATH_MARKET_NAME'),
		'CHILD' => array(
			'ID' => 'sale',
			'NAME' => GetMessage('OBX_MARKET_BASKET_CMP_PATH_SALE'),
			'SORT' => 10,
		),
	),
);
