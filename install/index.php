<?
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 DevTop
 */
use OBX\Market\Currency;
use OBX\Market\CurrencyFormat;
use OBX\Market\Price;
use OBX\Market\ECommerceIBlock;
use OBX\Market\CIBlockPropertyPrice;
use OBX\Market\OrderStatus;
use OBX\Market\OrderProperty;
use OBX\Market\OrderPropertyEnum;

class obx_market extends CModule {
	var $MODULE_ID = "obx.market";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	protected $installDir = null;
	protected $moduleDir = null;
	protected $bxModulesDir = null;
	protected $arErrors = array();
	protected $arWarnings = array();
	protected $arMessages = array();
	protected $bSuccessInstallDB = false;
	protected $bSuccessInstallFiles = false;
	protected $bSuccessInstallDeps = false;
	protected $bSuccessInstallEvents = false;
	protected $bSuccessInstallTasks = true;
	protected $bSuccessInstallData = false;
	protected $bSuccessUnInstallDB = false;
	protected $bSuccessUnInstallFiles = false;
	protected $bSuccessUnInstallDeps = false;
	protected $bSuccessUnInstallEvents = false;
	protected $bSuccessUnInstallTasks = true;
	protected $bSuccessUnInstallData = false;

	const DB = 1;
	const FILES = 2;
	const DEPS = 4;
	const EVENTS = 8;
	const TASKS = 16;
	const TARGETS = 31;

