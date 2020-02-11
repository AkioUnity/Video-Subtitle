<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
 include_once APPPATH.'/third_party/spaces/spaces.php';
 
class Space {

    public $space;
 
    public function __construct()
    {
        $key = "RKIPAY4TCPODDPV7IWMF";
        $secret = "zMcBQQksIyZaeOui7fBdPzQHEUXZBZuACKeHIRYy0zo";
        $space_name = "9729795080000";//video-caption";
        $region = "nyc3";
        
        $this->space = new SpacesConnect($key, $secret, $space_name, $region); 
    }
}

