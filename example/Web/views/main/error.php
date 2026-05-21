<?php
declare(strict_types=1);
use Oni\Web\Helper\HTML;
?>
<h1><?=$title?></h1>
<p>Go to <?=HTML::linkTo('/', 'home')?></p>