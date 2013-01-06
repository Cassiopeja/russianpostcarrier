<h2>Russian Post</h2>
<form action="{$glob.url}" class="form" method="POST">
    <fieldset>
        <legend>Настройки</legend>
        <label for="rouble_id_input">Валюта, представляющая рубли</label>
        <div class="margin-form">
            <select name="rouble_id" id="rouble_id_input">
            {foreach from=$currencies item="currency"}
                <option value="{$currency.id_currency}">{$currency.name}</option>
            {/foreach}
            </select>
        </div>

        <label for="russia_id_input">Страна, Российская Федерация</label>
        <div class="margin-form">
            <select name="russia_id" id="russia_id_input">
            {foreach from=$countries item="country"}
                <option value="{$country.id_country}">{$country.name}</option>
            {/foreach}
            </select>
        </div>

        <label for="max_weight_input">Максимальный вес отправления</label>
        <div class="margin-form">
            <input type="text" value="20" id="max_weight_input" />
        </div>

        <label for="hard_weight_input">Вес усложненной тарификации</label>
        <div class="margin-form">
            <input type="text" value="10" id="hard_weight_input" />
        </div>

        <label for="declared_cost_price_input">Процент за объявленную стоимость</label>
        <div class="margin-form">
            <input type="text" value="4" id="declared_cost_price_input">
        </div>
    </fieldset>
    <br />
    <fieldset>
        <legend>Стоимость отправки посылки весом до 0.5 килограмм (включительно)</legend>

        <label for="zone_1_first_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_1_first_half_kgs_input" />
        </div>

        <label for="zone_2_first_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_2_first_half_kgs_input" />
        </div>

        <label for="zone_3_first_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_3_first_half_kgs_input" />
        </div>

        <label for="zone_4_first_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_4_first_half_kgs_input" />
        </div>

        <label for="zone_5_first_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_5_first_half_kgs_input" />
        </div>

    </fieldset>

    <br />
    <fieldset>
        <legend>Стоимость отправки каждых дополнительных 0.5 килограмм</legend>

        <label for="zone_1_each_additional_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_1_each_additional_half_kgs_input" />
        </div>

        <label for="zone_2_each_additional_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_2_each_additional_half_kgs_input" />
        </div>

        <label for="zone_3_each_additional_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_3_each_additional_half_kgs_input" />
        </div>

        <label for="zone_4_each_additional_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_4_each_additional_half_kgs_input" />
        </div>

        <label for="zone_5_each_additional_half_kgs_input">Зона 1</label>
        <div class="margin-form">
            <input type="text" value="0" id="zone_5_each_additional_half_kgs_input" />
        </div>

    </fieldset>
</form>
