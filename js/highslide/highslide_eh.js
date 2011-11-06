function addHighSlideAttribute() {
    var isIE = (document.documentElement.getAttribute('style') ==
                document.documentElement.style);
    var anchors = document.getElementById('content').getElementsByTagName('a');
    for (var i = 0, len = anchors.length; i < len; i++) {
        if (anchors[i].className == 'highslide') {
            if (!anchors[i].getAttribute('onclick')) {
                isIE ? anchors[i].setAttribute('onclick', new Function('return hs.expand(this)')) :
                    anchors[i].setAttribute('onclick','return hs.expand(this)');
                isIE ? anchors[i].setAttribute('onkeypress', new Function('return hs.expand(this)')) :
                    anchors[i].setAttribute('onkeypress','return hs.expand(this)');
            }
        }
    }
}
