<?php
/**
 * 2016-2018 ROANJA.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author Romell Jaramillo <info@roanja.com>
 *  @copyright 2016-2018 ROANJA.COM
 *  @license GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 */

// namespace Roanja\Module\RjCarrier\Controller\Admin;

class AdminRjModuleController extends \ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        \Tools::redirectAdmin(\Context::getContext()->link->getAdminLink('AdminModules').'&configure=rj_carrier');
    }
}
