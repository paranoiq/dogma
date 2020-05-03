<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */


namespace Dogma\Application\Ansi;

use Dogma\InvalidValueException;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;

class Ansi
{
    use StrictBehaviorMixin;

    private $stack = [];

    public function process(string $string): string
    {
        $output = '';
        $offset = 0;
        $end = 0;
        [$start, $length] = Str::findTag($string, '{', '}', '{', '}', $offset);
        while ($start !== null) {
            $output .= substr($string, $end, $start);

            $body = substr($string, $start + 1, $length - 2);

            if ($body[-1] === '/') {
                // pair format tag start

            } elseif ($body[0] === '/') {
                // pair format tag end

            } elseif (strpos($body, ':') !== false) {
                // format tag
                [$chars, $text] = example(':', $body);
                $actions = $this->parseChars($chars);

                return $this->apply($actions, $text);
            } else {
                // command tag

            }
        }
        $output .= substr($string, $offset);
        rd($string);
        rd($output);

        return $output;
    }

    /**
     * @param string $string
     * @return mixed[]
     */
    private function parseChars(string $string): array
    {
        $colorSet = false;
        $actions = [];

        $length = strlen($string) - 1;
        $position = 0;

        while ($position <= $length) {
            $char = $string[$position];

            switch ($char) {
                case 'a': [$action, $value] = [null, null]; break; // (Ascii - ascii control characters)
                case 'A': [$action, $value] = [null, null]; break; //
                case 'b': [$action, $value] = [Action::COLOR, 'b']; break; // Blue dark (navy)
                case 'B': [$action, $value] = [Action::COLOR, 'B']; break; // Blue bright
                case 'c': [$action, $value] = [Action::COLOR, 'c']; break; // Cyan dark (teal)
                case 'C': [$action, $value] = [Action::COLOR, 'C']; break; // Cyan bright
                case 'd': [$action, $value] = [null, null]; break; //
                case 'D': [$action, $value] = [null, null]; break; //
                case 'e': [$action, $value] = [Action::FRAMING, 'encircled']; break; // Encircled
                case 'E': [$action, $value] = [Action::FRAMING, 'enframed']; break; // Enframed
                case 'f': // Font selector `{f<0-9>:text}`; Default font = 0 or no number `{f}`
                    if ($position < $length && ctype_digit($string[$position + 1])) {
                        $position++;
                        $value = $string[$position];
                    } else {
                        $value = 0;
                    }
                    $action = Action::FONT;
                    break;
                case 'F': [$action, $value] = [Action::FONT, 'fraktur']; break; // Fraktur font
                case 'g': [$action, $value] = [Action::COLOR, 'g']; break; // Green dark
                case 'G': [$action, $value] = [Action::COLOR, 'G']; break; // Green bright (lime)
                case 'h': [$action, $value] = [Action::HIDDEN, true]; break; // Hidden
                case 'H': [$action, $value] = [null, null]; break; //
                case 'i': [$action, $value] = [Action::FONT, 'italic']; break; // Italic font
                case 'I': [$action, $value] = [Action::INVERTED, true]; break; // Invert (flip background and foreground)
                case 'j': [$action, $value] = [null, null]; break; // (interlaced text)
                case 'J': [$action, $value] = [null, null]; break; //
                case 'k': [$action, $value] = [Action::COLOR, 'k']; break; // blacK
                case 'K': [$action, $value] = [Action::COLOR, 'K']; break; // blacK bright (gray)
                case 'l': [$action, $value] = [Action::BLINK, 'slow']; break; // slow bLink
                case 'L': [$action, $value] = [Action::BLINK, 'fast']; break; // fast bLink
                case 'm': [$action, $value] = [Action::COLOR, 'm']; break; // Magenta dark (purple)
                case 'M': [$action, $value] = [Action::COLOR, 'M']; break; // Magenta bright
                case 'n': [$action, $value] = [null, null]; break; // (negative colors)
                case 'N': [$action, $value] = [null, null]; break; //
                case 'o': [$action, $value] = [Action::OVERLINED, true]; break; // Overlined
                case 'O': [$action, $value] = [null, null]; break; //
                case 'p': [$action, $value] = [null, null]; break; //
                case 'P': [$action, $value] = [null, null]; break; //
                case 'q': [$action, $value] = [null, null]; break; //
                case 'Q': [$action, $value] = [null, null]; break; //
                case 'r': [$action, $value] = [Action::COLOR, 'r']; break; // Red dark (maroon)
                case 'R': [$action, $value] = [Action::COLOR, 'R']; break; // Red bright
                case 's': [$action, $value] = [Action::STRONG, 's']; break; // Strong/bright (switches from dark color to bright color)
                case 'S': [$action, $value] = [Action::STRIKE, true]; break; // Strike through
                case 't': [$action, $value] = [null, null]; break; // (Type/Terminal input sequences)
                case 'T': [$action, $value] = [null, null]; break; // (Transform case: Upper, Lower, First-upper, Capitalize, Dizzy, Random, 1337)
                case 'u': [$action, $value] = [Action::UNDERLINED, 1]; break; // Underline
                case 'U': [$action, $value] = [Action::UNDERLINED, 2]; break; // double Underline
                case 'v': // down arrow - cursor movement
                    $n = 1;
                    while ($position < $length && $string[$position + 1] === 'v') {
                        $position++;
                        $n++;
                    }
                    [$action, $value] = [Action::CLEAR_DOWN, $n];
                    break;
                case 'V': [$action, $value] = [null, null]; break; //
                case 'w': [$action, $value] = [Action::COLOR, 'w']; break; // White dark (silver)
                case 'W': [$action, $value] = [Action::COLOR, 'W']; break; // White bright
                case 'x': [$action, $value] = [null, null]; break; //
                case 'X': [$action, $value] = [null, null]; break; //
                case 'y': [$action, $value] = [Action::COLOR, 'y']; break; // Yellow dark (olive)
                case 'Y': [$action, $value] = [Action::COLOR, 'Y']; break; // Yellow bright
                case 'z': [$action, $value] = [null, null]; break; //
                case 'Z': [$action, $value] = [null, null]; break; //
                case '0': case '1': case '2': case '3': case '4': case '5': case '6': case '7': case '8': case '9':
                    // set cursor position command; `{42}` column in row; `{12,42}` row + column
                    $value = $char;
                    while ($position < $length && ctype_digit($string[$position + 1])) {
                        $position++;
                        $value .= $string[$position];
                    }
                    $value = (int) $value;
                    if ($string[$position + 1] === ',') {
                        $position++;
                        $value2 = '';
                        while ($position < $length && ctype_digit($string[$position + 1])) {
                            $position++;
                            $value2 .= $string[$position];
                        }
                        $value = [$value, (int) $value2];
                        $action = Action::CURSOR_POSITION;
                    } else {
                        $action = Action::CURSOR_COLUMN;
                    }
                    break;
                case ' ': [$action, $value] = [null, null]; break; // (space) separates command and arguments
                case '$': [$action, $value] = [null, null]; break; // (reserved for variables)
                case '@': [$action, $value] = [null, null]; break; // (reserved for scroll)
                case '#': // 24-bit or 12-bit color code {#ffffff:text} {#fff:text}
                    $value = '#';
                    while ($position < $length && ctype_xdigit($string[$position + 1])) {
                        $position++;
                        $value .= $string[$position];
                    }
                    if (strlen($value) !== 4 && strlen($value) !== 7) {
                        // todo
                        throw new InvalidValueException($value, 'color');
                    }
                    $action = Action::COLOR;
                    break;
                case '%': // 6x6x6 color cube {%123:text}
                    $value = '%';
                    while ($position < $length && ctype_xdigit($string[$position + 1])) {
                        $position++;
                        $n = $string[$position];
                        if ($n > 5) {
                            // todo
                            throw new InvalidValueException($n, 'color');
                        }
                        $value .= $n;
                    }
                    if (strlen($value) !== 4) {
                        // todo
                        throw new InvalidValueException($value, 'color');
                    }
                    $action = Action::COLOR;
                    break;
                case '&': // named HTML color, 24-bit {&magenta:text}
                    // todo #############################

                    [$action, $value] = [Action::COLOR, null];
                    break;
                case '*': // gray intensity from 0 = black to 25 = white {*17:text} and comments {* foo *}
                    $value = '';
                    while ($position < $length && ctype_digit($string[$position + 1])) {
                        $position++;
                        $value .= $string[$position];
                    }
                    if ($value > 25) {
                        // todo
                        throw new InvalidValueException('*' . $value, 'color');
                    }
                    [$action, $value] = [Action::COLOR, '*' . $value];
                    break;
                case '/': [$action, $value] = [null, null]; break; // pair marker indicator - {i/}text{/i}; pair markers do not have to match "{ir/}red italic{/i} still red{/r}"; {/} end last; {//} reset all formats
                case '+': [$action, $value] = [null, null]; break; //
                case '-': // indicates no change in foreground color, background color follows {-r:text}; or move to end {->}
                    if ($position < $length && $string[$position + 1] === '>') {
                        $action = Action::CURSOR_END;
                        $value = true;
                    } else {
                        $colorSet = true;
                    }
                    break;
                case '>': // right arrow - cursor movement
                    $value = 1;
                    while ($position < $length && $string[$position + 1] === '<') {
                        $position++;
                        $value++;
                    }
                    $action = Action::CURSOR_RIGHT;
                    break;
                case '<': // left arrow - cursor movement; with "-" means "move to end" {<-}
                    $value = 1;
                    while ($string[$position + 1] === '<') {
                        $position++;
                        $value++;
                    }
                    if ($value === 1 && $string[$position + 1] === '-') {
                        $position++;
                        $actions = Action::CURSOR_START;
                    } else {
                        $action = Action::CURSOR_LEFT;
                    }
                    break;
                case '^': // up arrow - cursor movement
                    $value = 1;
                    while ($string[$position + 1] === '^') {
                        $position++;
                        $value++;
                    }
                    $action = Action::CURSOR_UP;
                    break;
                case '~': // clear command; direction may follow; {~~} whole screen; {~~~} including scroll buffer; {~1,5} to position

                    [$action, $value] = [null, null];
                    break;
                case '=': [$action, $value] = [null, null]; break; //
                case '{': [$action, $value] = [null, null]; break; // command tag start; "{{" for actual "{" character
                case '}': [$action, $value] = [null, null]; break; // command tag end; "}}" for actual "}" character
                case '[': [$action, $value] = [null, null]; break; // index/range start
                case ']': [$action, $value] = [null, null]; break; // index/range end
                case '(': [$action, $value] = [null, null]; break; // (reserved for condition start)
                case ')': [$action, $value] = [null, null]; break; // (reserved for condition end)
                case '|': [$action, $value] = [null, null]; break; //
                case '\\':[$action, $value] = [null, null]; break; //
                case '_': [$action, $value] = [null, null]; break; // (reserved for self variable)
                case ':': [$action, $value] = [null, null]; break; // separates command and text
                case ';': [$action, $value] = [null, null]; break; //
                case ',': [$action, $value] = [null, null]; break; // separates list of formats applied per word
                case '.': [$action, $value] = [null, null]; break; // separates list of formats applied per character; indicates that format is applied per character; ".." for range operator
                case '?': [$action, $value] = [null, null]; break; // (reserved for conditions)
                case '!': [$action, $value] = [null, null]; break; // (reserved for conditions)
                case '"': [$action, $value] = [null, null]; break; // (avoided because of strings)
                case '\'':[$action, $value] = [null, null]; break; // (avoided because of strings)
                case '`': [$action, $value] = [null, null]; break; //
                default: [$action, $value] = [null, null];

            }

            if ($action === null) {
                // todo
                throw new InvalidValueException($char, 'unknown');
            }
            if ($colorSet && $action === Action::COLOR) {
                $action = Action::BG_COLOR;
            }
            if (isset($actions[$action])) {
                // todo
                throw new InvalidValueException($char, 'repeating');
            } else {
                $actions[$action] = $value;
            }

            $position++;
        }

        return $actions;
    }

    /**
     * @param mixed[] $actions
     * @param string $string
     * @return string
     */
    public function apply(array $actions, string $string): string
    {
        foreach ($actions as $action => $value) {
            Action::start();
        }
    }

}
