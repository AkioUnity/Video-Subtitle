<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home page
 */
class Home extends MY_Controller {

    public $default_space="video-caption";
    public function __construct()
    {
        parent::__construct();
        $this->load->library('space');
    }

    public function index()
	{

	    $space=$this->space->space;
//	    $spaces = $space->ListSpaces();
//        print_r($spaces);
        $space->SetSpace($this->default_space);
        $files = $space->ListObjects();
//        print_r($files);
        $video_file=$files[0]['Key'];
//        $file_info = $space->GetObject($video_file);
//        print_r($file_info);
        $valid_for = "1 day";  //1 hour
        $link = $space->CreateTemporaryURL($video_file);
        $this->mViewData['video_link'] = $link;
        $subtitle_file=$files[1]['Key'];
        $link = $space->CreateTemporaryURL($subtitle_file);
        $string = file_get_contents($link);
        $this->mViewData['text'] = $string;
        $this->mViewData['space'] = $this->default_space;
        $this->mViewData['subtitle_file'] = $subtitle_file;
        $this->load->view('home', $this->mViewData);
//		$this->render('home', 'full_width');
	}

    public function save_post()
    {
        $this->load->helper('file');

        $subtitle=$_POST['textbox'];
        $file=$_POST['file'];
        $space_name=$_POST['space'];
        write_file("spaces/".$file,$subtitle);

        $space=$this->space->space;
        $space->SetSpace($space_name);
        $space->UploadFile("spaces/".$file, "public",$file);

        echo ("All changes saved");
    }
}
