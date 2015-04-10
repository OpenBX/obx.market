<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */

namespace OBX\Market;

use OBX\Market\Currency as OBX_Currency;
use OBX\Market\CurrencyFormat as OBX_CurrencyFormat;

IncludeModuleLangFile(__FILE__);

class Settings_Currency extends Settings {
	protected $listTableColumns = 10;

	public function showTabContent() {
		$arCurrencyList = OBX_Currency::getListArray();
		$arCurrencyFormatList = OBX_CurrencyFormat::getListGroupedByLang(array(
			"CURRENCY_SORT" => "ASC",
			"CURRENCY" => "ASC",
			"LANGUAGE_SORT" => "ASC"
		));
		$arLangList = self::getLangList();
		$countLangList = count($arLangList);
		?>
		<tr>
		<td>
			<table class="internal" style="width:100%">
				<tr class="heading">
					<td class="field-name"></td>
					<td><span class="require">*</span>&nbsp;<?=GetMessage("OBX_SETT_CURRENCY_F_CODE")?></td>
					<Td><?=GetMessage("OBX_SETT_CURRENCY_F_SORT")?></Td>
					<td><?=GetMessage("OBX_SETT_CURRENCY_F_IS_DEFAULT")?></td>
					<td><?=GetMessage("OBX_SETT_CURRENCY_F_LANG")?></td>
					<td><span class="require">*</span>&nbsp;<?=GetMessage("OBX_SETT_CURRENCY_F_NAME")?></td>
					<td><span class="require">*</span>&nbsp;<?=GetMessage("OBX_SETT_CURRENCY_F_FORMAT")?></td>
					<td><?=GetMessage("OBX_SETT_CURRENCY_F_THOUS_SEP")?></td>
					<td><?=GetMessage("OBX_SETT_CURRENCY_F_DEC_POINT")?></td>
					<td><?=GetMessage("OBX_SETT_CURRENCY_F_PRECISION")?></td>
					<td><?=GetMessage("OBX_SETT_CURRENCY_BTN_DELETE")?></label></td>
				</tr>
				<? if (count($arCurrencyFormatList) > 0): ?>
					<? foreach ($arCurrencyFormatList as $currency => $arCurrency): ?>
						<tr>
						<td class="field-name"></td>
						<td rowspan="<?=$countLangList?>" class="currency-code center">
							<?=$currency?>
						</td>
						<td rowspan="<?=$countLangList?>" class="center">
							<input type="text" name="obx_currency_update[<?=$currency?>][sort]" size="4"
								   placeholder="<?=GetMessage("OBX_SETT_CURRENCY_F_SORT")?>" value="<?=$arCurrency["SORT"]?>"/>
						</td>
						<td rowspan="<?=($countLangList)?>" class="center"><input type="radio" name="obx_currency_default"
																				  value="<?=$currency?>"<?if ($arCurrency["IS_DEFAULT"] == "Y"): ?>
								checked="checked"<? endif?> /></td>
						<?$iLang = 0;
						foreach ($arCurrency["LANG"] as $languageID => &$arFormat):
							$iLang++;
							?>
							<? if ($iLang > 1): ?>
							<tr>
							<td class="field-name"></td>
						<? endif ?>
							<td><?=$arFormat["LANGUAGE_NAME"]?></td>
							<td>
								<input type="text" name="obx_currency_update[<?=$currency?>][<?=$languageID?>][name]"
									   value="<?=$arFormat["NAME"]?>"
									   placeholder="<?=GetMessage("OBX_SETT_CURRENCY_F_NAME")?>"/>
							</td>
							<td>
								<input type="text" name="obx_currency_update[<?=$currency?>][<?=$languageID?>][format]"
									   value="<?=$arFormat["FORMAT"]?>"
									   placeholder="<?=GetMessage("OBX_SETT_CURRENCY_F_FORMAT")?>"/>
							</td>
							<td>
								<input type="text" class="thous_sep"
									   name="obx_currency_update[<?=$currency?>][<?=$languageID?>][thousand_sep]"
									   value="<?=$arFormat["THOUSANDS_SEP"]?>"/>
								<label>
									<input type="checkbox" class="thous_sep_space"
										   name="obx_currency_update[<?=$currency?>][<?=$languageID?>][thousands_sep_space]"<?if ($arFormat["THOUSANDS_SEP"] == " "): ?>
										checked="checked"<? endif?> />
									<?=GetMessage("OBX_SETT_SPACE")?>
								</label>
							</td>
							<td>
								<input type="text" class="dec_point"
									   name="obx_currency_update[<?=$currency?>][<?=$languageID?>][dec_point]"
									   value="<?=$arFormat["DEC_POINT"]?>"/>
							</td>
							<td>
								<select name="obx_currency_update[<?=$currency?>][<?=$languageID?>][dec_precision]">
									<?for ($precision = 0; $precision <= 5; $precision++): ?>
										<option value="<?=$precision?>"<?if ($precision == $arFormat["DEC_PRECISION"]): ?>
											selected<? endif?>><?=$precision?></option>
									<? endfor?>
								</select>
							</td>
							<? if ($iLang == 1): ?>
							<td rowspan="<?=$countLangList?>" class="remove_currency_col center">
								<input type="checkbox" name="obx_currency_delete[<?=$currency?>]" value="<?=$currency?>"/>
							</td>
						<? endif ?>
							<? if ($iLang < $countLangList): ?>
							</tr>
						<? endif ?>
						<? endforeach ?>
						</tr>
					<? endforeach ?>
				<? endif; ?>

				<tr class="replace">
					<td colspan="<?=$this->listTableColumns?>"></td>
				</tr>
				<tr>
					<td class="field-name"></td>
					<td colspan="10"><input class="add_new_item" type="button"
											value="<?=GetMessage("OBX_SETT_CURRENCY_BTN_ADD_ITEM")?>"/></td>
				</tr>
				<tr>
					<td colspan="<?=$this->listTableColumns?>">
						<input type="button" class="adm-btn-save" id="obx_currency_btn_save" value="<?=GetMessage("OBX_SETT_CURRENCY_BTN_SAVE")?>"/>
						<input type="button" id="obx_currency_btn_cancel"
							   value="<?=GetMessage("OBX_SETT_CURRENCY_BTN_CANCEL")?>"/>
					</td>
				</tr>
			</table>
		</td>
		</tr><?
	}

