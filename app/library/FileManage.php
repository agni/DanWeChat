<?php
/**
 * User: yz.chen
 * Time: 2017-04-24 16:04
 */

namespace Dandelion;

class FileManage
{
    private $path;                   //上传文件保存的路径
    private $originName = "";        //源文件名
    private $tmpFileName = "";       //临时文件名
    private $errMessage = "";        //错误消息
    private $cache = "Cache/";       //zip临时目录

    public function getErrMessage()
    {
        return $this->errMessage;
    }

    public function getFileName()
    {
        return $this->originName;
    }

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function upload()
    {
        if ($_FILES["file"]["error"] > 0) {
            $this->errMessage = "";
            return false;
        }
        $this->originName = $_FILES["file"]["name"];
        $this->tmpFileName = $_FILES["file"]["tmp_name"];
        if (file_exists(rtrim($this->path, "/") . "/" . $_FILES["file"]["name"])) {
            $this->errMessage = $_FILES["file"]["name"] . " 文件已经存在。";
            return false;
        }

        move_uploaded_file($_FILES["file"]["tmp_name"],
            rtrim($this->path, "/") . "/" . $_FILES["file"]["name"]);
        echo file_exists($_FILES["file"]["tmp_name"]);
        return true;
    }

    public function rollback()
    {
        $this->delete($this->originName);
    }

    public function delete($filename, $zip = false)
    {
        $file = $zip ? $this->path . $this->cache . $filename : $this->path . $filename;
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function download($filename, $zip = false)
    {
        $file = $zip ? $this->path . $this->cache . $filename : $this->path . $filename;
        if (!file_exists($file)) {
            $this->errMessage = "文件不存在或已被删除";
            return false;
        }
        $fileStream = fopen($file, "r");
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($file));
        header("Content-Disposition: attachment; filename=" . $filename);
        ob_clean();
        while (!feof($fileStream)) {
            echo @fread($fileStream, 1024 * 8);
            ob_flush();
            flush();
        }
        fclose($fileStream);
        return true;
    }

    public function zip($zipName, array $files)
    {
        ini_set("max_execution_time", "0");
        $zip = new \ZipArchive();
        if (file_exists($this->path . $this->cache . $zipName)) {
            $this->errMessage = "打包过程中出现错误";
            return false;
        }
        $zip->open($this->path . $this->cache . $zipName, \ZipArchive::CREATE);
        foreach ($files as $file) {
            $zip->addFile($this->path . $file, $file);
        }
        $zip->close();
        return true;
    }

}