	public function obx_market() {
		self::includeLangFile();
		$this->installDir = str_replace(array("\\", "//"), "/", __FILE__);
		//10 == strlen("/index.php")
		//8 == strlen("/install")
		$this->installDir = substr($this->installDir, 0, strlen($this->installDir) - 10);
		$this->moduleDir = substr($this->installDir, 0, strlen($this->installDir) - 8);
		$this->bxModulesDir = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules";

		$arModuleVersion = include($this->installDir . "/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("OBX_MODULE_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("OBX_MODULE_INSTALL_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage("OBX_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("OBX_PARTNER_URI");
	}

	public function getErrors() {
		return $this->arErrors;
	}

	public function getWarnings() {
		return $this->arWarnings;
	}

	public function getMessages() {
		return $this->arMessages;
	}

	/**
	 * @param int $maskTarget
	 * @return bool
	 */
	public function isIntallationSuccess($maskTarget) {
		$bSuccess = true;
		if($maskTarget & self::DB) {
			$bSuccess = $this->bSuccessInstallDB && $bSuccess;
		}
		if($maskTarget & self::FILES) {
			$bSuccess = $this->bSuccessInstallFiles && $bSuccess;
		}
		if($maskTarget & self::DEPS) {
			$bSuccess = $this->bSuccessInstallDeps && $bSuccess;
		}
		if($maskTarget & self::EVENTS) {
			$bSuccess = $this->bSuccessInstallEvents && $bSuccess;
		}
		if($maskTarget & self::TASKS) {
			$bSuccess = $this->bSuccessInstallTasks && $bSuccess;
		}
		return $bSuccess;
	}

	/**
	 * @param int $maskTarget
	 * @return bool
	 */
	public function isUnIntallationSuccess($maskTarget) {
		$bSuccess = true;
		if($maskTarget & self::DB) {
			$bSuccess = $this->bSuccessUnInstallDB && $bSuccess;
		}
		if($maskTarget & self::FILES) {
			$bSuccess = $this->bSuccessUnInstallFiles && $bSuccess;
		}
		if($maskTarget & self::DEPS) {
			$bSuccess = $this->bSuccessUnInstallDeps && $bSuccess;
		}
		if($maskTarget & self::EVENTS) {
			$bSuccess = $this->bSuccessUnInstallEvents && $bSuccess;
		}
		if($maskTarget & self::TASKS) {
			$bSuccess = $this->bSuccessUnInstallTasks && $bSuccess;
		}
		return $bSuccess;
	}

	public function DoInstall() {
		$bSuccess = true;
		$bSuccess = $this->InstallDB() && $bSuccess;
		$bSuccess = $this->InstallFiles() && $bSuccess;
		$bSuccess = $this->InstallDeps() && $bSuccess;
		$bSuccess = $this->InstallEvents() && $bSuccess;
		$bSuccess = $this->InstallTasks() && $bSuccess;
		if($bSuccess) {
			if( !IsModuleInstalled($this->MODULE_ID) ) {
				RegisterModule($this->MODULE_ID);
			}
			$this->InstallData();
		}
		return $bSuccess;
	}
	public function DoUninstall() {
		$bSuccess = true;
		$bSuccess = $this->UnInstallTasks() && $bSuccess;
		$bSuccess = $this->UnInstallEvents() && $bSuccess;
		//$bSuccess = $this->UnInstallDeps() && $bSuccess;
		$bSuccess = $this->UnInstallFiles() && $bSuccess;
		$bSuccess = $this->UnInstallDB() && $bSuccess;		
		if($bSuccess) {
			if( IsModuleInstalled($this->MODULE_ID) ) {
				UnRegisterModule($this->MODULE_ID);
			}
		}
		return $bSuccess;
	}
	public function InstallFiles() {
		$this->bSuccessInstallFiles = true;
		if (is_file($this->installDir . "/install_files.php")) {
			require($this->installDir . "/install_files.php");
		}
		else {
			$this->bSuccessInstallFiles = false;
		}
		return $this->bSuccessInstallFiles;
	}
	public function UnInstallFiles() {
		$this->bSuccessUnInstallFiles = true;
		if (is_file($this->installDir . "/uninstall_files.php")) {
			require($this->installDir . "/uninstall_files.php");
		}
		else {
			$this->bSuccessUnInstallFiles = false;
		}
		return $this->bSuccessUnInstallFiles;
	}

	public function InstallDB() {
		global $DB, $DBType;
		$this->bSuccessInstallDB = true;
		if( is_file($this->installDir.'/db/'.$DBType.'/install.sql') ) {
			$this->prepareDBConnection();
			$arErrors = $DB->RunSQLBatch($this->installDir.'/db/'.$DBType.'/install.sql');
			if( is_array($arErrors) && count($arErrors)>0 ) {
				$this->arErrors = $arErrors;
				$this->bSuccessInstallDB = false;
			}
		}
		else {
			$this->bSuccessInstallDB = false;
		}
		return $this->bSuccessInstallDB;
	}
	public function UnInstallDB() {
		global $DB, $DBType;
		$this->bSuccessUnInstallDB = true;
		if( is_file($this->installDir.'/db/'.$DBType.'/uninstall.sql') ) {
			$this->prepareDBConnection();
			$arErrors = $DB->RunSQLBatch($this->installDir.'/db/'.$DBType.'/uninstall.sql');
			if( is_array($arErrors) && count($arErrors)>0 ) {
				$this->arErrors = $arErrors;
				$this->bSuccessUnInstallDB = false;
			}
		}
		else {
			$this->bSuccessUnInstallDB = false;
		}
		return $this->bSuccessUnInstallDB;
	}

	private function explicitIncludeModuleClasses() {
		require_once __DIR__.'/../../obx.core/lib/imessagepool.php';
		require_once __DIR__.'/../../obx.core/lib/imessagepoolstatic.php';
		require_once __DIR__.'/../../obx.core/lib/messagepool.php';
		require_once __DIR__.'/../../obx.core/lib/messagepooldecorator.php';
		require_once __DIR__.'/../../obx.core/lib/messagepoolstatic.php';
		require_once __DIR__.'/../../obx.core/lib/dbsimple/ientity.php';
		require_once __DIR__.'/../../obx.core/lib/dbsimple/ientitystatic.php';
		require_once __DIR__.'/../../obx.core/lib/dbsimple/entity.php';
		require_once __DIR__.'/../../obx.core/lib/dbsimple/entitystatic.php';
		require_once __DIR__.'/../lib/currencydbs.php';
		require_once __DIR__.'/../lib/currency.php';
		require_once __DIR__.'/../lib/currencyformatdbs.php';
		require_once __DIR__.'/../lib/currencyformat.php';
		require_once __DIR__.'/../lib/currencyinfo.php';
		require_once __DIR__.'/../lib/pricedbs.php';
		require_once __DIR__.'/../lib/price.php';
		require_once __DIR__.'/../lib/orderstatusdbs.php';
		require_once __DIR__.'/../lib/orderstatus.php';
		require_once __DIR__.'/../lib/orderpropertydbs.php';
		require_once __DIR__.'/../lib/orderproperty.php';
		require_once __DIR__.'/../lib/orderpropertyenumdbs.php';
		require_once __DIR__.'/../lib/orderpropertyenum.php';
		require_once __DIR__.'/../lib/ecommerceiblockdbs.php';
		require_once __DIR__.'/../lib/ecommerceiblock.php';
		require_once __DIR__.'/../lib/ciblockpropertypricedbs.php';
		require_once __DIR__.'/../lib/ciblockpropertyprice.php';
	}

	public function InstallEvents() {
		RegisterModuleDependences("main", "OnBuildGlobalMenu", "obx.market", "OBX_Market_BXMainEventsHandlers", "OnbBuildGlobalMenu");
		$this->explicitIncludeModuleClasses();
		ECommerceIBlock::registerModuleDependencies();
		CIBlockPropertyPrice::registerModuleDependencies();
		$this->bSuccessInstallEvents = true;
		return $this->bSuccessInstallEvents;
	}

	public function UnInstallEvents() {
		UnRegisterModuleDependences("main", "OnBuildGlobalMenu", "obx.market", "OBX_Market_BXMainEventsHandlers", "OnbBuildGlobalMenu");
		$this->explicitIncludeModuleClasses();
		ECommerceIBlock::unRegisterModuleDependencies();
		CIBlockPropertyPrice::unRegisterModuleDependencies();
		$this->bSuccessUnInstallEvents = true;
		return $this->bSuccessUnInstallEvents;
	}


	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D","L","P","W"),
			"reference" => array(
				"[D] ".GetMessage("OBX_MARKET_ROLE_DENIED"),
				"[L] ".GetMessage("OBX_MARKET_ROLE_ORDER_READ"),
				"[P] ".GetMessage("OBX_MARKET_ROLE_ORDER_EDIT"),
				"[W] ".GetMessage("OBX_MARKET_ROLE_ADMIN"),
			)
		);
		return $arr;
	}
	public function GetModuleTasks() {
		return array(
			"access_denied" => array(
				"LETTER" => "D",
				"BINDING" => "module",
				"OPERATIONS" => array(),
			),
			"order_view" => array(
				"LETTER" => "L",
				"BINDING" => "module",
				"OPERATIONS" => array(
					"obx_market_view_order",
				),
			),
			"order_edit" => array(
				"LETTER" => "P",
				"BINDING" => "module",
				"OPERATIONS" => array(
					"obx_market_view_order",
					"obx_market_edit_order",
				)
			),
			"module_admin" => array(
				"LETTER" => "W",
				"BINDING" => "module",
				"OPERATIONS" => array(
					"obx_market_view_order",
					"obx_market_edit_order",
					"obx_market_admin_module",
				)
			)
		);
	}
	public function InstallTasks() {
		$this->bSuccessInstallTasks = true;
		parent::InstallTasks();
		$this->bSuccessInstallTasks = (
			(require_once $_SERVER['DOCUMENT_ROOT']
				.BX_ROOT.'/modules/'
				.$this->MODULE_ID
				.'/install/install_tasks.php'
			)!==false
		)?true:false;
		return $this->bSuccessInstallTasks;
	}
	public function UnInstallTasks() {
		$this->bSuccessUnInstallTasks = true;
		parent::UnInstallTasks();
		return $this->bSuccessUnInstallTasks;
	}

