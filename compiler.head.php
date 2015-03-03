<?php

function smarty_compiler_head($arrParams,  $smarty){
    $strAttr = '';
    foreach ($arrParams as $_key => $_value) {
        $strAttr .= ' ' . $_key . '="<?php echo ' . $_value . ';?>"';
    }
    return '<head' . $strAttr . '>';
}

function smarty_compiler_headclose($arrParams,  $smarty){
    // $strResourceApiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/FISResource.class.php');
    // $strCode = '<?php ';
    // $strCode .= 'if(!class_exists(\'FISResource\', false)){require_once(\'' . $strResourceApiPath . '\');}';
    $strCode = '<?php if(!class_exists(\'FISResource\', false)){'.

            'foreach($_smarty_tpl->smarty->getPluginsDir() as $_plugin_dir) {'.
                '$file = $_plugin_dir . "FISResource.class.php";'.
                'if (file_exists($file)) { require_once($file);break;}'.
            '}'.
        '}';

    $strCode .= 'echo FISResource::cssHook();';
    $strCode .= '?>';
    $strCode .= '</head>';
    return $strCode;
}
