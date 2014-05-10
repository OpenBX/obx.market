<?php
/***********************************************
 ** @product OpenBX:Market Bitrix Module      **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Market\Test;

\OBX_Market_TestCase::includeLang(__FILE__);

class ModuleRolesTest extends \OBX_Market_TestCase
{
	public function _testInstallRoles() {
		@include_once OBX_DOC_ROOT.'/'.BX_ROOT.'/modules/obx.market/install/index.php';
		$obx_market = new \obx_market();
		$obx_market->InstallTasks();
	}

	public function testGetRight() {
		/**
		 * @global \CMain $APPLICATION
		 * @global \CUser $USER
		 */
		global $APPLICATION, $USER;
		$USER->Authorize(2);
		$arGrpRight = $APPLICATION->GetGroupRight('obx.market');
		$debug=1;
	}


}