	public function InstallDeps() {
		$arDepsList = $this->getDepsList();
		$this->bSuccessInstallDeps = true;
		foreach($arDepsList as $depModID => $depModClass) {
			$depModInstallerFile = $this->bxModulesDir."/".$depModID."/install/index.php";
			if( !IsModuleInstalled($depModID) ) {
				CopyDirFiles(
					$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$this->MODULE_ID.'/install/modules/'.$depModID,
					$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$depModID,
					true, true
					, false, 'update-'
				);

				if( !is_file($depModInstallerFile) ) {
					$this->bSuccessInstallDeps = false;
					$this->arErrors[] = 'Dependency installer not found ('.BX_ROOT.'/modules/'.$depModID.')';
				}
				else {
					require_once $depModInstallerFile;
					/** @var CModule $DepModInstaller */
					$DepModInstaller = new $depModClass;
					$bSuccess = true;
					$bSuccess = $DepModInstaller->InstallFiles() && $bSuccess;
					$bSuccess = $DepModInstaller->InstallDB() && $bSuccess;
					$bSuccess = $DepModInstaller->InstallEvents() && $bSuccess;
					$bSuccess = $DepModInstaller->InstallTasks() && $bSuccess;
					if( method_exists($DepModInstaller, 'InstallData') ) {
						$bSuccess = $DepModInstaller->InstallData() && $bSuccess;
					}
					if( $bSuccess ) {
						if( !IsModuleInstalled($depModID) ) {
							RegisterModule($depModID);
						}
					}
					else {
						if( method_exists($DepModInstaller, 'getErrors') ) {
							$arInstallErrors = $DepModInstaller->getErrors();
							foreach($arInstallErrors as $error) {
								$this->arErrors[] = 'Install dependency error '.$depModID.': '.$error;
							}
						}
						$this->bSuccessInstallDeps = false;
					}
				}
			}
			else {
				if( !is_file($depModInstallerFile) ) {
					$this->bSuccessInstallDeps = false;
					$this->arErrors[] = 'Dependency installer not found ('.BX_ROOT.'/modules/'.$depModID.')';
				}
				else {
					require_once $depModInstallerFile;
					/** @var CModule $DepModInstaller */
					$DepModInstaller = new $depModClass;

					$depInstallModulePath = $_SERVER['DOCUMENT_ROOT'].BX_ROOT
						.'/modules/'.$this->MODULE_ID
						.'/install/modules/'.$depModID
					;
					$depInstallModuleFolder = BX_ROOT
						.'/modules/'.$this->MODULE_ID
						.'/install/modules/'.$depModID
					;
					if( !is_dir($depInstallModulePath) ) {
						continue;
					}
					$depInstallDir = opendir($depInstallModulePath);
					$arUpdates = array();
					while($depInsFSEntry = readdir($depInstallDir)) {
						if($depInsFSEntry == '.' || $depInsFSEntry == '..') continue;
						if( strpos($depInsFSEntry, 'update-') !== false
							&& is_dir($depInstallModulePath.'/'.$depInsFSEntry)
						) {
							$arUpdateVersion = self::readVersion($depInsFSEntry);
							$arCurrentModuleVersion = self::readVersion($DepModInstaller->MODULE_VERSION);
							if(
								!empty($arUpdateVersion) && !empty($arCurrentModuleVersion)
								&& $arUpdateVersion['RAW_VERSION'] > $arCurrentModuleVersion['RAW_VERSION']
							) {
								$arUpdates[] = $depInsFSEntry;
							}
						}
					}
					closedir($depInstallDir);
					if( !empty($arUpdates) ) {
						uasort($arUpdates, array($this, 'compareVersions'));
						foreach($arUpdates as $updateFolder) {
							$strErrors = '';
							if(is_file($depInstallModulePath.'/'.$updateFolder.'/updater.dep.php')) {
								$GLOBALS['__runAutoGenUpdater'] = true;
								/** @noinspection PhpDynamicAsStaticMethodCallInspection */
								CUpdateSystem::AddMessage2Log('Run updater of Dependency '.$depModID);
								/** @noinspection PhpDynamicAsStaticMethodCallInspection */
								CUpdateSystem::RunUpdaterScript(
									$depInstallModulePath.'/'.$updateFolder.'/updater.dep.php',
									$strErrors,
									$depInstallModuleFolder.'/'.$updateFolder,
									$depModID
								);
								unset($GLOBALS['__runAutoGenUpdater']);
							}
							else {
								$strErrors .= 'Dependency updater-script not found('.$depInstallModuleFolder.'/'.$updateFolder.'/updater.dep.php'.')'."\n";
							}
							if(strlen($strErrors)>0) {
								$logError = 'Update dependency error '.$depModID.': '."\n".$strErrors;
								$this->arErrors[] = $logError;
								/** @noinspection PhpDynamicAsStaticMethodCallInspection */
								CUpdateSystem::AddMessage2Log($logError);
								$this->bSuccessInstallDeps = false;
							}
						}
					}
				}
			}
		}
		return $this->bSuccessInstallDeps;
	}

