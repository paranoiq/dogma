<?php declare(strict_types = 1);

namespace Dogma\Tests\Color;

use Dogma\Color\Color;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


isColor:
Assert::true(Color::isColor('red'));
Assert::true(Color::isColor('white'));
Assert::true(Color::isColor('black'));
Assert::true(Color::isColor('aqua')); // alias
Assert::true(Color::isColor('RED')); // case-insensitive
Assert::true(Color::isColor('fff'));
Assert::true(Color::isColor('ffffff'));
Assert::true(Color::isColor('#fff'));
Assert::true(Color::isColor('#ffffff'));
Assert::true(Color::isColor('FF0000')); // uppercase hex
Assert::false(Color::isColor('notacolor'));
Assert::false(Color::isColor('ff'));
Assert::false(Color::isColor('ffff'));
Assert::false(Color::isColor('fffffff'));
Assert::false(Color::isColor(''));

Assert::true(Color::isColor('#fff', true));
Assert::true(Color::isColor('#ffffff', true));
Assert::false(Color::isColor('fff', true));
Assert::false(Color::isColor('ffffff', true));
Assert::true(Color::isColor('red', true)); // named colors are always valid


invert:
Assert::same(Color::invert('000000'), 'ffffff');
Assert::same(Color::invert('ffffff'), '000000');
Assert::same(Color::invert('ff0000'), '00ffff');
Assert::same(Color::invert('00ff00'), 'ff00ff');
Assert::same(Color::invert('0000ff'), 'ffff00');
Assert::same(Color::invert('808080'), '7f7f7f');


rgbDistance:
Assert::same(Color::rgbDistance('000000', '000000'), 0);
Assert::same(Color::rgbDistance('000000', 'ffffff'), 765);
Assert::same(Color::rgbDistance('ffffff', '000000'), 765);
Assert::same(Color::rgbDistance('ff0000', '00ff00'), 510);
Assert::same(Color::rgbDistance('ff0000', '0000ff'), 510);
Assert::same(Color::rgbDistance('ff0000', 'ff0000'), 0);


filterByMinRgbDistance:
$colors = ['ff0000', '00ff00', '0000ff'];

// distance ff0000→ff0000=0, ff0000→00ff00=510, ff0000→0000ff=510
$result = Color::filterByMinRgbDistance($colors, ['ff0000'], 300);
Assert::same($result, [1 => '00ff00', 2 => '0000ff']); // only exact match removed

$result = Color::filterByMinRgbDistance($colors, ['ff0000'], 511);
Assert::same($result, []); // all removed (510 < 511)

$result = Color::filterByMinRgbDistance($colors, ['ff0000'], 510);
Assert::same($result, [1 => '00ff00', 2 => '0000ff']); // 510 not < 510, kept

$result = Color::filterByMinRgbDistance($colors, [], 300);
Assert::same($result, ['ff0000', '00ff00', '0000ff']); // no filter, all kept


pickMostRgbDistant:
$unused = ['black' => '000000', 'red' => 'ff0000'];
$used = ['white' => 'ffffff'];
$name = 'white';
$dist = -1;
$rgb = Color::pickMostRgbDistant($unused, $used, $name, $dist);
Assert::same($rgb, '000000');
Assert::same($name, 'black');
Assert::same($dist, 765);
Assert::same($used, ['white' => 'ffffff', 'black' => '000000']);
Assert::same($unused, ['red' => 'ff0000']);

// empty unused returns white
$unused = [];
$used = ['black' => '000000'];
$name = 'black';
$dist = 0;
Assert::same(Color::pickMostRgbDistant($unused, $used, $name, $dist), 'ffffff');
