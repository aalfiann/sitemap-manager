# Sitemap Manager

[![Version](https://img.shields.io/badge/stable-1.1.1-green.svg)](https://github.com/aalfiann/sitemap-manager)
[![Total Downloads](https://poser.pugx.org/aalfiann/sitemap-manager/downloads)](https://packagist.org/packages/aalfiann/sitemap-manager)
[![License](https://poser.pugx.org/aalfiann/sitemap-manager/license)](https://github.com/aalfiann/sitemap-manager/blob/HEAD/LICENSE.md)

A PHP class to manage static sitemap dynamically.

# Background
This SitemapManager class is allow you to do CRUD like insert and update url inside static sitemap. I've created this class because I need to manage sitemap for small sites which is static, file based or doesn't use any database engine. 

## Installation

Install this package via [Composer](https://getcomposer.org/).
```
composer require "aalfiann/sitemap-manager:^1.0"
```


## Example Usage

### Sitemap Index

#### Create blank custom sitemap index
Sitemap file is required before use this class, so you have to create one if you don't have any sitemap file exists on your server.
```php
require 'vendor/autoload.php';
use \SitemapManager\SitemapIndex;

$sm = new SitemapIndex;

// Create blank custom sitemap index
$sm->path = 'sitemap-index.xml';
$sm->create();
```

#### Auto insert sitemap into sitemap index

```php
// Set the first sitemap file
$sm->path = 'sitemap-index.xml';
// Set the last sitemap file (will increment automatically)
$sm->setLastFile();

// Check the url in all sitemap files
$url = 'http://yourdomain.com/sitemap-test.xml';
if(!$sm->find($url)){
    $sm->addBlock($url)
        ->addLastMod(date('Y-m-d'))
        ->save();
}
```

#### Add many sitemap into sitemap index directly
Note:  
 - Make sure you know the limit of the sitemap
 - Enqueue will stop if you reach the limit
```php
$sm->path = 'sitemap-index.xml';
for ($i=1;$i<10;$i++){
    $sm->addBlock('http://yourdomain.com/sitemap-test-'.$i.'.xml')
        ->addLastMod(date('Y-m-d'))
        ->enqueue();
}
$sm->save();
```

#### Update sitemap inside sitemap index dynamically
```php
$url = 'http://yourdomain.com/sitemap-test-5.xml';
// Set the default path sitemap (required for finding range sitemap files)
$sm->path = 'sitemap-index.xml';
// Find the sitemap files if you not sure where is url located
$path = $sm->find($url,false);
if(!empty($path)){
    $sm->path = $path;
    $sm->setBlock($url)
        ->unsetLastMod()
        ->update();
}
```

#### Update sitemap inside sitemap index directly
Note: Make sure you have already know where is sitemap url located inside sitemap file.
```php
$url = 'http://yourdomain.com/sitemap-test-4.xml';
// Set the path sitemap
$sm->path = 'sitemap-index.xml';
$sm->setBlock($url)
    ->unsetLastMod()
    ->update();
```

#### Delete sitemap inside sitemap index
```php
$sm->path = 'sitemap-index.xml';
$sm->delete('http://yourdomain.com/sitemap-test-3.xml');
```

#### Delete many sitemap inside sitemap index
```php
$sm->path = 'sitemap-index.xml';
for($i=5;$i<10;$i++){
    $sm->prepareDelete('http://yourdomain.com/sitemap-test-'.$i.'.xml')->enqueue();
}
$sm->save();
```

#### Delete sitemap file
```php
$sm->path = 'sitemap-index.xml';
$sm->deleteFile();
```

#### Generate Sitemap.xml
```php
// Generate All sitemap index into file sitemap.xml
// Note: You need a cronjob to make this refreshed automatically
$sm->generate();

// Generate All sitemap index into string
echo $sm->generate(false);
```

---

### Sitemap Urlset

#### Create blank custom sitemap urlset
Sitemap file is required before use this class, so you have to create one if you don't have any sitemap file exists on your server.
```php
require 'vendor/autoload.php';
use \SitemapManager\Sitemap;

$sm = new Sitemap;

// Create blank custom sitemap urlset
$sm->path = 'sitemap-post.xml';
$sm->create();
```

#### Auto insert url into sitemap urlset
```php
// Set the first sitemap file
$sm->path = 'sitemap-post.xml';
// Set the last sitemap file (will increment automatically)
$sm->setLastFile();
// Check the url in all sitemap files
$url = 'http://yourdomain.com/test-suka-suka-aja-13';
if(!$sm->find($url)){
    $sm->addBlock($url)
        ->addChangeFreq('monthly')
        ->addLastMod(date('Y-m-d'))
        ->addPriority(0.9)
        ->save();
}
```

#### Add many url into sitemap urlset directly
Note:  
 - Make sure you know the limit of the sitemap
 - Enqueue will stop if you reach the limit
```php
$sm->path = 'sitemap-post.xml';
for ($i=0;$i<10;$i++){
    $sm->addBlock('http://yourdomain.com/test-suka-suka-aja-'.$i)
        ->addChangeFreq('monthly')
        ->addLastMod(date('Y-m-d'))
        ->addPriority(0.9)
        ->enqueue();
}
$sm->save();
```

#### Update Url inside sitemap urlset dynamically
```php
$url = 'http://yourdomain.com/test-suka-suka-aja-7';
// Set the default path sitemap (required for finding range sitemap files)
$sm->path = 'sitemap-post.xml';
// Find the sitemap files if you not sure where is url located
$path = $sm->find($url,false);
if(!empty($path)){
    $sm->path = $path;
    $sm->setBlock($url)
        ->setChangeFreq('daily')
        ->setLastMod(date('Y-m-d'))
        ->setPriority(0.5)
        ->update();
}
```

#### Update Url inside sitemap urlset directly
Note: Make sure you have already know where is sitemap url located inside sitemap file.
```php
$url = 'http://yourdomain.com/test-suka-suka-aja-7';
// Set the path sitemap
$sm->path = 'sitemap-post.xml';
$sm->setBlock($url)
    ->setChangeFreq('monthly')
    ->setLastMod(date('Y-m-d'))
    ->setPriority(0.9)
    ->update();
```

#### Delete url inside sitemap urlset
```php
$sm->path = 'sitemap-post.xml';
$sm->delete('http://yourdomain.com/test-suka-suka-aja-7');
```

#### Delete many url inside sitemap urlset
```php
$sm->path = 'sitemap-post.xml';
for($i=0;$i<10;$i++){
    $sm->prepareDelete('http://yourdomain.com/test-suka-suka-aja-'.$i)->enqueue();
}
$sm->save();
```


How to Contribute
-----------------
### Pull Requests

1. Fork this `sitemap-manager` repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the develop branch