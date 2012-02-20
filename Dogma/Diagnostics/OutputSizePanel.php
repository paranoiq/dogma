<?php

namespace Dogma\Diagnostics;

use Nette\Diagnostics\Debugger;


/**
 * Output-Size panel for Nette debugger
 */
class OutputSizePanel implements \Nette\Diagnostics\IBarPanel {
    
    private $size = 0;
    
    
    static function initialize() {
        $panel = new static;
        if (!Debugger::$productionMode) {
            Debugger::$bar->addPanel($panel);
        }
        ob_start(array($panel, 'measure'), 1);
    }


    /**
     * @param string
     * @return bool
     */
    public function measure(&$content) {
        $this->size += strlen($content);
        return FALSE;
    }
    
    
    /**
     * Renders HTML code for custom tab.
     * @return string
     */
    public function getTab() {
        ob_end_flush();
        return "<span title=\"Page data size\"><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADoSURBVBgZBcExblNBGAbA2ceegTRBuIKOgiihSZNTcC5LUHAihNJR0kGKCDcYJY6D3/77MdOinTvzAgCw8ysThIvn/VojIyMjIyPP+bS1sUQIV2s95pBDDvmbP/mdkft83tpYguZq5Jh/OeaYh+yzy8hTHvNlaxNNczm+la9OTlar1UdA/+C2A4trRCnD3jS8BB1obq2Gk6GU6QbQAS4BUaYSQAf4bhhKKTFdAzrAOwAxEUAH+KEM01SY3gM6wBsEAQB0gJ+maZoC3gI6iPYaAIBJsiRmHU0AALOeFC3aK2cWAACUXe7+AwO0lc9eTHYTAAAAAElFTkSuQmCC\">"
            . \Nette\Templating\DefaultHelpers::Bytes($this->size, 1) . "</span>";
    }
    
    
    /**
     * Renders HTML code for custom panel.
     * @return string
     */
    public function getPanel() {
        //return 'ahoj';
    }
    
}
