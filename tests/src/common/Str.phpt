<?php declare(strict_types = 1);

namespace Dogma\Tests\Str;

use Dogma\InvalidValueException;
use Dogma\Str;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

checkEncoding:
Assert::true(Str::checkEncoding("\u{17E}lu\u{165}ou\u{10D}k\u{FD}")); // UTF-8   žluťoučký
Assert::true(Str::checkEncoding("\x01")); // C0
Assert::false(Str::checkEncoding("\xed\xa0\x80")); // surrogate pairs   xD800
Assert::false(Str::checkEncoding("\xf4\x90\x80\x80")); // out of range   x110000


fixEncoding:
Assert::same(Str::fixEncoding("\u{17E}lu\u{165}ou\u{10D}k\u{FD}"), "\u{17E}lu\u{165}ou\u{10D}k\u{FD}"); // UTF-8   žluťoučký
Assert::same(Str::fixEncoding("\x01"), "\x01"); // C0
Assert::same(Str::fixEncoding("foo\xed\xa0\x80bar"), "foobar"); // surrogate pairs   xD800
Assert::same(Str::fixEncoding("foo\xf4\x90\x80\x80bar"), "foobar"); // out of range   x110000


chr:
Assert::same(Str::chr(0x000000), "\x00");
Assert::same(Str::chr(0x00007F), "\x7F");
Assert::same(Str::chr(0x000080), "\u{80}");
Assert::same(Str::chr(0x0007FF), "\u{7FF}");
Assert::same(Str::chr(0x000800), "\u{800}");
Assert::same(Str::chr(0x00D7FF), "\u{D7FF}");
Assert::same(Str::chr(0x00E000), "\u{E000}");
Assert::same(Str::chr(0x00FFFF), "\u{FFFF}");
Assert::same(Str::chr(0x010000), "\u{10000}");
Assert::same(Str::chr(0x10FFFF), "\u{10FFFF}");

foreach ([-1, 0xD800, 0xDFFF, 0x110000] as $code) {
    Assert::exception(
        static function () use ($code): void {
            Str::chr($code);
        },
        InvalidValueException::class
    );
}


ord:
Assert::same(Str::ord("\x00"), 0x000000);
Assert::same(Str::ord("\x7F"), 0x00007F);
Assert::same(Str::ord("\u{80}"), 0x000080);
Assert::same(Str::ord("\u{7FF}"), 0x0007FF);
Assert::same(Str::ord("\u{800}"), 0x000800);
Assert::same(Str::ord("\u{D7FF}"), 0x00D7FF);
Assert::same(Str::ord("\u{E000}"), 0x00E000);
Assert::same(Str::ord("\u{FFFF}"), 0x00FFFF);
Assert::same(Str::ord("\u{10000}"), 0x010000);
Assert::same(Str::ord("\u{10FFFF}"), 0x10FFFF);


startsWith:
Assert::true(Str::startsWith("foobar", "foo"));
Assert::false(Str::startsWith("foobar", "bar"));


endsWith:
Assert::false(Str::endsWith("foobar", "foo"));
Assert::true(Str::endsWith("foobar", "bar"));


contains:
Assert::true(Str::contains("foobar", "foo"));
Assert::true(Str::contains("foobar", "bar"));
Assert::false(Str::contains("foobar", "baz"));


containsAny:
Assert::true(Str::containsAny("foobar", ["foo", "baz"]));
Assert::true(Str::containsAny("foobar", ["bar", "baz"]));
Assert::false(Str::containsAny("foobar", ["baz", "newt"]));


containsAll:
Assert::true(Str::containsAll("foobar", ["foo", "bar"]));
Assert::true(Str::containsAll("foobar", ["bar"]));
Assert::false(Str::containsAll("foobar", ["foo", "baz"]));


substring:
Assert::same(Str::substring("žluťoučký kůň", 3, 6), "ťoučký");
Assert::same(Str::substring("žluťoučký kůň", -10, 6), "ťoučký");


between:
Assert::same(Str::between('abc@def#ghi', '@', '#'), 'def');
Assert::same(Str::between('abc@def#ghi', '#', '@'), null);
Assert::same(Str::between('abc@def#ghi@jkl', '#', '@'), 'ghi');


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


getLineAt:
Assert::same(Str::getLineAt("111\n222\n333", 0), "111");
Assert::same(Str::getLineAt("111\n222\n333", 1), "111");
Assert::same(Str::getLineAt("111\n222\n333", 2), "111");
Assert::same(Str::getLineAt("111\n222\n333", 3), "111"); // \n belongs to previous
Assert::same(Str::getLineAt("111\n222\n333", 4), "222");
Assert::same(Str::getLineAt("111\n222\n333", 5), "222");
Assert::same(Str::getLineAt("111\n222\n333", 6), "222");
Assert::same(Str::getLineAt("111\n222\n333", 7), "222"); // -//-
Assert::same(Str::getLineAt("111\n222\n333", 8), "333");
Assert::same(Str::getLineAt("111\n222\n333", 9), "333");
Assert::same(Str::getLineAt("111\n222\n333", 10), "333");
Assert::same(Str::getLineAt("111\n222\n333", 11), ""); // after end
Assert::same(Str::getLineAt("111\n222\n333", 12), "");


join:
Assert::same(Str::join([], ', ', ' and '), '');
Assert::same(Str::join([1], ', ', ' and '), '1');
Assert::same(Str::join([1, 2], ', ', ' and '), '1 and 2');
Assert::same(Str::join([1, 2, 3], ', ', ' and '), '1, 2 and 3');
Assert::same(Str::join([1, 2, 3, 4], ', ', ' and '), '1, 2, 3 and 4');

// with limit
Assert::same(Str::join([], ', ', ' and ', 10), '');
Assert::same(Str::join([1], ', ', ' and ', 10), '1');
Assert::same(Str::join([1, 2], ', ', ' and ', 10), '1 and 2');
Assert::same(Str::join([1, 2, 3], ', ', ' and ', 10), '1, 2 and 3');
Assert::same(Str::join([1, 2, 3, 4], ', ', ' and ', 10), '1, 2, 3…'); // and won't fit
Assert::same(Str::join([1, 2, 3, 4, 5], ', ', ' and ', 10), '1, 2, 3, 4…');
Assert::same(Str::join([1, 2, 3, 4], ', ', ' and ', 10, '...'), '1, 2, 3...');
Assert::same(Str::join([1, 2, 3, 4, 5], ', ', ' and ', 10, '...'), '1, 2, 3...');


count:
Assert::same(Str::count('1, 2, 3, 4', ','), 3);
Assert::same(Str::count('1, 2, 3, 4', 'and'), 0);
Assert::same(Str::count('1, 2, 3 and 4', ','), 2);
Assert::same(Str::count('1, 2, 3 and 4', 'and'), 1);


replaceKeys:
Assert::same(Str::replaceKeys('1, 2, 3 and 4', [',' => ';', 'and' => 'or']), '1; 2; 3 or 4');


underscore:
Assert::same(Str::underscore('fooBarBaz'), 'foo_bar_baz');
Assert::same(Str::underscore('FooBarBaz'), 'foo_bar_baz');


trimLinesRight:
Assert::same(Str::trimLinesRight("foo \n bar\t\n\tbaz"), "foo\n bar\n\tbaz");


equals:


compare:


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


convertEncoding:
