<?php

namespace Gomee\Files;

use Gomee\Helpers\Any;

class Filemanager{
    use FileType, DirMethods, FileMethods, ZipMethods, FileConverter;


    /**
     * khoi tao doi tuong
     * @param string $dir
     */
    function __construct($dir = null, $make_dir_if_not_exists = false)
    {
        $this->dirInit();
        $this->zipInit();
        $this->setDir($dir,$make_dir_if_not_exists);
    }



    /**
     * copy file hoac folder
     * @param string $src
     * @param string $dst
     */
    public function copy($src, $dst)
    {
        if(!$this->checkDirAccepted($src) || !$this->checkDirAccepted($dst)) return false;
        if(is_dir($src)) return $this->copyFolder($src, $dst, false, false);
        elseif(is_file($src)) return $this->copyFile($src, $dst);
    }

    /**
     * copy file hoac folder
     * @param string $src
     * @param string $dst
     */
    public function move($src, $dst, $list = [])
    {
        if(!$this->checkDirAccepted($src) || !$this->checkDirAccepted($dst)) return false;
        $s = true;
        if(is_dir($src)) {
            if(!is_dir($dst)) $this->mkdir($dst);
            $d = rtrim($dst, '/') . '/';
            if(!$list){
                $listItem = $this->getList($src);
                if($t = count($listItem)){
                    for ($i=0; $i < $t; $i++) { 
                        $file = $listItem[$i];
                        $nf = $d . $file->name;
                        if($file->type == 'file'){
                            if($this->copyFile($file->path, $nf)){
                                $this->delete($file->path);
                            }else{
                                $s = false;
                            }
                        }else{
                            if($this->copyFolder($file->path, $nf)){
                                $this->delete($file->path);
                            }else{
                                $s = false;
                            }
                        }
                    }
                }
                if($s){
                    $this->delete($src);
                }
            }
            elseif(is_array($list)){
                $ds = rtrim($src, '/') . '/';
                $d = rtrim($dst, '/') . '/';
                foreach ($list as $id => $filename) {
                    if(file_exists($fs = $ds . $filename)){
                        $nf = $d . $filename;
                        if(is_dir($fs)){
                            if(!$this->move($fs, $nf)) $s = false;
                        }else{
                            if($this->copyFile($fs, $nf)){
                                $this->delete($fs);
                            }else{
                                $s = false;
                            }
                        }
                    }
                }
            }
        }
        elseif(is_file($src)) {
            $fns = explode('/', $src);
            $filename = array_pop($fns);
            if(is_dir($dst)){
                
                if($this->copyFile($src, $nf = rtrim($dst, '/') . '/' . $filename)){
                    if($nf!=$src) $this->delete($src);
                }
                else{
                    $s = false;
                }
            }
            elseif($this->copyFile($src, $dst)){
                if($dst!=$src) $this->delete($src);
            }else{
                $s = false;
            }
        }else{
            $s = false;
        }
        return $s;
    }

    
    

    /**
     * full path
     * l???y d???ng d???n tuy???t ?????i c???a file ho???c th?? m???c
     * @param string $filename
     * @return string
     */
    public function getPath($filename = null)
    {
        $path = null;
        if($filename){
            if(!$this->checkDirAccepted($filename)) $path = $this->_dir . '/' . $filename;
            else $path = $filename;
        }
        else $path = $this->_dir . '/' . $this->_filename;

        return $path;
    }

