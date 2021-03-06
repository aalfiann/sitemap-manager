<?php
namespace SitemapManager\Helper;
use \SitemapManager\Helper\Scanner;
use \SitemapManager\Helper\StringUtils;

/**
 * Class Filesystem for manage the files
 *
 * @package    SitemapManager/Helper
 * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
 * @copyright  Copyright (c) 2019 M ABD AZIZ ALFIAN
 * @license    https://github.com/aalfiann/sitemap-manager/blob/master/LICENSE.md MIT License
 */
class Filesystem
{

    /**
     * Reading file
     *
     * @param string $path     The absolute file path to write to
     * 
     * @return mixed            string/bool
     */
    public static function read($path)
    {
        if(is_file($path)) {
            $contents = '';
            $file = fopen($path, 'r');
            $size = filesize($path);
            if($size > 0) $contents = fread($file, $size);
            fclose($file);
            return $contents;
        }
        return false;
    }



    //--------------------------------------------------------------------


    /**
     * Writes data to the filesystem.
     *
     * @param  string $path     The absolute file path to write
     * @param  string $contents The contents of the file to write
     *
     * @return boolean          Returns true if write was successful, false if not.
     */
    public static function write($path, $contents)
    {
        // auto create directory when not exists
        if(!file_exists(dirname($path))) mkdir(dirname($path), 0777, true);

        $fp = fopen($path, 'w+');

        if(!flock($fp, LOCK_EX))
        {
            return false;
        }

        $result = fwrite($fp, $contents);

        flock($fp, LOCK_UN);
        fclose($fp);

        return $result !== false;
    }


    //--------------------------------------------------------------------


    /**
     * Delete file
     *
     * @param string $path  The absolute file path to delete
     *
     * @return boolean      True if deleted, false if not.
     */
    public static function delete($path)
    {
        if (is_file($path)) return unlink($path);
        return false;
    }


    //--------------------------------------------------------------------


    /**
     * Validates the name of the file to ensure it can be stored in the
     * filesystem.
     *
     * @param string $name              The name to validate against
     * @param boolean $safe_filename    Allows filename to be converted if fails validation
     *
     * @return bool                     Returns true if valid. Throws an exception if not.
     */
    public static function validateName($name, $safe_filename)
    {
        if (!preg_match('/^[0-9A-Za-z\_\-]{1,63}$/', $name))
        {
            if ($safe_filename === true)
            {
                // rename the file
                $name = preg_replace('/[^0-9A-Za-z\_\-]/','', $name);

                // limit the file name size
                $name = substr($name,0,63);
            }
            else
            {
                throw new \Exception(sprintf('`%s` is not a valid file name.', $name));
            }
        }

        return $name;
    }

    /**
     * Auto increment filename to prevent file replaced
     * 
     * @param string $path      is the path of filename
     * 
     * @return string           new/old path 
     */
    public static function incrementFilename($path)
    {
        $dir = dirname($path);
        $file = basename($path);
        if(is_file($path))
        {
            $file = preg_replace('@\-\[(.+?)\]@', '', $file);
            $file1 = explode('.',$file);
            $k = 0;
            $result = '';
            while(!$result)
            {
                $newfile = $dir.DIRECTORY_SEPARATOR.$file1[0].'-['.$k.']'.(!empty($file1[1])?'.'.$file1[1]:'');
                if(!is_file($newfile)) $result = $newfile;
                $k++;
            }
            return $result;    
        }
        return $dir.DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Last increment filename
     * 
     * @param string $path  is the path of filename 
     * 
     * @return string       new/old path 
     */
    public static function lastIncrementFilename($path)
    {
        $dir = dirname($path);
        $file = basename($path);
        if(is_file($path))
        {
            if($file == 'sitemap.xml'){
                return $file;
            }
            $lfile = $file;
            if (StringUtils::isMatchAny('-[',$lfile)){
                $lfile = preg_replace('@\-\[(.+?)\]@', '*', $lfile);
            } else {
                $lfile = str_replace('.xml', '*.xml', $lfile);
            }
            $lfiles = self::getAllFiles($dir.DIRECTORY_SEPARATOR.$lfile);
            if(count($lfiles)>1){
                $file = preg_replace('@\-\[(.+?)\]@', '', $file);
                $file1 = explode('.',$file);
                $k = 0;
                $result = '';
                while(!$result)
                {
                    $newfile = $dir.DIRECTORY_SEPARATOR.$file1[0].'-['.$k.']'.(!empty($file1[1])?'.'.$file1[1]:'');
                    if(!is_file($newfile)) {
                        if($k > 0){
                            $result = $dir.DIRECTORY_SEPARATOR.$file1[0].'-['.($k-1).']'.(!empty($file1[1])?'.'.$file1[1]:'');
                        } else {
                            $result = $newfile;
                        }
                    }
                    $k++;
                }
                return $result; 
            }
        }
        return $dir.DIRECTORY_SEPARATOR.$file;
    }


    //--------------------------------------------------------------------


    /**
     * Get an array containing the path of all files in this repository
     * 
     * @param $path         is the full path filename with wildcard
     * @param $recursive    if set to true then will search into sub folder. Default is false.
     *
     * @return array        An array, item is a file
     */
    public static function getAllFiles($path = '',$recursive=false)
    {
        if($recursive) {
            return Scanner::recursiveGlob($path);
        }
        return glob($path); 
    }

}
