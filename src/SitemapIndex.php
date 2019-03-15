<?php 
namespace SitemapManager;
use \SitemapManager\Helper\Filesystem;

class SitemapIndex extends SitemapHelper {

    var $path,$sitemapdata,$modifiedblock,$deleteblock,$mode,$block,$enqueue;
    var $limit='250';

    public function create(){
        if(!is_file($this->path)){
            $content = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"></sitemapindex>';
            return Filesystem::write($this->path,$content);
        }
        throw new \Exception(sprintf('File `%s` is already exists!', basename($this->path)));
    }

    public function setBlock($url){
        $this->block = "";
        $this->modifiedblock = "";
        preg_match('~<sitemap><loc>'.trim($url).'</loc>(.*)</sitemap>~Uis',$this->getDataSitemap(),$match);
        if (!empty($match)){
            $this->block = '<sitemap><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</sitemap>';
            $this->modifiedblock = '<sitemap><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</sitemap>';
        }
        return $this;
    }

    public function setLastMod($date){
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
        return $this;
    }

    public function unsetLastMod(){
        if(!empty($this->modifiedblock)){
            $this->modifiedblock = preg_replace('@<lastmod>(.+?)<\/lastmod>@', '', $this->modifiedblock);
        }
        return $this;
    }

    public function update(){
        $data = $this->getDataSitemap();
        if(!empty($this->block) && !empty($this->modifiedblock)){
            $data = str_replace($this->block,$this->modifiedblock,$data);
            return Filesystem::write($this->path,$data);
        }
        return false;
    }

    public function addBlock($url){
        if(!$this->has($url)) $this->block = '<sitemap><loc>'.trim($url).'</loc></sitemap>';
        return $this;
    }

    public function addLastMod($date){
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
        return $this;
    }

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

    public function delete($url){
        if($this->has($url)){
            $data = $this->getDataSitemap();
            preg_match('~<sitemap><loc>'.trim($url).'</loc>(.*)</sitemap>~Uis',$data,$match);
            if (!empty($match)){
                $data = str_replace('<sitemap><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</sitemap>','',$data);
                return Filesystem::write($this->path,$data);
            }
        }
        return false;
    }

    public function prepareDelete($url){
        $this->mode = 'delete';
        $data = $this->getDataSitemap();
        if($this->has($url)){
            preg_match('~<sitemap><loc>'.trim($url).'</loc>(.*)</sitemap>~Uis',$data,$match);
            if (!empty($match)){
                $this->deleteblock = '<sitemap><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</sitemap>';
            }
        }
        return $this;
    }

    public function generate($write=true){
        $files = Filesystem::getAllFiles('sitemap*.xml');
        if(($key = array_search('sitemap.xml',$files)) !== false){
            unset($files[$key]);
        }
        $content = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        foreach($files as $file){
            $content .= '<sitemap><loc>'.$file.'</loc></sitemap>';
        }
        $content .= '</sitemapindex>';
        if($write){
            return Filesystem::write('sitemap.xml',$content);
        } 
        return $content;
    }
}
