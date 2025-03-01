<?php

/**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Roanja
 *  @copyright 2024 Roanja
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

 use Roanja\Module\RjCarrier\Model\RjcarrierCompany;

class AdminRjCarrierCompanyController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'rj_carrier_company';
        $this->className = 'Roanja\Module\RjCarrier\Model\RjcarrierCompany';
        $this->lang = false;
        $this->deleted = false;
        $this->identifier = 'id_carrier_company';
        $this->bootstrap = true;

        parent::__construct();

        $this->getFieldsList();
    }

    protected function getFieldsList()
    {
        // Definir los campos que se mostrarán en la lista
        $this->fields_list = [
            'id_carrier_company' => ['title' => 'ID', 'align' => 'center', 'class' => 'fixed-width-xs'],
            'name' => ['title' => 'Name', 'align' => 'left'],
            'shortname' => ['title' => 'Shortname', 'align' => 'left'],
            'icon' => [
                'title' => 'Icon',
                'align' => 'center',
                'callback' => 'displayIcon',
                'orderby' => false,
                'search' => false
            ],
            'date_add' => ['title' => 'Date Added', 'align' => 'center'],
            'date_upd' => ['title' => 'Last Updated', 'align' => 'center'],
        ];

        // Permitir acciones CRUD
        $this->addRowAction('edit');
        $this->addRowAction('delete');
    }

    /**
     * Renderizar el formulario para crear o editar una compañía de transporte.
     */
    public function renderForm()
    {
        $this->fields_form = [
            'legend' => ['title' => 'Carrier Company Configuration', 'icon' => 'icon-cogs'],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'Name',
                    'name' => 'name',
                    'required' => true,
                    'col' => 4
                ],
                [
                    'type' => 'text',
                    'label' => 'Shortname',
                    'name' => 'shortname',
                    'required' => true,
                    'col' => 2
                ],
                [
                    'type' => 'text',
                    'label' => 'Icon URL',
                    'name' => 'icon',
                    'col' => 6
                ]
            ],
            'submit' => ['title' => 'Save']
        ];

        return parent::renderForm();
    }

    /**
     * Callback para mostrar el icono en la tabla
     */
    public function displayIcon($icon)
    {
        if (!empty($icon)) {
            return '<img src="' . $icon . '" alt="Carrier Icon" style="width:50px;height:50px;">';
        }
        return '-';
    }
}
