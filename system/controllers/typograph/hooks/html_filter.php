<?php

class onTypographHtmlFilter extends cmsAction {

    public function run($data){

        $errors = null;
        $is_auto_br = true;

        if (is_array($data)){
            $text = $data['text'];
            $is_auto_br = $data['is_auto_br'];
        } else {
            $text = $data;
        }

        return $this->getJevix($is_auto_br)->parse($text, $errors);

    }

    private function getJevix($is_auto_br){

        cmsCore::loadLib('jevix.class', 'Jevix');

        $jevix = new Jevix();

        // Устанавливаем разрешённые теги. (Все не разрешенные теги считаются запрещенными.)
        $jevix->cfgAllowTags(array(
            'p', 'br', 'span', 'div',
            'a', 'img',
            'b', 'i', 'u', 's', 'del', 'em', 'strong', 'sup', 'sub', 'hr', 'font',
            'ul', 'ol', 'li',
            'table', 'tbody', 'thead', 'tfoot', 'tr', 'td', 'th',
            'h1','h2','h3','h4','h5','h6',
            'pre', 'code', 'blockquote',
            'video', 'audio', 'youtube',
            'object', 'param', 'embed', 'iframe'
        ));

        // Устанавливаем коротие теги. (не имеющие закрывающего тега)
        $jevix->cfgSetTagShort(array(
            'br', 'img', 'hr', 'embed'
        ));

        // Устанавливаем преформатированные теги. (в них все будет заменятся на HTML сущности)
        $jevix->cfgSetTagPreformatted(array(
            'pre', 'video'
        ));

        // Устанавливаем теги, которые необходимо вырезать из текста вместе с контентом.
        $jevix->cfgSetTagCutWithContent(array(
            'script', 'style', 'meta'
        ));

        $jevix->cfgSetTagIsEmpty(array(
            'param','embed','a','iframe','div'
        ));

        // Устанавливаем разрешённые параметры тегов. Также можно устанавливать допустимые значения этих параметров.
        $jevix->cfgAllowTagParams('a', array('href' => '#link', 'name' => '#text'));
        $jevix->cfgAllowTagParams('img', array('src', 'style' => '#text', 'alt' => '#text', 'title' => '#text', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int', 'hspace' => '#int', 'vspace' => '#int', 'class' => '#text'));
        $jevix->cfgAllowTagParams('span', array('style' => '#text'));
        $jevix->cfgAllowTagParams('object', array('width' => '#int', 'height' => '#int', 'data' => array('#domain'=>array('youtube.com','rutube.ru','vimeo.com','vk.com')), 'type' => '#text'));
        $jevix->cfgAllowTagParams('param', array('name' => '#text', 'value' => '#text'));
        $jevix->cfgAllowTagParams('embed', array('src' => array('#domain'=>array('youtube.com','rutube.ru','vimeo.com','vk.com')), 'type' => '#text','allowscriptaccess' => '#text', 'allowfullscreen' => '#text','width' => '#int', 'height' => '#int', 'flashvars'=> '#text', 'wmode'=> '#text'));
        $jevix->cfgAllowTagParams('iframe', array('width' => '#int', 'height' => '#int', 'style' => '#text', 'frameborder' => '#int', 'allowfullscreen' => '#text', 'src' => array('#domain'=>array('youtube.com','rutube.ru','vimeo.com','vk.com','my.mail.ru'))));
        $jevix->cfgAllowTagParams('table', array('width' => '#int', 'height' => '#int', 'cellpadding' => '#int', 'cellspacing' => '#int', 'border' => '#int', 'style' => '#text', 'align'=>'#text', 'valign'=>'#text'));
        $jevix->cfgAllowTagParams('td', array('width' => '#int', 'height' => '#int', 'style' => '#text', 'align'=>'#text', 'valign'=>'#text', 'colspan'=>'#int', 'rowspan'=>'#int'));
        $jevix->cfgAllowTagParams('th', array('width' => '#int', 'height' => '#int', 'style' => '#text', 'align'=>'#text', 'valign'=>'#text', 'colspan'=>'#int', 'rowspan'=>'#int'));
        $jevix->cfgAllowTagParams('p', array('style' => '#text'));
        $jevix->cfgAllowTagParams('div', array('style' => '#text', 'class' => '#text'));

        // Устанавливаем параметры тегов являющиеся обязательными. Без них вырезает тег оставляя содержимое.
        $jevix->cfgSetTagParamsRequired('img', 'src');
        $jevix->cfgSetTagParamsRequired('a', 'href');

        // Устанавливаем теги которые может содержать тег контейнер
        $jevix->cfgSetTagChilds('ul',array('li'),false,true);
        $jevix->cfgSetTagChilds('ol',array('li'),false,true);
        $jevix->cfgSetTagChilds('table',array('tr', 'tbody', 'thead', 'tfoot', 'th', 'td'),false,true);
        $jevix->cfgSetTagChilds('tbody',array('tr', 'td', 'th'),false,true);
        $jevix->cfgSetTagChilds('thead',array('tr', 'td', 'th'),false,true);
        $jevix->cfgSetTagChilds('tfoot',array('tr', 'td', 'th'),false,true);
        $jevix->cfgSetTagChilds('tr',array('td'),false,true);
        $jevix->cfgSetTagChilds('tr',array('th'),false,true);

        // Устанавливаем автозамену
        $jevix->cfgSetAutoReplace(array('+/-', '(c)', '(с)', '(r)', '(C)', '(С)', '(R)'), array('±', '©', '©', '®', '©', '©', '®'));

        // включаем режим замены переноса строк на тег <br/>
        $jevix->cfgSetAutoBrMode($is_auto_br);

        // включаем режим автоматического определения ссылок
        $jevix->cfgSetAutoLinkMode(true);

        // Отключаем типографирование в определенном теге
        $jevix->cfgSetTagNoTypography('pre','youtube', 'iframe');

        // Ставим колбэк для youtube
        $jevix->cfgSetTagCallbackFull('youtube', array($this, 'parseYouTubeVideo'));

        // Ставим колбэк на iframe
        $jevix->cfgSetTagCallbackFull('iframe', array($this, 'parseIframe'));

        // Ставим колбэк для кода
        $jevix->cfgSetTagCallback('code', array($this, 'parseCode'));

        return $jevix;

    }

    public function parseIframe($tag, $params, $content) {

        if(empty($params['src'])){
            return '';
        }

        return $this->getVideoCode($params['src']);

    }

    public function parseYouTubeVideo($tag, $params, $content){

        $video_id = $this->parseYouTubeVideoID(trim(strip_tags($content)));

        return $this->getVideoCode('//www.youtube.com/embed/'.$video_id);

    }

    private function getVideoCode($src) {
        return '<div class="video_wrap"><iframe class="video_frame" src="'.$src.'" frameborder="0" allowfullscreen></iframe></div>';
    }

    private function parseYouTubeVideoID($url) {

        $pattern = '#^(?:(?:https|http)?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';
        preg_match($pattern, $url, $matches);
        return (isset($matches[1])) ? $matches[1] : false;

    }

    public function parseCode($content){

        cmsCore::loadLib('geshi/geshi', 'GeSHi');

        $geshi = new GeSHi(trim($content), 'php');

        return $geshi->parse_code();

    }

}
