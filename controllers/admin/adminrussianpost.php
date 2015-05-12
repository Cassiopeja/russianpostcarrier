<?php

class AdminRussianPostController extends ModuleAdminController {

    public function __construct() {

        $this->table = 'russian_post';
        $this->className = 'RussianPost';
        $this->identifier = 'id';

        //$this->context = Context::getContext();
        $this->bootstrap	=	true;
        parent::__construct();

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            )
        );

        $this->fields_list = array(
            'id' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 20
            ),
            'id_state' => array(
                'title' => $this->l('State'),
                'width' => 'auto',
                'callback' => 'getStateName',
            ),
            'id_post_zone' => array(
                'title' => $this->l('Zone'),
                'width' => 'auto'
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center'
            ),
        );

        $this->fields_options = array(
            'configuration_options' => array(
                'title' => $this->l('Common module configuration'),
                'fields' => array(
                    'RUSSIANPOST_MAX_WEIGHT' => array(
                        'title' => $this->l('Maximum weight of the parcel (kg)'),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isUnsignedFloat',
                    ),
                    'RUSSIANPOST_PONDROUS_WEIGHT' => array(
                        'title' => $this->l('Pondreous parcel (kg)'),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isUnsignedFloat'
                    ),
                    'RUSSIANPOST_INSURED_VALUE' => array(
                        'title' => $this->l('The fee for the amount of the insured value parcels'),
                        'cast' => 'intval',
                        'type' => 'text',
                        'validation' => 'isPercentage',
                        'suffix' => '%'
                    ),
                ),
                'submit' => array(
                    'title'	=>	$this->l('Save'),
                ),
            ),
            'zones_base_price' => array(
                'title' => $this->l('Base price for 0.5 kg parcel'),
                'icon' => 'delivery',
                'fields' => array(
                    'RUSSIANPOST_ZONE1_BASE_PRICE' => array(
                        'title' => $this->l('Zone 1'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_ZONE2_BASE_PRICE' => array(
                        'title' => $this->l('Zone 2'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_ZONE3_BASE_PRICE' => array(
                        'title' => $this->l('Zone 3'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_ZONE4_BASE_PRICE' => array(
                        'title' => $this->l('Zone 4'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_ZONE5_BASE_PRICE' => array(
                        'title' => $this->l('Zone 5'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                ),
                'submit' => array(
                    'title'	=>	$this->l('Save'),
                ),
            ),
            'zones_additional_weight_cost' => array(
                'title' => $this->l('Cost of each additional 0.5 kg of parcel'),
                'fields' => array(
                    'RUSSIANPOST_ZONE1_ADD_PRICE' => array(
                        'title' => $this->l('Zone 1'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_ZONE2_ADD_PRICE' => array(
                        'title' => $this->l('Zone 2'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_ZONE3_ADD_PRICE' => array(
                        'title' => $this->l('Zone 3'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_ZONE4_ADD_PRICE' => array(
                        'title' => $this->l('Zone 4'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_ZONE5_ADD_PRICE' => array(
                        'title' => $this->l('Zone 5'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                ),
                'submit' => array(
                    'title'	=>	$this->l('Save'),
                ),
            ),
            'money_transfer_constant' => array(
                'title' => $this->l('Fee for money transfer. Constant.'),
                'fields' => array(
                    'RUSSIANPOST_MONEY_TRANSF_CONST1' => array(
                        'title' => $this->l('Range 1'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_MONEY_TRANSF_CONST2' => array(
                        'title' => $this->l('Range 2'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_MONEY_TRANSF_CONST3' => array(
                        'title' => $this->l('Range 3'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                    'RUSSIANPOST_MONEY_TRANSF_CONST4' => array(
                        'title' => $this->l('Range 4'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'floatval',
                        'type' => 'text',
                        'validation' => 'isPrice'
                    ),
                ),
                'submit' => array(
                    'title'	=>	$this->l('Save'),
                ),
            ),
            'money_transfer_percent' => array(
                'title' => $this->l('Fee for money transfer. Percent.'),
                'fields' => array(
                    'RUSSIANPOST_MONEY_TRANSF_PERC1' => array(
                        'title' => $this->l('Range 1'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'intval',
                        'type' => 'text',
                        'validation' => 'isPercentage',
                        'suffix' => '%'
                    ),
                    'RUSSIANPOST_MONEY_TRANSF_PERC2' => array(
                        'title' => $this->l('Range 2'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'intval',
                        'type' => 'text',
                        'validation' => 'isPercentage',
                        'suffix' => '%'
                    ),
                    'RUSSIANPOST_MONEY_TRANSF_PERC3' => array(
                        'title' => $this->l('Range 3'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'intval',
                        'type' => 'text',
                        'validation' => 'isPercentage',
                        'suffix' => '%'
                    ),
                    'RUSSIANPOST_MONEY_TRANSF_PERC4' => array(
                        'title' => $this->l('Range 4'),
                        'suffix' => $this->context->currency->getSign(),
                        'cast' => 'intval',
                        'type' => 'text',
                        'validation' => 'isPercentage',
                        'suffix' => '%'
                    ),
                ),
                'submit' => array(
                    'title'	=>	$this->l('Save'),
                ),
            )
        );
    }

    /*******************************************
     * Form to add new
     * */

    public function renderForm() {

        $this->fields_form = array(
            'legend' => array(
                'title' => 'Legend',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Country'),
                    'name' => 'id_country',
                    'options' => array(
                        'query' => Country::getCountries($this->context->language->id, true, true),
                        'id' => 'id_country',
                        'name' => 'name',
                    //'default' => array('value'=>$this->context->country->id, 'label'=>$this->l($this->context->country->name)),//array() or value???
                    ),
                    'required' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('State'),
                    'name' => 'id_state',
                    'required' => true,
                    'options' => array(
                        'query' => State::getStates(),
                        'id' => 'id_state',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => 'Tariff Zone',
                    'name' => 'id_post_zone',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 1,
                                'value' => 'Zone 1'
                            ),
                            array(
                                'id' => 2,
                                'value' => 'Zone 2'
                            ),
                            array(
                                'id' => 3,
                                'value' => 'Zone 3'
                            ),
                            array(
                                'id' => 4,
                                'value' => 'Zone 4'
                            ),
                            array(
                                'id' => 5,
                                'value' => 'Zone 5'
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'value'
                    ),
                    'required' => true
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Status'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ),
                    ),
                    'desc' => $this->l('Enable delivery to this Country/State'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button',
            ),
        );

        return parent::renderForm();
    }

    public function postProcess() {
        parent::postProcess();
    }

    public function processSave() {
        parent::processSave();
    }

    public function initProcess() {
        parent::initProcess();
    }

    protected function processUpdateOptions() {
        parent::processUpdateOptions();
    }

    public function getStateName($echo, $row) {
        $id_state = $row['id_state'];

        $state = new State($id_state);
        $cn = new Country(177);

        if ($state->id) {
            $country = Country::getNameById(Context::getContext()->language->id, $state->id_country);
            return "{$state->name} ({$country})";
        }

        return $this->l('Out of the World');
    }

    public function renderList() {
        $this->tpl_list_vars['postZones'] = array(
            array(
                'id_post_zone' => 1,
                'name' => $this->l('Zone 1'),
            ),
            array(
                'id_post_zone' => 2,
                'name' => $this->l('Zone 2'),
            ),
            array(
                'id_post_zone' => 3,
                'name' => $this->l('Zone 3'),
            ),
            array(
                'id_post_zone' => 4,
                'name' => $this->l('Zone 4'),
            ),
            array(
                'id_post_zone' => 5,
                'name' => $this->l('Zone 5'),
            ),
        );

        return parent::renderList();
    }

}
