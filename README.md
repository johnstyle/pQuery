pQuery
======

PHP DOM selector jQuery like

Example
======

<pre>

$html = '<div>
<span class="test gogo wawa">test <sup>10</sup><sup><strong>11</strong></sup><sup>12</sup></span>
<span class="test demo toto">demo <sup class="oo"><strong>15</strong></sup></span>
</div>';

$pQuery = new pQuery($html);
$pQuery->find('span[class*=gogo] sup:last');
echo $pQuery->text();

</pre>