	public function showTabScripts() {
		$arLangList = self::getLangList();
		$countLangList = count($arLangList);?>
		<?=
		'<script type="text/x-custom-template" id="obx_market_currency_row_tmpl">'
		; ?>
		<tr data-new-row="$index">
			<td rowspan="<?=($countLangList + 1)?>" class="field-name"></td>
			<td rowspan="<?=($countLangList + 1)?>" class="remove_new_item center">
				<input type="text" name="obx_currency_new[$index][currency]" size="3"
					   placeholder="<?=GetMessage("OBX_SETT_CURRENCY_F_CODE_")?>" value="$currency"/>
			</td>
			<td rowspan="<?=$countLangList + 1?>" class="center">
				<input type="text" name="obx_currency_new[$index][sort]" size="4"
					   placeholder="<?=GetMessage("OBX_SETT_CURRENCY_F_SORT")?>" value="$sort"/>
			</td>
			<td rowspan="<?=($countLangList + 1)?>" class="center"><input type="radio" name="obx_currency_default" value="new_$index"/>
			</td>
		</tr>
		<?$iLang = 0;
		foreach ($arLangList as $arLang): ?>
			<?$iLang++;?>
			<tr data-new-row="$index">
				<td><?=$arLang["NAME"]?></td>
				<td>
					<input type="text" name="obx_currency_new[$index][<?=$arLang["ID"]?>][name]"
						   placeholder="<?=GetMessage("OBX_SETT_CURRENCY_F_NAME")?>" value="$name"/>
				</td>
				<td>
					<input type="text" name="obx_currency_new[$index][<?=$arLang["ID"]?>][format]"
						   placeholder="<?=GetMessage("OBX_SETT_CURRENCY_F_FORMAT")?>" value="$format"/>
				</td>
				<td>
					<input type="text" class="thous_sep"
						   name="obx_currency_new[$index][<?=$arLang["ID"]?>][thousand_sep]" value="$thousSep"/>
					<label>
						<input type="checkbox" class="thous_sep_space"
							   name="obx_currency_new[$index][<?=$arLang["ID"]?>][thousands_sep_space]" $thousSpaceSepChecked />
						<?=GetMessage("OBX_SETT_SPACE")?>
					</label>
				</td>
				<td>
					<input type="text" class="dec_point"
						   name="obx_currency_new[$index][<?=$arLang["ID"]?>][dec_point]"
						   value="$decPoint"/>
				</td>
				<td>
					<select name="obx_currency_new[$index][<?=$arLang["ID"]?>][dec_precision]">
						<?for ($precision = 0; $precision <= 5; $precision++): ?>
							<option value="<?=$precision?>" $decimalPrecisionSelected_<?=$precision?>><?=$precision?></option>
						<? endfor?>
					</select>
				</td>
				<?if($iLang ==1):?>
					<td rowspan="<?=count($arLangList)?>" class="remove_new_item center">
						<input data-new-row="$index" type="button" value="<?=GetMessage("OBX_SETT_PRICE_BTN_DELETE")?>"/>
					</td>
				<?endif?>
			</tr>
		<? endforeach ?>
		<tr class="replace">
			<td colspan="<?=$this->listTableColumns?>">
			</td>
		</tr>
		<?= '</script>'?>
	<?
	}

