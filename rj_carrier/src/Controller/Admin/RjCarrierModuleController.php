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

namespace Roanja\Module\RjCarrier\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RjCarrierModuleController.
 *
 * @ModuleActivated(moduleName="rj_carrier", redirectRoute="admin_module_manage")
 */
class RjCarrierModuleController extends FrameworkBundleAdminController
{

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     */
    public function indexAction()
    {
        \Tools::redirectAdmin(\Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'rj_carrier']));
    }
}
