{**
 * plugins/generic/cemog/bookreaderLink.tpl
 * Copyright (c) 2017 FU Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Link to embedded viewing of a Bookreader galley.
 *}

	{url|assign:"imgBaseUrl" op="download" path=$publishedMonograph->getId()|to_array:$submissionFile->getAssocId():$submissionFile->getFileIdAndRevision() escape=false}{* Assoc ID is publication format *}
	{url|assign:"bookUrl" page="catalog" op="book" path=$publishedMonograph->getId() escape=false}
	{assign var=bookTitle value=$publishedMonograph->getLocalizedFullTitle()|strip_unsafe_html}	
	{assign var=bookTitleTruncated value=$publishedMonograph->getLocalizedFullTitle()|strip_unsafe_html|truncate:70}
	{assign var=bookAuthor value=$publishedMonograph->getAuthorString()}
	{assign var=submissionId value=$publishedMonograph->getId()}
	{assign var=bookAbstract value=$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html|replace:"\n":""}
	
    <link rel="stylesheet" type="text/css" href="{$pluginUrl}/bookreader/BookReader/mmenu/dist/css/jquery.mmenu.css" />
    <link rel="stylesheet" type="text/css" href="{$pluginUrl}/bookreader/BookReader/mmenu/dist/addons/navbars/jquery.mmenu.navbars.css" />
    <link rel="stylesheet" type="text/css" href="{$pluginUrl}/bookreader/BookReader/BookReader.css" id="BRCSS"/>

    <!-- Custom CSS overrides -->
    <link rel="stylesheet" type="text/css" href="{$pluginUrl}/bookreader/BookReaderCeMoG/BookReaderCeMoG.css"/>

    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReaderCeMoG/jquery-1.10.1.js"></script>
    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReader/jquery-ui-1.12.0.min.js"></script>
    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReader/jquery.browser.min.js"></script>
    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReader/dragscrollable-br.js"></script>
    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReader/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReader/jquery.bt.min.js"></script>
    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReader/mmenu/dist/js/jquery.mmenu.min.js"></script>
    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReader/mmenu/dist/addons/navbars/jquery.mmenu.navbars.min.js" ></script>
    <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReader/BookReader.js"></script> 

<script type="text/javascript"><!--{literal}
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var imgBaseUrl = '{/literal}{$imgBaseUrl|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var bookUrl = '{/literal}{$bookUrl|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var bookTitle = '{/literal}{$bookTitle|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var bookTitleTruncated = '{/literal}{$bookTitleTruncated|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var bookAuthor = '{/literal}{$bookAuthor|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var baseUrl = '{/literal}{$baseUrl|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var numLeafs = '{/literal}{$fileCount|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var submissionId = '{/literal}{$submissionId|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));
	document.writeln(unescape("%3Cscript type='text/javascript'%3E") +" var bookAbstract = '{/literal}{$bookAbstract|escape:'javascript'}{literal}';" + unescape("%3C/script%3E"));

// -->{/literal}
</script>  
   
   
   
   <div id="BookReader">
      <noscript>
      <p>
          The BookReader requires JavaScript to be enabled. Please check that your browser supports JavaScript and that it is enabled in the browser settings.  You can also try one of the <a href="http://www.archive.org/details/goodytwoshoes00newyiala"> other formats of the book</a>.
      </p>
      </noscript>
  </div>
  <script type="text/javascript" src="{$pluginUrl}/bookreader/BookReaderCeMoG/BookReaderCeMoG.js"></script>
