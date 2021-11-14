<?php declare(strict_types = 1);

namespace Dogma\Tests\Str;

use Dogma\Str;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


toFirst:
Assert::same(Str::toFirst('abc@def', '@'), 'abc');
Assert::same(Str::toFirst('abc@def', '#'), 'abc@def');


fromFirst:
Assert::same(Str::fromFirst('abc@def', '@'), 'def');
Assert::same(Str::fromFirst('abc@def', '#'), '');


splitByFirst:
Assert::same(Str::splitByFirst('abc@def', '@'), ['abc', 'def']);
Assert::same(Str::splitByFirst('abc@def@ghi', '@'), ['abc', 'def@ghi']);


splitByLast:
Assert::same(Str::splitByLast('abc@def', '@'), ['abc', 'def']);
Assert::same(Str::splitByLast('abc@def@ghi', '@'), ['abc@def', 'ghi']);


trimLinesRight:
Assert::same(Str::trimLinesRight("foo \n bar\t\n\tbaz"), "foo\n bar\n\tbaz");


findTag:
Assert::same(Str::findTag(' foo ', '{', '}'), [null, null]);
Assert::same(Str::findTag(' {foo} ', '{', '}'), [1, 5]);
Assert::same(Str::findTag(' {foo} {foo} ', '{', '}'), [1, 5]);

Assert::same(Str::findTag(' {{foo}} ', '{', '}', '{', '}'), [null, null]);
Assert::same(Str::findTag(' {{foo} {foo} ', '{', '}', '{', '}'), [8, 5]);
Assert::same(Str::findTag(' {foo}} foo} ', '{', '}', '{', '}'), [1, 11]);

Assert::same(Str::findTag(' \\{foo\\} ', '{', '}', '\\', '\\'), [null, null]);
Assert::same(Str::findTag(' \\{foo} {foo} ', '{', '}', '\\', '\\'), [8, 5]);
Assert::same(Str::findTag(' {foo\\} foo} ', '{', '}', '\\', '\\'), [1, 11]);

Assert::same(Str::findTag(' {foo} ', '{', '}', null, null, 1), [1, 5]);
Assert::same(Str::findTag(' {foo} ', '{', '}', null, null, 2), [null, null]);
Assert::same(Str::findTag(' {foo} {foo} ', '{', '}', null, null, 5), [7, 5]);


levenshtein:
Assert::same(Str::levenshtein('příliš', 'příliš'), 0);
Assert::same(Str::levenshtein('žluťoučký', 'Žluťoučký'), 1);
Assert::same(Str::levenshtein('kůň', 'kuň'), 2);
Assert::same(Str::levenshtein('úpěl', 'úpl'), 4);
Assert::same(Str::levenshtein('ďábelské', 'ďábelskéé'), 4);
Assert::same(Str::levenshtein('ódy', 'údy'), 4);
Assert::same(Str::levenshtein('ódy', 'óyd'), 8);


optimalDistance:
Assert::same(Str::optimalDistance('příliš', 'příliš'), 0);
Assert::same(Str::optimalDistance('žluťoučký', 'Žluťoučký'), 1);
Assert::same(Str::optimalDistance('kůň', 'kuň'), 2);
Assert::same(Str::optimalDistance('úpěl', 'úpl'), 4);
Assert::same(Str::optimalDistance('ďábelské', 'ďábelskéé'), 4);
Assert::same(Str::optimalDistance('ódy', 'údy'), 4);
Assert::same(Str::optimalDistance('ódy', 'óyd'), 4);


optimalDistanceBin:
Assert::same(Str::optimalDistanceBin('příliš', 'příliš'), 0);
Assert::same(Str::optimalDistanceBin('žluťoučký', 'Žluťoučký'), 1);
Assert::same(Str::optimalDistanceBin('kůň', 'kuň'), 2);
Assert::same(Str::optimalDistanceBin('úpěl', 'úpl'), 2);
Assert::same(Str::optimalDistanceBin('ďábelské', 'ďábelskéé'), 2);
Assert::same(Str::optimalDistanceBin('ódy', 'údy'), 1); // sub-character
Assert::same(Str::optimalDistanceBin('ódy', 'óyd'), 1);


removeDiacritics:
Assert::same(Str::removeDiacritics('příliš žluťoučký kůň úpěl ďábelské ódy'), 'prilis zlutoucky kun upel dabelske ody');
Assert::same(Str::removeDiacritics('PŘÍLIŠ ŽLUŤOUČKÝ KŮŇ ÚPĚL ĎÁBELSKÉ ÓDY'), 'PRILIS ZLUTOUCKY KUN UPEL DABELSKE ODY');
