<?php

include 'pQuery.php';

$html = '<div>
            <span class="test gogo wawa">
                test
                <span>
                    test2
                    <span>test4</span>
                    112
                </span>
                test3
            </span>
            <span class="test demo toto">
                demo
                <sup class="oo">
                    <strong>15</strong>
                </sup>
            </span>
            <span class="link">
                <a href="window.open(\'http://www.google.fr\');"></a>
            </span>
         </div>';

$pQuery = new pQuery($html);
//$pQuery->find('span[class*=gogo] span');
$pQuery->find('span.gog span');
echo $pQuery->text() . "\n";

/*
$pQuery = new pQuery($html);
$pQuery->find('span[class=link] a');
echo $pQuery->attr('href') . "\n";
*/