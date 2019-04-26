<?php 
namespace SitemapManager;
use \SitemapManager\Helper\Filesystem;

/**
 * Class SitemapIndex for manage the sitemap inside sitemap index
 *
 * @package    SitemapManager
 * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
 * @copyright  Copyright (c) 2019 M ABD AZIZ ALFIAN
 * @license    https://github.com/aalfiann/sitemap-manager/blob/master/LICENSE.md MIT License
 */
class SitemapIndex extends SitemapHelper {

    /**
     * Sitemap path
     */
    var $path;

    /**
     * Sitemap data
     */
    var $sitemapdata;

    /**
     * Modified block
     */
    var $modifiedblock;

    /**
     * Delete block (used in queue for delete)
     */
    var $deleteblock;

    /**
     * Mode (used in saving queue for delete)
     */
    var $mode;

    /**
     * Block is temporary data for compare 
     */
    var $block;

    /**
     * Equeue data
     */
    var $enqueue;

    /**
     * Limit for each sitemap file
     */
    var $limit='1000';

    /**
     * Create blank sitemap
     * 
     * @return bool
     */
    public function create(){
        if(!is_file($this->path)){
            $content = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"></sitemapindex>';
            return Filesystem::write($this->path,$content);
        }
        throw new \Exception(sprintf('File `%s` is already exists!', basename($this->path)));
    }

    /**
     * Set block <sitemap> tags inside sitemap
     * 
     * @param $url is the sitemap loc
     * 
     * @return $this
     */
    public function setBlock($url){
        if(!empty($url)){
            $this->block = "";
            $this->modifiedblock = "";
            $url = $this->xml_entities($url);
            preg_match('~<sitemap><loc>'.trim($url).'</loc>(.*)</sitemap>~Uis',$this->getDataSitemap(),$match);
            if (!empty($match)){
                $this->block = '<sitemap><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</sitemap>';
                $this->modifiedblock = '<sitemap><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</sitemap>';
            }
        }
        return $this;
    }

    /**
     * Set <lastmod> property inside <sitemap>
     * 
     * @param $date is the date for last modified
     * 
     * @return $this
     */
    public function setLastMod($date){
        if(!empty($date)){
            if(!empty($this->modifiedblock)){
                $block = $this->modifiedblock;
                $temp = explode('<lastmod>',$block);
                if(!empty($temp[1])){
                    $temp1 = explode('</lastmod>',$temp[1]);
                    $block = str_replace('<lastmod>'.$temp1[0].'</lastmod>','<lastmod>'.$date.'</lastmod>',$block);
                    $this->modifiedblock = $block;
                } else {
                    $block = str_replace(['<sitemap>','</sitemap>'],'',$block);
                    $this->modifiedblock = '<sitemap>'.$block.'<lastmod>'.$date.'</lastmod></sitemap>';
                }
            }
        }
        return $this;
    }

    /**
     * Remove Last Mod property sitemap
     * 
     * @return $this
     */
    public function unsetLastMod(){
        if(!empty($this->modifiedblock)){
            $this->modifiedblock = preg_replace('@<lastmod>(.+?)<\/lastmod>@', '', $this->modifiedblock);
        }
        return $this;
    }

    /**
     * Execute for update
     * 
     * @return bool
     */
    public function update(){
        $data = $this->getDataSitemap();
        if(!empty($this->block) && !empty($this->modifiedblock)){
            $data = str_replace($this->block,$this->modifiedblock,$data);
            return Filesystem::write($this->path,$data);
        }
        return false;
    }

    /**
     * Add new block <sitemap> tags inside sitemap
     * 
     * @param $url is the url loc
     * 
     * @return $this
     */
    public function addBlock($url){
        if(!empty($url)){
            $url = $this->xml_entities($url);
            if(!$this->has($url)) $this->block = '<sitemap><loc>'.trim($url).'</loc></sitemap>';
        }
        return $this;
    }

    /**
     * Add new <lastmod> property inside <sitemap> tags
     * 
     * @param $date is the date for last modified
     * 
     * @return $this
     */
    public function addLastMod($date){
        if(!empty($date)){
            if(!empty($this->block)){
                $block = $this->block;
                $temp = explode('<lastmod>',$block);
                if(!empty($temp[1])){
                    $temp1 = explode('</lastmod>',$temp[1]);
                    $block = str_replace('<lastmod>'.$temp1[0].'</lastmod>','<lastmod>'.$date.'</lastmod>',$block);
                    $this->block = $block;
                } else {
                    $block = str_replace(['<sitemap>','</sitemap>'],'',$block);
                    $this->block = '<sitemap>'.$block.'<lastmod>'.$date.'</lastmod></sitemap>';
                }
            }
        }
        return $this;
    }

