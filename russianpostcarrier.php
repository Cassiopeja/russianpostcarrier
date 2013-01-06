<?php

/**
 *
 * TODO: Разобраться с исключениями, они здесь просто напрашиваются
 *
 * */
if (!defined('_PS_VERSION_'))
    exit;

require_once(_PS_MODULE_DIR_ . 'russianpostcarrier/models/RussianPost.php');

class russianpostcarrier extends CarrierModule {

    private $model;
    // Хоть и неочевидно, но здесь это должно быть. Кем-то присваивается.
    public $id_carrier;

    public function __construct() {

        $this->name = 'russianpostcarrier';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.6';
        $this->author = 'Serge Rodovnichenko';

        parent::__construct();

        $this->displayName = $this->l('Russian Post');
        $this->description = $this->l('Calculate a shipping cost using Russian Post formulas');

        $this->RussianPost = new RussianPost();

        $this->carrierIdStr = 'SR_RUSSIAN_POST_CARRIER_ID';
        $this->carrierCODIdStr = 'SR_RUSSIAN_POST_CARRIER_COD_ID';
    }

    public function getOrderShippingCost($params, $shipping_cost) {

        //В $params лежит тупо объект типа Cart. ВРОДЕ БЫ. Может, не всегда?
        //Параметр $params содержит в виде массива cart, customer и address. 
        //$shipping_cost - стоимость доставки расчитанная стандартным методом 
        //(через таблицы зависимости от региона и диапазонов)
        $is_COD = false;
        if ($this->id_carrier != (int) Configuration::get('SR_RUSSIAN_POST_CARRIER_ID'))
            {
                if ($this->id_carrier != (int) Configuration::get('SR_RUSSIAN_POST_CARRIER_COD_ID'))
                    return false;
                else
                    $is_COD = true;
            }


        $addr = new Address($params->id_address_delivery);

        // TODO: проверить куки, а не рубить с плеча!
        if (!Validate::isLoadedObject($addr))
            return false;

        $rp_zone = $this->RussianPost->getRpZone($addr);
        if ($rp_zone == 0)
            return false;

        $weight = $params->getTotalWeight();

        // Цена за первые полкило
        $base_price = Configuration::get("RUSSIANPOST_ZONE{$rp_zone}_BASE_PRICE");
        $additional_half_kg_price = Configuration::get("RUSSIANPOST_ZONE{$rp_zone}_ADD_PRICE");

        //Сколько дополнительных "полкило" в товаре
        $add_parts = ceil((($weight < 0.5 ? 0.5 : $weight) - 0.5) / 0.5);

        $price = $base_price + $add_parts * $additional_half_kg_price;

        // Тяжеловесная посылка, +30%
        if ($weight >= Configuration::get("RUSSIANPOST_PONDROUS_WEIGHT"))
            $price = $price * 1.3;

        // если это объект типа Cart, то должен быть этот метод
        // Cart::BOTH_WITHOUT_SHIPPING — надеюсь, что это стоимость продуктов
        // вместе со скидками
        $orderTotal = $params->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);

        // Страховой тариф за объявленную стоимость. Кто не страхует, тот … ставит 0
        // Страховать будем на стоимость заказа (или надо заказ+доставка?)
        $price = $price + $orderTotal * Configuration::get("RUSSIANPOST_INSURED_VALUE") / 100;

        //Если есть затраты на упаковку добавляем их
        $price = $price + $shipping_cost;

        //Если доставка осуществляется наложенным платежом
        //Нужно включить сумму пересылки
        if ($is_COD) 
        {
            $cod_const = 0;
            $cod_perc = 0;
            $range_num = 0;
            $orderTotalDelivery = $orderTotal + $price;
            //Определяем в какой диапазон попадает сумма
            if ($orderTotalDelivery <= 1000)
                $range_num = 1;
            elseif ($orderTotalDelivery > 1000 and $price <= 5000)
                $range_num = 2;
            elseif ($orderTotalDelivery > 5000 and $price <= 20000)
                $range_num = 3;
            elseif ($orderTotalDelivery > 20000 and $price <= 500000)
                $range_num = 4;
            else
                return false;

            $cod_const = Configuration::get("RUSSIANPOST_MONEY_TRANSF_CONST{$range_num}");
            $cod_perc  = Configuration::get("RUSSIANPOST_MONEY_TRANSF_PERC{$range_num}");

            $price = $price + $orderTotalDelivery * $cod_perc / 100 + $cod_const;
        }

