<?php
/***********************************************
** @product OBX:Market Bitrix Module         **
** @authors                                  **
**         Maksim S. Makarov aka pr0n1x      **
**         Artem P. Morozov  aka tashiro     **
** @license Affero GPLv3                     **
** @mailto rootfavell@gmail.com              **
** @mailto tashiro@yandex.ru                 **
** @copyright 2013 DevTop                    **
***********************************************/

namespace OBX\Market;

use \OBX\Core\MessagePoolDecorator;

IncludeModuleLangFile(__FILE__);


class Order extends MessagePoolDecorator {

	const EVENT_FINISH = 'onMakeOrderFinish';
	const EVENT_MODULE = 'obx.market';


	/**
	* @var null|OrderDBS
	*/
	protected $_OrderDBS = null;

	/**
	* @var null|BasketItemDBS
	*/
	protected $_BasketItemDBS = null;

	/**
	* @var null|OrderStatusDBS
	*/
	protected $_OrderStatusDBS = null;

	/**
	* @var null|OrderPropertyDBS
	*/
	protected $_OrderPropertyDBS = null;

	/**
	 * @var null|OrderPropertyValuesDBS
	 */
	protected $_OrderPropertyValuesDBS = null;

	/**
	* @var null|OrderCommentDBS
	*/
	protected $_OrderCommentDBS = null;

	/**
	* @var null|ECommerceIBlockDBS
	*/
	protected $_EComIBlockDBS = null;

	/**
	* @var null|PriceDBS
	*/
	protected $_PriceDBS = null;

	/**
	* @var null|CIBlockPropertyPriceDBS
	*/
	protected $_CIBlockPropertyPriceDBS = null;

	/**
	* @var Basket
	*/
	protected $_Basket = null;

	protected $_arOrder = array();
	protected $_arOrderStatus = null;
	protected $_bFieldsChanged = true;

	protected $_productCount = null;
	protected $_itemsCount = 0;
	protected $_costValue = 0;
	protected $_costTotalValue = 0;
	protected $_weightValue = 0;
	protected $_discountValue = 0;

	protected $_arItemsCache = null;

	// Кострутор объекта из БД или из ID заказа
	protected function __construct() {
		$this->_OrderDBS = OrderDBS::getInstance();
		$this->_OrderStatusDBS = OrderStatusDBS::getInstance();
		$this->_OrderPropertyDBS = OrderPropertyDBS::getInstance();
		$this->_OrderPropertyValuesDBS = OrderPropertyValuesDBS::getInstance();
		$this->_OrderCommentDBS = OrderCommentDBS::getInstance();
		$this->_BasketItemDBS = BasketItemDBS::getInstance();
		$this->_EComIBlockDBS = ECommerceIBlockDBS::getInstance();
		$this->_PriceDBS = PriceDBS::getInstance();
		$this->_CIBlockPropertyPriceDBS = CIBlockPropertyPriceDBS::getInstance();
		//$this->_Basket = _Basket::getByOrderID();
	}

	protected function __clone() {
	}

