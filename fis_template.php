<?php
if(!class_exists('FISResource', false)) {
    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'FISResource.class.php');
}

class Fis_Template extends Smarty_Internal_Template {

    // public function __construct($template_resource, $smarty, $_parent = null, $_cache_id = null, $_compile_id = null, $_caching = null, $_cache_lifetime = null)
    // {

    //     parent::__construct($template_resource, $smarty, $_parent, $_cache_id, $_compile_id, $_caching, $_cache_lifetime);
    // }

    /**
     * Create code frame for compiled and cached templates
     *
     * @param string $content   optional template content
     * @param bool   $cache     flag for cache file
     * @return string
     */
    public function createTemplateCodeFrame($content = '', $cache = false)
    {
        $plugins_string = '';
        // include code for plugins
        if (!$cache) {
            if (!empty($this->required_plugins['compiled'])) {
                $plugins_string = '<?php ';
                foreach ($this->required_plugins['compiled'] as $tmp) {
                    foreach ($tmp as $data) {
                        $file = addslashes($data['file']);
                        if (is_Array($data['function'])){
                            $plugins_string .= "if (!is_callable(array('{$data['function'][0]}','{$data['function'][1]}'))) include '{$file}';\n";
                        } else {
                            $plugins_string .= "if (!is_callable('{$data['function']}')) include '{$file}';\n";
                        }
                    }
                }
                $plugins_string .= '?>';
            }
            if (!empty($this->required_plugins['nocache'])) {
                $this->has_nocache_code = true;
                $plugins_string .= "<?php echo '/*%%SmartyNocache:{$this->properties['nocache_hash']}%%*/<?php \$_smarty = \$_smarty_tpl->smarty; ";
                foreach ($this->required_plugins['nocache'] as $tmp) {
                    foreach ($tmp as $data) {
                        $file = addslashes($data['file']);
                        if (is_Array($data['function'])){
                            $plugins_string .= addslashes("if (!is_callable(array('{$data['function'][0]}','{$data['function'][1]}'))) include '{$file}';\n");
                        } else {
                            $plugins_string .= addslashes("if (!is_callable('{$data['function']}')) include '{$file}';\n");
                        }
                    }
                }
                $plugins_string .= "?>/*/%%SmartyNocache:{$this->properties['nocache_hash']}%%*/';?>\n";
            }
        }
        // build property code
        $this->properties['has_nocache_code'] = $this->has_nocache_code;
        $output = '';
        if (!$this->source->recompiled) {
            $output = "<?php /*%%SmartyHeaderCode:{$this->properties['nocache_hash']}%%*/";
            if ($this->smarty->direct_access_security) {
                $output .= "if(!defined('SMARTY_DIR')) exit('no direct access allowed');\n";
            }
        }
        if ($cache) {
            // remove compiled code of{function} definition
            unset($this->properties['function']);
            if (!empty($this->smarty->template_functions)) {
                // copy code of {function} tags called in nocache mode
                foreach ($this->smarty->template_functions as $name => $function_data) {
                    if (isset($function_data['called_nocache'])) {
                        foreach ($function_data['called_functions'] as $func_name) {
                            $this->smarty->template_functions[$func_name]['called_nocache'] = true;
                        }
                    }
                }
                 foreach ($this->smarty->template_functions as $name => $function_data) {
                    if (isset($function_data['called_nocache'])) {
                        unset($function_data['called_nocache'], $function_data['called_functions'], $this->smarty->template_functions[$name]['called_nocache']);
                        $this->properties['function'][$name] = $function_data;
                    }
                }
            }
        }
        $this->properties['version'] = Smarty::SMARTY_VERSION;
        if (!isset($this->properties['unifunc'])) {
            $this->properties['unifunc'] = 'content_' . str_replace('.', '_', uniqid('', true));
        }
        if (!$this->source->recompiled) {
            $output .= "\$_valid = \$_smarty_tpl->decodeProperties(" . var_export($this->properties, true) . ',' . ($cache ? 'true' : 'false') . "); /*/%%SmartyHeaderCode%%*/?>\n";

            $output .= '<?php if(!class_exists(\'FISResource\', false)){'.

                'foreach($_smarty_tpl->smarty->getPluginsDir() as $_plugin_dir) {'.
                    '$file = $_plugin_dir . "FISResource.class.php";'.
                    'if (file_exists($file)) { require_once($file);break;}'.
                '}'.
            '}';

            $deps = array();
            foreach ($this->properties['file_dependency'] as $dependency) {
                $deps[$dependency[0]] = FISResource::getTplMd5($dependency[0], $this->smarty);
            }

            $output .= '$_valid = $_valid && FISResource::checkCompile(' . var_export($deps, true)  . ', $_smarty_tpl->smarty, $_smarty_tpl); ?>';

            $output .= '<?php if ($_valid && !is_callable(\'' . $this->properties['unifunc'] . '\')) {function ' . $this->properties['unifunc'] . '($_smarty_tpl) {?>';
        }
        $output .= $plugins_string;
        $output .= $content;
        if (!$this->source->recompiled) {
            $output .= '<?php }} ?>';
        }
        return $output;
    }


}
