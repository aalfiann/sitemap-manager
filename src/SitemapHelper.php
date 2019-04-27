<?php 
namespace SitemapManager;
use \SitemapManager\Helper\Filesystem;
use \SitemapManager\Helper\StringUtils;

/**
 * Class SitemapHelper
 *
 * @package    SitemapManager
 * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
 * @copyright  Copyright (c) 2019 M ABD AZIZ ALFIAN
 * @license    https://github.com/aalfiann/sitemap-manager/blob/master/LICENSE.md MIT License
 */
class SitemapHelper {

    /**
     * Get Data from reading the file sitemap
     * 
     * @return string
     */
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

    /**
     * Set the last path file of sitemap
     * Note: 
     * - Manage many sitemap files is need to know where is the last filename
     * - This will change the path of sitemap
     */
    public function setLastFile(){
        $this->path = Filesystem::lastIncrementFilename($this->path);
    }

    /**
     * Get the last path file of sitemap
     * 
     * @return string
     */
    public function getLastFile(){
        return Filesystem::lastIncrementFilename($this->path);
    }

    /**
     * Count how many loc inside sitemap
     * 
     * @return int
     */
    public function count(){
        $count = preg_match_all('@<loc>(.+?)<\/loc>@', $this->getDataSitemap(), $matches);
        return $count;
    }

    /**
     * Check if sitemap file is already limit or not
     * 
     * @return bool
     */
    public function isLimit($val=0){
        return ((($this->count()+(int)$val) < (int)$this->limit)?false:true);
    }

    /**
     * Check if loc already inside of current sitemap
     * 
     * @param $url is the url loc
     * @param $xml_entities if set to true then will parse url into xml_entities. Default is false
     * 
     * @return bool
     */
    public function has($url,$xml_entities=false){
        if($xml_entities) $url = $this->xml_entities($url);
        return StringUtils::isMatchAny('<loc>'.$url.'</loc>',$this->getDataSitemap());
    }

    /**
     * Find the loc through all sitemap file
     * 
     * @param $url      is the url loc
     * @param $bool     if you set to false then will return string of path
     * 
     * @return mixed    bool/string
     */
    public function find($url,$bool=true){
        if(!empty($url)){
            $dir = dirname($this->path);
            $file = basename($this->path);
            $url = $this->xml_entities($url);
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
        }
        return ($bool?false:'');
    }

    /**
     * Get current path
     * 
     * @return string
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * Delete file
     * 
     * @return bool
     */
    public function deleteFile(){
        return Filesystem::delete($this->path);
    }

    /**
     * Show modified block
     * 
     * @return string
     */
    public function showModifiedBlock(){
        return $this->modifiedblock;
    }

    /**
     * Show queue block
     * 
     * @return string
     */
    public function showQueueBlock(){
        return $this->enqueue;
    }

    /**
     * Show the original block (not modified)
     * 
     * @return string
     */
    public function showBlock(){
        return $this->block;
    }

    /**
     * Build queue for update or delete
     * 
     * @return $this
     */
    public function enqueue(){
        if ($this->mode == 'delete'){
            $this->enqueue[] = $this->deleteblock;
        } else {
            $this->enqueue .= $this->block;
        }
        return $this;
    }

    /**
     * Set Path
     * 
     * @param $path is the path of sitemap
     * 
     * @return $this
     */
    public function setPath($path){
        $this->path = $path;
        return $this;
    }

    /**
     * Set Limit
     * 
     * @param $value is the value for limit loc in sitemap file
     * 
     * @return $this
     */
    public function setLimit($value){
        $this->limit = $value;
        return $this;
    }

    /**
     * Parsing string into xml entities
     * 
     * @param $string is the string value to parse
     * 
     * @return string
     */
    public function xml_entities($string) {
        if(!empty($string)){
            $string = trim($string);
            $string = html_entity_decode($string, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $string = htmlspecialchars($string, ENT_QUOTES | ENT_XML1, 'UTF-8', false);
        }
        return $string;
    }
    
}
