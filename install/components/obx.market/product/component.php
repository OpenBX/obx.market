<?
/***********************************************
 ** @product OBX:Market Bitrix Module         **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arParams["USE_FILTER"]=="Y")
{
	if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
		$arParams["FILTER_NAME"] = "arrFilter";
}
else
	$arParams["FILTER_NAME"] = "";

$arDefaultUrlTemplates404 = array(
	"sections" => "",
	"section" => "#SECTION_ID#/",
	"element" => "#SECTION_ID#/#ELEMENT_ID#/",
	"action" => "action/#ACTION_CODE#",
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"SECTION_ID",
	"SECTION_CODE",
	"ELEMENT_ID",
	"ELEMENT_CODE",
	"ACTION_CODE",
);

if($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	$b404 = false;
	if(!$componentPage)
	{
		$componentPage = "sections";
		$b404 = true;
	}

	if(
		$componentPage == "section"
		&& isset($arVariables["SECTION_ID"])
		&& intval($arVariables["SECTION_ID"])."" !== $arVariables["SECTION_ID"]
	)
		$b404 = true;

	if($b404 && $arParams["SET_STATUS_404"]==="Y")
	{
		$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
		if ($folder404 != "/")
			$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
		if (substr($folder404, -1) == "/")
			$folder404 .= "index.php";

			if($folder404 != $APPLICATION->GetCurPage(true))
			CHTTP::SetStatus("404 Not Found");
	}

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}
else
{
	$arVariables = array();

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if(isset($arVariables["ACTION_CODE"]) && strlen($arVariables["ACTION_CODE"]) > 0) {
		$componentPage = "action";
	}
	elseif(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0) {
		$componentPage = "element";
	}
	elseif(isset($arVariables["ELEMENT_CODE"]) && strlen($arVariables["ELEMENT_CODE"]) > 0) {
		$componentPage = "element";
	}
	elseif(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0) {
		$componentPage = "section";
	}
	elseif(isset($arVariables["SECTION_CODE"]) && strlen($arVariables["SECTION_CODE"]) > 0) {
		$componentPage = "section";
	}
	else {
		$componentPage = "sections";
	}

	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => Array(
			"section" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#",
			"element" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"."&".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#",
			"action" => htmlspecialchars($APPLICATION->GetCurPage())."?".$arVariableAliases["ACTION_CODE"]."=#ACTION_CODE#",
		),
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}

$this->IncludeComponentTemplate($componentPage);
?>