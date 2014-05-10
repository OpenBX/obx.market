<?php
/******************************************
 ** @product OpenBX:Market Bitrix Module **
 ** @authors                             **
 **         Maksim S. Makarov            **
 ** @license Affero GPLv3                **
 ** @mailto rootfavell@gmail.com         **
 ** @copyright 2013 DevTop               **
 ******************************************/
namespace OBX\Market;
function tr($name, $aReplace=false) {
	return GetMessage($name, $aReplace=false);
}