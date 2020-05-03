Ansi output string formatter and controls

basic syntax:
{<params>:<text>}
{<params>/} start tag
{/<params>} end tag
{<command> <args>} non-formatting commands
"{{" and "}}" to write "{" and "}"

formatting chars table:

|Chr| Description
|---|---------------------------------------------------------------------------
| a | (Ascii - ascii control characters)
| A |
| b | Blue dark (navy)
| B | Blue bright
| c | Cyan dark (teal)
| C | Cyan bright
| d |
| D |
| e | Encircled
| E | Enframed
| f | Font selector `{f<0-9>:text}`; Default font = 0 or no number `{f}`
| F | Fraktur font
| g | Green dark
| G | Green bright (lime)
| h | Hidden
| H |
| i | Italic font
| I | Invert (flip background and foreground)
| j | (interlaced text)
| J | 
| k | blacK
| K | blacK bright (gray)
| l | slow bLink
| L | fast bLink
| m | Magenta dark (purple)
| M | Magenta bright
| n | (Negative)
| N |
| o | Overlined
| O |
| p |
| P |
| q |
| Q |
| r | Red dark (maroon)
| R | Red bright
| s | Strong/bright - switches from dark color to bright color
| S | Strike through
| t | (Type/Terminal input sequences)
| T | (Transform case: Upper, Lower, First-upper, Capitalize, Dizzy, Random, 1337)
| u | Underline
| U | double Underline
| v | down arrow - cursor movement
| V |
| w | White dark (silver)
| W | White bright
| x |
| X | 
| y | Yellow dark (olive)
| Y | Yellow bright
| z | 
| Z | 
|0-9| set cursor position command; `{42}` column in row; `{12,42}` row + column
|   | (space) separates command and arguments
| $ | (reserved for variables)
| @ | (reserved for scroll)
| # | 24-bit or 12-bit color code {#ffffff:text} {#fff:text}
| % | 6x6x6 color cube {%123:text}
| & | named HTML color, 24-bit {&magenta:text}
| * | gray intensity from 0 = black to 25 = white {*17:text} and comments {* foo *}
| / | pair marker indicator - `{i/}text{/i}`; pair markers do not have to match: `{ir/}red italic{/i} still red{/r}`; `{/}` end last; `{//}` reset all formats
| + |
| - | indicates no change in foreground color, background color follows `{-r:text}`; or movement - see below
| > | right arrow - cursor movement; with `-` means "move to end" `{<-}`
| < | left arrow - cursor movement; with `-` means "move to end" `{->}`
| ^ | up arrow - cursor movement
| ~ | clear command; direction may follow; `{~~} whole screen; {~~~}` including scroll buffer; {~1,5} to position
| = |
| { | command tag start; `{{` for actual `{` character
| } | command tag end; `}}` for actual `}` character
| [ | index/range start
| ] | index/range end
| ( | (reserved for condition start)
| ) | (reserved for condition end)
| ¦ |
| \ |
| _ | (reserved for self variable)
| : | separates command and text
| ; |
| , | separates list of formats applied per word
| . | separates list of formats applied per character; indicates that format is applied per character; `..` for range operator
| ? | (reserved for conditions)
| ! | (reserved for conditions)
| " | (avoided because of strings)
| ' | (avoided because of strings)
| ` |

todo:
x invert colors
x reversed text
x interlaced text
- upper caps, lower caps, capitalize

colors:
{r:Hello} red foreground (16 colors mode, rgbcmykwRGBCMYKW)
{wr:Hello} white foreground, red background
{-r:Hello} keep foreground, red background
{%025:Hello} 216 colors cube (RGB, 0-5)
{*25:Hello} 0-25 grayscale intensity (including black/white)
{#ffffff:Hello} 24bit colors
{#fff:Hello} 12bit colors (24bit)
{&magenta:Hello} named html colors (24bit)

formats:
{s:Hello} {strong:} strong (bold)
{S:Hello} {strike:} strike through
{i:Hello} {italic:}
{I:Hello} {invert:} flip background and foreground
{u:Hello} {under:}  underline
{U:Hello} {under2:} double underline
{f:Hello} {font <n>:}
{F:Hello} {framed:}
{e:Hello} {encircled:}
{o:Hello} {overlined:}
{h:Hello} {hidden:}
{l:Hello} {blink:} slow blink
{L:Hello} {fastBlink:} fast blink

multi-rules, ranges, conditions:
{r,g,b:Red Green Blue} rules applied word by word
{r.g.b:RGB} rules applied character by character
{r;g;b:RGB WTF OMG} rules applied character by character on each word
{r,,g:red yellow green} range applied word by word
{r..g:Hello} range applied character by character
{r;;g:Hello} range applied character by character on each word
{r[1]:Red Hat} applied to second word
{r[1.]:Red Hat} applied to second character
{r[1;]:Red Hat} applied to second character on each word
{r[0,,2]:Red Hat} applied to words range
{r[0..2]:Red Hat} applied to characters range
{r[0;;2]:Red Hat} applied to characters range on each word

start/end variants:
{r/} color start
{/r} color end (previous color)
{i/} italic start
{/i} italic end (previous font)
{//} reset everything - font, colors..

cursor/screen commands:
{up 1}           {^} {^^^} {^3}
{down 1}         {v}
{right 1}        {>} {->}
{left 1}         {<} {<-}
{previous 1}     {^<-}
{next 1}         {v<-}
{column 1}       {1}
{go 1,1}         {1,1}
{fill 2,2}       {~2,2}
{clearLine}      {~}
{clearRight}     {~>}
{clearLeft}      {~<}
{clearUp}        {~^}
{clearDown}      {~v}
{clearAll}       {~~}
{clearBuffer}    {~~~}
{scrollUp 1}     {@^}
{scrollDown 1}   {@v}
{reset}          {//}
{getPosition}