    /**
     * Execute for save
     * 
     * @return bool
     */
    public function save(){
        $data = $this->getDataSitemap();
        if($this->mode == 'delete' && is_array($this->enqueue)){
            $data = str_replace($this->enqueue,'',$data);
            $this->mode == "";
            $this->enqueue = "";
            return Filesystem::write($this->path,$data);
        } else {
            if(!empty($this->block) && empty($this->enqueue)){
                $temp = explode('">',$data);
                if(!empty($temp[0]) && !empty($temp[1])){
                    $temp1 = explode('</sitemapindex>',$temp[1]);
                    if($this->isLimit()){
                        $data = $temp[0].'">'.$this->block.'</sitemapindex>';
                        return Filesystem::write(Filesystem::incrementFilename($this->path),$data);
                    } else {
                        $data = $temp[0].'">'.$temp1[0].$this->block.'</sitemapindex>';
                        return Filesystem::write($this->path,$data);
                    }
                }
                if(!empty($temp[0])){
                    $data = $temp[0].'">'.$this->block.'</sitemapindex>';
                    return Filesystem::write($this->path,$data);
                }
            }
    
            if(!empty($this->enqueue)){
                $temp = explode('">',$data);
                if(!empty($temp[0]) && !empty($temp[1])){
                    $temp1 = explode('</sitemapindex>',$temp[1]);
                    $temp2 = explode('</sitemap>',$this->enqueue);
                    if($this->isLimit((count($temp2)-1))){
                        $data = $temp[0].'">'.$this->enqueue.'</sitemapindex>';
                        $this->enqueue = "";
                        return Filesystem::write(Filesystem::incrementFilename($this->path),$data);
                    } else {
                        $data = $temp[0].'">'.$temp1[0].$this->enqueue.'</sitemapindex>';
                        $this->enqueue = "";
                        return Filesystem::write($this->path,$data);
                    }
                }
                if(!empty($temp[0])){
                    $data = $temp[0].'">'.$this->enqueue.'</sitemapindex>';
                    $this->enqueue = "";
                    return Filesystem::write($this->path,$data);
                }
            }
        }

        return false;
    }

    /**
     * Delete block <sitemap> inside sitemapindex
     * 
     * @param $url is the url loc
     * 
     * @return bool 
     */
    public function delete($url){
        if(!empty($url)){
            $url = $this->xml_entities($url);
            if($this->has($url)){
                $data = $this->getDataSitemap();
                preg_match('~<sitemap><loc>'.trim($url).'</loc>(.*)</sitemap>~Uis',$data,$match);
                if (!empty($match)){
                    $data = str_replace('<sitemap><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</sitemap>','',$data);
                    return Filesystem::write($this->path,$data);
                }
            }
        }
        return false;
    }

    /**
     * Prepare item block for queue to delete
     * 
     * @param $url is the url loc
     * 
     * @return $this
     */
    public function prepareDelete($url){
        if(!empty($url)){
            $this->mode = 'delete';
            $data = $this->getDataSitemap();
            $url = $this->xml_entities($url);
            if($this->has($url)){
                preg_match('~<sitemap><loc>'.trim($url).'</loc>(.*)</sitemap>~Uis',$data,$match);
                if (!empty($match)){
                    $this->deleteblock = '<sitemap><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</sitemap>';
                }
            }
        }
        return $this;
    }

    /**
     * Generate Sitemap Index automatically
     * 
     * @param string $url           this is your url website. Ex. https://yourdomain.com [without trailing slash]
     * @param string|array $dir     you can add another directory of sitemap here. [path without trailing slash]
     * @param bool $write           if you set this to true then will create sitemap.xml, if false will return string
     * 
     * @return mixed    bool/string
     */
    public function generate($url,$dir='',$write=true){
        if(!empty($url)){
            if(!empty($dir)){
                $files = Filesystem::getAllFiles('sitemap*.xml');
                if(is_array($dir)){
                    foreach($dir as $value){
                        $new = Filesystem::getAllFiles($value.'/'.'sitemap*.xml');
                        foreach($new as $val){
                            $files[] = $val;
                        }
                    }
                } else {
                    $new = Filesystem::getAllFiles($dir.'/'.'sitemap*.xml');
                    foreach($new as $val){
                        $files[] = $val;
                    }
                }
            } else {
                $files = Filesystem::getAllFiles('sitemap*.xml');
            }
            if(($key = array_search('sitemap.xml',$files)) !== false){
                unset($files[$key]);
            }
            $content = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
            foreach($files as $file){
                $content .= '<sitemap><loc>'.$url.'/'.$file.'</loc></sitemap>';
            }
            $content .= '</sitemapindex>';
            if($write){
                return Filesystem::write('sitemap.xml',$content);
            }
        }
        return $content;
    }
    
}