    /**
     * g???i h??m theo d???nh d???ng file
     * @param string $method
     * @param array $params
     */
    public function __call($method, $params)
    {
        $filename = null;
        $data = null;
        $mime = null;
        $action = null;
        $n = strtolower($method);
        $has_params = (is_array($params) && $t = count($params));
        // tr?????ng h???p 1: g???i h??m b???ng get + ph???n m??? r???ng c???a file, 
        // v?? d??? getHtml th?? s??? set ?????nh d???ng html ????? chu???n h??a d?????ng d???n r???i tr??? v??? n???i dung file nenu61 file t???n t???i
        if((substr($n,0, 3) == 'get') && ($info = $this->getMimeType(substr($n, 3)))){
            // n???u c?? tham s???
            if($has_params){
                // tham s??? ?????u ti??n s??? l?? t??n file
                $filename = $params[0];
            }
            // set lo???i file
            $mime = $info->type;
            // set h??nh d???ng
            $action = 'get';
        }
        // tr?????ng h???p 2: g???i h??m b???ng save + ph???n m??? r???ng c???a file, 
        // v?? d??? saveHtml th?? s??? set ?????nh d???ng html ????? chu???n h??a d?????ng d???n r???i th??m ??u??i file n???u ng?????i d???ng qu??n r???i l??u
        elseif((substr($n,0, 4) == 'save') && ($info2 = $this->getMimeType(substr($n, 4))))
        {
            if($has_params){
                if($t == 1){
                    // n???u ch??? c?? 1 tham s??? th?? data s??? l?? tham s??? ?????u ti??n
                    $data = $params[0];
                }else{
                    // n???u nhi???u h??n th?? t??n file l?? tham s??? d???u ti??n
                    $filename = $params[0];
                    // d??? li???u l?? tham s??? th??? 2
                    $data = $params[1];
                }
            }
            $mime = $info2->type;
            $action = 'save';
        }
        // tr?????ng h???p 2: g???i h??m b???ng  ph???n m??? r???ng c???a file, 
        // v?? d??? html th?? s??? set ?????nh d???ng html ????? chu???n h??a d?????ng d???n r???i th??m ??u??i file n???u ng?????i d???ng qu??n r???i l??u ho???c l???y d??? li???u
        elseif($info3 = $this->getMimeType($n)){
            if($has_params){
                if($t == 1){
                    // n???u tham s??? ?????u ti??n l?? chu???i th?? s??? l?? file name v?? l???y data
                    if(is_string($params[0])){
                        $filename = $params[0];
                        $action = 'get';
                    }
                    // ng?????c l???i l?? l??u v???i tham s??? ?????u ti??n l?? d??? li???u
                    else{
                        $data = $params[0];
                        $action = 'save';
                    }
                }
                // n???u c?? h??n 1 tham s??? th?? ch???c ch???n s??? l??u ch??? v???i tham s??? l?? filename v?? data
                else{
                    $filename = $params[0];
                    $data = $params[1];
                    $action = 'save';
                }
            }
            // n???u ko tham s??? s??? l?? l???y d??? li???u theo d?????ng d???n v?? t??n file dc set trc ????
            else{
                $action = 'get';
            }
            $mime = $info3->type;
        }

        if($action == 'get'){
            return $this->getContent($filename, $mime);
        }elseif($action == 'save'){
            return $this->save($filename, $data, $mime);
        }
        return $this;
    }

    
    
    /**
     * l??u file t??? d??? li???u base 64
     * @param string $base64 d??? li???u file ???????c m?? h??a base64
     * @param string $filenameWithoutExtension t??n file kh??ng bao g???m ph???n m??? r???ng
     * @param int $upload_by ng?????i upload
     * 
     * @return Gomee\Helpers\Arr|null
     */
    public function saveBase64($base64, $filenameWithoutExtension = null, $path = null)
    {
        if($file = $this->getBase64Data($base64)){
            if($path){
                $this->setDir($path);
            }
            $original = null;
            // neu co ten file cu
            if($file->filename){
                $original = $file->filename;
            }
            if($fn = $this->getFilenameWithoutExtension($filenameWithoutExtension)){
                $attachment = $fn;
            }elseif ($original) {
                $attachment = $this->getFilenameWithoutExtension($original).'-' . uniqid();
            }
            else{
                $attachment = str_slug(microtime(),'-');
            }
            $filename = $attachment.'.'. $file->extension;
            if($saveFile = $this->save($filename, $file->data, $file->extension)){
                return $saveFile;
            }
        }

        return false;
    }

    
    /**
     * l???y t??n file ko c?? ph???n m??? r???ng
     * @param string $filenameWithoutExtension
     * @return string|null
     */
    public function getFilenameWithoutExtension($filenameWithoutExtension = null)
    {
        if($filenameWithoutExtension){
            $of = explode('.',$filenameWithoutExtension);
            $ext = array_pop($of);
            if($mime = $this->getMimeType($ext)){
                $filename = implode('.',$of);
            }else{
                $filename = $filenameWithoutExtension;
            }
            
            
            if($filename) return $filename;
        }
        return null;
    }

}