	public function saveTabData() {
		$this->updateCurrencyList();
		$this->deleteCurrencyList();
		$this->addNewCurrencyList();
	}

	protected function deleteCurrencyList() {
		if (!is_array($_REQUEST["obx_currency_delete"])) {
			return false;
		}
		$arCurrencyDeleteID = $_REQUEST["obx_currency_delete"];
		$arDeleteSuccessList = array();
		foreach ($arCurrencyDeleteID as $currency => $delText) {
			$bDelFormatSuccess = OBX_CurrencyFormat::deleteByFilter(array("CURRENCY" => $currency));
			$bDelCurrencySuccess = OBX_Currency::delete($currency);
			if (!$bDelFormatSuccess) {
				$this->addError(OBX_CurrencyFormat::popLastError());
			}
			if (!$bDelCurrencySuccess) {
				$this->addError(OBX_Currency::popLastError());
			}
			if ($bDelFormatSuccess && $bDelCurrencySuccess) {
				if (!in_array($currency, $arDeleteSuccessList)) {
					$arDeleteSuccessList[] = $currency;
				}
			}
		}
		if (count($arDeleteSuccessList) > 0) {
			$this->addNotice(GetMessage("OBX_SETT_CURRENCY_MESSAGE_3", array(
				"#CURRENCY_LIST#" => implode(', ', $arDeleteSuccessList)
			)), 3);
			return true;
		}
		return false;
	}