	public function UnInstallDeps() {
		$arDepsList = $this->getDepsList();
		foreach($arDepsList as $depModID => $depModClass) {
			$depModInstallerFile = $this->bxModulesDir."/".$depModID."/install/index.php";
			if( is_file($depModInstallerFile) ) {
				require_once $depModInstallerFile;
				/** @var CModule $DepModInstaller */
				$bSuccess = true;
				$DepModInstaller = new $depModClass;
				$bSuccess = true;
				$bSuccess = $DepModInstaller->UnInstallTasks() && $bSuccess;
				$bSuccess = $DepModInstaller->UnInstallEvents() && $bSuccess;
				$bSuccess = $DepModInstaller->UnInstallFiles() && $bSuccess;
				$bSuccess = $DepModInstaller->UnInstallDB() && $bSuccess;
				if( $bSuccess ) {
					if( IsModuleInstalled($depModID) ) {
						UnRegisterModule($depModID);
					}
					$this->bSuccessUnInstallDeps = true;
				}
				else {
					if( method_exists($DepModInstaller, 'getErrors') ) {
						$arInstallErrors = $DepModInstaller->getErrors();
						foreach($arInstallErrors as $error) {
							$this->arErrors[] = $error;
						}
					}
					$this->bSuccessUnInstallDeps = false;
				}
			}
		}
		return $this->bSuccessUnInstallDeps;
	}

