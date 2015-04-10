<?php
/**
 * @product OpenBX:Market Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */


namespace OBX\Market;

use OBX\Core\DBSimple\EntityStatic;


class BasketList extends EntityStatic {}
BasketList::__initEntity(BasketDBS::getInstance());