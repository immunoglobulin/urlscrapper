# urlscrapper
A simple yet powerful url scrapper in PHP. To make url preview like facebook.

# How To use
1. Include the UrlScrapper Class
  include_once PATH/TO/UrlScrapper.php';
2. Create Object of UrlScrapper
  $urlScrapper = new UrlScrapper($url);
3. Call 'scrap_url' and pass Full URL as parameter
  $result = $urlScrapper->scrap_url($full_url)
  
# Response format
Array(
'protocol'=>'',//http or https
'embed_url'=>'',//In case of youtube or vimeo link, You can use embed url to directly show the preview
'title'=>'',//Page title
'site'=>'',//Domain
'description'=>'',//More information about the link
'thumbnail'=>'',//Thumbnail of the page
'url'=>''//Full URL itself
)
