<?php 
namespace SitemapManager;
use \SitemapManager\Helper\Filesystem;
use \SitemapManager\Helper\StringUtils;

class SitemapHelper {

    public function getDataSitemap(){
        if(empty($this->sitemapdata)){
            $this->sitemapdata = Filesystem::read($this->path);
            $temp = explode('">',$this->sitemapdata);
            if(!empty($temp[1])) {
                $temp[1] = preg_replace('/\s+/', '', $temp[1]);
                $this->sitemapdata = $temp[0].'">'.$temp[1];
            }
        }
        return trim($this->sitemapdata);
    }

    public function setLastFile(){
        $this->path = Filesystem::lastIncrementFilename($this->path);
    }

    public function getLastFile(){
        return Filesystem::lastIncrementFilename($this->path);
    }

    public function count(){
        $count = preg_match_all('@<loc>(.+?)<\/loc>@', $this->getDataSitemap(), $matches);
        return $count;
    }

    public function isLimit($val=0){
        return ((($this->count()+(int)$val) < (int)$this->limit)?false:true);
    }

    public function has($url){
        return StringUtils::isMatchAny('<loc>'.$url.'</loc>',$this->getDataSitemap());
    }

    public function find($url,$bool=true){
        $dir = dirname($this->path);
        $file = basename($this->path);
        if (StringUtils::isMatchAny('-[',$file)){
            $file = preg_replace('@\-\[(.+?)\]@', '*', $file);
        } else {
            $file = str_replace('.xml', '*.xml', $file);
        }
        $newpath = $dir.DIRECTORY_SEPARATOR.$file;
        $files = Filesystem::getAllFiles($newpath);
        foreach($files as $sitemap){
            $sitemapdata = Filesystem::read($sitemap);
            $temp = explode('">',$sitemapdata);
            if(!empty($temp[1])) {
                $temp[1] = preg_replace('/\s+/', '', $temp[1]);
                $sitemapdata = $temp[0].'">'.$temp[1];
            }
            if(StringUtils::isMatchAny('<loc>'.$url.'</loc>',$sitemapdata)){
                return ($bool?true:$sitemap);
            }
        }
        return ($bool?false:'');
    }

    public function getPath(){
        return $this->path;
    }

    public function deleteFile(){
        return Filesystem::delete($this->path);
    }

    public function showModifiedBlock(){
        return $this->modifiedblock;
    }

    public function showQueueBlock(){
        return $this->enqueue;
    }

    public function showBlock(){
        return $this->block;
    }

    public function enqueue(){
        if ($this->mode == 'delete'){
            $this->enqueue[] = $this->deleteblock;
        } else {
            $this->enqueue .= $this->block;
        }
        return $this;
    }

}