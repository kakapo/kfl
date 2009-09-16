<?php
/**
 * PHP is a template engine.
 * @author Kakapo wu <kakapowu at gmail dot com>
 * @package PhpTemplate
 * @version 0.0.1
 */
class PhpTemplate
{
	/**
     * PhpTemplate version number
     *
     * @var string
     */
    private $_version              = '0.0.1';
 	/**
     * The name of the directory where templates are located.
     *
     * @var string
     */
    public  $template_dir          =  'views';    
    /**
     * where assigned template vars are kept
     *
     * @var array
     */
    private  $_tpl_vars             = array();
    /**
     * assigns values to template variables
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed $value the value to assign
     */	
	function assign($tpl_var, $value = null){
	    if (is_array($tpl_var)){
            foreach ($tpl_var as $key => $val) {
                if ($key != '') {
                    $this->_tpl_vars[$key] = $val;
                }
            }
        } else {
            if ($tpl_var != '')
                $this->_tpl_vars[$tpl_var] = $value;
        }	
	}
	 /**
     * display
     *
     * @param string $template the template
     */	
	function display($template){
		
		extract($this->_tpl_vars);
		unset($this->_tpl_vars);
		if(file_exists($this->template_dir."/".$template)){
			$r = @include($this->template_dir."/".$template);
		}else{
			//trigger_error("{$template} template file not exist!",E_USER_ERROR);
			//die();
		}
		
		
	}
}
?>