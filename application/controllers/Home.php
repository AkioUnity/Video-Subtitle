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

    public function index()
	{
//	    $spaces = $space->ListSpaces();
//        print_r($spaces);
//        $this->space->SetSpace($this->default_space);
        $files = $this->space->ListObjects();
//        print_r($files);
        $videos=array();
        for ($i=0;$i<count($files);$i++){
            $key=$files[$i]['Key'];
            $pos=strpos($key,'.mp4');
            if ($pos>0){  //mp4 file
                $video=substr($key,0,$pos);
                array_push($videos,$video);
            }
        }
        $this->mViewData['files'] = $videos;

        $file=$this->input->get('file')?:$videos[0];
        $this->load_video($file);

//        $file_info = $space->GetObject($video_file);
//        print_r($file_info);
        $valid_for = "1 day";  //1 hour

//		$this->render('home', 'full_width');
	}

	public function load_video($file){
        $link = $this->space->CreateTemporaryURL($file.'.mp4');
        $this->mViewData['video_link'] = $link;

        $subtitle_file=$file.'.srt';
        $string='';
        if ($this->space->DoesObjectExist($subtitle_file)){
            $link = $this->space->CreateTemporaryURL($subtitle_file);
            $string = file_get_contents($link);
        }

        $this->mViewData['text'] = $string;
        $this->mViewData['space'] = $this->default_space;
        $this->mViewData['subtitle_file'] = $subtitle_file;
        $this->load->view('home', $this->mViewData);
    }

    public function save_subtitle()
    {
        $this->load->helper('file');

        $subtitle=$_POST['textbox'];
        $file=$_POST['file'];
        $space_name=$_POST['space'];
//        $this->space->SetSpace($space_name);
        write_file("spaces/".$file,$subtitle);
        $this->space->UploadFile("spaces/".$file, "public",$file);

        echo ("All changes saved");
    }
}
