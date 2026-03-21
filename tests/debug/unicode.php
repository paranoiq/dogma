<?php declare(strict_types = 1);

namespace Dogma\Tests\Str;

use AnyAscii;
use Dogma\InvalidValueException;
use Dogma\Language\IntlHelper;
use Dogma\Re;
use Dogma\Str;
use IntlChar;
use Language\AsciiTranslit;
use Transliterator;
use function array_search;
use function dechex;
use function floor;
use function htmlspecialchars;
use function reset;
use function str_replace;
use function substr;
use function trim;

require_once __DIR__ . '/../src/bootstrap.php';

/**
 * This script renders most Unicode code pages to inspect and debug ASCII transliteration
 * Most CJK pages are turned off to save the browser from putting your CPU on fire.
 */

?>
<style>
    body {
        font-family: Calibri, sans-serif;
        background-color: white;
    }
    .tw {
        float: left;
        height: 1024px;
    }
    table {
        margin: 4px;
        border-collapse: collapse;
        border: 2px silver solid;
    }
    td {
        border: 1px silver solid;
        padding: 0px 4px;
        text-align: center;
        min-width: 60px;
        max-height: 60px;
    }
    abbr { color: silver; }
    strong abbr { color: blue; }
    td.empty {
        background:
            linear-gradient(to top left, transparent calc(50% - 0.5px), silver, transparent calc(50% + 0.5px)),
            linear-gradient(to top right, transparent calc(50% - 0.5px), silver, transparent calc(50% + 0.5px));
    }
    #THAANA td, #SAMARITAN td, #MANDAIC td, #MYANMAR td, #TAGALOG td, #HANUNOO td, #BUHID td, #TAGBANWA td, #LIMBU td,
    #TAI_THAM td, #COMBINING_DIACRITICAL_MARKS_EXTENDED td, #BALINESE td, #SUNDANESE td, #BATAK td, #LEPCHA td,
    #COMBINING_DIACRITICAL_MARKS_SUPPLEMENT td, #CONTROL_PICTURES td, #BOX_DRAWING td, #BOX_DRAWING td, #HIRAGANA td,
    #KATAKANA td, #KATAKANA_PHONETIC_EXTENSIONS td, #ENCLOSED_CJK_LETTERS_AND_MONTHS td, #CJK_COMPATIBILITY td,
    #MODIFIER_TONE_LETTERS td, #SYLOTI_NAGRI td, #SAURASHTRA td, #KAYAH_LI td, #REJANG td, #JAVANESE td,
    #MYANMAR_EXTENDED_B td, #CHAM td, #MYANMAR_EXTENDED_A td, #TAI_VIET td, #MEETEI_MAYEK_EXTENSIONS td,
    #HALFWIDTH_AND_FULLWIDTH_FORMS td, #LINEAR_B_SYLLABARY td, #LINEAR_B_IDEOGRAMS td, #AEGEAN_NUMBERS td,
    #COPTIC_EPACT_NUMBERS td, #OLD_PERMIC td, #OSMANYA td, #ELBASAN td, #CAUCASIAN_ALBANIAN td, #VITHKUQI td,
    #LINEAR_A td, #LATIN_EXTENDED_F td, #PALMYRENE td, #NABATAEAN td, #HATRAN td, #MEROITIC_HIEROGLYPHS td,
    #MEROITIC_CURSIVE td, #OLD_NORTH_ARABIAN td, #MANICHAEAN td, #AVESTAN td, #PSALTER_PAHLAVI td, #OLD_HUNGARIAN td,
    #HANIFI_ROHINGYA td, #YEZIDI td, #OLD_SOGDIAN td, #SOGDIAN td, #OLD_UYGHUR td, #CHORASMIAN td, #ELYMAIC td,
    #KAITHI td, #MAHAJANI td, #SHARADA td, #KHOJKI td, #MULTANI td, #KHUDAWADI td, #GRANTHA td, #NEWA td, #TIRHUTA td,
    #SIDDHAM td, #MODI td, #TAKRI td, #AHOM td, #DOGRA td, #WARANG_CITI td, #DIVES_AKURU td, #NANDINAGARI td,
    #ZANABAZAR_SQUARE td, #SOYOMBO td, #PAU_CIN_HAU td, #BHAIKSUKI td, #MARCHEN td, #MASARAM_GONDI td, #GUNJALA_GONDI td,
    #MAKASAR td, #KAWI td, #TAMIL_SUPPLEMENT td, #CYPRO_MINOAN td, #ANATOLIAN_HIEROGLYPHS td, #MRO td, #TANGSA td,
    #BASSA_VAH td, #PAHAWH_HMONG td, #MEDEFAIDRIN td, #MIAO td, #NUSHU td, #DUPLOYAN td, #BYZANTINE_MUSICAL_SYMBOLS td,
    #MUSICAL_SYMBOLS td, #ANCIENT_GREEK_MUSICAL_NOTATION td, #MAYAN_NUMERALS td, #LATIN_EXTENDED_G td,
    #NYIAKENG_PUACHUE_HMONG td, #TOTO td, #WANCHO td, #NAG_MUNDARI td, #MENDE_KIKAKUI td, #INDIC_SIYAQ_NUMBERS td,
    #OTTOMAN_SIYAQ_NUMBERS td, #SUPPLEMENTAL_PUNCTUATION td {
        padding-top: 1px;
        line-height: 1.2;
    }

    /* menu */
    .container {
        width: 100%;
        height: 1200px;
        /*display: flex;
        flex-wrap: wrap;
        gap: 10px; /* Optional: adds spacing between items */
    }
    .item {
        float: left;
        margin-right: 8px;
        /*flex: 0 1 auto; /* Allows items to maintain their size or grow/shrink */
    }
    li { margin-left: -20px; }
