<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */

use OBX\Core\Settings\Tab as SettingsTab;
use OBX\Core\Settings\AdminPage as SettingsAdminPage;

IncludeModuleLangFile(__FILE__);

/** @global \CUser $USER */
if(!$USER->IsAdmin()) return;
if(!CModule::IncludeModule("obx.market")) return;


class Settings_Roles extends OBX\Core\Settings\ATab
{
	const MODULE_ID = 'obx.market';

	public function showTabContent()
	{
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		$arTasks = \CTask::GetTasksInModules(false, self::MODULE_ID);
		if(empty($arTasks)) {
			$this->showInstallRolesForm();
		}
		else {
			$this->showGroupRoleSettings();
		}
	}

	protected function showInstallRolesForm() {
		?>
		<tr>
			<td width="40%">
				<b><?= GetMessage('OBX_MARKET_INSTALL_ROLES_TITLE') ?></b>
				<br />
				<small><?=GetMessage('OBX_MARKET_INSTALL_ROLES_DESCRIPTION')?></small>
			</td>
			<td width="60%">
				<input type="checkbox" id="obx_market_install_roles" name="obx_market_install_roles" value="Y"/>
				<label for="obx_market_install_roles"></label>
			</td>
		</tr>
		<?
	}

	protected function showGroupRoleSettings() {
		//define all global vars
		global $__GlobalKeys;
		global $__GlobalKeysIterator;
		$__GlobalKeys = array_keys($GLOBALS);
		for($__GlobalKeysIterator=0;
			$__GlobalKeysIterator<count($__GlobalKeys);
			$__GlobalKeysIterator++
		) {
			if(
				$__GlobalKeys[$__GlobalKeysIterator]!='GLOBALS'
				&& $__GlobalKeys[$__GlobalKeysIterator]!='strTitle'
				&& $__GlobalKeys[$__GlobalKeysIterator]!='filepath'
				&& $__GlobalKeys[$__GlobalKeysIterator]!='__GlobalKeys'
				&& $__GlobalKeys[$__GlobalKeysIterator]!='__GlobalKeysIterator'
			) {
				global ${$__GlobalKeys[$__GlobalKeysIterator]};
			}
		}
		unset($GLOBALS['__GlobalKeys']);
		unset($GLOBALS['__GlobalKeysIterator']);
		// Это не даст скрипту обработать сохранение. Ибо есть свой обработчик
		unset($Update, $Apply);
		$module_id = self::MODULE_ID;
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/group_rights2.php');
	}

	public function saveTabData()
	{
		if(array_key_exists('obx_market_install_roles', $_REQUEST) && $_REQUEST['obx_market_install_roles']) {
			$this->installRoles();
		}
		else {
			$this->saveRoles();
		}
	}

	protected function installRoles() {
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		$module = \CModule::CreateModuleObject(self::MODULE_ID);
		$module->InstallTasks();
		$this->addNotice(GetMessage('OBX_MARKET_ROLES_INSTALLED'));
	}

	protected function saveRoles() {
		global $APPLICATION;
		$module_id = 'obx.market';
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		$md = \CModule::CreateModuleObject($module_id);

		$GROUP_DEFAULT_TASK = (array_key_exists('GROUP_DEFAULT_TASK', $_REQUEST)
			?$_REQUEST['GROUP_DEFAULT_TASK']
			:\COption::GetOptionString($module_id, 'GROUP_DEFAULT_TASK')
		);

		$arGROUPS = array();
		$arFilter = Array("ACTIVE"=>"Y");
		if($md->SHOW_SUPER_ADMIN_GROUP_RIGHTS != "Y")
			$arFilter["ADMIN"] = "N";
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		$z = \CGroup::GetList($v1="sort", $v2="asc", $arFilter);
		while($zr = $z->Fetch())
		{
			$ar = array();
			$ar["ID"] = intval($zr["ID"]);
			$ar["NAME"] = htmlspecialcharsbx($zr["NAME"]);
			$arGROUPS[] = $ar;
		}

		\COption::SetOptionString($module_id, "GROUP_DEFAULT_TASK", $GROUP_DEFAULT_TASK, "Task for groups by default");
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		$letter = ($l = \CTask::GetLetter($GROUP_DEFAULT_TASK)) ? $l : 'D';
		\COption::SetOptionString($module_id, "GROUP_DEFAULT_RIGHT", $letter, "Right for groups by default");

		$arTasksInModule = Array();
		foreach($arGROUPS as $value)
		{
			$tid = $GLOBALS["TASKS_".$value["ID"]];
			if ($tid) {
				$arTasksInModule[$value["ID"]] = Array('ID'=>$tid);
			}

			/** @noinspection PhpDynamicAsStaticMethodCallInspection */
			$rt = ($tid) ? \CTask::GetLetter($tid) : '';
			if (strlen($rt) > 0 && $rt != "NOT_REF") {
				$APPLICATION->SetGroupRight($module_id, $value["ID"], $rt);
			}
			else {
				$APPLICATION->DelGroupRight($module_id, array($value["ID"]));
			}
		}
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		\CGroup::SetTasksForModule($module_id, $arTasksInModule);

		return true;
	}

	public function showTabScripts()
	{

	}
}

$ModuleSettings = new SettingsAdminPage('obx_market_settings');
//$AdminPage->addTab(new SettingsTab(
//	'obx.market',
//	'main',
//	array(
//		"DIV" => "obx_market_settings_main",
//		"TAB" => GetMessage("MAIN_TAB_SET"),
//		"ICON" => "settings_main",
//		"TITLE" => GetMessage("MAIN_TAB_TITLE_SET")
//	),
//	array(
//
//	)
//);
$ModuleSettings->addTab(new Settings_Roles(array(
	"DIV" => "obx_market_settings_access",
	"TAB" => GEtMessage("OBX_MARKET_SETTINGS_TAB_ACCESS"),
	"ICON" => "settings_access",
	"TITLE" => GEtMessage("OBX_MARKET_SETTINGS_TITLE_ACCESS"),
)));

if($ModuleSettings->checkSaveRequest()) {
	$ModuleSettings->save();
}
if($ModuleSettings->checkRestoreRequest()) {
	$ModuleSettings->restoreDefaults();
}
?>
<div id="obx_market_settings">
	<?$ModuleSettings->show()?>
</div>
