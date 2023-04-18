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
                        <label for="vendus_plugin_form_config_api_key">Api Key</label>
                    </th>
                    <td>
                        <input name="vendus_plugin_config_api_key" type="text" id="vendus_plugin_form_config_api_key" value="<?php echo esc_attr($apiKey)?>" class="regular-text">
                    </td>
                </tr>

                <?php if(isset($registersList) && !empty($registersList)): ?>
                    <tr>
                        <th scope="row">
                            <label for="vendus_plugin_form_config_register">Caixa</label>
                        </th>
                        <td>
                            <select name="vendus_plugin_form_config_register" id="vendus_plugin_form_config_register" class="regular-text">
                                <option value=""></option>
                                <?php foreach($registersList as $register): ?>
                                    <option value="<?php echo esc_attr($register['id'])?>"><?php echo esc_html($register['title'])?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vendus_plugin_form_config_exemption">Motivo da Isenção <?php echo wc_help_tip('Se o Imposto é Isento (0%) é necessário indicar o respetivo motivo da isenção.');?></label>
                        </th>
                        <td>
                            <select name="vendus_plugin_form_config_exemption" id="vendus_plugin_form_config_exemption" class="regular-text">
                                <option value=""></option>
                                <?php foreach($exemptionList as $key=>$item): ?>
                                    <option value="<?php echo esc_attr($key)?>"><?php echo esc_html($item)?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vendus_plugin_form_config_exemption_law">Norma Legal aplicável <?php echo wc_help_tip('Especificação do preceito legal aplicável às isenções de IVA.');?></label>
                        </th>
                        <td>
                            <input name="vendus_plugin_form_config_exemption_law" type="text" id="vendus_plugin_form_config_exemption_law" value="<?php echo esc_attr($exemptionLaw)?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vendus_plugin_form_config_ignore_check_completed">
								Ignorar Estado da Encomenda <?php echo wc_help_tip('Ao ativar é possível emitir faturas sem a encomenda estar "Concluída".');?>
							</label>
                        </th>
                        <td>
                            <input name="vendus_plugin_form_config_ignore_check_completed" type="checkbox" id="vendus_plugin_form_config_ignore_check_completed" value="1" <?php echo $ignoreCheck ? 'checked' : ''?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vendus_plugin_form_config_ignore_notes">
								Ignorar Notas da Encomenda <?php echo wc_help_tip('Ao ativar as notas da encomenda não vai aparecer nas faturas.');?>
							</label>
                        </th>
                        <td>
                            <input name="vendus_plugin_form_config_ignore_notes" type="checkbox" id="vendus_plugin_form_config_ignore_notes" value="1" <?php echo $ignoreNotes ? 'checked' : ''?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vendus_plugin_form_config_exemption_law">Tipo de Fatura</label>
                        </th>
                        <td>
                            <select name="vendus_plugin_form_config_invoice_type" id="vendus_plugin_form_config_invoice_type" class="regular-text">
                                <?php foreach($invoiceList as $key=>$item): ?>
                                    <option value="<?php echo esc_attr($key)?>"><?php echo esc_html($item)?></option>
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
        document.getElementById('vendus_plugin_form_config_register').value = "<?php echo esc_attr($registerId)?>";
    <?php endif; ?>

    <?php if(isset($exemption) && !empty($exemption)): ?>
        document.getElementById('vendus_plugin_form_config_exemption').value = "<?php echo esc_attr($exemption)?>";
    <?php endif; ?>
    
    <?php if(isset($invoiceType) && !empty($invoiceType)): ?>
        document.getElementById('vendus_plugin_form_config_invoice_type').value = "<?php echo esc_attr($invoiceType)?>";
    <?php endif; ?>
</script>
