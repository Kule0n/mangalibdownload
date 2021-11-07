<?php
header('Content-Type: text/html; charset=utf-8');
include( './simplehtmldom_1_9_1/simple_html_dom.php' );

$url = "https://mangalib.me/gwihwanjaui-mabeob-eun-teugbyeolhaeya-habnida";


get_chapter($url);

function create_folder_main($name){
	$structure = './'.$name;
	
	if (!mkdir($structure, 0777, true)) {
		die('Не удалось создать директории...');
	}
}
function create_folder_second($namefolder,$chapter_number){
	$structure = './'.$namefolder."/".$chapter_number;
	if (!mkdir($structure, 0777, true)) {
	    die('Не удалось создать директории...');
	}
}
function GetImageFromUrl($link) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch,CURLOPT_URL,$link);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result=curl_exec($ch);
	curl_close($ch);
	return $result;
}
function save_image($url,$chapter_number,$chapter_slug,$chapter_id,$mangaslug,$image,$namefolder,$i){
	$sourcecode= GetImageFromUrl('https://img33.cdnlibs.link/manga/'.$mangaslug.'/chapters/'.$chapter_slug.'/'.$image);
	$savefile = fopen('./'.$namefolder."/".$chapter_number."/".$i.".png", 'w');
	fwrite($savefile, $sourcecode);
	fclose($savefile);

}
function get_image_info($url,$chapter_number,$chapter_slug,$chapter_id,$mangaslug,$namefolder){
	$url= $url.$chapter_number;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	    echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);
	$html2 = new simple_html_dom(  );
	$html2->load( $result );

	$script2 = $html2->find('script[id=pg]');
	foreach($script2 as $s) {
	    if(strpos($s->innertext, 'window.__pg') !== false) {
	        $script2 = $s->innertext;
	        break;
	    }
	}
	$script2 = substr($script2, 26);
	$script2 = preg_replace("!(?<=}]).+!is", "", $script2);
	$scriptarray2 = json_decode($script2);

	create_folder_second($namefolder,$chapter_number);
	$i = 1;
	foreach ($scriptarray2 as $key => $imagename) {
		save_image($url,$chapter_number,$chapter_slug,$chapter_id,$mangaslug,$imagename->u,$namefolder,$i);
		$i++;
	}


}
function get_chapter($url){
	$url = $url."?section=chapters";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
   curl_setopt($ch, CURLOPT_VERBOSE, 0);
   curl_setopt($ch, CURLOPT_POST, 1);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
   curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	    echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);
	$html = new simple_html_dom(  );
	$html->load( $result );
	$scripts = $html->find('script');
	$linkcharters = $html->find('a[class=button button_block button_primary]');

	foreach ($linkcharters as $key => $link) {
		if(strpos($link->innertext, 'Начать') !== false) {
       		$linkurl = $link->href."</br>";
       		$linkurl = substr($linkurl,0,-6);
        break;
    	}
	}

	foreach($scripts as $s) {
	    if(strpos($s->innertext, 'window.__DATA__') !== false) {
	        $script = $s->innertext;
	        break;
	    }
	}
	$script = substr($script, 31);
	$script = preg_replace("!(?<=]}}).+!is", "", $script);
	$scriptarray = json_decode($script);

	create_folder_main($scriptarray->manga->rusName);
	$scriptarray->chapters->list =array_reverse($scriptarray->chapters->list);
	foreach ($scriptarray->chapters->list as $key => $value) {
		get_image_info($linkurl,$value->chapter_number,$value->chapter_slug,$value->chapter_id,$scriptarray->manga->slug,$scriptarray->manga->rusName);
	}
}