        return $price;
    }

    public function getOrderShippingCostExternal($params) {

        // Как показала практика, этот метод вообще непонятно когда вызывается
        return $this->getOrderShippingCost($params, 0);
    }

    public function install() {

        // ID нашей несчастной почты в системе
        $idCarrier = $this->installCarrier();

        $res = false;

        // Не удалось создать, то и все остальное не уперлось
        // Хорошо бы Exceptions употреблять. Потом, с ними
        // еще разбираться надо
        if (!$idCarrier) {

            return false;
        }

        //Создание перевозчика с наложенным платежом
        $idCarrierCOD = $this->installCarrier(1);
        if (!$idCarrierCOD) {

            return false;
        }


        if (!$this->RussianPost->createTable()) {

            $this->uninstallCarrier($idCarrier);
            $this->uninstallCarrier($idCarrierCOD);
        }

        // Здесь мы создаем пункт вехнего подменю.
        // Сначала проверим, есть-ли оно уже
        $idTab = Tab::getIdFromClassName('AdminRussianPost');
        // Если нет, создадим
        // TODO: поработать с этим куском
        if (!$idTab) {
            $tab = new Tab();
            $tab->class_name = 'AdminRussianPost';
            $tab->module = 'russianpostcarrier';
            $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentShipping');

            $languages = Language::getLanguages(false);

            foreach ($languages as $lang) {
                $tab->name[(int) $lang['id_lang']] = 'Russian Post';
            }

         
            if (!$tab->save()) {
                // Если что-то пошло не так, удалим перевозчика и закруглимся
                $this->uninstallCarrier($idCarrier);
                $this->uninstallCarrier($idCarrierCOD);
                return $this->_abortInstall($this->l('Unable to create the "Russian Post" tab'));
            }
        } else {
            $tab = new Tab((int) $idTab);
        }

        //Обновляем в БД tab id вкладки "Russian Post" или завершаем с ошибкой
        if (Validate::isLoadedObject($tab))
            Configuration::updateValue('SR_RUSSIAN_POST_TAB_ID', $tab->id);
        else
            return $this->_abortInstall($this->l('Unable to load the "Russian Post" tab'));

        // Если родительский метод не срабатывает, то все удаляем,
        // и самоустраняемся
        if (!parent::install() OR !$this->registerHook('ActionCarrierUpdate')) {
            parent::uninstall();

            $this->uninstallTab($tab->id);
            $this->uninstallCarrier($idCarrier);
            $this->uninstallCarrier($idCarrierCOD);
            $this->RussianPost->dropTable();

            return $this->_abortInstall($this->l('Unable to invoke parent method install or registerHook "ActionCarrierUpdate" failed'));
        }

        // Нам будут полезны ID пункта меню и перевозчика
        // TODO: Некисло и результат этой операции проверять, конечно
        Configuration::updateValue('SR_RUSSIAN_POST_CARRIER_ID', $idCarrier);
        Configuration::updateValue('SR_RUSSIAN_POST_CARRIER_COD_ID', $idCarrierCOD);

        return true;
    }

    // TODO: подумать, что и как. Оно должно деинсталлироваться, даже если\
    // возникли какие-то ошибки
    public function uninstall() {

        $res = true;

        $res = $this->unregisterHook('ActionCarrierUpdate');
        $res = $this->uninstallTab();
        //Просто почта России
        $res = $this->uninstallCarrier($this->carrierId(0));
        //Почта России наложенный платеж
        $res = $this->uninstallCarrier($this->carrierId(1));
        $res = $this->RussianPost->dropTable();

        Configuration::updateValue('SR_RUSSIAN_POST_TAB_ID', NULL);
        Configuration::updateValue('SR_RUSSIAN_POST_CARRIER_ID', NULL);
        Configuration::updateValue('SR_RUSSIAN_POST_CARRIER_COD_ID', NULL);

        if (!$res || !parent::uninstall())
            return false;

        return true;
    }

    // Хук на обновление информации о перевозчике
    public function hookActionCarrierUpdate($params) {

        if ((int) $params['id_carrier'] == (int) Configuration::get('SR_RUSSIAN_POST_CARRIER_ID')) {
            Configuration::updateValue('SR_RUSSIAN_POST_CARRIER_ID', (int) $params['carrier']->id);
        }
        
        if ((int) $params['id_carrier'] == (int) Configuration::get('SR_RUSSIAN_POST_CARRIER_COD_ID')) {
            Configuration::updateValue('SR_RUSSIAN_POST_CARRIER_COD_ID', (int) $params['carrier']->id);
        }
    }

    /*     * **
     * Добавление нового перевозчика вынес в отдельный метод, чтоб не мусорить
     * $is_COD - если равен 1, то создается перевозчик Почта России наложенный 
     * платеж
     *
     * */

    private function installCarrier($is_COD = 0) {


        $carrier = new Carrier();

        $carrier->name = 'Почта России';

        //Проверяем какого перевозчика создаем
        if ($is_COD)
            $carrier->name .= ' (наложенный платеж)';

        // @deprecated since 1.5.0
        //$carrier->id_tax_rules_group' = 0;
        // TODO: проверить -- это точно обязательно?
        //Способ доставки активен
        $carrier->active = true;

        // TODO: проверить -- это точно обязательно?
        //Не удален. При удалении способа доставки он не удаляется из
	//базы данных, а только помечается как удаленный
        $carrier->deleted = 0;

        // TODO: это может быть интересным -- стоимость упаковки и пр.
        //Доставка и обработка
        $carrier->shipping_handling = false;

        // Что делать, если Out Of Range. 0 -- считать, 1 -- не считать
        // Мы, ведь, сами определяем можем или не можем, настроек range
        // никаких не будет.
        //Исключения: применить наибольшую цену доставки
        $carrier->range_behavior = 0;
        
        // Тут зависимости от языка
        // TODO: по идее это время доставки, но для разных пунктов
        // оно может сильно отличасться. Посмотреть, можно-ли это как-то
        // динамически править
        $delay_str_list = array('ru'=>'Срок доставки зависит от удаленности', 'default'=>'Delivery time depens on distance');
        $languages = Language::getLanguages(false);
        foreach ($languages as $language)
        {
            //Проверяем есть ли текущий язык в списке предусмотренных языков
            if (!isset($delay_str_list[$language['iso_code']]))
                //Если нет, то ставим фразу по умолчанию
                $carrier->delay[(int) $language['id_lang']] = $delay_str_list['default'];
            else
                //Иначе берем фразу из конфига
                $carrier->delay[(int) $language['id_lang']] = $delay_str_list[$language['iso_code']];
        }

        // Этот перевозчик связан с модулем
        //Расчет производится из внешнего источника
        $carrier->shipping_external = true;
        //Признак того, что способ доставки принадлежит модулю
        $carrier->is_module = true;
        //Имя модуля, которому принадлежит способ доставки
        $carrier->external_module_name = 'russianpostcarrier';

        // Если я правильно понял, то заданные лимиты
        // нам не уперлись, у нас внутре свои лимиты задаются
        // UPD: Но оно в этом случае, похоже, не работает. Придется задавать лимиты
        $carrier->need_range = true;

        // TODO: еще полезные переменные, мы ими потом займемся,
        // надо понять, как оно считает
        // $carrier->max_width => 1;
        // $carrier->max_height => 1;
        // $carrier->max_depth => 1;
        // $carrier->max_weight => 1; // вот это вот особенно!

        if ($carrier->add()) {

            // Добавим нашего несчастного перевозчика всем группам
            $groups = Group::getGroups(true);
            foreach ($groups as $group)
                Db::getInstance()->autoExecute(_DB_PREFIX_ . 'carrier_group', array(
                    'id_carrier' => (int) $carrier->id,
                    'id_group' => (int) $group['id_group']
                        ), 'INSERT');

            // Без указания пределов по весу и стоимости оно не заработало
            // Сделаем, хотя оно нам не надо

            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '100500';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '100500'; //Предельные тяжеловесные посылки 20 кг
            $rangeWeight->add();

            $zones = Zone::getZones(true);
            foreach ($zones as $z) {
                Db::getInstance()->autoExecute(_DB_PREFIX_ . 'carrier_zone', array('id_carrier' => (int) $carrier->id, 'id_zone' => (int) $z['id_zone']), 'INSERT');
                Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_ . 'delivery', array('id_carrier' => $carrier->id, 'id_range_price' => (int) $rangePrice->id, 'id_range_weight' => NULL, 'id_zone' => (int) $z['id_zone'], 'price' => '0'), 'INSERT');
                Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_ . 'delivery', array('id_carrier' => $carrier->id, 'id_range_price' => NULL, 'id_range_weight' => (int) $rangeWeight->id, 'id_zone' => (int) $z['id_zone'], 'price' => '0'), 'INSERT');
            }

            copy(dirname(__FILE__) . '/carrier.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');

            return $carrier->id;
        }

        return false;
    }

    private function uninstallTab() {

        $res = true;

        $idTab = Tab::getIdFromClassName('AdminRussianPost');

        if ($idTab) {
            $tab = new Tab((int) $idTab);
            $res = $tab->delete();
        }

        return $res;
    }

    private function uninstallCarrier($carrierId) {

        //$carrierId = $this->carrierId();

        if (!is_null($carrierId)) {
            $carrier = new Carrier($carrierId);

            $langDefault = (int) Configuration::get('PS_LANG_DEFAULT');

            $carriers = Carrier::getCarriers($langDefault, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);

            // Если наш перевозчик был по умолчанию, назначим кого-нибудь другого
            if (Configuration::get('PS_CARRIER_DEFAULT') == $carrierId) {

                foreach ($carriers as $c) {

                    if ($c['active'] && !$c['deleted'] && ($c['name'] != $carrier->name)) {

                        Configuration::updateValue('PS_CARRIER_DEFAULT', $c['id_carrier']);
                    }
                }
            }

            if (!$carrier->deleted) {
                $carrier->deleted = 1;
                if (!$carrier->update())
                    return false;
            }
        }

        return true;
    }

    private function carrierId($is_COD = 0, $val = NULL) {

        if (!is_null($val))
            if ($is_COD)
                Configuration::updateValue($this->carrierCODIdStr, $val);
            else
                Configuration::updateValue($this->carrierIdStr, $val);

        if ($is_COD)
            return Configuration::get($this->carrierCODIdStr);
        else
            return Configuration::get($this->carrierIdStr);
    }

    /**
    * Set installation errors and return false
    *
    * @param string $error Installation abortion reason
    * @return boolean Always false
    */
    protected function _abortInstall($error)
    {
            if (version_compare(_PS_VERSION_, '1.5.0.0 ', '>='))
                    $this->_errors[] = $error;
            else
                    echo '<div class="error">'.strip_tags($error).'</div>';

            return false;
    }
}
