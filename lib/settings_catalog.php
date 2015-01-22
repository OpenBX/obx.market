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

class Settings_Catalog extends Settings {

	protected $listTableColumns = 5;

	public function showTabContent() {
		$arECommerceIBlockList = ECommerceIBlock::getFullList();
		$arPriceList = Price::getListArray();
		?>
		<tr>
			<td>
				<table class="internal" style="width: 100%">
					<input type="hidden" name="obx_ecom_iblock_save" value="Y"/>
					<tr>
						<td colspan="<?=$this->listTableColumns?>">
							<input type="button" class="adm-btn-save obx_ecom_iblock_save" value="<?=GetMessage("OBX_SETT_CATALOG_B_SAVE")?>"/>
							<input type="button" class="obx_ecom_iblock_cancel" value="<?=GetMessage("OBX_SETT_CATALOG_B_CANCEL")?>"/>
						</td>
					</tr>
					<tr class="heading">
						<td class="field-name"></td>
						<td><?=GetMessage("OBX_SETT_CATALOG_F_IBLOCK")?></td>
						<td><?=GetMessage("OBX_SETT_CATALOG_F_IBLOCK_IS_ECOM")?></td>
						<td><?=GetMessage("OBX_SETT_CATALOG_F_PRICE")?></td>
						<td><?=GetMessage("OBX_SETT_CATALOG_F_IBLOCK_PROP")?></td>
					</tr>
					<?
					foreach ($arECommerceIBlockList as &$arIBlock) {
					$arPricePropList = CIBlockPropertyPrice::getFullPriceList($arIBlock["ID"]);
					$rsPropIntList = \CIBlockProperty::GetList(
						array("SORT" => "ASC"),
						array("IBLOCK_ID" => $arIBlock["ID"], "PROPERTY_TYPE" => "N")
					);
					$arPropIntList = array();
					while (($arPropInt = $rsPropIntList->GetNext())) {
						$arPropIntList[] = $arPropInt;
					}
					$countPricePropList = count($arPricePropList);
					$bIBockIsECom = ($arIBlock["IS_ECOM"] == "Y") ? true : false;
					?>
					<? /*/?>
			<tr>
				<td class="field-name"></td>
				<td><?wd($arPricePropList, '$arPricePropList')?></td>
			</tr>
			<?//*/
					?>
					<tr>
						<td class="field-name"></td>
						<td rowspan="<?=$countPricePropList?>" class="center">
							<?=$arIBlock["NAME"]?> <br />
							[<?=$arIBlock["ID"]?> / <?=$arIBlock["IBLOCK_TYPE_ID"]?>]
						</td>
						<td rowspan="<?=$countPricePropList?>" class="center">
							<label class="ecom_iblock checkbox">
								<input name="obx_iblock_is_ecom[<?=$arIBlock["ID"]?>]"
									   class="obx_iblock_is_ecom"
									   data-checked-text="<?=GetMessage("OBX_SETT_CATALOG_L_DO_SIMPLE")?>"
									   data-unchecked-text="<?=GetMessage("OBX_SETT_CATALOG_L_DO_ECOM")?>"
									   data-iblock-id="<?=$arIBlock["ID"]?>"
									   type="checkbox"
									   value="Y"
									<?if ($bIBockIsECom): ?> checked="checked"<? endif?> />
						<span class="label-text">
							<?if (!$bIBockIsECom): ?><?= GetMessage("OBX_SETT_CATALOG_L_DO_ECOM") ?>
							<? else: ?><?= GetMessage("OBX_SETT_CATALOG_L_DO_SIMPLE") ?><?endif?>
						</span>
							</label>

							<label class="select ibpprice-link-control <?if(!$bIBockIsECom):?> iblock-is-not-ecom<?endif?>" data-iblock-id="<?=$arIBlock["ID"]?>">
								<span class="select-label-text"><?=GetMessage('OBX_SETT_CATALOG_S_DISCOUNT_PROP')?></span>
								<select class="obx_ib_discount_prop" name="obx_ib_discount_prop[<?=$arIBlock["ID"]?>]"<?if(!$bIBockIsECom):?> disabled="disabled"<?endif?>>
									<option value="0">
										<?if ($arIBlock["DISCOUNT_VAL_PROP_ID"] > 0): ?><?= GetMessage("OBX_SETT_CATALOG_S_REMOVE_LINK") ?>
										<? else: ?><?= GetMessage("OBX_SETT_CATALOG_S_DOESNOT_SET") ?><?endif?>
									</option>
									<option value="-1"><?=GetMessage("OBX_SETT_CATALOG_S_NEW_PROP")?></option>
									<?foreach ($arPropIntList as &$arPropInt): ?>
										<option value="<?=$arPropInt["ID"]?>"<?if($arPropInt["ID"] == $arIBlock["DISCOUNT_VAL_PROP_ID"]):
											?> selected="selected"<?endif?>>
											<?=$arPropInt["NAME"]?>
											[<?=$arPropInt["ID"]?><?=((strlen($arPropInt["CODE"]) ? ":" : "") . $arPropInt["CODE"])?>]
										</option>
									<? endforeach?>
								</select>
							</label>

							<label class="select ibpprice-link-control <?if(!$bIBockIsECom):?> iblock-is-not-ecom<?endif?>" data-iblock-id="<?=$arIBlock["ID"]?>">
								<span class="select-label-text"><?=GetMessage('OBX_SETT_CATALOG_S_WEIGHT_PROP')?></span>
								<select class="obx_ib_weight_prop" name="obx_ib_weight_prop[<?=$arIBlock["ID"]?>]"<?if(!$bIBockIsECom):?> disabled="disabled"<?endif?>>
									<option value="0">
										<?if ($arIBlock["WEIGHT_VAL_PROP_ID"] > 0): ?><?= GetMessage("OBX_SETT_CATALOG_S_REMOVE_LINK") ?>
										<? else: ?><?= GetMessage("OBX_SETT_CATALOG_S_DOESNOT_SET") ?><?endif?>
									</option>
									<option value="-1"><?=GetMessage("OBX_SETT_CATALOG_S_NEW_PROP")?></option>
									<?foreach ($arPropIntList as &$arPropInt): ?>
										<option value="<?=$arPropInt["ID"]?>"<?if($arPropInt["ID"] == $arIBlock["WEIGHT_VAL_PROP_ID"]):
											?> selected="selected"<?endif?>>
											<?=$arPropInt["NAME"]?>
											[<?=$arPropInt["ID"]?><?=((strlen($arPropInt["CODE"]) ? ":" : "") . $arPropInt["CODE"])?>]
										</option>
									<? endforeach?>
								</select>
							</label>

						</td>

						<?
						$iProp = 0;
						foreach ($arPricePropList as &$arProp) {
						$iProp++;
						?>
						<? if ($iProp > 1): ?>
					</tr>
					<tr>
						<td class="field-name"></td>
						<? endif ?>
						<td>
							<div data-iblock-id="<?=$arIBlock["ID"]?>"
								 class="ibpprice-link-control<?if (!$bIBockIsECom): ?> iblock-is-not-ecom<? endif?>">
								<?=$arProp["PRICE_NAME"]?> [<?=$arProp["PRICE_CODE"]?>]
							</div>
						</td>
						<td>
							<div data-iblock-id="<?=$arIBlock["ID"]?>"
								 class="ibpprice-link-control<?if (!$bIBockIsECom): ?> iblock-is-not-ecom<? endif?>">
								<select <?if (!$bIBockIsECom): ?>disabled<?endif;?>
										name="obx_ib_price_prop[<?=$arIBlock["ID"]?>][<?=$arProp["PRICE_ID"]?>]">
									<option value="0">
										<?if ($arProp["PROPERTY_ID"] > 0): ?><?= GetMessage("OBX_SETT_CATALOG_S_REMOVE_LINK") ?>
										<? else: ?><?= GetMessage("OBX_SETT_CATALOG_S_DOESNOT_SET") ?><?endif?>
									</option>
									<option value="-1"><?=GetMessage("OBX_SETT_CATALOG_S_NEW_PROP")?></option>
									<?foreach ($arPropIntList as &$arPropInt): ?>
										<option value="<?=$arPropInt["ID"]?>"<?if ($arPropInt["ID"] == $arProp["PROPERTY_ID"]): ?>
											selected="selected"<? endif?>>
											<?=$arPropInt["NAME"]?>
											[<?=$arPropInt["ID"]?><?=((strlen($arPropInt["CODE"]) ? ":" : "") . $arPropInt["CODE"])?>]
										</option>
									<? endforeach?>
								</select>
							</div>
						</td>
						<?
						}
						}
						?>
					</tr>
					<tr>
						<td colspan="<?=$this->listTableColumns?>">
							<input type="button" class="adm-btn-save obx_ecom_iblock_save" value="<?=GetMessage("OBX_SETT_CATALOG_B_SAVE")?>"/>
							<input type="button" class="obx_ecom_iblock_cancel" value="<?=GetMessage("OBX_SETT_CATALOG_B_CANCEL")?>"/>
						</td>
					</tr>
				</table>
			</td>
		</tr>

	<?
	}

