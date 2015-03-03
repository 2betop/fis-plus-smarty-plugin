<?php

function smarty_compiler_style($params,  $smarty){
    $strCode = '<?php ';
    if (isset($params['id'])) {
        // $strResourceApiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/FISResource.class.php');
        // $strCode .= 'if(!class_exists(\'FISResource\', false)){require_once(\'' . $strResourceApiPath . '\');}';
        $strCode .= 'if(!class_exists(\'FISResource\', false)){'.

            'foreach($_smarty_tpl->smarty->getPluginsDir() as $_plugin_dir) {'.
                '$file = $_plugin_dir . "FISResource.class.php";'.
                'if (file_exists($file)) { require_once($file);break;}'.
            '}'.
        '}';
        $strCode .= 'FISResource::$cp = ' . $params['id'].';';
    }
    $strCode .= 'ob_start();?>';
    return $strCode;
}

function smarty_compiler_styleclose($params,  $smarty){
    $strResourceApiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/FISResource.class.php');
    $strCode  = '<?php ';
    $strCode .= '$style=ob_get_clean();';
    $strCode .= 'if($style!==false){';
    // $strCode .= 'if(!class_exists(\'FISResource\', false)){require_once(\'' . $strResourceApiPath . '\');}';
    $strCode .= '<?php if(!class_exists(\'FISResource\', false)){'.

            'foreach($_smarty_tpl->smarty->getPluginsDir() as $_plugin_dir) {'.
                '$file = $_plugin_dir . "FISResource.class.php";'.
                'if (file_exists($file)) { require_once($file);break;}'.
            '}'.
        '}';

    $strCode .=     'if(FISResource::$cp){';
    $strCode .=         'if (!in_array(FISResource::$cp, FISResource::$arrEmbeded)){';
    $strCode .=             'echo "<style type=\'text/css\'>" . $style . "</style>";';
    $strCode .=             'FISResource::$arrEmbeded[] = FISResource::$cp;';
    $strCode .=         '}';
    $strCode .=     '} else {';
    $strCode .=         'echo "<style type=\'text/css\'>" . $style . "</style>";';
    $strCode .=     '}';
    $strCode .= '}';
    $strCode .= 'FISResource::$cp = false;?>';
    return $strCode;
}
