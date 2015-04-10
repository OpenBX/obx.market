<?php
/***********************************************
 ** @product OBX:Market Bitrix Module         **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Market;

use OBX\Core\DBSimple\Entity;


IncludeModuleLangFile(__FILE__);

class CurrencyDBS extends Entity {
	protected $_entityModuleID = 'obx.market';
	protected $_entityID = 'Currency';
	protected $_arTableList = array(
		'C' => 'obx_currency'
	);
	protected $_mainTable = 'C';
	protected $_arTableLinks = array();
	protected $_arTableFields = array(
		'CURRENCY'		=> array('C' => 'CURRENCY'),
		'SORT'			=> array('C' => 'SORT'),
		'COURSE'		=> array('C' => 'COURSE'),
		'RATE'			=> array('C' => 'RATE'),
		'IS_DEFAULT'	=> array('C' => 'IS_DEFAULT')
	);
	protected $_mainTablePrimaryKey = 'CURRENCY';
	protected $_mainTableAutoIncrement = null;

	protected $_arTableFieldsDefault = array(
		'SORT' => '100',
		'COURSE' => '1',
		'RATE' => '1'
	);

	protected $_bSetJustUpdatedCurrencyDefault = null;
	protected $_bSetJustCreatedCurrencyDefault = null;

	public function __construct() {
		$this->_arTableFieldsCheck = array(
			'CURRENCY'		=> self::FLD_T_NO_CHECK | self::FLD_NOT_NULL | self::FLD_NOT_ZERO,
			'SORT'			=> self::FLD_T_INT
								| self::FLD_NOT_NULL
								| self::FLD_NOT_ZERO
								| self::FLD_UNSIGNED
								| self::FLD_DEFAULT,

			'COURSE'		=> self::FLD_T_FLOAT,
			'RATE'			=> self::FLD_T_FLOAT,
			'IS_DEFAULT'	=> self::FLD_T_BCHAR,
		);
		$this->_arDBSimpleLangMessages = array(
			'REQ_FLD_CURRENCY' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_MARKET_CURRENCY_ERROR_1'),
				'CODE' => 1
			),
			'DUP_PK' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_MARKET_CURRENCY_ERROR_2'),
				'CODE' => 2
			),
			'NOTHING_TO_UPDATE' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_MARKET_CURRENCY_ERROR_3'),
				'CODE' => 3
			),
			'NOTHING_TO_DELETE' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_MARKET_CURRENCY_ERROR_4'),
				'CODE' => 4
			),
		);
		$this->_getEntityEvents();
	}

	public function __check_CURRENCY(&$value, &$arCheckResult = null) {
		if( !preg_match('~^[a-zA-Z\_][a-z0-9A-Z\_]{0,2}$~', $value) ) {
			return false;
		}
		return true;
	}

	protected function _onBeforeAdd(&$arFields, &$arCheckResult) {
		// +++ automatic setDefault() in add()
		if($arFields['IS_DEFAULT'] == 'Y') {
			$arFields['IS_DEFAULT'] = 'N';
			$this->_bSetJustCreatedCurrencyDefault = $arFields['CURRENCY'];
		}
		// ^^^ automatic setDefault() in add()
		return true;
	}

	protected function _onAfterAdd(&$arFields) {
		// +++ automatic setDefault() in add()
		if($this->_bSetJustCreatedCurrencyDefault != null) {
			$this->setDefault($this->_bSetJustCreatedCurrencyDefault);
		}
		$this->_bSetJustCreatedCurrencyDefault = null;
		// ^^^ automatic setDefault() in add()
		return true;
	}

	protected function _onBeforeUpdate(&$arFields, &$arCheckResult) {
		// +++ automatic setDefault() in update()
		if( $arFields['IS_DEFAULT'] == 'Y' ) {
			$this->_bSetJustUpdatedCurrencyDefault = $arFields['CURRENCY'];
		}
		// ^^^ automatic setDefault() in update()
		$bSuccess = parent::_onBeforeUpdate($arFields, $arCheckResult);
		return $bSuccess;
	}

	protected function _onBeforeExecUpdate(&$arFields, &$arCheckResult) {
		// +++ automatic setDefault() in update()
		if($this->_bSetJustUpdatedCurrencyDefault != null && $arCheckResult['__EXIST_ROW']['IS_DEFAULT'] == 'Y') {
			$this->_bSetJustUpdatedCurrencyDefault = null;
		}
		// ^^^ automatic setDefault() in update()
		$bSuccess = parent::_onBeforeExecUpdate($arFields, $arCheckResult);
		return $bSuccess;
	}

	protected function _onAfterUpdate(&$arFields) {
		// +++ automatic setDefault() in update()
		if($this->_bSetJustUpdatedCurrencyDefault != null) {
			$this->setDefault($this->_bSetJustUpdatedCurrencyDefault);
		}
			// Clear currency info cache
			CurrencyInfo::clearInstance($this->_bSetJustUpdatedCurrencyDefault);
		$this->_bSetJustUpdatedCurrencyDefault = null;
		// ^^^ automatic setDefault() in update()
		$bSuccess = parent::_onAfterUpdate($arFields);
		return $bSuccess;
	}


	public function setDefault($currency) {
		global $DB;
		if( !preg_match('~^[a-zA-Z\_][a-z0-9A-Z\_]{0,2}$~', $currency) ) {
			return false;
		}
		$rsExists = $DB->Query(
			'SELECT `CURRENCY`, `IS_DEFAULT` FROM `'.$this->_arTableList['C'].'`'
				.' WHERE `CURRENCY`=\''.$currency.'\'',
			false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		if( ! ($arExists = $rsExists->Fetch()) ) {
			return false;
		}
		$DB->Query('UPDATE `'.$this->_arTableList['C'].'` SET IS_DEFAULT=\'N\' WHERE IS_DEFAULT=\'Y\'');
		$DB->Query('UPDATE `'.$this->_arTableList['C'].'` SET IS_DEFAULT=\'Y\' WHERE `CURRENCY`=\''.$arExists['CURRENCY'].'\'');
		return true;
	}

	public function getDefault() {
		$arDefault = $this->getDefaultArray();
		if( array_key_exists('IS_DEFAULT', $arDefault) ) {
			return $arDefault['CURRENCY'];
		}
		else {
			return null;
		}
	}
	public function getDefaultArray() {
		global $DB;
		$rsDefault = parent::getList(null, array(
			'IS_DEFAULT' => 'Y'
		));
		if( ($arDefault = $rsDefault->Fetch()) ) {
			return $arDefault;
		}
		$rsDefault = parent::getList(array('SORT' => 'ASC','ID' => 'ASC'));
		if( ($arDefault = $rsDefault->Fetch()) ) {
			$bSuccess = $this->setDefault($arDefault['CURRENCY']);
			if($bSuccess) {
				$arDefault['IS_DEFAILT'] = 'Y';
				return $arDefault;
			}
			else {
				return array();
			}
		}
		$this->addError(GetMessage('OBX_MARKET_CURRENCY_ERROR_5'), 5);
		return array();
	}
}

