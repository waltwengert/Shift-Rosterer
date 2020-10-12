<?php
  $xmlDoc = new DOMDocument();
  $xmlDoc->load("http://retlawnews.blogspot.com.au/feeds/posts/default?alt=rss");

  //retrieve blog title data
  $blog=$xmlDoc->getElementsByTagName('channel')->item(0);
  $blog_title = $blog->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
  $blog_link = $blog->getElementsByTagName('link')->item(0)->childNodes->item(0)->nodeValue;
  echo("<h2><a href='".$blog_link."'>".$blog_title."</a></h2>");

  //retrieve blog post data
  $x=$xmlDoc->getElementsByTagName('item');
  for ($i=0; $i<=2; $i++) {
    $post_title=$x->item($i)->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
    $post_link=$x->item($i)->getElementsByTagName('link')->item(0)->childNodes->item(0)->nodeValue;
    $post_desc=$x->item($i)->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;
    echo ("<h3><a href='".$post_link."'>".$post_title."</a></h3>");
    echo ("<p class='blog-body'>".$post_desc."</p>");
  }
?>
