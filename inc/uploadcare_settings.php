<?php
function uploadcare_settings_init()
{
    register_setting('pluginPage', 'uploadcare_settings');

    /*
     * Add Uploadcare settings sections
     */
    add_settings_section(
        'uploadcare_pluginPage_section_API',
        __('API Keys <a href="https://uploadcare.com/documentation/keys/">[?]</a>', 'uploadcare'),
        'uploadcare_settings_section_callback',
        'pluginPage'
    );

    add_settings_section(
        'uploadcare_pluginPage_section_OPTIONS',
        __('Options', 'uploadcare'),
        'uploadcare_settings_section_callback',
        'pluginPage'
    );

    add_settings_section(
        'uploadcare_pluginPage_section_TABS',
        __('Source tabs', 'uploadcare'),
        'uploadcare_settings_section_callback',
        'pluginPage'
    );

    add_settings_section(
        'uploadcare_pluginPage_section_TUNING',
        __('Widget fine tuning <a href="https://uploadcare.com/documentation/widget/#advanced-configuration">[?]</a>', 'uploadcare'),
        'uploadcare_settings_section_callback',
        'pluginPage'
    );


    /*
     * Add Uploadcare settings fields
     */
    add_settings_field(
        'uploadcare_public',
        __('Public key:', 'uploadcare'),
        'uploadcare_public_render',
        'pluginPage',
        'uploadcare_pluginPage_section_API'
    );

    add_settings_field(
        'uploadcare_secret',
        __('Secret key:', 'uploadcare'),
        'uploadcare_secret_render',
        'pluginPage',
        'uploadcare_pluginPage_section_API'
    );

    add_settings_field(
        'uploadcare_original',
        __('Insert image with URL to the original image', 'uploadcare'),
        'uploadcare_original_render',
        'pluginPage',
        'uploadcare_pluginPage_section_OPTIONS'
    );

    add_settings_field(
        'uploadcare_multiupload',
        __('Allow multiupload in Uploadcare widget', 'uploadcare'),
        'uploadcare_multiupload_render',
        'pluginPage',
        'uploadcare_pluginPage_section_OPTIONS'
    );

    add_settings_field(
        'uploadcare_source_tabs',
        __('Tabs:', 'uploadcare'),
        'uploadcare_source_tabs_render',
        'pluginPage',
        'uploadcare_pluginPage_section_TABS'
    );

    add_settings_field(
        'uploadcare_finetuning',
        __('Code:', 'uploadcare'),
        'uploadcare_finetuning_render',
        'pluginPage',
        'uploadcare_pluginPage_section_TUNING'
    );

}

/*
  * Fields renders
*/
function uploadcare_public_render()
{
    $options = get_option('uploadcare_settings');
    ?>
    <input type='text' name='uploadcare_settings[uploadcare_public]'
           value='<?php echo $options['uploadcare_public']; ?>'>
<?php
}

function uploadcare_secret_render()
{
    $options = get_option('uploadcare_settings');
    ?>
    <input type='text' name='uploadcare_settings[uploadcare_secret]'
           value='<?php echo $options['uploadcare_secret']; ?>'>
<?php
}

function uploadcare_original_render()
{
    $options = get_option('uploadcare_settings');
    ?>
    <input type='checkbox'
           name='uploadcare_settings[uploadcare_original]' <?php checked($options['uploadcare_original'], 1); ?>
           value='1'>
<?php
}

function uploadcare_multiupload_render()
{
    $options = get_option('uploadcare_settings');
    ?>
    <input type='checkbox'
           name='uploadcare_settings[uploadcare_multiupload]' <?php checked($options['uploadcare_multiupload'], 1); ?>
           value='1'>
<?php
}

function uploadcare_source_tabs_render()
{
    $options = get_option('uploadcare_settings');
    $tabs = array(
        'file',
        'url',
        'facebook',
        'instagram',
        'flickr',
        'gdrive',
        'evernote',
        'box',
        'skydrive',
        'dropbox',
        'vk'
    );
    ?>
    <select name='uploadcare_settings[uploadcare_source_tabs][]' multiple='' size='12' style='width: 120px;'>
        <?
        $options = get_option('uploadcare_settings');
        $selected = in_array('all', $options['uploadcare_source_tabs']) ? 'selected="selected"' : '';
        echo '<option ' . $selected . ' value="all">All tabs</option>';
        foreach ($tabs as $tab) {
            $selected = in_array($tab, $options['uploadcare_source_tabs']) ? 'selected="selected"' : '';
            echo '<option ' . $selected . ' value="' . $tab . '">' . $tab . '</option>';
        }
        ?>
    </select>
<?php
}

function uploadcare_finetuning_render()
{
    $options = get_option('uploadcare_settings');
    ?>
    <textarea cols='40' rows='5'
              name='uploadcare_settings[uploadcare_finetuning]'><?php echo stripcslashes($options['uploadcare_finetuning']); ?></textarea>
<?php

}


function uploadcare_settings_section_callback()
{
    // Show sections description
    // NOT NULL
}

/*
  * Generate Page
*/
function uploadcare_options_page()
{
    ?>
    <form action='options.php' method='post'>

        <h2>Uploadcare</h2>
        <?php
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        ?>

    </form>
<?php
}
?>