	public function showTabScripts() {
	}

	public function saveTabData() {
		if (empty($_REQUEST["obx_ecom_iblock_save"])) {
			return true;
		}
		$rsIBlockList = ECommerceIBlock::getFullList(true);
		while (($arIBlock = $rsIBlockList->GetNext())) {
			if (array_key_exists($arIBlock["ID"], $_REQUEST["obx_iblock_is_ecom"])) {
				if ($arIBlock["IS_ECOM"] == "N") {
					$nowEComIBlockID = ECommerceIBlock::add(array("IBLOCK_ID" => $arIBlock["ID"]));
					if(!$nowEComIBlockID) {
						$arECommAddError = ECommerceIBlock::popLastError('ARRAY');
						$this->addError(
							GetMessage('OBX_SETT_CATALOG_ERROR_300', array('#IBLOCK_ID#' => $arIBlock['ID']))
							.' '.$arECommAddError['TEXT'].'; code: '.$arECommAddError['CODE']
							, (300 + $arECommAddError['CODE'])
						);
					}
				}

			} else {
				if ($arIBlock["IS_ECOM"] == "Y") {
					ECommerceIBlock::delete($arIBlock["ID"]);
				}
			}
		}
		if (empty($_REQUEST["obx_ib_price_prop"])) {
			return true;
		}
		$arIBlockList = ECommerceIBlock::getFullList(false);
		foreach ($arIBlockList as &$arIBlock) {
			if ($arIBlock["IS_ECOM"] == "N" ) {
				continue;
			}

			$arEComFieldsUpdate = array();
			if( array_key_exists($arIBlock["ID"], $_REQUEST["obx_ib_weight_prop"]) ) {
				$arEComFieldsUpdate["IBLOCK_ID"] = $arIBlock["ID"];
				$arEComFieldsUpdate['WEIGHT_VAL_PROP_ID'] = intval($_REQUEST["obx_ib_weight_prop"][$arIBlock["ID"]]);
				if($arEComFieldsUpdate['WEIGHT_VAL_PROP_ID'] == -1) {
					$arWeightPropFields = array(
						'IBLOCK_ID' => $arIBlock['ID'],
						'NAME' => GetMessage('OBX_SETT_WEIGHT'),
						'CODE' => 'WEIGHT',
						'SORT' => 500,
						'PROPERTY_TYPE' => 'N',
						'ACTIVE' => 'Y'
					);
					$IBProp = new \CIBlockProperty;
					$newID = $IBProp->Add($arWeightPropFields);
					if(!$newID) {
						$this->addError(
							GetMessage('OBX_SETT_CATALOG_ERROR_5', array('#IBLOCK_ID#' => $arIBlock['ID'])
								.' '.$IBProp->LAST_ERROR
							), 5
						);
						return false;
					}
					$arEComFieldsUpdate['WEIGHT_VAL_PROP_ID'] = $newID;
				}
			}
			if( array_key_exists($arIBlock["ID"], $_REQUEST["obx_ib_discount_prop"]) ) {
				$arEComFieldsUpdate["IBLOCK_ID"] = $arIBlock["ID"];
				$arEComFieldsUpdate['DISCOUNT_VAL_PROP_ID'] = intval($_REQUEST["obx_ib_discount_prop"][$arIBlock["ID"]]);
				if($arEComFieldsUpdate['DISCOUNT_VAL_PROP_ID'] == -1) {
					$arDiscountPropFields = array(
						'IBLOCK_ID' => $arIBlock['ID'],
						'NAME' => GetMessage('OBX_SETT_DISCOUNT'),
						'CODE' => 'DISCOUNT',
						'SORT' => 500,
						'PROPERTY_TYPE' => 'N',
						'ACTIVE' => 'Y'
					);
					$IBProp = new \CIBlockProperty;
					$newID = $IBProp->Add($arDiscountPropFields);
					if(!$newID) {
						$this->addError(
							GetMessage('OBX_SETT_CATALOG_ERROR_4', array('#IBLOCK_ID#' => $arIBlock['ID'])
								.' '.$IBProp->LAST_ERROR
							), 4
						);
						return false;
					}
					$arEComFieldsUpdate['DISCOUNT_VAL_PROP_ID'] = $newID;
				}
			}
			if(
				!empty($arEComFieldsUpdate)
				&& (
					$arEComFieldsUpdate['DISCOUNT_VAL_PROP_ID'] != $arIBlock['DISCOUNT_VAL_PROP_ID']
					||
					$arEComFieldsUpdate['WEIGHT_VAL_PROP_ID'] != $arIBlock['WEIGHT_VAL_PROP_ID']
				)
			) {
				if( intval($arEComFieldsUpdate['WEIGHT_VAL_PROP_ID'])<1 ) {
					$arEComFieldsUpdate['WEIGHT_VAL_PROP_ID'] = null;
				}
				if( intval($arEComFieldsUpdate['DISCOUNT_VAL_PROP_ID'])<1 ) {
					$arEComFieldsUpdate['DISCOUNT_VAL_PROP_ID'] = null;
				}
				$bEComIBlockUpdateSuccess = ECommerceIBlock::update($arEComFieldsUpdate);
				if(!$bEComIBlockUpdateSuccess) {
					$arErrorUpdateECommIBlock = ECommerceIBlock::popLastError('ARRAY');
					$this->addError(
						GetMessage('OBX_SETT_CATALOG_ERROR_600', array('#IBLOCK_ID#' => $arIBlock['ID']))
						.' '.$arErrorUpdateECommIBlock['TEXT'].'; code: '.$arErrorUpdateECommIBlock['CODE']
						, (600 + $arErrorUpdateECommIBlock['CODE'])
					);
				}
			}


			if( isset($_REQUEST["obx_ib_price_prop"][$arIBlock["ID"]]) ) {
				$arIBPricePropFullList = CIBlockPropertyPrice::getFullPropList($arIBlock["ID"]);

				// Обработка свойств-цен
				$rawSetPriceProp = $_REQUEST["obx_ib_price_prop"][$arIBlock["ID"]];
				$arNewPricePropLinkList = array();
				$arUniquePR = array();
				$arUniquePP = array();
				foreach ($rawSetPriceProp as $priceID => $propID) {
					$priceID = intval($priceID);
					$propID = intval($propID);
					if ($priceID > 0) {
						if ($propID > 0) {
							$keyPR = $arIBlock["ID"] . "_" . $priceID;
							$keyPP = $arIBlock["ID"] . "_" . $propID;
							if (array_key_exists($keyPR, $arUniquePR)) {
								$this->addError(GetMessage("OBX_SETT_CATALOG_ERROR_1", array(
									"#IBLOCK_ID#" => $arIBlock["ID"]
								)), 1);
								return false;
							}
							if (array_key_exists($keyPP, $arUniquePP)) {
								$this->addError(GetMessage("OBX_SETT_CATALOG_ERROR_2", array(
									"#IBLOCK_PROP_ID#" => htmlspecialcharsEx($propID)
								)), 2);
								return false;
							}
							$arUniquePR[$keyPR] = true;
							$arUniquePP[$keyPP] = true;
							$arNewPricePropLinkList[] = array(
								"__ACTION" => "ADD",
								"IBLOCK_ID" => $arIBlock["ID"],
								"PRICE_ID" => $priceID,
								"IBLOCK_PROP_ID" => $propID
							);
						} elseif ($propID == 0) {
							$arDelFilter = array(
								"IBLOCK_ID" => $arIBlock["ID"],
								"PRICE_ID" => $priceID,
							);
							$arExists = CIBlockPropertyPrice::getListArray(null, $arDelFilter, null, null, null, false);
							if (!empty($arExists)) {
								$arExists["__ACTION"] = "DELETE";
								$arNewPricePropLinkList[] = $arExists;
							}
						} elseif ($propID == -1) {
							$arNewPricePropLinkList[] = array(
								"__ACTION" => "NEW_PROP",
								"IBLOCK_ID" => $arIBlock["ID"],
								"PRICE_ID" => $priceID,
							);
						}
					}
				}
				CIBlockPropertyPrice::deleteByFilter(array("IBLOCK_ID" => $arIBlock["ID"]));
				CIBlockPropertyPrice::clearErrors();
				foreach ($arNewPricePropLinkList as &$arNewPricePropLink) {
					if ($arNewPricePropLink["__ACTION"] == "ADD") {
						$bSuccess = CIBlockPropertyPrice::add($arNewPricePropLink);
					}
					//elseif($arNewPricePropLink["__ACTION"] == "DELETE") {
					//	$bSuccess = CIBlockPropertyPrice::deleteByFilter($arNewPricePropLink);
					//}
					elseif ($arNewPricePropLink["__ACTION"] == "NEW_PROP") {
						$bSuccess = CIBlockPropertyPrice::addIBlockPriceProperty($arNewPricePropLink);
					}
					if (!$bSuccess) {
						$arError = CIBlockPropertyPrice::popLastError('ALL');
						$this->addError($arError["TEXT"], $arError["CODE"]);
					}
				}
			}

		}
		if($this->countErrors() <1) {
			$this->addNotice(GetMessage('OBX:MARKET:SETTINGS:CATALOG:INFO_UPDATED'));
		}
		return true;
	}
}
