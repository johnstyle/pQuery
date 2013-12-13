pQuery
======

PHP DOM selector jQuery like

Example
======

<pre>

$html = '&lt;div&gt;
         &lt;span class=&quot;test gogo wawa&quot;&gt;test &lt;sup&gt;10&lt;/sup&gt;&lt;sup&gt;&lt;strong&gt;11&lt;/strong&gt;&lt;/sup&gt;&lt;sup&gt;12&lt;/sup&gt;&lt;/span&gt;
         &lt;span class=&quot;test demo toto&quot;&gt;demo &lt;sup class=&quot;oo&quot;&gt;&lt;strong&gt;15&lt;/strong&gt;&lt;/sup&gt;&lt;/span&gt;
         &lt;/div&gt;';

$pQuery = new pQuery($html);
$pQuery->find('span[class*=gogo] sup:last');
echo $pQuery->text();

</pre>