</style>

<?php

$icu = INTL_ICU_VERSION;
$uni = IntlChar::UNICODE_VERSION;
echo "<h2>ICU {$icu}, Unicode {$uni}</h2>";
echo "<div class='container'>";
$lastSubcat = null;
foreach (IntlHelper::CATEGORIES as $category => $blocks) {
    if ($category !== 'African Scripts'
        && $category !== 'American Scripts'
        && $category !== 'Indones. & Philippine Sc.'
        && $category !== 'Central Asian Scripts'
        && $category !== 'Punctuation'
        && $category !== 'Alphanumeric Symbols'
        && $category !== 'Numbers & Digits'
        && $category !== 'Emoji & Pictographs'
        && $category !== 'Other Symbols'
    ) {
        echo "<div class='item'>";
    }
    echo "<h3>{$category}</h3>";
    echo "<ul><ul>";
    foreach ($blocks as $block => $id) {
        $age = IntlHelper::getBlockVersion($id);
        $age = $age ? " ({$age})" : ' …';
        if ($block[0] === '#') {
            $block = substr($block, 1);
            $b = formatBlockName($block);
            $lastSubcat = $b;
            echo "</ul><li><a href='#{$block}'><b>{$b}</b></a>{$age}\n<ul>";
        } else {
            $b = formatBlockName($block);
            if (Str::startsWith($b, $lastSubcat)) {
                $b = '… ' . Str::after($b, $lastSubcat);
            } elseif (Str::startsWith($b, substr($lastSubcat, 0, -1))) {
                $b = '… ' . Str::after($b, substr($lastSubcat, 0, -1));
            } elseif (Str::startsWith($b, 'Combining ')) {
                $b = '… ' . Str::after($b, 'Combining ');
            }
            echo "<li><a href='#{$block}'>{$b}</a>{$age}\n";
        }
    }
    echo "</ul></ul>";
    if ($category !== 'Mixed Scripts'
        && $category !== 'African Scripts'
        && $category !== 'Southeast Asian Scripts'
        && $category !== 'West Asian Scripts'
        && $category !== 'Notational Systems'
        && $category !== 'Punctuation'
        && $category !== 'Alphanumeric Symbols'
        && $category !== 'Mathematical Symbols'
        && $category !== 'Emoji & Pictographs'
    ) {
        echo "</div>";
    }
}
echo "</div><div></div><hr>";

function formatBlockName(string $name): string
{
    $name = Str::capitalize(Str::lower(str_replace('_', ' ', $name)));

    $name = str_replace(
        ['Cjk', 'Ipa ', 'Supplemental ', 'Supplementary ', 'Characters', 'Punctuation', 'Pictographs', 'Presentation ', 'Description', 'Compatibility', 'Miscellaneous', 'Mathematical ', ' Cuneiform', 'Unified Canadian Aboriginal Syllabics'],
        ['CJK', 'IPA ', 'Suppl. ',       'Suppl. ',        'Chars.',     'Punct.',      'Pictogr.',    'Pres. ',        'Desc.',       'Compat.',       'Misc.',         'Math. ',        ' Cunei.',    'Uni. Canadian Abor. Syll.'],
        $name
    );

    return Re::replace($name, '~Supplement$~', 'Suppl.');
}

