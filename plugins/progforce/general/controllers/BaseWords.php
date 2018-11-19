<?php namespace Progforce\General\Controllers;

use Backend;
use Backend\Classes\Controller;

/**
 * BaseWords Back-end Controller
 */
class BaseWords extends Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->addJs('/plugins/progforce/general/assets/js/backend.js');
    }
}


