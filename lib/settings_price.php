<?php
/***********************************************
 ** @product OBX:Market Bitrix Module         **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Market;

IncludeModuleLangFile(__FILE__);

class Settings_Price extends Settings {

	protected $listTableColumns = 7;

	public function showTabContent() {
		$arPriceList = Price::getListArray();
		$arCurrencyList = CurrencyFormat::getListArray(null, array("LANGUAGE_ID" => LANGUAGE_ID));?>
		<tr>
			<td>
				<table class="internal" style="width:100%">
					<tr class="heading">
						<td class="field-name"></td>
						<td style="width: 25px;">ID</td>
						<td><?=GetMessage("OBX_SETT_PRICE_F_NAME")?></td>
						<td><?=GetMessage("OBX_SETT_PRICE_F_CODE")?></td>
						<td><?=GetMessage("OBX_SETT_PRICE_F_SORT")?></td>
						<td><?=GetMessage("OBX_SETT_PRICE_F_CURRENCY")?></td>
						<td><?=GetMessage("OBX_SETT_PRICE_F_GROUPS")?></td>
						<td><?=GetMessage("OBX_SETT_PRICE_BTN_DELETE")?></td>
					</tr>
					<? foreach ($arPriceList as &$arPrice): ?>
						<tr>
							<td class="field-name"></td>
							<td><?=$arPrice["ID"]?><input type="hidden" name="obx_price_update[]" value="<?=$arPrice["ID"]?>"/></td>
							<td>
								<input type="text" name="obx_price[<?=$arPrice["ID"]?>][name]" value="<?=$arPrice["NAME"]?>"
									   placeholder="<?=GetMessage("OBX_SETT_PRICE_F_NAME")?>"/>
							</td>
							<td>
								<input type="text" name="obx_price[<?=$arPrice["ID"]?>][code]" value="<?=$arPrice["CODE"]?>"
									   placeholder="<?=GetMessage("OBX_SETT_PRICE_F_CODE")?>"/>
							</td>
							<td>
								<input type="text" name="obx_price[<?=$arPrice["ID"]?>][sort]" value="<?=$arPrice["SORT"]?>"
									   placeholder="<?=GetMessage("OBX_SETT_PRICE_F_SORT")?>"/>
							</td>
							<td>
								<select name="obx_price[<?=$arPrice["ID"]?>][currency]">
									<?foreach ($arCurrencyList as $arCurrency): ?>
										<option <?//
												?>value="<?=$arCurrency["CURRENCY"]?>"<?
										if ($arCurrency["CURRENCY"] == $arPrice["CURRENCY"] && $arPrice["CURRENCY_LANG_ID"] == LANGUAGE_ID) {
											?> selected="selected" <?
										}
										?>><?=$arCurrency["NAME"]?></option>
									<? endforeach?>
								</select>
							</td>
							<td>
								<div class="group_container">
									<?$curPriceGroups = Price::getGroupList($arPrice["ID"]);?>
									<?
									$i = 0;
									foreach ($curPriceGroups as $groupID):?>
										<div class="group_select">
											<select name="obx_price_ugrp[<?=$arPrice["ID"]?>][<?=$i?>]" data-price-id="<?=$arPrice["ID"]?>" data-count-id="<?=$i?>">
												<option value="-1">(<?=GetMessage("OBX_SETT_PRICE_DEL_GROUP")?>)</option>
												<?
												$by = "c_sort";
												$order = "desc";
												$rsGroups = \CGroup::GetList($by, $order, array("ACTIVE" => "Y"));
												while ($arGroup = $rsGroups->Fetch()):?>
													<option <?if ($arGroup["ID"] == $curPriceGroups[$i]): ?>selected=""<? endif;?>value="<?=$arGroup["ID"]?>">[<?=$arGroup["ID"]?>] <?=$arGroup["NAME"]?></option>
												<? endwhile;?>
											</select>
										</div>
										<?
										$i++;
									endforeach;
									?>
									<?if ($i==0):?>
										<div class="group_select">
											<select name="obx_price_ugrp[<?=$arPrice["ID"]?>][<?=$i?>]">
												<option value="-1">(<?=GetMessage("OBX_SETT_PRICE_DEL_GROUP")?>)</option>
												<?
												$by = "c_sort";
												$order = "desc";
												$rsGroups = \CGroup::GetList($by, $order, array("ACTIVE" => "Y"));
												while ($arGroup = $rsGroups->Fetch()):?>
													<option <?if ($arGroup["ID"] == $curPriceGroups[$i]): ?>selected=""<? endif;?>value="<?=$arGroup["ID"]?>">[<?=$arGroup["ID"]?>] <?=$arGroup["NAME"]?></option>
												<? endwhile;?>
											</select>
										</div>
									<?endif;?>
								</div>
								<a href="javascript:void(0)" class="bx-action-href add-new-group"><?=GetMessage('OBX_MARKET_SETT_ADD_GROUP_ACCESS');?></a>
							</td>
							<td class="center">
								<input type="checkbox" name="obx_price_delete[<?=$arPrice["ID"]?>]" value="<?=$arPrice["ID"]?>"/>
							</td>
						</tr>
					<? endforeach; ?>
					<tr class="replace">
						<td colspan="<?=$this->listTableColumns?>"></td>
					</tr>
					<tr>
						<td class="field-name"></td>
						<td colspan="<?=$this->listTableColumns?>"><input class="add_new_item" type="button"
																		  value="<?=GetMessage("OBX_SETT_PRICE_BTN_ADD_ITEM")?>"/></td>
					</tr>
					<tr>
						<td colspan="<?=$this->listTableColumns?>">
							<input type="button" class="adm-btn-save" id="obx_price_btn_save" value="<?=GetMessage("OBX_SETT_PRICE_BTN_SAVE")?>"/>
							<input type="button" id="obx_price_btn_cancel" value="<?=GetMessage("OBX_SETT_PRICE_BTN_CANCEL")?>"/>
						</td>
					</tr>
				</table>
			</td>
		</tr>

	<?
	}

	public function showTabScripts() {
		$arCurrencyList = CurrencyFormat::getListArray(null, array("LANGUAGE_ID" => LANGUAGE_ID));

		if (false):?><table><? endif; // это надо для корректной подсветки html в NetBeans IDE?>
		<?=
		'<script type="text/x-custom-template" id="obx_market_price_row_tmpl">'
		; ?>
		<tr data-new-row="$index">
			<td class="field-name"></td>
			<td style="width: 25px;">$index</td>
			<td><input type="text" name="obx_price_new[$index][name]" value=""
					   placeholder="<?=GetMessage("OBX_SETT_PRICE_F_NAME")?>"/></td>
			<td><input type="text" name="obx_price_new[$index][code]" value=""
					   placeholder="<?=GetMessage("OBX_SETT_PRICE_F_CODE")?>"/></td>
			<td><input type="text" name="obx_price_new[$index][sort]" value=""
					   placeholder="<?=GetMessage("OBX_SETT_PRICE_F_SORT")?>"/></td>
			<td>
				<select name="obx_price_new[$index][currency]">
					<?foreach ($arCurrencyList as $arCurrency): ?>
						<option value="<?=$arCurrency["CURRENCY"]?>"><?=$arCurrency["NAME"]?></option>
					<? endforeach?>
				</select>
			</td>
			<td colspan="2" class="center">
				<input type="button" class="remove_new_item" value="<?=GetMessage("OBX_SETT_PRICE_BTN_DELETE")?>"/>
			</td>
		</tr>
		<tr class="replace">
			<td colspan="<?=$this->listTableColumns?>"></td>
		</tr>
		<?= '</script>' ?>
		<? if (false): ?></table><?endif;
	}

	public function saveTabData() {
		$this->updatePriceList();
		$this->deletePriceList();
		$this->addNewPriceList();
	}

	protected function updatePriceList() {
		if ((!is_array($_REQUEST["obx_price_update"])
			|| !is_array($_REQUEST["obx_price"]))
		) {
			return false;
		}
		$arPriceDeleteID = $_REQUEST["obx_price_delete"];
		$arPriceUpdateID = $_REQUEST["obx_price_update"];
		$arPriceData = $_REQUEST["obx_price"];

		$arPriceUserGroup = $_REQUEST["obx_price_ugrp"];

		$arLangList = self::getLangList();
		foreach ($arPriceDeleteID as &$delPriceID) {
			$delPriceID = intval($delPriceID);
		}
		$strUpdateSuccessID = '';
		foreach ($arPriceUpdateID as $priceID) {
			$priceID = intval($priceID);
			$arPriceExists = Price::getByID($priceID);
			if (!empty($arPriceExists)) {
				$arPriceExists;
				$arUpdateFields = array();
				if (!empty($arPriceData[$priceID]) && empty($arPriceDeleteID[$priceID])) {
					$arUpdateFieldsRaw = array(
						"ID" => $priceID,
						"CODE" => $arPriceData[$priceID]["code"],
						"CURRENCY" => $arPriceData[$priceID]["currency"],
						"NAME" => $arPriceData[$priceID]["name"],
					);
					foreach ($arUpdateFieldsRaw as $field => $rawValue) {
						if ($field == "ID") {
							$arUpdateFields[$field] = $rawValue;
						}
						if ($field != "ID"
							&& isset($arPriceExists[$field])
							&& $arPriceExists[$field] != $rawValue
						) {
							$arUpdateFields[$field] = $rawValue;
						}
					}
					if (count($arUpdateFields) > 1) {
						if (Price::update($arUpdateFields)) {
							$strUpdateSuccessID .= ((strlen($strUpdateSuccessID) > 0) ? ", " : "") . $priceID;
						} else {
							$arError = Price::popLastError('ALL');
							$this->addError($arError["TEXT"], $arError["CODE"]);
						}
					}
				}
			} else {
				$this->addError(GetMessage("OBX_SETT_PRICE_ERROR_1", array("#ID#" => $priceID)));
			}
			// UPDATE price groups
			$curGroups = $arPriceUserGroup[$priceID];
			Price::setGroupList($priceID,array_unique($curGroups));

		}
		if (strlen($strUpdateSuccessID) > 0) {
			$this->addNotice(GetMessage("OBX_SETT_PRICE_MESSAGE_2", array(
				"#ID_LIST#" => $strUpdateSuccessID
			)), 2);
		}
	}

	protected function deletePriceList() {
		if (!is_array($_REQUEST["obx_price_delete"])) {
			return false;
		}
		$arPriceUpdateID = $_REQUEST["obx_price_delete"];
		foreach ($arPriceUpdateID as $priceID => $delText) {
			$priceID = intval($priceID);
			if (!Price::delete($priceID)) {
				$this->addError(Price::popLastError());
			}
		}
	}

	protected function addNewPriceList() {
		if (!is_array($_REQUEST["obx_price_new"])) {
			return false;
		}

		$arNewPricesRaw = $_REQUEST["obx_price_new"];
		//d($arNewCurrenciesRaw, '$arNewCurrenciesRaw');
		$strNewSuccessID = '';
		foreach ($arNewPricesRaw as $arNewCurrencyRaw) {

			$arNewPriceFields = array(
				"NAME" => $arNewCurrencyRaw["name"],
				"CODE" => $arNewCurrencyRaw["code"],
				"CURRENCY" => $arNewCurrencyRaw["currency"],
			);
			$newPriceID = Price::add($arNewPriceFields);
			if (!$newPriceID) {
				$this->addError(Price::popLastError());
			} else {
				$strNewSuccessID .= ((strlen($strNewSuccessID) > 0) ? ", " : "") . $newPriceID;
			}
		}
		if (strlen($strNewSuccessID) > 0) {
			$this->addNotice(GetMessage("OBX_SETT_PRICE_MESSAGE_1", array(
				"#ID_LIST#" => $strNewSuccessID
			)), 1);
		}
	}
}
