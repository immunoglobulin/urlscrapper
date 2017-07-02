<?php

/**
* URL Scrapper Class
* @author Vijay Rathore <vijayrathore8492@gmail.com>
*/

class UrlScrapper 
{
	private $url;
	private $document;
	function __construct($url)
	{
		$this->url = $url;
		$this->loadDocument($url);

	}

	/**
     * [urlScrapperAction Extract information about url]
     * @param [string] $url [URL to scrap]
     * @return [Array]        [information about the url]
     * @author Vijay Rathore <vijayrathore8492@gmail.com>
     */
	public function scrap_url()
	{
		libxml_use_internal_errors(true);
		$tags = get_meta_tags($this->curlWithUserAgent());
		//echo "<pre>";print_r($tags);die;
		try{
			$result = array();
			$urlinfo = parse_url($this->url);
			$result['protocol'] = $urlinfo['scheme'];
			$result['embed_url'] = !empty($tags['twitter:player'])?$tags['twitter:player']:'';
			//page title
			if(!empty($tags['twitter:title'])){
				$result['title'] = $tags['twitter:title'];
			}else if(!empty($tags['title'])){
				$result['title'] = $tags['title'];
			}
			//page site
			if(!empty($tags['twitter:site'])){
				$result['site'] = $tags['twitter:site'];
			}else if(!empty($tags['twitter:domain'])){
				$result['site'] = $tags['twitter:domain'];
			}
			//page description
			if(!empty($tags['twitter:description'])){
				$result['description'] = $tags['twitter:description'];
			}else if(!empty($tags['description'])){
				$result['description'] = $tags['description'];
			}
			//page image
			if(!empty($tags['twitter:image'])){
				$result['thumbnail'] = $tags['twitter:image'];
			}

			$result['url'] = !empty($tags['twitter:url'])?$tags['twitter:url']:$this->url;
		}
		catch(Exception $e){
			return array();
		}

		if(empty($result['site'])){
			$result['site'] = $urlinfo['host'];
		}

		if(empty($result['title'])){
			$result['title'] = $this->mineUrlByTag(array('title','h1','h2','h3'));
		}

		if(empty($result['thumbnail'])){
			$result['thumbnail'] = $this->mineLogoImage();
			if (filter_var($result['thumbnail'], FILTER_VALIDATE_URL) === false) {
			    $result['thumbnail'] = $result['protocol'].'://'.$result['site'].$result['thumbnail'];
			}
		}

		if(empty($result['description'])){
			$result['description'] = $this->mineUsefulText();
		}

		return $result;
	}


	private function mineLogoImage()
	{
		$nodes = $this->document->getElementsByTagName('a');
		foreach ($nodes as $node) {
			foreach ($node->attributes as $attr) {
				if( $attr->name =='class' && ( stripos($attr->textContent,'logo')!==false || stripos($attr->textContent,'brand') !==false || stripos($attr->textContent,'icon') !==false )){
					foreach ($node->childNodes as $child) {
						if(!empty($child->tagName) && $child->tagName == 'img'){
							foreach ($child->attributes as $ch_attr) {
								if($ch_attr->name == 'src'){
									return $ch_attr->textContent;
								}
							}
						}
					}
				}
			}
		}
		
		$fevicon = $this->findFevicon();
		if(!empty($fevicon)){
			foreach ($fevicon->attributes as $attr) {
				if($attr->name == 'href'){
					return $attr->textContent;
				}
			}
		}
		
		//return default image
		return SITE_HTTP.SITE_URL.'/image/engagement/old/newsurvey.png';
	}

	private function mineUsefulText()
	{
		$nodes = $this->document->getElementsByTagName('p');
		foreach ($nodes as $node) {
			if(strlen($node->textContent)>50)
				return $node->textContent;
		}
		return '';
	}


	private function findFevicon()
	{
		$nodes = $this->document->getElementsByTagName('link');
		foreach ($nodes as $node) {
			foreach ($node->attributes as $attr) {
				if($attr->name == 'rel' && (stripos($attr->textContent,'icon') !== false || stripos($attr->textContent,'shortcut') !== false)){
					return $node;
				}
			}
		}
		return null;
	}

	private function loadDocument()
	{
		if(!empty($this->document)) return $this->document;
		libxml_use_internal_errors(true);
		$html = file_get_contents($this->curlWithUserAgent());
		$this->document = new DOMDocument();
		$this->document->loadHTML($html);
	}

	private function mineUrlByTag($tags)
	{
		if(empty($tags)) return false;
		$nodes = $this->document->getElementsByTagName($tags[0]);
		$node1st = null;
		foreach ($nodes as $node) {
			$node1st = $node;
			break;
		}
		if(!empty($node1st->textContent)){
			return $node1st->textContent;
		}
		array_shift($tags);
		return $this->mineUrlByTag($tags);
	}

	private function curlWithUserAgent()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		$html = curl_exec($ch);
		curl_close($ch);
		$temp_file = tempnam(sys_get_temp_dir(), 'htm');
		$handle = fopen($temp_file, "w");
		fwrite($handle, $html);
		fclose($handle);
		return $temp_file;
	}
}