$transliterator = Transliterator::create('Any-Latin; Latin-ASCII');
$blocksOrderedByCategory = [];
foreach (IntlHelper::CATEGORIES as $blocks) {
    foreach ($blocks as $block => $id) {
        $blocksOrderedByCategory[trim($block, '#')] = $id;
    }
}
$boundaries = IntlHelper::getBlockExtents($blocksOrderedByCategory);

$block = $blockName = null;
$groups = [];
$count = 0;
foreach ($blocksOrderedByCategory as $blockId) {
    if ($blockId < 1) {
        continue;
    }

    [$first, $last] = $boundaries[$blockId] ?? [-1, -1];
    if ($first === -1) {
        continue;
    }
    for ($cp = $first; $cp <= $last; $cp++) {
        $row = floor($cp / 16) % 16;
        $cell = $cp % 16;

        $h = dechex($cp);
        try {
            $c = Str::chr($cp);
        } catch (InvalidValueException) {
            $c = '';
        }
        $t = $transliterator->transliterate($c);
        $a = AnyAscii::transliterate($t);
        if ($cp === 29) {
            $c = ' ';
            $a = ' ';
        }
        $x = AsciiTranslit::REPLACEMENTS[$c] ?? null;
        $b = IntlChar::getBlockCode($cp);
        if ($b !== $block) {
            if ($block !== null) {
                renderGroup($groups, $blockName);
                $groups = [];
                $count = 0;
            }
            $block = $b;
            $blockName = array_search($b, IntlHelper::BLOCKS, true) ?: 'UNKNOWN_' . $b;
        }
        $groups[$cell][$row] = [$cp, $h, $c, $t, $a, $x];
        $count++;

        if ($count === 256) {
            renderGroup($groups, $blockName);
            $groups = [];
            $count = 0;
        }
    }
}

echo "<hr style='clear:both;'>";

function renderGroup(array $group, ?string $block): void {
    if (   $block === 'NO_BLOCK'
        || $block === 'HIGH_SURROGATES'
        || $block === 'LOW_SURROGATES'
        || $block === 'HIGH_PRIVATE_USE_SURROGATES'
        || $block === 'PRIVATE_USE_AREA'
        || $block === 'SUPPLEMENTARY_PRIVATE_USE_AREA_A'
        || $block === 'SUPPLEMENTARY_PRIVATE_USE_AREA_B'
        || $block === 'VARIATION_SELECTORS'
        || $block === 'VARIATION_SELECTORS_SUPPLEMENT'
        || $block === 'HANGUL_SYLLABLES'
        || $block === 'HANGUL_JAMO_EXTENDED_B'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_A'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_B'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_C'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_D'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_E'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_F'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_G'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_H'
        || $block === 'CJK_UNIFIED_IDEOGRAPHS_EXTENSION_I'
        || $block === 'CJK_COMPATIBILITY_IDEOGRAPHS'
        || $block === 'CJK_COMPATIBILITY_IDEOGRAPHS_SUPPLEMENT'
        || $block === 'CJK_RADICALS_SUPPLEMENT'
        || $block === 'TANGUT'
        || $block === 'TANGUT_COMPONENTS'
        || $block === 'TANGUT_SUPPLEMENT'
        || $block === 'YI_SYLLABLES'
        || $block === 'YI_RADICALS'
        || $block === 'SUTTON_SIGNWRITING'
        || $block === 'ZNAMENNY_MUSICAL_NOTATION'

    ) {
        return;
    }
    $g = reset($group);
    if ($g === false) {
        return;
    }
    $cp = reset($g)[0];

    $names = [];
    IntlChar::enumCharNames($cp, $cp + 256, static function ($codepoint, $nameChoice, $name) use (&$names): void {
        $names[$codepoint] = $name;
    });
    echo "<div class='tw'><table id='{$block}'><caption><b>{$block}</b></caption>";
    foreach ($group as $i => $row) {
        echo "<tr>";
        foreach ($row as $j => $cell) {
            [$cp, $h, $c, $t, $a, $x] = $cell;
            $type = IntlChar::charType($cp);
            $x = htmlspecialchars((string) $x);
            $name = $names[$cp] ?? '';
            if ($type === IntlChar::CHAR_CATEGORY_UNASSIGNED) {
                echo "<td class='empty'></td>";
            } elseif ($x) {
                echo "<td><abbr title='{$name}'>{$h}</abbr><br>{$c}<br><strong><abbr title='{$a}'>{$x}</strong></td>";
            } else {
                echo "<td><abbr title='{$name}'>{$h}</abbr><br>{$c}<br>{$a}</td>";
            }
        }
        echo "</tr>";
    }
    echo "</table></div>";
}
