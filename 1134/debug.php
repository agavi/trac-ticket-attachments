<?php

$locales = array('de_DE', 'en_US');

$values = array (
	'9',
	'9,95',
	'9.999,95',
	'9.999.999,95',
	'9.95',
	'9,999.95',
	'9,999,999.95',
);

foreach ($locales as $locale)
{
	echo ':: LOCALE=' . $locale . "\n\n";

	foreach ($values as $value)
	{
		echo ':: Current Value: ' . $value . "\n";

		$hasExtraChars = false;
		$localeObj = $this->getContext()->getTranslationManager()->getLocale($locale);

		echo 'DecimalFormatter: ';
		var_dump (AgaviDecimalFormatter::parse($value, $localeObj, $hasExtraChars));
		echo 'hasExtraChars: ';
		var_dump($hasExtraChars);
		echo "\n";
	}
}

?>