	protected function getDepsList() {
		$arDepsList = array();
		if( is_dir($this->installDir."/modules") && is_file($this->installDir.'/dependencies.php') ) {
			$arDepsList = require $this->installDir.'/dependencies.php';
		}
		return $arDepsList;
	}

	public function InstallData() {
		$this->explicitIncludeModuleClasses();


		Currency::add(array(
			'CURRENCY' => 'RUB',
			'SORT' => '10'
		));
		Currency::add(array(
			'CURRENCY' => 'USD',
			'SORT' => '50'
		));
		Currency::setDefault('RUB');
		CurrencyFormat::add(array(
			'CURRENCY' => 'RUB',
			'NAME' => GetMessage('OBX_MARKET_INS_CURRRENCY_RUB'),
			'LANGUAGE_ID' => 'ru',
			'FORMAT' => GetMessage('OBX_MARKET_INS_CURRRENCY_RUB_FORMAT'),
			'THOUSANDS_SEP' => ' ',
		));
		CurrencyFormat::add(array(
			'CURRENCY' => 'RUB',
			'NAME' => 'Roubles',
			'LANGUAGE_ID' => 'en',
			'FORMAT' => '# Rub.',
			'THOUSANDS_SEP' => '\'',
		));
		CurrencyFormat::add(array(
			'CURRENCY' => 'USD',
			'NAME' => GetMessage('OBX_MARKET_INS_CURRRENCY_USD'),
			'LANGUAGE_ID' => 'ru',
			'FORMAT' => GetMessage('OBX_MARKET_INS_CURRRENCY_USD_FORMAT'),
			'THOUSANDS_SEP' => ' ',
		));
		CurrencyFormat::add(array(
			'CURRENCY' => 'USD',
			'NAME' => 'US Dollars',
			'LANGUAGE_ID' => 'en',
			'FORMAT' => '$#',
			'THOUSANDS_SEP' => '\'',
		));

		$priceID = Price::add(array(
			'CODE' => 'PRICE',
			'NAME' => GetMessage('OBX_MARKET_INS_BASE_PRICE'),
			'CURRENCY' => 'RUB',
			'SORT' => 10
		));
		Price::add(array(
			'CODE' => 'WHOLESALE',
			'NAME' => GetMessage('OBX_MARKET_INS_WHOLESALE_PRICE'),
			'CURRENCY' => 'RUB',
			'SORT' => 20
		));
		Price::setGroupList($priceID, array(2));
		OrderStatus::add(array(
			'CODE' => 'ACCEPTED',
			'NAME' => GetMessage('OBX_MARKET_INS_ORDER_STATUS_ACCEPTED'),
			'SORT' => '10',
			'IS_SYS' => 'Y',
			OBX_MAGIC_WORD => 'Y'
		));
		OrderStatus::add(array(
			'CODE' => 'COMPLETE',
			'NAME' => GetMessage('OBX_MARKET_INS_ORDER_STATUS_COMPLETE'),
			'COLOR' => '77D26B',
			'SORT' => '1000',
			'IS_SYS' => 'Y',
			OBX_MAGIC_WORD => 'Y'
		));
		OrderStatus::add(array(
			'CODE' => 'CANCELED',
			'NAME' => GetMessage('OBX_MARKET_INS_ORDER_STATUS_CANCELED'),
			'COLOR' => 'D0D0D0',
			'SORT' => '10000',
			'IS_SYS' => 'Y',
			OBX_MAGIC_WORD => 'Y'
		));
		OrderProperty::add(array(
			'CODE' => 'IS_PAID',
			'NAME' => GetMessage('OBX_MARKET_INS_ORDER_PROP_IS_PAID'),
			'DESCRIPTION' => GetMessage('OBX_MARKET_INS_ORDER_PROP_IS_PAID_DESCR'),
			'SORT' => 100,
			'PROPERTY_TYPE' => 'C',
			'ACTIVE' => 'Y',
			'ACCESS' => 'R',
			'IS_SYS' => 'Y',
			OBX_MAGIC_WORD => 'Y'
		));
		$deliveryPropID = OrderProperty::add(array(
			'CODE' => 'DELIVERY',
			'NAME' => GetMessage('OBX_MARKET_INS_ORDER_PROP_DELIVERY'),
			'DESCRIPTION' => GetMessage('OBX_MARKET_INS_ORDER_PROP_DELIVERY_DESCR'),
			'SORT' => 100,
			'PROPERTY_TYPE' => 'L',
			'ACTIVE' => 'Y',
			'ACCESS' => 'W',
			'IS_SYS' => 'Y',
			OBX_MAGIC_WORD => 'Y'
		));
		if($deliveryPropID>0) {
			OrderPropertyEnum::add(array(
				'CODE' => '1',
				'PROPERTY_ID' => $deliveryPropID,
				'VALUE' => GetMessage('OBX_MARKET_INS_ORDER_PROP_DELIVERY_ENUM_1'),
				'SORT' => '10'
			));
			OrderPropertyEnum::add(array(
				'CODE' => '2',
				'PROPERTY_ID' => $deliveryPropID,
				'VALUE' => GetMessage('OBX_MARKET_INS_ORDER_PROP_DELIVERY_ENUM_2'),
				'SORT' => '20'
			));
		}
		$payMethodPropID = OrderProperty::add(array(
			'CODE' => 'PAYMENT',
			'NAME' => GetMessage('OBX_MARKET_INS_ORDER_PROP_PAYMENT'),
			'DESCRIPTION' => GetMessage('OBX_MARKET_INS_ORDER_PROP_PAYMENT_DESCR'),
			'SORT' => 100,
			'PROPERTY_TYPE' => 'L',
			'ACTIVE' => 'Y',
			'ACCESS' => 'W',
			'IS_SYS' => 'Y',
			OBX_MAGIC_WORD => 'Y'
		));
		if($payMethodPropID>0) {
			OrderPropertyEnum::add(array(
				'CODE' => '1',
				'PROPERTY_ID' => $payMethodPropID,
				'VALUE' => GetMessage('OBX_MARKET_INS_ORDER_PROP_PAYMENT_ENUM_1'),
				'SORT' => '10'
			));
		}
		$this->bSuccessInstallData = true;
		return $this->bSuccessInstallData;
	}
	public function UnInstallData() { $this->bSuccessUnInstallData = true; return $this->bSuccessUnInstallData; }


