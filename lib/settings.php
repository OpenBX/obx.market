<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */

namespace OBX\Market;

use OBX\Core\MessagePoolDecorator;

IncludeModuleLangFile(__FILE__);

abstract class Settings extends MessagePoolDecorator{

	final protected function __construct() {
	}

	final protected function __clone() {
	}

	static protected $_arInstances = array();
	static protected $_arLangList = null;

	/**
	 * @param String $tabCode Постфикс имени класса
	 * @return Settings
	 */
	final static public function getController($tabCode) {
		if (!preg_match('~^[a-zA-Z\_][a-zA-Z0-9\_]*$~', $tabCode)) {
			return null;
		}
		if (!class_exists('OBX\Market\Settings_' . $tabCode)) {
			return null;
		}

		if (empty(self::$_arInstances[$tabCode])) {
			$className = 'OBX\Market\Settings_' . $tabCode;
			$TabContentObject = new $className;
			if ($TabContentObject instanceof self) {
				self::$_arInstances[$tabCode] = $TabContentObject;
			}
		}
		return self::$_arInstances[$tabCode];
	}


	/**
	 * @return Array
	 */
	static public function getLangList() {
		if (self::$_arLangList == null) {
			$rsLang = \CLanguage::GetList($by = "sort", $sort = "asc", $arLangFilter = array("ACTIVE" => "Y"));
			$arLangList = array();
			while ($arLang = $rsLang->Fetch()) {
				$arLangList[$arLang["ID"]] = $arLang;
			}
			if (!empty($arLangList)) {
				self::$_arLangList = $arLangList;
			}
		}
		return self::$_arLangList;
	}


	protected $listTableColumns = 1;

	public function showMessages($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arMessagesList = $this->getNotices();
		if (count($arMessagesList) > 0) {
			?>
		<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arMessagesList as $arMessage) {
					ShowNote($arMessage["TEXT"]);
				}
				?></td>
		</tr><?
		}
	}

	public function showWarnings($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arWarningsList = $this->getWarnings();
		if (count($arWarningsList) > 0) {
			?>
		<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arWarningsList as $arWarning) {
					ShowNote($arWarning["TEXT"]);
				}
				?></td>
		</tr><?
		}
	}

	public function showErrors($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arErrorsList = $this->getErrors();
		if (count($arErrorsList) > 0) {
			?>
		<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arErrorsList as $arError) {
					ShowError($arError["TEXT"]);
				}
				?></td>
		</tr><?
		}
	}

	abstract public function showTabContent();

	public function showTabScripts() {

	}

	abstract public function saveTabData();
}