	protected function updateCurrencyList() {
		if (!is_array($_REQUEST["obx_currency_update"])) {
			return false;
		}
		$arCurrencyDeleteID = $_REQUEST["obx_currency_delete"];
		$arCurrencyUpdateID = $_REQUEST["obx_currency_update"];

		$arLangList = self::getLangList();
		//$countLangList = count($arLangList);

		$arUpdateSuccessCurrencyList = array();

		foreach ($arCurrencyUpdateID as $currecny => &$arCurrencyDataRaw) {
			$arCurrencyExistsList = OBX_Currency::getListArray($currecny);
			if (empty($arCurrencyExistsList)) {
				$this->addError(GetMessage("OBX_SETT_CURRENCY_ERROR_2", array("#CURRENCY#" => htmlspecialcharsEx($currecny))));
				continue;
			}
			$arExistCurrency = $arCurrencyExistsList[0];
			if (!empty($arCurrencyDataRaw) && empty($arCurrencyDeleteID[$currecny])) {
				$arUpdateCurrencyFields = array("CURRENCY" => $currecny);
				$bCurrencyUpdateSuccess = false;
				if (isset($arCurrencyDataRaw["sort"]) && $arCurrencyDataRaw["sort"] != $arExistCurrency["SORT"]) {
					$arUpdateCurrencyFields["SORT"] = intval($arCurrencyDataRaw["sort"]);
				}
				if (isset($arCurrencyDataRaw["rate"]) && $arCurrencyDataRaw["rate"] != $arExistCurrency["RATE"]) {
					$arUpdateCurrencyFields["RATE"] = intval($arCurrencyDataRaw["rate"]);
				}
				if (isset($arCurrencyDataRaw["cource"]) && $arCurrencyDataRaw["cource"] != $arExistCurrency["COURSE"]) {
					$arUpdateCurrencyFields["COURSE"] = intval($arCurrencyDataRaw["cource"]);
				}

				if (count($arUpdateCurrencyFields) > 1) {
					$bCurrencyUpdateSuccess = OBX_Currency::update($arUpdateCurrencyFields);
					if (!$bCurrencyUpdateSuccess) {
						$arCurrencyLastError = OBX_Currency::popLastError('ARRAY');
						$this->addError($arCurrencyLastError["TEXT"], $arCurrencyLastError["CODE"]);
					} else {
						if (!in_array($currecny, $arUpdateSuccessCurrencyList)) {
							$arUpdateSuccessCurrencyList[] = $currecny;
						}
					}
				}

				foreach ($arLangList as &$arLang) {
					if (!array_key_exists($arLang["LID"], $arCurrencyDataRaw)) {
						continue;
					}
					$bFormatUpdateSuccess = false;
					$arExistsFormatList = OBX_CurrencyFormat::getListArray(null, array(
						"CURRENCY" => $currecny,
						"LANGUAGE_ID" => $arLang["LID"]
					));
					if (empty($arExistsFormatList)) {
						continue;
					}
					$arExistsFormat = $arExistsFormatList[0];
					$arFormatRaw = $arCurrencyDataRaw[$arLang["LID"]];
					$arUpdateFormatFields = array("ID" => $arExistsFormat["ID"]);
					if (isset($arFormatRaw["name"]) && $arFormatRaw["name"] != $arExistsFormat["NAME"]) {
						$arUpdateFormatFields["NAME"] = trim($arFormatRaw["name"]);
					}
					if (isset($arFormatRaw["format"]) && $arFormatRaw["format"] != $arExistsFormat["FORMAT"]) {
						$arUpdateFormatFields["FORMAT"] = $arFormatRaw["format"];
					}
					if (isset($arFormatRaw["thousand_sep"]) && $arFormatRaw["thousand_sep"] != $arExistsFormat["THOUSANDS_SEP"]) {
						$arUpdateFormatFields["THOUSANDS_SEP"] = trim($arFormatRaw["thousand_sep"]);
					}
					if (isset($arFormatRaw["dec_precision"]) && $arFormatRaw["dec_precision"] != $arExistsFormat["DEC_PRECISION"]) {
						$arUpdateFormatFields["DEC_PRECISION"] = intval($arFormatRaw["dec_precision"]);
					}
					if (isset($arFormatRaw["dec_point"]) && $arFormatRaw["dec_point"] != $arExistsFormat["DEC_POINT"]) {
						$arUpdateFormatFields["DEC_POINT"] = trim($arFormatRaw["dec_point"]);
					}
					if (count($arUpdateFormatFields) > 1) {
						if (empty($arUpdateFormatFields["ID"])) {
							$arNewCurrencyFormatOnUpdate = $arUpdateFormatFields;
							$arNewCurrencyFormatOnUpdate["CURRENCY"] = $currecny;
							$arNewCurrencyFormatOnUpdate["LANGUAGE_ID"] = $arLang["LID"];
							if (!isset($arUpdateFormatFields["NAME"])) {
								$arNewCurrencyFormatOnUpdate["NAME"] = '';
							}
							if (!isset($arUpdateFormatFields["FORMAT"])) {
								$arNewCurrencyFormatOnUpdate["FORMAT"] = '#';
							}
							if (!isset($arUpdateFormatFields["THOUSANDS_SEP"])) {
								$arNewCurrencyFormatOnUpdate["THOUSANDS_SEP"] = " ";
							}
							if (!isset($arUpdateFormatFields["DEC_PRECISION"])) {
								$arNewCurrencyFormatOnUpdate["DEC_PRECISION"] = 2;
							}
							if (!isset($arUpdateFormatFields["DEC_POINT"])) {
								$arNewCurrencyFormatOnUpdate["DEC_POINT"] = '.';
							}
							$bFormatAddOnCurrencyUpdateSuccess = OBX_CurrencyFormat::add($arNewCurrencyFormatOnUpdate);
							if (!$bFormatAddOnCurrencyUpdateSuccess) {
								$arAddOnUpdateError = OBX_CurrencyFormat::popLastError('ALL');
								$this->addError($arAddOnUpdateError["TEXT"], $arAddOnUpdateError["CODE"]);
							}
						} else {
							$bFormatUpdateSuccess = OBX_CurrencyFormat::update($arUpdateFormatFields);
							if (!$bFormatUpdateSuccess) {
								$arFormatLastError = OBX_CurrencyFormat::popLastError('ARRAY');
								$this->addError($arFormatLastError["TEXT"], $arFormatLastError["CODE"]);
							} else {
								if (!in_array($currecny, $arUpdateSuccessCurrencyList)) {
									$arUpdateSuccessCurrencyList[] = $currecny;
								}
							}
						}
					}
				}
			}
		}
		if (isset($_REQUEST["obx_currency_default"])) {
			if (strpos($_REQUEST["obx_currency_default"], 'new_') === false) {
				$defCurrency = substr($_REQUEST["obx_currency_default"], 0, 3);
				if (!array_key_exists($defCurrency, $arCurrencyDeleteID)) {
					$bIsAlreadyDefault = false;
					if (OBX_Currency::setDefault($defCurrency, $bIsAlreadyDefault)) {
						if (!$bIsAlreadyDefault) {
							if (!in_array($defCurrency, $arUpdateSuccessCurrencyList)) {
								$arUpdateSuccessCurrencyList[] = $defCurrency;
							}
						}
					}
				}
			}
		}
		if (count($arUpdateSuccessCurrencyList) > 0) {
			$this->addNotice(GetMessage("OBX_SETT_CURRENCY_MESSAGE_2", array(
				"#CURRENCY_LIST#" => implode(', ', $arUpdateSuccessCurrencyList)
			)), 2);
		}
	}

