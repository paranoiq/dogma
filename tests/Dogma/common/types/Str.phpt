<?php declare(strict_types = 1);

namespace Dogma\Tests\Str;

use Dogma\Str;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


// toFirst()
Assert::same(Str::toFirst('abc@def', '@'), 'abc');
Assert::same(Str::toFirst('abc@def', '#'), 'abc@def');

// fromFirst()
Assert::same(Str::fromFirst('abc@def', '@'), 'def');
Assert::same(Str::fromFirst('abc@def', '#'), '');

// splitByFirst()
Assert::same(Str::splitByFirst('abc@def', '@'), ['abc', 'def']);
Assert::same(Str::splitByFirst('abc@def@ghi', '@'), ['abc', 'def@ghi']);

// levenshteinUnicode()
Assert::same(Str::levenshteinUnicode('příliš', 'příliš'), 0.0);
Assert::same(Str::levenshteinUnicode('žluťoučký', 'Žluťoučký'), 0.25);
Assert::same(Str::levenshteinUnicode('kůň', 'kuň'), 0.5);
Assert::same(Str::levenshteinUnicode('úpěl', 'úpl'), 1.0);
Assert::same(Str::levenshteinUnicode('ďábelské', 'ďábelskéé'), 1.0);
Assert::same(Str::levenshteinUnicode('ódy', 'údy'), 1.0);

// removeDiacritics()
Assert::same(Str::removeDiacritics('příliš žluťoučký kůň úpěl ďábelské ódy'), 'prilis zlutoucky kun upel dabelske ody');
Assert::same(Str::removeDiacritics('PŘÍLIŠ ŽLUŤOUČKÝ KŮŇ ÚPĚL ĎÁBELSKÉ ÓDY'), 'PRILIS ZLUTOUCKY KUN UPEL DABELSKE ODY');
