<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home page
 */
class Home extends MY_Controller {

    public $default_space="video-caption";

	public function index()
	{
        $this->load->library('space');
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
        $link = $space->CreateTemporaryURL($files[1]['Key']);
        $string = file_get_contents($link);
        $this->mViewData['text'] = $string;

        $this->load->view('home', $this->mViewData);
//		$this->render('home', 'full_width');
	}

	public function save(){

    }
}