	protected function prepareDBConnection() {
		global $APPLICATION, $DB, $DBType;
		if (defined('MYSQL_TABLE_TYPE') && strlen(MYSQL_TABLE_TYPE) > 0) {
			$DB->Query("SET table_type = '" . MYSQL_TABLE_TYPE . "'", true);
		}
		if (defined('BX_UTF') && BX_UTF === true) {
			$DB->Query('SET NAMES "utf8"');
			//$DB->Query('SET sql_mode=""');
			$DB->Query('SET character_set_results=utf8');
			$DB->Query('SET collation_connection = "utf8_unicode_ci"');
		}
	}

	public function registerModule() {
		if( !IsModuleInstalled($this->MODULE_ID) ) {
			RegisterModule($this->MODULE_ID);
		}
	}
	public function unRegisterModule() {
		if( IsModuleInstalled($this->MODULE_ID) ) {
			UnRegisterModule($this->MODULE_ID);
		}
	}
	public function isInstalledModule() {
		return IsModuleInstalled($this->MODULE_ID);
	}

	static public function getModuleCurDir() {
		static $modCurDir = null;
		if ($modCurDir === null) {
			$modCurDir = str_replace("\\", "/", __FILE__);
			// 18 = strlen of "/install/index.php"
			$modCurDir = substr($modCurDir, 0, strlen($modCurDir) - 18);
		}
		return $modCurDir;
	}
	static public function readVersion($version) {
		$regVersion = (
			'~^'.(
				'(?:'.(
					'('.(
						'(?:[a-zA-Z0-9]{1,}\.)?'
						.'(?:[a-zA-Z0-9]{1,})'
					).')'
					.'\-'
				).')?'
				.'([\d]{1,2})\.([\d]{1,2})\.([\d]{1,2})'.'(?:\-r([\d]{1,4}))?'
			).'$~'
		);
		$arVersion = array();
		if( preg_match($regVersion, $version, $arMatches) ) {
			$arVersion['NAME'] = $arMatches[1];
			$arVersion['MAJOR'] = $arMatches[2];
			$arVersion['MINOR'] = $arMatches[3];
			$arVersion['FIXES'] = $arMatches[4];
			$arVersion['REVISION'] = 0;
			$arVersion['VERSION'] = $arMatches[2].'.'.$arMatches[3].'.'.$arMatches[4];
			if($arMatches[5]) {
				$arVersion['REVISION'] = $arMatches[5];
				$arVersion['VERSION'] .= '-r'.$arVersion['REVISION'];
			}
			$arVersion['RAW_VERSION'] =
				($arVersion['MAJOR'] *   1000000000)
				+ ($arVersion['MINOR'] * 10000000)
				+ ($arVersion['FIXES'] * 10000)
				+ ($arVersion['REVISION'])
			;
		}
		return $arVersion;
	}

	static public function compareVersions($versionA, $versionB) {
		$arVersionA = self::readVersion($versionA);
		$arVersionB = self::readVersion($versionB);
		if($arVersionA['RAW_VERSION'] == $arVersionB['RAW_VERSION']) return 0;
		return ($arVersionA['RAW_VERSION'] < $arVersionB['RAW_VERSION'])? -1 : 1;
	}
	static public function includeLangFile() {
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $MESS;
		/** @noinspection PhpIncludeInspection */
		@include(self::getModuleCurDir().'/lang/'.LANGUAGE_ID.'/install/index.php');
	}
}
