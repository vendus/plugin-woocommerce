<div class="wrap">
    <h1>Vendus Plugin - Config</h1>
    <p>Integração Woocommerce com o Vendus</p>
    <form method="post" action="" novalidate="novalidate">
        <?php settings_fields('vp-form-config'); ?>
        <?php do_settings_sections('vp-form-config'); ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="vp_form_config_api_key">Api Key</label>
                    </th>
                    <td>
                        <input name="vp_config_api_key" type="text" id="vp_form_config_api_key" value="<?=$apiKey?>" class="regular-text">
                    </td>
                </tr>

                <?php if(isset($registersList) && !empty($registersList)): ?>
                    <tr>
                        <th scope="row">
                            <label for="vp_form_config_register">Caixa</label>
                        </th>
                        <td>
                            <select name="vp_form_config_register" id="vp_form_config_register" class="regular-text">
                                <option value=""></option>
                                <?php foreach($registersList as $register): ?>
                                    <option value="<?=$register['id']?>"><?=$register['title']?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vp_form_config_exemption">Motivo da Isenção <? echo wc_help_tip('Se o Imposto é Isento (0%) é necessário indicar o respetivo motivo da isenção.');?></label>
                        </th>
                        <td>
                            <select name="vp_form_config_exemption" id="vp_form_config_exemption" class="regular-text">
                                <option value=""></option>
                                <?php foreach($exemptionList as $key=>$item): ?>
                                    <option value="<?=$key?>"><?=$item?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vp_form_config_exemption_law">Norma Legal aplicável <? echo wc_help_tip('Especificação do preceito legal aplicável às isenções de IVA.');?></label>
                        </th>
                        <td>
                            <input name="vp_form_config_exemption_law" type="text" id="vp_form_config_exemption_law" value="<?=$exemptionLaw?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vp_form_config_exemption_law">Tipo de Fatura</label>
                        </th>
                        <td>
                            <select name="vp_form_config_invoice_type" id="vp_form_config_invoice_type" class="regular-text">
                                <?php foreach($invoiceList as $key=>$item): ?>
                                    <option value="<?=$key?>"><?=$item?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php submit_button();?>
    </form>
</div>

<script type="text/javascript">
    <?php if(isset($registerId) && !empty($registerId)): ?>
        document.getElementById('vp_form_config_register').value = "<?=$registerId?>";
    <?php endif; ?>

    <?php if(isset($exemption) && !empty($exemption)): ?>
        document.getElementById('vp_form_config_exemption').value = "<?=$exemption?>";
    <?php endif; ?>
    
    <?php if(isset($invoiceType) && !empty($invoiceType)): ?>
        document.getElementById('vp_form_config_invoice_type').value = "<?=$invoiceType?>";
    <?php endif; ?>
</script>
