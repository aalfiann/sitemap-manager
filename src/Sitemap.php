<?php 
namespace SitemapManager;
use \SitemapManager\Helper\Filesystem;

class Sitemap extends SitemapHelper {

    var $path,$sitemapdata,$modifiedblock,$deleteblock,$mode,$block,$enqueue;
    var $limit='250';

    public function create(){
        if(!file_exists($this->path)){
            $content = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"></urlset>';
            return Filesystem::write($this->path,$content);
        }
        throw new \Exception(sprintf('File `%s` is already exists!', basename($this->path))); 
    }

    public function setBlock($url){
        $this->block = "";
        $this->modifiedblock = "";
        preg_match('~<url><loc>'.trim($url).'</loc>(.*)</url>~Uis',$this->getDataSitemap(),$match);
        if (!empty($match)){
            $this->block = '<url><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</url>';
            $this->modifiedblock = '<url><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</url>';
        }
        return $this;
    }

    public function setChangeFreq($freq){
        if(!empty($this->modifiedblock)){
            $block = $this->modifiedblock;
            $temp = explode('<changefreq>',$block);
            if(!empty($temp[1])){
                $temp1 = explode('</changefreq>',$temp[1]);
                $block = str_replace('<changefreq>'.$temp1[0].'</changefreq>','<changefreq>'.$freq.'</changefreq>',$block);
                $this->modifiedblock = $block;
            } else {
                $block = str_replace(['<url>','</url>'],'',$block);
                $this->modifiedblock = '<url>'.$block.'<changefreq>'.$freq.'</changefreq></url>';
            }
        }
        return $this;
    }

    public function unsetChangeFreq(){
        if(!empty($this->modifiedblock)){
            $this->modifiedblock = preg_replace('@<changefreq>(.+?)<\/changefreq>@', '', $this->modifiedblock);
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
                $block = str_replace(['<url>','</url>'],'',$block);
                $this->modifiedblock = '<url>'.$block.'<lastmod>'.$date.'</lastmod></url>';
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

    public function setPriority($priority){
        if(!empty($this->modifiedblock)){
            $block = $this->modifiedblock;
            $temp = explode('<priority>',$block);
            if(!empty($temp[1])){
                $temp1 = explode('</priority>',$temp[1]);
                $block = str_replace('<priority>'.$temp1[0].'</priority>','<priority>'.$priority.'</priority>',$block);
                $this->modifiedblock = $block;
            } else {
                $block = str_replace(['<url>','</url>'],'',$block);
                $this->modifiedblock = '<url>'.$block.'<priority>'.$priority.'</priority></url>';
            }
        }
        return $this;
    }

    public function unsetPriority(){
        if(!empty($this->modifiedblock)){
            $this->modifiedblock = preg_replace('@<priority>(.+?)<\/priority>@', '', $this->modifiedblock);
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
        if(!$this->has($url)) $this->block = '<url><loc>'.trim($url).'</loc></url>';
        return $this;
    }

    public function addChangeFreq($freq){
        if(!empty($this->block)){
            $block = $this->block;
            $temp = explode('<changefreq>',$block);
            if(!empty($temp[1])){
                $temp1 = explode('</changefreq>',$temp[1]);
                $block = str_replace('<changefreq>'.$temp1[0].'</changefreq>','<changefreq>'.$freq.'</changefreq>',$block);
                $this->block = $block;
            } else {
                $block = str_replace(['<url>','</url>'],'',$block);
                $this->block = '<url>'.$block.'<changefreq>'.$freq.'</changefreq></url>';
            }
        }
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
                $block = str_replace(['<url>','</url>'],'',$block);
                $this->block = '<url>'.$block.'<lastmod>'.$date.'</lastmod></url>';
            }
        }
        return $this;
    }

    public function addPriority($priority){
        if(!empty($this->block)){
            $block = $this->block;
            $temp = explode('<priority>',$block);
            if(!empty($temp[1])){
                $temp1 = explode('</priority>',$temp[1]);
                $block = str_replace('<priority>'.$temp1[0].'</priority>','<priority>'.$priority.'</priority>',$block);
                $this->block = $block;
            } else {
                $block = str_replace(['<url>','</url>'],'',$block);
                $this->block = '<url>'.$block.'<priority>'.$priority.'</priority></url>';
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
                    $temp1 = explode('</urlset>',$temp[1]);
                    if($this->isLimit()){
                        $data = $temp[0].'">'.$this->block.'</urlset>';
                        return Filesystem::write(Filesystem::incrementFilename($this->path),$data);
                    } else {
                        $data = $temp[0].'">'.$temp1[0].$this->block.'</urlset>';
                        return Filesystem::write($this->path,$data);
                    }
                }
                if(!empty($temp[0])){
                    $data = $temp[0].'">'.$this->block.'</urlset>';
                    return Filesystem::write($this->path,$data);
                }
            }
    
            if(!empty($this->enqueue)){
                $temp = explode('">',$data);
                if(!empty($temp[0]) && !empty($temp[1])){
                    $temp1 = explode('</urlset>',$temp[1]);
                    $temp2 = explode('</url>',$this->enqueue);
                    if($this->isLimit((count($temp2)-1))){
                        $data = $temp[0].'">'.$this->enqueue.'</urlset>';
                        $this->enqueue = "";
                        return Filesystem::write(Filesystem::incrementFilename($this->path),$data);
                    } else {
                        $data = $temp[0].'">'.$temp1[0].$this->enqueue.'</urlset>';
                        $this->enqueue = "";
                        return Filesystem::write($this->path,$data);
                    }
                }
                if(!empty($temp[0])){
                    $data = $temp[0].'">'.$this->enqueue.'</urlset>';
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
            preg_match('~<url><loc>'.trim($url).'</loc>(.*)</url>~Uis',$data,$match);
            if (!empty($match)){
                $data = str_replace('<url><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</url>','',$data);
                return Filesystem::write($this->path,$data);
            }
        }
        return false;
    }

    public function prepareDelete($url){
        $this->mode = 'delete';
        $data = $this->getDataSitemap();
        if($this->has($url)){
            preg_match('~<url><loc>'.trim($url).'</loc>(.*)</url>~Uis',$data,$match);
            if (!empty($match)){
                $this->deleteblock = '<url><loc>'.trim($url).'</loc>'.(!empty($match[1])?trim($match[1]):'').'</url>';
            }
        }
        return $this;
    }
}
