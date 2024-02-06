<?php

namespace Maveriks\Pattern\Mvc;

use Maveriks\Util\Common;
use Smarty;

class SmartyView extends View
{
    /**
     * @var Smarty class instance
     */
    protected $smarty;

    /**
     * Class constructor
     *
     * @param string $tpl
     */
    public function __construct($tpl = '')
    {
        // Call the parent constructor
        parent::__construct($tpl);

        // Instance Smarty class
        $this->smarty = new Smarty();

        // Set Smarty temporal paths
        $this->smarty->compile_dir = defined('PATH_SMARTY_C') ? PATH_SMARTY_C : sys_get_temp_dir();
        $this->smarty->cache_dir = defined('PATH_SMARTY_CACHE') ? PATH_SMARTY_CACHE : sys_get_temp_dir();

        // If the paths don't exist we need to create them
        if (!is_dir($this->smarty->compile_dir)) {
            Common::mk_dir($this->smarty->compile_dir);
        }
        if (!is_dir($this->smarty->cache_dir)) {
            Common::mk_dir($this->smarty->cache_dir);
        }
    }

    /**
     * Get "smarty" property
     *
     * @return Smarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }

    /**
     * Assign a value to a Smarty piece in the template
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function assign($name, $value = null)
    {
        $this->smarty->assign($name, $value);
    }

    /**
     * Render the Smarty template
     */
    public function render()
    {
        $this->smarty->display($this->getTpl());
    }
}
