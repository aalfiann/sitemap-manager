<?php 
namespace SitemapManager\Helper;

/**
 * Class StringUtils is the utilities for do the things with string
 *
 * @package    SitemapManager/Helper
 * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
 * @copyright  Copyright (c) 2019 M ABD AZIZ ALFIAN
 * @license    https://github.com/aalfiann/sitemap-manager/blob/master/LICENSE.md MIT License
 */
class StringUtils {
    /**
     * First char checker (alternative to preg_match)
     * 
     * @param match = is the text to match
     * @param string = is the source text
     * 
     * @return bool 
     */
    public static function isMatchFirst($match,$string){
        if (substr($string, 0, abs(strlen($match))) == $match) return true;
        return false;
    }

    /**
     * Any char checker (alternative to preg_match)
     * 
     * @param match = is the text to match
     * @param string = is the source text
     * 
     * @return bool 
     */
    public static function isMatchAny($match,$string){
        if(strpos($string,$match) !== false) return true;
        return false;
    }

    /**
     * Last char checker (alternative to preg_match)
     * 
     * @param match = is the text to match
     * @param string = is the source text
     * 
     * @return bool 
     */
    public static function isMatchLast($match,$string){
        if (substr($string, (-1 * abs(strlen($match)))) == $match) return true;
        return false;
    }
}