	protected function addNewCurrencyList() {
		if (!is_array($_REQUEST["obx_currency_new"])) {
			return false;
		}
		$arLangList = self::getLangList();
		$countLangList = count($arLangList);
		$arNewCurrenciesRaw = $_REQUEST["obx_currency_new"];
		$strNewSuccessIDList = '';

		$newDefaultIndex = false;
		if (isset($_REQUEST["obx_currency_default"])) {
			if (strpos($_REQUEST["obx_currency_default"], 'new_') !== false) {
				$newDefaultIndex = substr($_REQUEST["obx_currency_default"], 4);
				$newDefaultIndex = intval($newDefaultIndex);
			}
		}

		foreach ($arNewCurrenciesRaw as $newCurrencyIndex => &$arNewCurrencyRaw) {
			$newCurrencyIndex = intval($newCurrencyIndex);
			$arNewCurrency = array(
				"SORT" => intval($arNewCurrencyRaw["sort"]),
				"CURRENCY" => substr($arNewCurrencyRaw["currency"], 0, 3),
				"COURSE" => floatval($arNewCurrencyRaw["cource"]),
				"RATE" => floatval($arNewCurrencyRaw["rate"])
			);
			if (!preg_match('~^[a-zA-Z0-9]{1,3}$~', trim($arNewCurrency["CURRENCY"]))) {
				self::addError(GetMessage("OBX_SETT_CURRENCY_ERROR_1"), 1);
				continue;
			}
			$arNewCurrency["CURRENCY"] = trim($arNewCurrency["CURRENCY"]);
			$bAddCurrencySuccess = false;
			$countAllFormatsIsSuccess = 0;
			foreach ($arLangList as $langID => &$arLang) {
				if (!array_key_exists($arLang["LID"], $arNewCurrencyRaw)) {
					$this->addError(GetMessage("OBX_SETT_CURRENCY_ERROR_3"), 3);
					continue;
				}
				if (strlen(trim($arNewCurrencyRaw[$arLang["LID"]]["name"])) < 1) {
					$this->addError(GetMessage("OBX_SETT_CURRENCY_ERROR_4", array(
						"#CURRENCY#" => $arNewCurrency["CURRENCY"],
						"#LANG_NAME#" => $arLang["NAME"]
					)), 4);
					continue;
				}
				if (!$bAddCurrencySuccess) {
					$bAddCurrencySuccess = OBX_Currency::add($arNewCurrency);
					if (!$bAddCurrencySuccess) {
						$this->addError(OBX_Currency::popLastError());
						continue;
					}
				}

				$arNewCurrencyFormat = array(
					"CURRENCY" => $arNewCurrency["CURRENCY"],
					"LANGUAGE_ID" => $arLang["LID"],
					"NAME" => trim($arNewCurrencyRaw[$arLang["LID"]]["name"]),
					"FORMAT" => trim($arNewCurrencyRaw[$arLang["LID"]]["format"])
				);
				if (!empty($arNewCurrencyRaw[$arLang["LID"]]["thousand_sep"])) {
					$arNewCurrencyFormat["THOUSANDS_SEP"] = $arNewCurrencyRaw[$arLang["LID"]]["thousand_sep"];
				}
				if (!empty($arNewCurrencyRaw[$arLang["LID"]]["dec_precision"])) {
					$arNewCurrencyFormat["DEC_PRECISION"] = $arNewCurrencyRaw[$arLang["LID"]]["dec_precision"];
				}

				$newCurrencyFormatID = OBX_CurrencyFormat::add($arNewCurrencyFormat);
				if (!$newCurrencyFormatID) {
					$arErrorNewCurrencyFormat = OBX_CurrencyFormat::popLastError('ALL');
					$this->addError($arErrorNewCurrencyFormat["TEXT"], $arErrorNewCurrencyFormat["CODE"]);
					continue;
				}
				if ($newDefaultIndex !== false && $newCurrencyIndex == $newDefaultIndex) {
					OBX_Currency::setDefault($arNewCurrency["CURRENCY"]);
				}
				$countAllFormatsIsSuccess++;
			}
			if ($countAllFormatsIsSuccess == $countLangList) {
				$strNewSuccessIDList .= ((strlen($strNewSuccessIDList) > 0) ? ", " : "") . $arNewCurrency["CURRENCY"];
			} else {
				OBX_CurrencyFormat::deleteByFilter(array("CURRENCY" => $arNewCurrency["CURRENCY"]));
				OBX_Currency::delete($arNewCurrency["CURRENCY"]);
			}
		}
		if (strlen($strNewSuccessIDList) > 0) {
			$this->addNotice(GetMessage("OBX_SETT_CURRENCY_MESSAGE_1", array(
				"#CURRENCY_LIST#" => $strNewSuccessIDList
			)), 1);
		}
	}
}
