<?php

include '../vendor/autoload.php';

use Masterminds\HTML5;

if (true) {
    $html = file_get_contents('cache.html');
} else {
    $url = 'https://www.craigslist.org/about/sites';
    $html = file_get_contents($url);
    file_put_contents('cache.html', $html);
}

$data = [];

$html5 = new HTML5();
$dom = $html5->loadHTML($html);
$finder = new DomXPath($dom);

$section = $dom->getElementsByTagName('section')->item(0);

$region_names_list = $section->getElementsByTagName('h1');
$region_list = $finder->query("//*[contains(@class, 'colmask')]", $section);

foreach($region_list as $region_i => $region){
    $region_name = $region_names_list->item($region_i)->textContent;
    $data[$region_name] = [];
    $state_list = $region->getElementsByTagName('h4');
    $city_lists = $region->getElementsByTagName('ul');
    foreach($state_list as $state_i => $state){
        $state_name = $state->textContent;
        $data[$region_name][$state_name] = [];
        $city_list = $city_lists->item($state_i)->getElementsByTagName('li');
        foreach($city_list as $city_i => $city){
            $a = $city->getElementsByTagName('a')->item(0);
            $link = $a->getAttribute('href');
            $sub_domain = substr($link, 2, strpos($link, '.') - 2);
            $city_name = ucwords($a->textContent);
            $data[$region_name][$state_name][$sub_domain] = [
                'name' => $city_name,
                'link' => $link
            ];
        }
    }
}

$json = json_encode($data, JSON_PRETTY_PRINT);

file_put_contents('../data.json', $json);