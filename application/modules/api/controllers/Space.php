<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Demo Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class Space extends API_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function save_post()
    {
        $this->load->helper('file');
        $this->load->library('space');

        $subtitle=$_POST['textbox'];
        $file=$_POST['file'];
        $space_name=$_POST['space'];
        write_file("spaces/".$file,$subtitle);
        echo ("All changes saved");

        $space=$this->space->space;
        $space->SetSpace($space_name);
        $space->UploadFile($file, "public");

    }
}
