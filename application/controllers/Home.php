<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home page
 */
class Home extends MY_Controller {

    public $default_space="video-caption";
    public $space;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('space',null,'space0');
        $this->space=$this->space0->space;
    }

    function _remap($param) {
        $this->index($param);
    }

    public function index()
	{
	    $id=$this->input->get('id')?:0;
//	    $spaces = $space->ListSpaces();
//        print_r($spaces);
//        $this->space->SetSpace($this->default_space);
        $files = $this->space->ListObjects();
//        print_r($files);
        $this->mViewData['files'] = $files;

        $video_file=$files[$id]['Key'];
        $subtitle_file=$files[$id+1]['Key'];
        $this->load_video($video_file,$subtitle_file);

//        $file_info = $space->GetObject($video_file);
//        print_r($file_info);
        $valid_for = "1 day";  //1 hour

//		$this->render('home', 'full_width');
	}

	public function load_video($video_file,$subtitle_file){
        $link = $this->space->CreateTemporaryURL($video_file);
        $this->mViewData['video_link'] = $link;

        $link = $this->space->CreateTemporaryURL($subtitle_file);
        $string = file_get_contents($link);

        $this->mViewData['text'] = $string;
        $this->mViewData['space'] = $this->default_space;
        $this->mViewData['subtitle_file'] = $subtitle_file;
        $this->load->view('home', $this->mViewData);
    }

    public function save_post()
    {
        $this->load->helper('file');

        $subtitle=$_POST['textbox'];
        $file=$_POST['file'];
        $space_name=$_POST['space'];
        write_file("spaces/".$file,$subtitle);

        $space=$this->space->space;
//        $space->SetSpace($space_name);
        $space->UploadFile("spaces/".$file, "public",$file);

        echo ("All changes saved");
    }
}