	/**
	* @param null $arSort
	* @param null $arFilter
	* @param null $arGroupBy
	* @param null $arPagination
	* @param null $arSelect
	* @param bool $bShowNullFields
	* @return OrderDBResult
	*/
	public static function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true) {
		$OrderList = OrderDBS::getInstance();
		$res = $OrderList->getList($arSort, $arFilter, $arGroupBy, $arPagination, $arSelect, $bShowNullFields);

		$OrderDBResult = new OrderDBResult($res, $arSelect);

		return $OrderDBResult;
	}

	/**
	* @param null $arSort
	* @param null $arFilter
	* @param null $arGroupBy
	* @param null $arPagination
	* @param null $arSelect
	* @param bool $bShowNullFields
	* @return array
	*/
	public static function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true) {
		$arResult = array();
		$res = self::getList($arSort, $arFilter, $arGroupBy, $arPagination, $arSelect, $bShowNullFields);
		while ($arOrder = $res->Fetch()) {
			$arResult[] = $arOrder;
		}
		return $arResult;
	}

	/**
	* @param $orderID
	* @param null $arSelect
	* @return array|bool|mixed
	*/
	public static function getByID($orderID, $arSelect = null) {
		$OrderDBS = OrderDBS::getInstance();
		$rsOrder = $OrderDBS->getList(null, array('ID' => $orderID), null, null, $arSelect, false);
		$arOrder = $rsOrder->Fetch();
		if (empty($arOrder)) {
			return array();
		}
		return $arOrder;
	}

	/**
	* @param null $arFields
	* @param array $arErrors
	* @return null|Order
	*/
	public static function add($arFields = null, &$arErrors = array()) {
		$Order = new self;
		$Order->_OrderDBS->clearErrors();
		$newID = $Order->_OrderDBS->add($arFields);

		if ($newID <= 0) {
			$arErrors = $Order->_OrderDBS->getErrors();
			return null;
		}
		$bSuccess = $Order->read($newID);
		if (!$bSuccess) {
			$arErrors = $Order->getErrors();
			return null;
		}
		return $Order;
	}

	static public function makeOrder($arFields = null, Basket $Basket = null, &$arErrors = array()) {
		$arProperties = null;
		if( array_key_exists('PROPERTIES', $arFields) ) {
			$arProperties = $arFields['PROPERTIES'];
		}
		$Order = static::add($arFields, $arErrors);
		if(null === $Order) {
			return null;
		}
		if(null !== $arProperties) {
			$Order->setProperties($arProperties);
		}
		if($Basket instanceof Basket) {
			$OrderBasket = Basket::getByOrderID($Order->getID());
			$OrderBasket->mergeBasket($Basket, true);
			$arLastBasketError = $OrderBasket->getLastError('ARRAY');
			if(null !== $arLastBasketError) {
				$arErrors[] = $arLastBasketError;
			}
		}
		if( false === $Order->callFinishEvent() ) {
			$arErrors[] = $Order->getLastError('ARRAY');
			return null;
		}
		return $Order;
	}

	/**
	* @param $orderID
	*/
	public static function delete($orderID) {
		$OrderDBS = OrderDBS::getInstance();
		$OrderDBS->delete($orderID);
	}

	/**
	* @param $ID
	* @param array $arErrors
	* @return null|Order
	*/
	public static function getOrder($ID, &$arErrors = array()) {
		$Order = new self;
		$bSuccess = $Order->read($ID);
		if (!$bSuccess) {
			$arErrors = $Order->getErrors();
			return null;
		}
		return $Order;
	}

	/**
	* @param $orderID
	* @return bool
	*/
	protected function read($orderID) {
		$arOrderSelect = array(
			'DATE_CREATED',
			'ID',
			'STATUS_ID',
			'TIMESTAMP_X',
			'USER_ID',
			'CURRENCY'
		);
		$arOrderFilter = array('ID' => null);
		if ($orderID instanceof OrderDBResult) {
			$arOrder = $orderID->Fetch();
			if (isset($arOrder['ID'])) {
				$arOrderFilter = array('ID' => $orderID['ID']);
			}
		} elseif (is_numeric($orderID)) {
			$arOrderFilter = array('ID' => $orderID);
		} elseif (!empty($orderID) && is_array($orderID)) {
			if (isset($orderID['ID']) && intval($orderID['ID']) > 0) {
				$arOrderFilter = array('ID' => $orderID['ID']);
			}
		}
		else {
			$this->addError(GetMessage('OBX_ORDER_CLASS_ERROR_3'), 3);
			return false;
		}

		$arOrderList = $this->_OrderDBS->getListArray(null, $arOrderFilter, null, null, $arOrderSelect);


		if (empty($arOrderList) || !is_array($arOrderList)) {
			$this->addError(GetMessage('OBX_ORDER_CLASS_ERROR_3'), 3);
			return false;
		}
		$arOrder = $arOrderList[0];

		$this->_arOrder = $arOrder;
		$this->_bFieldsChanged = false;
		$this->_Basket = Basket::getByOrderID($arOrder['ID']);
		return true;
	}

	/**
	* @param $basketID
	*/
	public function setBasketID($basketID) {
		$Basket = Basket::getInstance($basketID);
		if ($Basket !== null) {
			$this->_Basket = $Basket;
		}
	}

	/**
	* @return array
	*/
	public function getFields() {
		if ($this->_bFieldsChanged) {
			$this->read($this->_arOrder['ID']);
		}
		return $this->_arOrder;
	}

	public function getID() {
		return $this->_arOrder['ID'];
	}

	/**
	* @param $arFields
	* @return bool
	*/
	public function setFields($arFields) {
		$arFields['ID'] = $this->_arOrder['ID'];
		// Для установки статуса есть отдельный метод
		if( array_key_exists('STATUS_ID', $arFields) && !array_key_exists(OBX_MAGIC_WORD, $arFields) ) {
			unset($arFields['STATUS_ID']);
		}
		if ($this->_OrderDBS->update($arFields)) {
			$this->_bFieldsChanged = true;
			return true;
		}
		else {
			$arError = $this->_OrderDBS->getLastError('ARRAY');
			$this->addError($arError['TEXT'], $arError['CODE']);
		}
		return false;
	}


	/**
	* Получить значения свойств заказа
	* @param null|array $arSort
	* @param null $arFilter
	* @return array
	*/
	public function getProperties($arSort = null, $arFilter = null) {
		$arResult = array();

		if (is_array($arFilter)) {
			$arFilter['ORDER_ID'] = $this->_arOrder['ID'];
		} else {
			$arFilter = array('ORDER_ID' => $this->_arOrder['ID']);
		}
		$arSortRaw = $arSort;
		$arSort = array();
		if( array_key_exists('ID', $arSortRaw) ) {
			$arSort['PROPERTY_ID'] = $arSortRaw['ID'];
		}
		if( array_key_exists('PROPERTY_ID', $arSortRaw) ) {
			$arSort['PROPERTY_ID'] = $arSortRaw['PROPERTY_ID'];
		}
		if( array_key_exists('CODE', $arSortRaw) ) {
			$arSort['PROPERTY_CODE'] = $arSortRaw['CODE'];
		}
		if( array_key_exists('NAME', $arSortRaw) ) {
			$arSort['PROPERTY_NAME'] = $arSortRaw['NAME'];
		}
		if( array_key_exists('PROPERTY_TYPE', $arSortRaw) ) {
			$arSort['PROPERTY_TYPE'] = $arSortRaw['PROPERTY_TYPE'];
		}
		if( array_key_exists('SORT', $arSortRaw) ) {
			$arSort['PROPERTY_SORT'] = $arSortRaw['SORT'];
		}
		if( array_key_exists('VALUE', $arSortRaw) ) {
			$arSort['VALUE'] = $arSortRaw['VALUE'];
		}
		if( array_key_exists('VALUE_ENUM_ID', $arSortRaw) ) {
			$arSort['VALUE_ENUM_ID'] = $arSortRaw['VALUE_ENUM_ID'];
		}
		$arFilterRaw = $arFilter;
		$arFilter = array();
		if( array_key_exists('ID', $arFilterRaw) ) {
			$arFilter['PROPERTY_ID'] = $arFilterRaw['ID'];
		}
		if( array_key_exists('PROPERTY_ID', $arFilterRaw) ) {
			$arFilter['PROPERTY_ID'] = $arFilterRaw['PROPERTY_ID'];
		}
		if( array_key_exists('CODE', $arFilterRaw) ) {
			$arFilter['PROPERTY_CODE'] = $arFilterRaw['CODE'];
		}
		if( array_key_exists('NAME', $arFilterRaw) ) {
			$arFilter['PROPERTY_NAME'] = $arFilterRaw['NAME'];
		}
		if( array_key_exists('PROPERTY_TYPE', $arFilterRaw) ) {
			$arFilter['PROPERTY_TYPE'] = $arFilterRaw['PROPERTY_TYPE'];
		}
		if( array_key_exists('SORT', $arFilterRaw) ) {
			$arFilter['PROPERTY_SORT'] = $arFilterRaw['SORT'];
		}
		if( array_key_exists('VALUE', $arFilterRaw) ) {
			$arFilter['VALUE'] = $arFilterRaw['VALUE'];
		}
		if( array_key_exists('VALUE_ENUM_ID', $arFilterRaw) ) {
			$arFilter['VALUE_ENUM_ID'] = $arFilterRaw['VALUE_ENUM_ID'];
		}
		$arSelect = array(
			'PROPERTY_ID',
			'PROPERTY_CODE',
			'PROPERTY_NAME',
			'PROPERTY_DESCRIPTION',
			'PROPERTY_TYPE',
			'PROPERTY_SORT',
			'ORDER_ID',
			'VALUE',
			'VALUE_ENUM_ID',
			'PROPERTY_ID',
		);
		$arPropertyValues = $this->_OrderPropertyValuesDBS->getListArray(
			$arSort, $arFilter, null, null, $arSelect, true
		);

		foreach ($arPropertyValues as &$arPropValue) {
			$arResult[$arPropValue['PROPERTY_ID']] = array(
				'ID' => $arPropValue['PROPERTY_ID'],
				'PROPERTY_ID' => $arPropValue['PROPERTY_ID'],
				'CODE' => $arPropValue['PROPERTY_CODE'],
				'NAME' => $arPropValue['PROPERTY_NAME'],
				'DESCRIPTION' => $arPropValue['PROPERTY_DESCRIPTION'],
				'PROPERTY_TYPE' => $arPropValue['PROPERTY_TYPE'],
				'SORT' => $arPropValue['PROPERTY_SORT'],
				'ORDER_ID' => $arPropValue['ORDER_ID'],
				'VALUE' => $arPropValue['VALUE'],
				'VALUE_ENUM_ID' => $arPropValue['VALUE_ENUM_ID']
			);
		}
		return $arResult;
	}

	/**
	* Задать значения свойств заказа
	* @param array $arProperties
	* @return bool
	*/
	public function setProperties($arProperties) {
		$arExistsPropValueLst = $this->_OrderPropertyValuesDBS->getListArray(
			null,
			array(
				'ORDER_ID' => $this->_arOrder['ID'],
			)
			, null
			, null
			, array(
				'ID', 'ORDER_ID', 'PROPERTY_ID', 'PROPERTY_CODE', 'PROPERTY_TYPE', 'VALUE'
			)
		);
		$bSetPropValuesSuccess = true;
		$bEvenOneUpdateSuccess = false;
		foreach ($arExistsPropValueLst as $valKey => &$arPropVal) {
			$bCreateNewPropValue = false;
			$bSuccess = false;
			$propValue = null;
			if (isset($arProperties[$arPropVal['PROPERTY_ID']])) {
				$propValue = $arProperties[$arPropVal['PROPERTY_ID']];
			} elseif (isset($arProperties[$arPropVal['PROPERTY_CODE']])) {
				$propValue = $arProperties[$arPropVal['PROPERTY_CODE']];
			}
			if ($propValue !== null && $propValue !== "null") {
				$arPropValueFields = array(
					'ORDER_ID' => $arPropVal['ORDER_ID'],
					'PROPERTY_ID' => $arPropVal['PROPERTY_ID'],
					'VALUE' => $propValue
				);
				if (!isset($arPropVal['ID']) || intval($arPropVal['ID']) <= 0) {
					$newPropValueID = $this->_OrderPropertyValuesDBS->add($arPropValueFields);
					$bSuccess = (intval($newPropValueID) > 0) ? true : false;
				} else {
					$bSuccess = $this->_OrderPropertyValuesDBS->update($arPropValueFields);
				}
				if (!$bSuccess) {
					$arError = $this->_OrderPropertyValuesDBS->popLastError('ARRAY');
					$this->addError($arError['TEXT'], $arError['CODE']);
					$bSetPropValuesSuccess = false;
				} else {
					$bEvenOneUpdateSuccess = true;
				}
			}
		}
		unset ($arPropVal);
		if ($bEvenOneUpdateSuccess) {
			$curTime = date('Y-m-d H:i:s');
			$this->_OrderDBS->update(array('ID' => $this->_arOrder['ID'], 'TIMESTAMP_X' => $curTime));
		}
		return $bSetPropValuesSuccess;
	}


	/**
	* Получить текущее значение статуса заказа
	* @return array
	*/
	public function getStatus() {
		if ($this->_bFieldsChanged || null === $this->_arOrderStatus) {
			$bReadSuccess = $this->read($this->_arOrder['ID']);
			if(false === $bReadSuccess) {
				return array();
			}
			$arStatus = $this->_OrderStatusDBS->getByID($this->_arOrder['STATUS_ID']);
			if (is_array($arStatus)) {
				$this->_arOrderStatus = $arStatus;
			}
			return $arStatus;
		} else {
			return $this->_arOrderStatus;
		}
	}

	/**
	* Установить статуса заказа
	* @param $statusVar
	* @return bool
	*/
	public function setStatus($statusVar) {
		$arStatus = array();
		if (is_numeric($statusVar)) {
			$arStatus = $this->_OrderStatusDBS->getByID($statusVar);
		} else {
			$arStatus = $this->_OrderStatusDBS->getListArray(null, array('CODE' => $statusVar));
			if (is_array($arStatus)) {
				$arStatus = $arStatus[0];
			} else {
				$this->addError(GetMessage('OBX_ORDER_CLASS_ERROR_1'), 1);
				return false;
			}
		}
		$arCurrentStatus = $this->getStatus();
		if($statusVar == $arCurrentStatus['ID']) {
			return true;
		}
		$bUpdSuccess = $this->_OrderDBS->update(array(
			'ID' => $this->_arOrder['ID'],
			'STATUS_ID' => $arStatus['ID']
		));
		if( !$bUpdSuccess ) {
			$arError = $this->_OrderDBS->popLastError('ARRAY');
			$this->addError($arError['TEXT'], $arError['CODE']);
			return false;
		}

		$this->_arOrder['STATUS_ID'] = $arStatus['ID'];
		$this->_arOrderStatus = $arStatus;

		$this->_bFieldsChanged = true;
		return true;

	}

	/**
	 * @param bool $bGetCached
	 * @return array
	 */
	public function getItems($bGetCached = false) {
		if(true !== $bGetCached || null === $this->_arItemsCache) {
			$this->_arItemsCache = $this->_BasketItemDBS->getListArray(array('ID' => 'ASC'), array('ORDER_ID' => $this->_arOrder['ID']));
		}
		$this->_productCount = 0;
		$this->_itemsCount = 0;
		$this->_costValue = 0;
		$this->_costTotalValue = 0;
		$this->_discountValue = 0;
		$this->_weightValue = 0;
		foreach($this->_arItemsCache as &$arItem) {
			$this->_productCount++;
			$prodItemsCount = floatval($arItem['QUANTITY']);
			$this->_itemsCount += floatval($prodItemsCount);
			$this->_costValue += floatval($arItem['PRICE_VALUE'])*$prodItemsCount;
			$this->_costTotalValue += floatval($arItem['TOTAL_PRICE_VALUE'])*$prodItemsCount;
			$this->_discountValue += floatval($arItem['DISCOUNT_VALUE'])*$prodItemsCount;
			$this->_weightValue += floatval($arItem['WEIGHT'])*$prodItemsCount;
		}
		return $this->_arItemsCache;
	}

	/**
	* @param $arItems
	* @param bool $bHardListSet
	* @param bool $bQuantityAdd
	* @return bool
	*/
	public function setItems($arItems, $bHardListSet = false, $bQuantityAdd = false) {
		/*
		$arItems = array(
			0 => array(
				'IBLOCK_ID' => $IblockID,
				'PRODUCT_ID' => $PRODUCT_ID,
				'PRODUCT_NAME' => 'STRING',
				'QUANTITY' => 1,
				'WEIGHT' => 2.12,
				'PRICE_ID' => 1,
				'PRICE_VALUE' => 18.50,
				'DISCOUNT_VALUE' => 18.50,
				'VAT_ID' => NULL,
				'VAT_VALUE' => 18.00
				)
			);
		*/
		global $DB;

		//$arEComIBlockList = $this->_EComIBlockDBS->getListArray();

		$arExistsOrderItems = array();
		$arExistsOrderItemsList = $this->_BasketItemDBS->getListArray(
			null,
			array('ORDER_ID' => $this->_arOrder['ID']),
			null, null
		//,array('ID', 'ORDER_ID', 'IBLOCK_ID', 'PRODUCT_ID', 'PRODUCT_NAME', 'QUANTITY')
		);
		$arExistsOrderItems = array();
		if (count($arExistsOrderItemsList) > 0) {
			foreach ($arExistsOrderItemsList as &$arExistsItem) {
				$arExistsOrderItems[$arExistsItem['PRODUCT_ID']] = array(
					'ID' => $arExistsItem['ID'],
					'PRODUCT_ID' => $arExistsItem['PRODUCT_ID'],
					'PRODUCT_NAME' => $arExistsItem['PRODUCT_NAME'],
					'QUANTITY' => $arExistsItem['QUANTITY'],
					'EXISTS_IN_ARGUMENT' => false,
				);
			}
		}
		unset($arExistsOrderItemsList);
		$bSuccess = true;
		foreach ($arItems as $keyItem => $arFields) {
			if (isset($arFields['QUANTITY']) && $arFields['QUANTITY'] <= 0) {
				if (array_key_exists($arFields['PRODUCT_ID'], $arExistsOrderItems)) {
					$this->_BasketItemDBS->delete($arExistsOrderItems[$arFields['PRODUCT_ID']]['ID']);
				}
				continue;
			}
			if (!isset($arFields['PRICE_VALUE'])) {
				$arOptimalPrice = $this->_PriceDBS->getOptimalProductPrice($arFields['PRODUCT_ID'], $this->_arOrder['USER_ID']);
				if (is_array($arOptimalPrice)) {
					$arFields['PRICE_ID'] = $arOptimalPrice['PRICE_ID'];
					$arFields['PRICE_VALUE'] = $arOptimalPrice['TOTAL_VALUE'];
				}
			}
			$arFields['ORDER_ID'] = $this->_arOrder['ID'];
			if (array_key_exists($arFields['PRODUCT_ID'], $arExistsOrderItems)) {
				if (array_key_exists('QUANTITY_ADD', $arFields) && $arFields['QUANTITY_ADD'] == 'Y'
						|| $bQuantityAdd
				) {
					$arFields['QUANTITY'] = $arFields['QUANTITY'] + $arExistsOrderItems[$arFields['PRODUCT_ID']]['QUANTITY'];
					unset($arFields['QUANTITY_ADD']);
				}
				$bSuccess = $this->_BasketItemDBS->update($arFields);
				if (!$bSuccess) {
					$this->_BasketItemDBS->popLastError('ARRAY');
				}
				$arExistsOrderItems[$arFields['PRODUCT_ID']]['EXISTS_IN_ARGUMENT'] = true;
			} else {
				$bCorrect = false;
				$arFields["PRODUCT_ID"] = intval($arFields["PRODUCT_ID"]);
				if ($arFields["PRODUCT_ID"] > 0) {
					// стремное решение, надо добавить больше возможности в DBSimple
					// TODO: Find a better solution
					$sQuery = "SELECT b.IBLOCK_ID FROM b_iblock_element as a
					LEFT JOIN obx_ecom_iblock as b on(a.IBLOCK_ID = b.IBLOCK_ID)
					WHERE a.ID=" . $arFields["PRODUCT_ID"];
					$res = $DB->Query($sQuery);
					$arIblock = $res->Fetch();
					if (is_array($arIblock) && !empty($arIblock) && !empty($arIblock["IBLOCK_ID"])) {
						$bCorrect = true;
					}
					// ^^^
				}
				if ($bCorrect) {
					$newOrderItemID = $this->_BasketItemDBS->add($arFields);
					$bSuccess = ($newOrderItemID > 0) ? true : false;
				} else {
					$bSuccess = false;
					$this->addError(GetMessage('OBX_ORDER_CLASS_ERROR_NOT_ECONOM_IBLOCK'));
				}
			}
			if (!$bSuccess) {
				$arErrorsList = $this->_BasketItemDBS->getErrors();
				$this->_BasketItemDBS->getMessagePool()->addError(GetMessage('OBX_ORDER_CLASS_ERROR_2') . ': ' . implode("<br />\n", $arErrorsList), 2);
			}
		}

		if ($bHardListSet) {
			foreach ($arExistsOrderItems as &$arExistsItem) {
				if ($arExistsItem['EXISTS_IN_ARGUMENT'] == false) {
					$this->_BasketItemDBS->delete($arExistsItem['ID']);
				}
			}
		}
		$arEr = $this->getMessagePool()->getErrors();
		if (!empty($arEr)) {
			$bSuccess = false;
		}
		$this->_bFieldsChanged = true;
		// Сбратываем кеш summary данных
		$this->_productCount = null;
		return $bSuccess;
	}

	public function getSummary($bFormatValues = false, $bGetCached = false) {
		if($this->_productCount === null) {
			// Получаем summary данные
			$this->getItems($bGetCached);
		}
		if(true === $bFormatValues) {
			return array(
				'PRODUCT_COUNT' => $this->_productCount,
				'ITEMS_COUNT' => $this->_itemsCount,
				'COST' => $this->_costValue,
				'COST_FORMATTED' => CurrencyFormat::formatPrice($this->_costValue, $this->_arOrder['CURRENCY']),
				'TOTAL_COST' => $this->_costTotalValue,
				'TOTAL_COST_FORMATTED' => CurrencyFormat::formatPrice($this->_costTotalValue, $this->_arOrder['CURRENCY']),
				'DISCOUNT' => $this->_discountValue,
				'DISCOUNT_FORMATTED' => CurrencyFormat::formatPrice($this->_discountValue, $this->_arOrder['CURRENCY']),
				'WEIGHT' => $this->_weightValue
			);
		}
		return array(
			'PRODUCT_COUNT' => $this->_productCount,
			'ITEMS_COUNT' => $this->_itemsCount,
			'COST' => $this->_costValue,
			'TOTAL_COST' => $this->_costTotalValue,
			'DISCOUNT' => $this->_discountValue,
			'WEIGHT' => $this->_weightValue
		);
	}

	public function callFinishEvent() {
		$this->getItems(false);
		$arEventList = GetModuleEvents(self::EVENT_MODULE, self::EVENT_FINISH, true);
		$arParams = array(&$this);
		$bSuccess = true;
		foreach($arEventList as &$arEvent) {
			$bSuccess = (ExecuteModuleEventEx($arEvent, $arParams)!==false) && $bSuccess;
		}
		return $bSuccess;
	}
}
