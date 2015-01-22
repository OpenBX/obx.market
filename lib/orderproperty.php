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
use OBX\Core\DBSimple\EntityStatic;
/**
 * Class OrderProperty
 * @package OBX\Market
 * @method @static OrderProperty getInstance
 */
class OrderProperty extends EntityStatic {}
OrderProperty::__initEntity(OrderPropertyDBS::getInstance());
