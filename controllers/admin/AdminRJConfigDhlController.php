<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the GNU General Public License, version 3 (GPL-3.0).
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* @author    Roanja www.roanja.com <info@roanja.com>
* @copyright 2021 Roanja.com
* @license   https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
*/
class AdminRJConfigDhlController extends ModuleAdminController
{

    public function initContent()
    {
        $this->title = $this->module->l('My module title');
    }

}