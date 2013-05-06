<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
     
    require 'Swift/swift_required.php';
     
    class Swift_libraries{
    
    	function __construct(){
    
    	}
    	
    	/**
	     * @author Semyon Babushkin next15@mail.ru
	     * Make all images embed in email body
	     * @param string $body with embed images
	     */
    	
    	function embed_images(&$message,$body){
	    	preg_match_all("/(src|background)=[\"'](.*)[\"']/Ui", $body, $images); 
			
	    	if(isset($images[2]) && count($images[2])) {
				foreach($images[2] as $i => $url) {
					$filename = basename($url);
					// проверяем есть ли ответ от сервера с кодом 200 - ОК
					$Headers = @get_headers($url);
					if(!preg_match("|200|", $Headers[0])) {
					    continue;
					}
					$cid = $message->embed(Swift_Image::fromPath($url));
					$body = preg_replace("/".$images[1][$i]."=[\"']".preg_quote($url, '/')."[\"']/Ui", $images[1][$i]."=\"".$cid."\"", $body);
				}
			}
			return $body;
    	}
    }
