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

if (!defined('_PS_VERSION_')) {
    # module validation
    exit;
}

class AdminTDModuleController extends ModuleAdminControllerCore
{
    public function __construct()
    {
        parent::__construct();
        // $url = 'index.php?controller=AdminModules&configure=rj_topdormitorios&token=' . Tools::getAdminTokenLite('AdminModules');
        // Tools::redirectAdmin($url);

        \Tools::redirectAdmin(\Context::getContext()->link->getAdminLink('AdminModules').'&configure=rj_topdormitorios');
    }
}
