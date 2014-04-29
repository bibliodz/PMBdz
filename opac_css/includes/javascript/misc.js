// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: misc.js,v 1.1 2012-08-07 14:21:20 dbellamy Exp $


function replace_texte(string,text,by) {
    var strLength = string.length, txtLength = text.length;
    if ((strLength == 0) || (txtLength == 0)) return string;

    var i = string.indexOf(text);
    if ((!i) && (text != string.substring(0,txtLength))) return string;
    if (i == -1) return string;

    var newstr = string.substring(0,i) + by;

    if (i+txtLength < strLength)
        newstr += replace_texte(string.substring(i+txtLength,strLength),text,by);

    return newstr;
}


function reverse_html_entities(text) {
    
    text = replace_texte(text,'&quot;',unescape('%22'));
    text = replace_texte(text,'&amp;',unescape('%26'));
    text = replace_texte(text,'&lt;',unescape('%3C'));
    text = replace_texte(text,'&gt;',unescape('%3E'));
    text = replace_texte(text,'&nbsp;',unescape('%A0'));
    text = replace_texte(text,'&iexcl;',unescape('%A1'));
    text = replace_texte(text,'&cent;',unescape('%A2'));
    text = replace_texte(text,'&pound;',unescape('%A3'));
    text = replace_texte(text,'&yen;',unescape('%A5'));
    text = replace_texte(text,'&brvbar;',unescape('%A6'));
    text = replace_texte(text,'&sect;',unescape('%A7'));
    text = replace_texte(text,'&uml;',unescape('%A8'));
    text = replace_texte(text,'&copy;',unescape('%A9'));
    text = replace_texte(text,'&ordf;',unescape('%AA'));
    text = replace_texte(text,'&laquo;',unescape('%AB'));
    text = replace_texte(text,'&not;',unescape('%AC'));
    text = replace_texte(text,'&shy;',unescape('%AD'));
    text = replace_texte(text,'&reg;',unescape('%AE'));
    text = replace_texte(text,'&macr;',unescape('%AF'));
    text = replace_texte(text,'&deg;',unescape('%B0'));
    text = replace_texte(text,'&plusmn;',unescape('%B1'));
    text = replace_texte(text,'&sup2;',unescape('%B2'));
    text = replace_texte(text,'&sup3;',unescape('%B3'));
    text = replace_texte(text,'&acute;',unescape('%B4'));
    text = replace_texte(text,'&micro;',unescape('%B5'));
    text = replace_texte(text,'&para;',unescape('%B6'));
    text = replace_texte(text,'&middot;',unescape('%B7'));
    text = replace_texte(text,'&cedil;',unescape('%B8'));
    text = replace_texte(text,'&sup1;',unescape('%B9'));
    text = replace_texte(text,'&ordm;',unescape('%BA'));
    text = replace_texte(text,'&raquo;',unescape('%BB'));
    text = replace_texte(text,'&frac14;',unescape('%BC'));
    text = replace_texte(text,'&frac12;',unescape('%BD'));
    text = replace_texte(text,'&frac34;',unescape('%BE'));
    text = replace_texte(text,'&iquest;',unescape('%BF'));
    text = replace_texte(text,'&Agrave;',unescape('%C0'));
    text = replace_texte(text,'&Aacute;',unescape('%C1'));
    text = replace_texte(text,'&Acirc;',unescape('%C2'));
    text = replace_texte(text,'&Atilde;',unescape('%C3'));
    text = replace_texte(text,'&Auml;',unescape('%C4'));
    text = replace_texte(text,'&Aring;',unescape('%C5'));
    text = replace_texte(text,'&AElig;',unescape('%C6'));
    text = replace_texte(text,'&Ccedil;',unescape('%C7'));
    text = replace_texte(text,'&Egrave;',unescape('%C8'));
    text = replace_texte(text,'&Eacute;',unescape('%C9'));
    text = replace_texte(text,'&Ecirc;',unescape('%CA'));
    text = replace_texte(text,'&Euml;',unescape('%CB'));
    text = replace_texte(text,'&Igrave;',unescape('%CC'));
    text = replace_texte(text,'&Iacute;',unescape('%CD'));
    text = replace_texte(text,'&Icirc;',unescape('%CE'));
    text = replace_texte(text,'&Iuml;',unescape('%CF'));
    text = replace_texte(text,'&ETH;',unescape('%D0'));
    text = replace_texte(text,'&Ntilde;',unescape('%D1'));
    text = replace_texte(text,'&Ograve;',unescape('%D2'));
    text = replace_texte(text,'&Oacute;',unescape('%D3'));
    text = replace_texte(text,'&Ocirc;',unescape('%D4'));
    text = replace_texte(text,'&Otilde;',unescape('%D5'));
    text = replace_texte(text,'&Ouml;',unescape('%D6'));
    text = replace_texte(text,'&times;',unescape('%D7'));
    text = replace_texte(text,'&Oslash;',unescape('%D8'));
    text = replace_texte(text,'&Ugrave;',unescape('%D9'));
    text = replace_texte(text,'&Uacute;',unescape('%DA'));
    text = replace_texte(text,'&Ucirc;',unescape('%DB'));
    text = replace_texte(text,'&Uuml;',unescape('%DC'));
    text = replace_texte(text,'&Yacute;',unescape('%DD'));
    text = replace_texte(text,'&THORN;',unescape('%DE'));
    text = replace_texte(text,'&szlig;',unescape('%DF'));
    text = replace_texte(text,'&agrave;',unescape('%E0'));
    text = replace_texte(text,'&aacute;',unescape('%E1'));
    text = replace_texte(text,'&acirc;',unescape('%E2'));
    text = replace_texte(text,'&atilde;',unescape('%E3'));
    text = replace_texte(text,'&auml;',unescape('%E4'));
    text = replace_texte(text,'&aring;',unescape('%E5'));
    text = replace_texte(text,'&aelig;',unescape('%E6'));
    text = replace_texte(text,'&ccedil;',unescape('%E7'));
    text = replace_texte(text,'&egrave;',unescape('%E8'));
    text = replace_texte(text,'&eacute;',unescape('%E9'));
    text = replace_texte(text,'&ecirc;',unescape('%EA'));
    text = replace_texte(text,'&euml;',unescape('%EB'));
    text = replace_texte(text,'&igrave;',unescape('%EC'));
    text = replace_texte(text,'&iacute;',unescape('%ED'));
    text = replace_texte(text,'&icirc;',unescape('%EE'));
    text = replace_texte(text,'&iuml;',unescape('%EF'));
    text = replace_texte(text,'&eth;',unescape('%F0'));
    text = replace_texte(text,'&ntilde;',unescape('%F1'));
    text = replace_texte(text,'&ograve;',unescape('%F2'));
    text = replace_texte(text,'&oacute;',unescape('%F3'));
    text = replace_texte(text,'&ocirc;',unescape('%F4'));
    text = replace_texte(text,'&otilde;',unescape('%F5'));
    text = replace_texte(text,'&ouml;',unescape('%F6'));
    text = replace_texte(text,'&divide;',unescape('%F7'));
    text = replace_texte(text,'&oslash;',unescape('%F8'));
    text = replace_texte(text,'&ugrave;',unescape('%F9'));
    text = replace_texte(text,'&uacute;',unescape('%FA'));
    text = replace_texte(text,'&ucirc;',unescape('%FB'));
    text = replace_texte(text,'&uuml;',unescape('%FC'));
    text = replace_texte(text,'&yacute;',unescape('%FD'));
    text = replace_texte(text,'&thorn;',unescape('%FE'));
    text = replace_texte(text,'&yuml;',unescape('%FF'));
    return text;

}

