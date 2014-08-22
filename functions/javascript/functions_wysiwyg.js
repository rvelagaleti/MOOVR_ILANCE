/*
+--------------------------------------------------------------------------
|
|   WARNING: REMOVING THIS COPYRIGHT HEADER IS EXPRESSLY FORBIDDEN
|
|   Rich Text Editor Version 4.2 (June 30, 2007) Working with Safari 1.3.2 or higher
|   ========================================
|   by Khoi Hong webmaster@cgi2k.com
|   (c) 1999 - 2007 CGI2K.COM - All right reserved 
|   http://www.cgi2k.com 
|   ========================================
|   Web: http://www.ecardmax.com
|   Email: webmaster@cgi2k.com
|   Purchase Info: http://www.ecardmax.com/index.php?step=Purchase
|   Support: http://www.ecardmax.com/index.php?step=Support
|
|   HotEditor homepage: http://www.ecardmax.com/index.php?step=Hoteditor 
+--------------------------------------------------------------------------
*/
var show_arrow_up_down = 1;
var mydirection = 'ltr'; 
var wysiwyg_path = IMAGEBASE + 'wysiwyg';
var TitleText = 'WYSIWYG Mode';
var TitleText_Texarea = 'BBCode Mode';
var iframe_meta_tag = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' + "\n";
var iframe_style = 'body {margin: 0px; padding: 8px; font-family: Verdana, Arial, Sans-Serif, Tahoma; font-size: 13px; color: black;}';
var show_custom_bbcode_bar = 0;
var array_toolbar_user_custom = new Array();
var toolbar1 = 'btRemove_Format,btFont_Name,SPACE,btFont_Size,SPACE,btFont_Color,SPACE,btUndo,btRedo,btDeleteAll';
var toolbar2 = 'btBold,btItalic,btUnderline,SPACE,btAlign_Left,btCenter,btAlign_Right,SPACE,btBullets,btNumbering,btDecrease_Indent,btIncrease_Indent,SPACE,btInsert_Image,SPACE,btTable,SPACE,btIESpell,btStrikethrough';
var toolbar3 = '';
var textarea_toolbar1 = 'btRemove_Format,btFont_Name,SPACE,btFont_Size,SPACE,btFont_Color,SPACE,btUndo,btRedo,btDeleteAll';
var textarea_toolbar2 = 'btBold,btItalic,btUnderline,SPACE,btAlign_Left,btCenter,btAlign_Right,SPACE,btBullets,btNumbering,btDecrease_Indent,btIncrease_Indent,SPACE,btInsert_Image,SPACE,btTable,SPACE,btIESpell,btStrikethrough';
var textarea_toolbar3 = '';
var minibar = 'btDeleteAll,SPACE,btFont_Name,SPACE,btFont_Color,SPACE,btHighlight,SPACE,btBold,btItalic,btUnderline,SPACE,btHyperlink,btEmotions,SPACE,btQuote,btCode,btPHP';
var textarea_minibar = 'btDeleteAll,btFont_Name,btFont_Color,SPACE,btBold,btItalic,btUnderline,SPACE,btHyperlink,btEmotions,SPACE,btQuote,btCode,btPHP';
var forecolor_frame_width = 235;
var forecolor_frame_height = 185;
var pop_Select_Forecolor = "Font Color";
var hilitecolor_frame_width = 165;
var hilitecolor_frame_height = 110;
var pop_Select_Hilitecolor = "Text Highlight Color";
var fontname_frame_width = 205;
var fontname_frame_height = 300;
var pop_Select_Font = "Font Face";
var fontsize_frame_width = 80;
var fontsize_frame_height = 249;
var pop_Select_FontSize = "Font Size";
var simley_frame_width = 370;
var simley_frame_height = 340;
var pop_Select_Smile = "Insert your emotions to document";
var wordart_frame_width = 370;
var wordart_frame_height = 340;
var pop_Select_WordArt = "Insert WordArt to document";
var clipart_frame_width = 370;
var clipart_frame_height = 340;
var pop_Select_ClipArt = "Insert ClipArt to document";
var calendar_frame_width = 330;
var calendar_frame_height = 350;
var pop_Select_Calendar = "View Calendar / World Clock";
var upload_frame_width = 385;
var upload_frame_height = 250;
var pop_Select_Upload = "Upload your image files";
var moretags_frame_width = 190;
var moretags_frame_height = 150;
var pop_Insert_Moretags ="Insert Forum Tags";
var symbol_frame_width = 382;
var symbol_frame_height = 300;
var pop_Insert_Symbol = "Insert Symbol - Special characters";
var array_fontname = new Array();
array_fontname[0] = "Arial";
array_fontname[1] = "Arial Black";
array_fontname[2] = "Arial Narrow";
array_fontname[3] = "Book Antiqua";
array_fontname[4] = "Century Gothic";
array_fontname[5] = "Comic Sans MS";
array_fontname[6] = "Courier New";
array_fontname[7] = "Fixedsys";
array_fontname[8] = "Franklin Gothic Medium";
array_fontname[9] = "Garamond";
array_fontname[10] = "Georgia";
array_fontname[11] = "Impact";
array_fontname[12] = "Lucida Console";
array_fontname[13] = "Lucida Sans Unicode";
array_fontname[14] = "Microsoft Sans Serif";
array_fontname[15] = "Palatino Linotype";
array_fontname[16] = "System";
array_fontname[17] = "Tahoma";
array_fontname[18] = "Times New Roman";
array_fontname[19] = "Trebuchet MS";
array_fontname[20] = "Verdana";
array_fontname[21] = "Wingdings";
var array_fontcolor = new Array();
array_fontcolor[0] ="#FFFFFF";
array_fontcolor[6] ="#000000";
array_fontcolor[12] ="#EEECE1";
array_fontcolor[18] ="#1F497D";
array_fontcolor[24] ="#4F81BD";
array_fontcolor[30] ="#C0504D";
array_fontcolor[36] ="#9BBB59";
array_fontcolor[42] ="#8064A2";
array_fontcolor[48] ="#4BACC6";
array_fontcolor[54] ="#F79646";
array_fontcolor[1] ="#F2F2F2";
array_fontcolor[7] ="#7F7F7F";
array_fontcolor[13] ="#DDD9C3";
array_fontcolor[19] ="#C6D9F0";
array_fontcolor[25] ="#DBE5F1";
array_fontcolor[31] ="#F2DCDB";
array_fontcolor[37] ="#EBF1DD";
array_fontcolor[43] ="#E5E0EC";
array_fontcolor[49] ="#DBEEF3";
array_fontcolor[55] ="#FDEADA";
array_fontcolor[2] ="#D8D8D8";
array_fontcolor[8] ="#595959";
array_fontcolor[14] ="#C4BD97";
array_fontcolor[20] ="#8DB3E2";
array_fontcolor[26] ="#B8CCE4";
array_fontcolor[32] ="#E5B9B7";
array_fontcolor[38] ="#D7E3BC";
array_fontcolor[44] ="#CCC1D9";
array_fontcolor[50] ="#B7DDE8";
array_fontcolor[56] ="#FBD5B5";
array_fontcolor[3] ="#BFBFBF";
array_fontcolor[9] ="#3F3F3F";
array_fontcolor[15] ="#938953";
array_fontcolor[21] ="#548DD4";
array_fontcolor[27] ="#95B3D7";
array_fontcolor[33] ="#D99694";
array_fontcolor[39] ="#C3D69B";
array_fontcolor[45] ="#B2A2C7";
array_fontcolor[51] ="#92CDDC";
array_fontcolor[57] ="#FAC08F";
array_fontcolor[4] ="#A5A5A5";
array_fontcolor[10] ="#262626";
array_fontcolor[16] ="#494429";
array_fontcolor[22] ="#17365D";
array_fontcolor[28] ="#366092";
array_fontcolor[34] ="#953734";
array_fontcolor[40] ="#76923C";
array_fontcolor[46] ="#5F497A";
array_fontcolor[52] ="#31859B";
array_fontcolor[58] ="#E36C09";
array_fontcolor[5] ="#7F7F7F";
array_fontcolor[11] ="#0C0C0C";
array_fontcolor[17] ="#1D1B10";
array_fontcolor[23] ="#0F243E";
array_fontcolor[29] ="#244061";
array_fontcolor[35] ="#632423";
array_fontcolor[41] ="#4F6128";
array_fontcolor[47] ="#3F3151";
array_fontcolor[53] ="#205867";
array_fontcolor[59] ="#974806";
array_fontcolor[60] ="#C00000";
array_fontcolor[61] ="#FF0000";
array_fontcolor[62] ="#FFC000";
array_fontcolor[63] ="#FFFF00";
array_fontcolor[64] ="#92D050";
array_fontcolor[65] ="#00B050";
array_fontcolor[66] ="#00B0F0";
array_fontcolor[67] ="#0070C0";
array_fontcolor[68] ="#002060";
array_fontcolor[69] ="#7030A0";
// messages
var safari_paste_command = "Please press key Command + V to paste text to editor.";
var safari_enter_text_link = "Enter Text Link";
var safari_bullets_numbering_prompt = "Write your text here. Click Cancel button or press Escape when you're done";
var flash_enter_url = "Enter Flash URL";
var flash_width_number_text = "Enter Width number";
var flash_width_number_default = 425;
var flash_height_number_text = "Enter Height number";
var flash_height_number_default = 350;
var enter_url_text = "Enter a URL:";
var enter_email_text = "Enter Email Address:";
var enter_image_url = "Enter Image URL:";
var capIESpell = "Spell check with IESpell";		
var alertNoIESpell = "IESpell Tool has not installed.\n\nWould you like to download and install it now?\n\nClick OK button to open IESpell download page in new window";		
var IESpellURL = "http://www.iespell.com/download.php";
var IESpellError = "Sorry! Your browser can't load IESpell";
var capDesignModeTitle = "Switch WYSIWYG Mode";
var capFont_Name = "Font Name";
var capFont_Size = "Font Size";
var capFont_Color = "Font Color";
var capHighlight = "Highlight";
var capRemove_Format = "Clear Formatting";
var capBold = "Bold (Ctrl-B)";
var capItalic = "Italic (Ctrl-I)";
var capUnderline = "Underline (Ctrl-U)";
var capAlign_Left = "Align Text Left";
var capCenter = "Center";
var capAlign_Right = "Align Text Right";
var capJustify = "Justify";
var capBreakLine = "Break (Shift Enter)";
var capBullets = "Bullets";
var capNumbering = "Numbering";
var capDecrease_Indent = "Decrease Indent";
var capIncrease_Indent = "Increase Indent";
var capDecrease_Size = "Decrease Editor Size";
var capIncrease_Size = "Increase Editor Size";
var capQuote = "Wrap [QUOTE][/QUOTE]";
var capCode = "Wrap [CODE][/CODE]";
var capPHP = "Wrap [PHP][/PHP]";
var capHTML = "Wrap [HTML][/HTML]";
var capMoreTags = "View More Tags [xxx][/xxx]";
var capFlash = "Insert Flash";
var capYouTube = "Insert YouTube Video";
var promptYouTube = "Enter YouTube URL";
var URLDefaultYouTube = "http://www.youtube.com/watch?v=XXXXXXX";
var capGoogle = "Insert Google Video";
var promptGoogle = "Enter Google Video URL";
var URLDefaultGoogle = "http://video.google.com/videoplay?docid=XXXXXXXXXXXXXX&hl=en";
var capYahoo = "Insert Yahoo Video";
var promptYahoo = "Enter Yahoo Add to Site Code";
var URLDefaultYahoo = "<embed src='http://us.i1.yimg.com/cosmos.bcst.yahoo.com/player...........' type='application/x-shockwave-flash' width='425' height='350'></embed>";
var capTable = "Insert Table";
var capCut = "Cut (Ctrl-X)";
var capCopy = "Copy (Ctrl-C)";
var capPaste = "Paste (Ctrl-V)";
var capUndo = "Undo (Ctrl-Z)";
var capRedo = "Redo (Ctrl-Y)";
var capHyperlink = "Insert Hyperlink (Ctrl-K)";
var capHyperlink_Email = "Insert Email Link";
var capRemovelink = "Remove Hyperlink";
var capCalendar = "View Calendar / World Clock";
var capInsert_Image = "Insert Image";
var capClipart = "Insert Clipart";
var capWordArt = "Insert WordArt";
var capEmotions = "Insert your emotions";
var capUpload = "Upload your own Photo";
var capStrikethrough = "Strikethrough";
var capSubscript = "Subscript";
var capSuperscript = "Superscript";
var capHorizontal = "Horizontal Line";
var capSymbol = "Insert Symbol";
var capVirtualKeyboard = "Open Virtual Keyboard";
var capViewHTML = "View/Edit HTML source code";
var capDelete_All = "Delete All";
var capOnOff_RichText = "Switch On/Off WYSIWYG Mode";
var bbNumbering = "LIST=1,*";
var bbBullets = "LIST,*";
var bbFlash = "FLASH";
var starup = 0;
var is_rich_text = false;
var rng;
var currentRTE;
var currentTEXT;
var allRTEs = "";
var isIE;
var isIE_Mac;
var isGecko;
var isOpera9;
var isSafari;
var isSafari3;
var isKonqueror;
var isICab;
var isMacOS;
var HTML_ON;
var chkViewHTML;
var chkVK = 0;
var editor_size;
var editor_cookie;
var ImgSwitch = '';
var print_dir = '';
var currentwindow = '';
var currenteditor = '';
var editor_type;
var ua = navigator.userAgent.toLowerCase();
if (mydirection == 'rtl')
{
    print_dir = ' dir="rtl" ';
}
else
{
    print_dir = ' dir="ltr" ';
}
isIE = ((ua.indexOf("msie") != -1) && (ua.indexOf("opera") == -1) && (ua.indexOf("webtv") == -1));
isGecko = (ua.indexOf("gecko") != -1 && ua.indexOf("safari") == -1);
isOpera9 = (ua.indexOf("opera") != -1 && ua.indexOf("safari") == -1);
isSafari = (ua.indexOf("gecko") != -1 && ua.indexOf("safari") != -1 && ua.indexOf("version/3") == -1);
isSafari3 = (ua.indexOf("gecko") != -1 && ua.indexOf("safari") != -1 && ua.indexOf("version/3") != -1);
isKonqueror = (ua.indexOf("konqueror") != -1);
isICab = (ua.indexOf("icab") != -1);
isIE_Mac = (ua.indexOf("msie") != -1 && ua.indexOf("mac") != -1);
isMacOS = (ua.indexOf("macintosh") != -1);
var ns4 = document.layers;
var ie4 = document.all;
var ns6 = document.getElementById && !document.all;
var steditor = 0;
var nsx;
var nsy;
var nstemp;
if (document.getElementById && document.designMode && !isKonqueror && !isIE_Mac)
{
    is_rich_text = true;
}
function print_wysiwyg_editor(a, b, c, d, height, width, js, show_switch, show_mode_editor, view_richtext, tabindex)
{
    starup = 1;
    editor_size = parseInt(height);
    var f = true;
    var g = false;
    view_richtext = '1';
    if (view_richtext == '0')
    {
        show_mode_editor = 0;
    }
    if (view_richtext == '0')
    {
        is_rich_text = false;
    }
    if (is_rich_text)
    {
        if (allRTEs.length > 0)
        {
            allRTEs += ";";
        }
        allRTEs += b;
        editor_cookie = read_cookie(b + '_cookie');
	editor_cookie = '1';
        if (editor_cookie == '1')
        {
            ImgSwitch = "switch_richtext_on.gif";
            editor_type = '1';
            c = parse_bbcode_to_html(c);
        }
        else if (editor_cookie == '0')
        {
            ImgSwitch = "switch_richtext_off.gif";
            editor_type = '0';
            c = parse_html_to_bbcode(c);
        }
        else
        {
            if (show_mode_editor == '1')
            {
                ImgSwitch = "switch_richtext_on.gif";
                editor_type = '1';
                c = parse_bbcode_to_html(c);
            }
            else
            {
                ImgSwitch = "switch_richtext_off.gif";
                editor_type = '0';
                c = parse_html_to_bbcode(c);
            }
        }
        
    }
    else
    {
        if (allRTEs.length > 0)
        {
            allRTEs += ";";
        }
        allRTEs += b;
        show_switch = '0';
        ImgSwitch = "switch_richtext_off.gif";
        editor_type = '0';
        c = parse_html_to_bbcode(c);
    }
    //always start with richtext on
    ImgSwitch = "switch_richtext_on.gif";
    editor_type = '1';
    write_wysiwyg_editor(a, b, c, d, height, f, g, width, js, show_switch, show_mode_editor, view_richtext, tabindex);
}

function enable_design_mode(a, b, c)
{
    b = b.replace(/&amp;#/gi, "&#");
    var d = '<html ' + print_dir + ' id="' + a + '">';
    d += "<head>\n" + iframe_meta_tag + "\n<style><!--p { margin-top: 0px; margin-bottom: 0px; } " + iframe_style + "--></style>\n</head>\n";
    d += '<body background="' + wysiwyg_path + '/iframe_background.gif" style="background-attachment: fixed; background-repeat: repeat">';
    d += b + "\n";
    d += "</body>\n";
    d += '</html>';
    if (document.all)
    {
        var f = frames[a].document;
        f.open();
        f.write(d);
        f.close();
        if (!c)
        {
            f.designMode = "On"
        }
    }
    else
    {
        try
        {
            if (!c) document.getElementById(a).contentDocument.designMode = "on";
            try
            {
                var f = document.getElementById(a).contentWindow.document;
                f.open();
                if (isGecko || isSafari)
                {
                    f.write(d + "<br><br>")
                }
                else
                {
                    f.write(d)
                }
                f.close();
                if (isGecko && !c)
                {
                    f.addEventListener("keypress", kb_handler, true)
                }
            }
            catch(e)
            {
                alert_js("Error preloading content.")
            }
        }
        catch(e)
        {
            if (isGecko)
            {
                setTimeout("enable_design_mode('" + a + "', '" + b + "');", 10)
            }
            else
            {
                return false
            }
        }
    }
}

function prepare_bbeditor_wysiwygs(view_richtext)
{
    var a = allRTEs.split(";");
    for (var i = 0; i < a.length; i++)
    {
        update_bbeditor_wysiwyg(a[i], view_richtext);
    }
}

function update_bbeditor_wysiwyg(a, view_richtext)
{
    starup = 0;
    if (editor_type == '1')
    {
        if (HTML_ON == 'no')
        {
            document.getElementById('chkSrc' + a).checked = false;
            toggle_html_source(a);
        }
        var b = document.getElementById(a).contentWindow.document.body.innerHTML;
        b = b.replace(/<div><\/div>/ig, "");
        b = b.replace(/<br[^>]*>/ig, "<br>");
        b = b.replace(/[\n\r]/ig, '');
        b = parse_html_to_bbcode(b);
        b = b.replace(/\[table\]\n+/gi, "[TABLE]");
        b = b.replace(/\n+\[td\]/gi, "[TD]");
        b = b.replace(/\n+\[\/table\]/gi, "[/TABLE]");
        b = b.replace(/\n+\[\/td\]/gi, "[/TD]");
        b = b.replace(/\n+\[tr\]/gi, "[TR]");
        b = b.replace(/\n+\[\/tr\]/gi, "[/TR]");
        document.getElementById('bbeditor_bbcode_ouput_' + a).value = b;
        document.getElementById('bbeditor_html_ouput_' + a).value = parse_bbcode_to_html(b);
    }
    else if (editor_type == '0')
    {
        var b = document.getElementById("textarea_" + a).value;
        b = parse_bbcode_to_html(b);
        b = b.replace(/[\r\n]/gi, "");
        b = b.replace(/<br><(div|ul|ol)/gi, "<$1");
        document.getElementById(a).contentWindow.document.body.innerHTML = b;
        b = document.getElementById(a).contentWindow.document.body.innerHTML;
        b = b.replace(/[\r\n]/gi, "");
        document.getElementById('bbeditor_html_ouput_' + a).value = b;
        b = parse_html_to_bbcode(b);
        b = b.replace(/\[table\]\n+/gi, "[TABLE]");
        b = b.replace(/\n+\[td\]/gi, "[TD]");
        b = b.replace(/\n+\[\/table\]/gi, "[/TABLE]");
        b = b.replace(/\n+\[\/td\]/gi, "[/TD]");
        b = b.replace(/\n+\[tr\]/gi, "[TR]");
        b = b.replace(/\n+\[\/tr\]/gi, "[/TR]");
        document.getElementById('bbeditor_bbcode_ouput_' + a).value = b;
    }
}

function toggle_html_source(a)
{
    var b;
    if (isIE)
    {
        b = frames[a].document
    }
    else
    {
        b = document.getElementById(a).contentWindow.document
    }
    if (document.getElementById('chkSrc' + a).checked)
    {
        HTML_ON = 'no';
        if (isIE)
        {
            b.body.innerText = b.body.innerHTML
        }
        else
        {
            var c = b.createTextNode(b.body.innerHTML);
            b.body.innerHTML = '';
            b.body.appendChild(c)
        }
    }
    else
    {
        HTML_ON = 'yes';
        if (isIE)
        {
            b.body.innerHTML = b.body.innerText
        }
        else
        {
            var c = b.body.ownerDocument.createRange();
            c.selectNodeContents(b.body);
            b.body.innerHTML = c.toString()
        }
    }
}

function switch_editor(a)
{
    if (HTML_ON == 'no')
    {
        alert_js('Please uncheck the HTML checkbox');
        return false;
    }
    starup = 0;
    var b = editor_size + 70;
    currenteditor = a;
    // rebuild bbcode toolbar
    if (editor_type == '1')
    {
        editor_type = '0';
        document.getElementById('textarea_' + a).value = '';
        document.getElementById('editor_switch' + a).src = wysiwyg_path + '/' + 'switch_richtext_off.gif';
        document.getElementById('change_title_editor' + a).innerHTML = TitleText_Texarea;
        var c = document.getElementById(a).contentWindow.document.body.innerHTML;
        c = c.replace(/[\n\r]/ig, '');
        create_cookie(a + '_cookie', '0', 365);
        c = parse_html_to_bbcode(c);
        if (isMacOS && isGecko)
        {
            hot_showid2('bbeditor_richtool' + a, 'none');
            hot_showid2('bbeditor_texttool' + a, 'block');
            document.getElementById(a).style.height = '0px';
            document.getElementById(a).style.width = '0px';
            document.getElementById('textarea_' + a).style.height = b + 'px';
            document.getElementById('textarea_' + a).style.width = '595px';
        }
        else if (isSafari)
        {
            document.getElementById('bbeditor_richtool' + a).style.height = '0px';
            document.getElementById('bbeditor_richtool' + a).style.visibility = 'hidden';
            hot_showid2('bbeditor_texttool' + a, 'block');
        }
        else
        {
            hot_showid2('bbeditor_richtool' + a, 'none');
            hot_showid2('bbeditor_texttool' + a, 'block');
        }
        document.getElementById('textarea_' + a).value = c;
    }
    // rebuild wysiwyg toolbar
    else
    {
        editor_type = '1';
        if (isMacOS && isGecko)
        {
            hot_showid2('bbeditor_richtool' + a, 'block');
            hot_showid2('bbeditor_texttool' + a, 'none');
            document.getElementById('textarea_' + a).style.height = '0px';
            document.getElementById('textarea_' + a).style.width = '0px';
            document.getElementById(a).style.height = b + 'px';
            document.getElementById(a).style.width = '590px';
        }
        else if (isSafari)
        {
            document.getElementById('bbeditor_richtool' + a).style.visibility = 'visible';
            document.getElementById('bbeditor_richtool' + a).style.height = b + 'px';
            hot_showid2('bbeditor_texttool' + a, 'none');
        }
        else
        {
            hot_showid2('bbeditor_richtool' + a, 'block');
            hot_showid2('bbeditor_texttool' + a, 'none');
        }
        if (isIE)
        {
            oRTE = frames[a];
        }
        else
        {
            oRTE = document.getElementById(a).contentWindow;
        }
        var d = document.getElementById('textarea_' + a).value;
        var e = parse_bbcode_to_html(d);
        create_cookie(a + '_cookie', '1', 365);
        oRTE.document.body.innerHTML = e;
        document.getElementById('editor_switch' + a).src = wysiwyg_path + '/' + 'switch_richtext_on.gif';
        document.getElementById('change_title_editor' + a).innerHTML = TitleText;
    }
}

function resize_editor(a, b, c)
{
    currenteditor = b;
    if (a == 'decrease_size')
    {
        if (isKonqueror)
        {
            var d = parseInt(document.getElementById(c).style.height);
            if (d > editor_size) document.getElementById(c).style.height = d - 50 + "px";
            return false
        }
        if (editor_type == '1')
        {
            var d = parseInt(document.getElementById(b).style.height);
            if (d > editor_size) document.getElementById(b).style.height = d - 50 + "px"
        }
        else
        {
            var d = parseInt(document.getElementById(c).style.height);
            if (d > editor_size) document.getElementById(c).style.height = d - 50 + 'px'
        }
    }
    else if (a == 'increase_size')
    {
        if (isKonqueror)
        {
            var d = parseInt(document.getElementById(c).style.height);
            document.getElementById(c).style.height = d + 50 + 'px';
            return false
        }
        if (editor_type == '1')
        {
            var d = parseInt(document.getElementById(b).style.height);
            document.getElementById(b).style.height = d + 50 + 'px'
        }
        else
        {
            var d = parseInt(document.getElementById(c).style.height);
            document.getElementById(c).style.height = d + 50 + 'px'
        }
    }
}

function write_wysiwyg_editor(a, b, c, d, height, f, g, width, js, show_switch, show_mode_editor, view_richtext, tabindex)
{
    document.write('<table cellpadding="0" cellspacing="0" border="0"><tr><td class="wysiwyg_wrapper" colspan="3">');
    if (is_rich_text)
    {
        document.write('<div id="bbeditor_richtool' + b + '"><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>');
        if (toolbar1 != '' && a != 'min')
        {
            document.write('<td nowrap="nowrap">');
            array = toolbar1.split(",");
            for (i = 0; i <= array.length; i++)
            {
                if (array[i])
                {
                    construct_wysiwyg_toolbar(array[i], b);
                }
            }
            document.write('</td>');
            if (show_arrow_up_down == 1)
            {        
                document.write('<td width="100%" align="right"><div class="wysiwygbutton"><img style="cursor: hand; cursor: pointer;" onclick="resize_editor(\'decrease_size\',\'' + b + '\',\'' + 'textarea_' + b + '\');\" src="' + IMAGEBASE + 'wysiwyg/resize_0.gif" width="21" height="9" alt="' + capDecrease_Size + '" /></div><div class="wysiwygbutton"><img style="cursor: hand; cursor: pointer;" onclick="resize_editor(\'increase_size\',\'' + b + '\',\'' + 'textarea_' + b + '\');\" src="' + IMAGEBASE + 'wysiwyg/resize_1.gif" width="21" height="9" alt="' + capIncrease_Size + '" /></div></td>');
            }
            if (is_rich_text && view_richtext == '1' && show_switch == '1')
            {
                document.write('<td width="100%"><div class="wysiwygbutton" id="switch_span' + b + '" title="' + capOnOff_RichText + '"><img style="cursor: hand; cursor: pointer;" onclick="switch_editor(\'' + b + '\');" id="editor_switch' + b + '" src="' + IMAGEBASE + 'wysiwyg/switch_richtext_on.gif" width="21" height="20" alt="' + capDesignModeTitle + '" /></div></td>');
            }
        }
        document.write('</tr>');
        if (toolbar2 != '' && a != 'min')
        {
            document.write('<tr><td nowrap="nowrap">');
            array = toolbar2.split(",");
            for (i = 0; i <= array.length; i++)
            {
                if (array[i])
                {
                    construct_wysiwyg_toolbar(array[i], b);
                }
            }
            document.write('</td></tr>');
        }
        if (toolbar3 != "" && a != "min")
        {
            document.write('<tr><td nowrap="nowrap">');
            array = toolbar3.split(",");
            for (i = 0; i <= array.length; i++)
            {
                if (array[i])
                {
                    construct_wysiwyg_toolbar(array[i], b);
                }
            }
            document.write('</td></tr>');
        }
        if (show_custom_bbcode_bar == "1" && a != "min" && !isSafari)
        {
            document.write('<tr><td nowrap="nowrap">');
            for (i = 0; i <= array_toolbar_user_custom.length; i++)
            {
                if (array_toolbar_user_custom[i])
                {
                    show_custom_toolbar(array_toolbar_user_custom[i], b);
                }
            }
            document.write('</td></tr>');
        }
        if (minibar != "" && a == "min")
        {
            document.write('<tr><td nowrap="nowrap">');
            array = minibar.split(",");
            for (i = 0; i <= array.length; i++)
            {
                if (array[i])
                {
                    construct_wysiwyg_toolbar(array[i], b);
                }
            }
            document.write('</td></tr>');
        }
        document.write('</table>');
        if (isMacOS && isGecko)
        {
            document.write('</div>');
        }
        document.write('<iframe style="width:' + width + '; height:' + height + '; background-color: white" frameborder="1" class="wysiwyg" id="' + b + '" name="' + b + '" tabindex="' + tabindex + '"></iframe>');
        if (isMacOS && isGecko)
        {
            var h = "";
        }
        else
        {
            document.write('</div>');
        }
    }
    document.write('<div id="bbeditor_texttool' + b + '"><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>');
    if (textarea_toolbar1 != '' && a != 'min')
    {
        document.write('<td nowrap="nowrap">');
        array = textarea_toolbar1.split(",");
        for (i = 0; i <= array.length; i++)
        {
            if (array[i])
            {
                show_toolbar_textarea(array[i], 'textarea_' + b);
            }
        }
        document.write('</td>');
        if (show_arrow_up_down == 1)
        {        
            document.write('<td width="100%" align="right"><div class="wysiwygbutton"><img onclick="resize_editor(\'decrease_size\', \'' + b + '\', \'' + 'textarea_' + b + '\');\" src="' + IMAGEBASE + 'wysiwyg/resize_0.gif" width="21" height="9" alt="' + capDecrease_Size + '" /></div><div class="wysiwygbutton"><img onclick="resize_editor(\'increase_size\', \'' + b + '\', \'' + 'textarea_' + b + '\');\" src="' + IMAGEBASE + 'wysiwyg/resize_1.gif" width="21" height="9" alt="' + capIncrease_Size + '" /></div></td>');
        }
        if (is_rich_text && view_richtext == '1' && show_switch == '1')
        {
            document.write('<td width="100%"><div class="wysiwygbutton" id="switch_span' + b + '" title="' + capOnOff_RichText + '"><img style="cursor: hand; cursor: pointer;" onclick="switch_editor(\'' + b + '\');" id="editor_switch' + b + '" src="' + IMAGEBASE + 'wysiwyg/switch_richtext_off.gif" width="21" height="20" alt="' + capDesignModeTitle + '" /></div></td>');
        }
    }
    document.write('</tr>');
    if (textarea_toolbar2 != '' && a != 'min')
    {
        document.write('<tr><td nowrap="nowrap">');
        array = textarea_toolbar2.split(",");
        for (i = 0; i <= array.length; i++)
        {
            if (array[i])
            {
                show_toolbar_textarea(array[i], "textarea_" + b);
            }
        }
        document.write('</td></tr>');
    }
    if (textarea_toolbar3 != '' && a != 'min')
    {
        document.write('<tr><td nowrap="nowrap">');
        array = textarea_toolbar3.split(",");
        for (i = 0; i <= array.length; i++)
        {
            if (array[i])
            {
                show_toolbar_textarea(array[i], "textarea_" + b);
            }
        }
        document.write('</td></tr>');
    }
    
    if (show_custom_bbcode_bar == '1' && a != 'min')
    {
        document.write('<tr><td nowrap="nowrap">');
        for (i = 0; i <= array_toolbar_user_custom.length; i++)
        {
            if (array_toolbar_user_custom[i])
            {
                show_custom_toolbar_bbcode(array_toolbar_user_custom[i], b);
            }
        }
        document.write('</td></tr>');
    }
    
    if (textarea_minibar != '' && a == 'min')
    {
        document.write('<tr><td nowrap="nowrap">');
        array = textarea_minibar.split(",");
        for (i = 0; i <= array.length; i++)
        {
            if (array[i])
            {
                show_toolbar_textarea(array[i], "textarea_" + b);
            }
        }
        document.write('</td></tr>');
    }
    document.write('</table>');
    if (isMacOS && isGecko)
    {
        document.write('</div>');
    }
    var j = c.replace(/<br>/ig, "\n");
    j = j.replace(/&lt;/g, "<");
    j = j.replace(/&gt;/g, ">");
    j = j.replace(/&amp;#/gi, "&#");
    j = j.replace(/\[\/tr\]/gi, "\n[/TR]");
    j = j.replace(/\[tr\]/gi, "\n[TR]");
    j = j.replace(/\[td\]/gi, "\n[TD]");
    j = j.replace(/\[\/table\]/gi, "\n[/TABLE]");
    j = j.replace(/\[\/table\]$/gi, "[/TABLE]\n");
    document.write('<div style="padding-top:6px"><textarea wrap="auto" ' + print_dir + ' rows="10" cols="60" style="width:' + width + '; height:' + height + '; font-family: Verdana, Arial, Sans-Serif, Tahoma; font-size: 12px; color: black; border: 1px solid #6593CF;" class="wysiwyg" id="textarea_' + b + '" name="textarea_' + b + '" ' + js + '>' + j + '</textarea></div>');
    if (isMacOS && isGecko)
    {
        var h = "";
    }
    else
    {
        document.write('</div>');
    }
    document.write('</td></tr>');
    if (editor_type == '1')
    {
        var v = '<span id="change_title_editor' + b + '">' + TitleText + '</span>';
    }
    else
    {
        var v = '<span id="change_title_editor' + b + '">' + TitleText_Texarea + '</span>';
    }
    document.write('<tr><td align="left" nowrap="nowrap" style="height:7px; font-size: 8pt; font-family: Verdana, Tahoma, Arial, sans-serif;">' + v + '</td></tr></table><input type="hidden" id="bbeditor_html_ouput_' + b + '" name="bbeditor_html_ouput_' + b + '" value="" /><input type="hidden" id="bbeditor_bbcode_ouput_' + b + '" name="bbeditor_bbcode_ouput_' + b + '" value="" />');
    if (!document.getElementById("hotmem"))
    {
        document.write('<input type="hidden" id="hotmem" name="hotmem" value="" />');
    }
    if (is_rich_text)
    {
        enable_design_mode(b, c, g);
    }
    var w = editor_size + 92;
    if (isMacOS && isGecko)
    {
        if (editor_type == '1')
        {
            hot_showid2('bbeditor_richtool' + b, 'block');
            hot_showid2('bbeditor_texttool' + b, 'none');
            document.getElementById('textarea_' + b).style.height = '0px';
            document.getElementById('textarea_' + b).style.width = '0px';
        }
        else
        {
            hot_showid2('bbeditor_richtool' + b, 'none');
            hot_showid2('bbeditor_texttool' + b, 'block');
            document.getElementById(b).style.height = '0px';
            document.getElementById(b).style.width = '0px';
        }
    }
    else
    {
        if (editor_type == '1')
        {
            hot_showid2('bbeditor_richtool' + b, 'block');
            hot_showid2('bbeditor_texttool' + b, 'none');
        }
        else
        {
            if (isSafari)
            {
                document.getElementById('bbeditor_richtool' + b).style.height = '0px';
                document.getElementById("bbeditor_richtool" + b).style.visibility = 'hidden';
                hot_showid2('bbeditor_texttool' + b, 'block');
            }
            else
            {
                hot_showid2('bbeditor_richtool' + b, 'none');
                hot_showid2('bbeditor_texttool' + b, 'block');
            }
        }
    }
}

function show_custom_toolbar(a, b)
{
    if (a == 'SPACE')
    {
        document.write('<img align="absmiddle" src="' + wysiwyg_path + '/space.gif" border="0" /><img src="' + wysiwyg_path + '/button_space.gif" border="0" />');
    }
    else
    {
        var c = a.split('::');
        document.write("<img align=\"absmiddle\" title=\"" + c[1] + "\" src=\"" + wysiwyg_path + "/" + c[0] + "\" onmouseover=\"this.className='wysiwygbutton_over'; hide_it('" + b + "');\" onmouseout=\"this.className='wysiwygbutton_out';\" onclick=\"write_html_custom(" + "'" + c[2] + "'" + ", '" + c[3] + "', '" + b + "')\"><img src=\"" + wysiwyg_path + "/button_space.gif\" border=\"0\" />");
    }
}

function show_custom_toolbar_bbcode(a, b)
{
    if (a == 'SPACE')
    {
        document.write('<img align="absmiddle" src="' + wysiwyg_path + '/space.gif" border="0" /><img src="' + wysiwyg_path + '/button_space.gif" border="0" />');
    }
    else
    {
        var c = a.split('::');
        document.write("<img align=\"absmiddle\" class=\"wysiwygbutton\" title=\"" + c[1] + "\" src=\"" + wysiwyg_path + "/" + c[0] + "\" onmouseover=\"this.className='wysiwygbutton_over';hide_it('" + b + "');\" onmouseout=\"this.className='wysiwygbutton_out';\" onclick=\"write_text_custom(" + "'" + c[2] + "'" + ", '" + c[3] + "', '" + b + "')\"><img src=\"" + wysiwyg_path + "/button_space.gif\" border=\"0\" />")
    }
}

function construct_wysiwyg_toolbar(a, b)
{
    a = a.replace(" ", "");
    if (a == 'SPACE')
    {
        document.write('<img align="absmiddle" src="' + wysiwyg_path + '/space.gif" border="0" /><img src="' + wysiwyg_path + '/button_space.gif" border="0" />');
    }
    else if (a == 'btMoreTags' && !isSafari)
    {
        write_button_richtext(b, capMoreTags, "more_tags.gif", "more_tags", "", "more_tags_" + b);
    }
    else if (a == 'btFont_Name')
    {
        write_button_richtext(b, capFont_Name, "fontname.gif", "fontname", "", "fontname_" + b);
    }
    else if (a == 'btFont_Size')
    {
        write_button_richtext(b, capFont_Size, "fontsize.gif", "fontsize", "", "fontsize_" + b);
    }
    else if (a == 'btFont_Color')
    {
        write_button_richtext(b, capFont_Color, "fontcolor.gif", "forecolor", "", "forecolor_" + b);
    }
    else if (a == 'btHighlight')
    {
        write_button_richtext(b, capHighlight, "highlinght.gif", "hilitecolor", "", "hilitecolor_" + b);
    }
    else if (a == 'btRemove_Format' && !isSafari)
    {
        write_button_richtext(b, capRemove_Format, "remove.gif", "removeformat", "", "remove_format_" + b);
    }
    else if (a == 'btBold')
    {
        write_button_richtext(b, capBold, "bold.gif", "bold", "", "bold_" + b);
    }
    else if (a == 'btItalic')
    {
        write_button_richtext(b, capItalic, "italic.gif", "italic", "", "itatlic_" + b);
    }
    else if (a == 'btUnderline')
    {
        write_button_richtext(b, capUnderline, "underline.gif", "underline", "", "underline_" + b);
    }
    else if (a == 'btAlign_Left')
    {
        write_button_richtext(b, capAlign_Left, "aleft.gif", "justifyleft", "", "aleft_" + b);
    }
    else if (a == 'btCenter')
    {
        write_button_richtext(b, capCenter, "acenter.gif", "justifycenter", "", "acenter_" + b);
    }
    else if (a == 'btAlign_Right')
    {
        write_button_richtext(b, capAlign_Right, "aright.gif", "justifyright", "", "aright_" + b);
    }
    else if (a == 'btJustify')
    {
        write_button_richtext(b, capJustify, "ajustify.gif", "justifyfull", "", "ajustify_" + b);
    }
    else if (a == 'btBullets')
    {
        write_button_richtext(b, capBullets, "listbullets.gif", "insertunorderedlist", "", "bullet_" + b);
    }
    else if (a == 'btNumbering')
    {
        write_button_richtext(b, capNumbering, "listnumber.gif", "insertorderedlist", "", "numbering_" + b);
    }
    else if (a == 'btDecrease_Indent' && !isSafari)
    {
        write_button_richtext(b, capDecrease_Indent, "indentleft.gif", "outdent", "", "indent1_" + b);
    }
    else if (a == 'btIncrease_Indent' && !isSafari)
    {
        write_button_richtext(b, capIncrease_Indent, "indentright.gif", "indent", "", "indent2_" + b);
    }
    else if (a == 'btTable')
    {
        write_button_richtext(b, capTable, "table.gif", "addtable", "", "table_" + b);
    }
    else if (a == 'btHyperlink')
    {
        write_button_richtext(b, capHyperlink, "createlink.gif", "createlink", "", "link_" + b);
    }
    else if (a == 'btHyperlink_Email')
    {
        write_button_richtext(b, capHyperlink_Email, "createlink_email.gif", "createlink_email", "", "email_" + b);
    }
    else if (a == 'btRemovelink' && !isSafari)
    {
        write_button_richtext(b, capRemovelink, "removelink.gif", "unlink", "", "removelink_" + b);
    }
    else if (a == 'btFlash')
    {
        write_button_richtext(b, capFlash, "flash.gif", "flash", "", "flash_" + b);
    }
    else if (a == 'btYouTube')
    {
        write_button_richtext(b, capYouTube, "youtube.gif", "youtube", "", "youtube_" + b);
    }
    else if (a == 'btGoogle')
    {
        write_button_richtext(b, capGoogle, "google.gif", "google", "", "google_" + b);
    }
    else if (a == 'btYahoo')
    {
        write_button_richtext(b, capYahoo, "yahoo.gif", "yahoo", "", "yahoo_" + b);
    }
    else if (a == 'btQuote' && !isSafari)
    {
        write_button_richtext(b, capQuote, "quote.gif", "quote", "", "quote_" + b);
    }
    else if (a == 'btCode' && !isSafari)
    {
        write_button_richtext(b, capCode, "code.gif", "code", "", "code_" + b);
    }
    else if (a == 'btPHP' && !isSafari)
    {
        write_button_richtext(b, capPHP, "php.gif", "php", "", "php_" + b);
    }
    else if (a == 'btHTML' && !isSafari)
    {
        write_button_richtext(b, capHTML, "html_tag.gif", "html", "", "html_" + b);
    }
    else if (a == 'btStrikethrough')
    {
        write_button_richtext(b, capStrikethrough, "strikethrough.gif", "Strikethrough", "", "strike_" + b);
    }
    else if (a == 'btSubscript')
    {
        write_button_richtext(b, capSubscript, "subscript.gif", "Subscript", "", "sub_" + b);
    }
    else if (a == 'btSuperscript')
    {
        write_button_richtext(b, capSuperscript, "superscript.gif", "Superscript", "", "sup_" + b);
    }
    else if (a == 'btHorizontal')
    {
        write_button_richtext(b, capHorizontal, "line.gif", "inserthorizontalrule", "", "hr_" + b);
    }
    else if (a == 'btCut')
    {
        write_button_richtext(b, capCut, "cut.gif", "cut", "", "cut_" + b);
    }
    else if (a == 'btCopy')
    {
        write_button_richtext(b, capCopy, "copy.gif", "copy", "", "copy_" + b);
    }
    else if (a == 'btPaste')
    {
        write_button_richtext(b, capPaste, "paste.gif", "paste", "", "paste_" + b);
    }
    else if (a == 'btUndo')
    {
        write_button_richtext(b, capUndo, "undo.gif", "undo", "", "undo_" + b);
    }
    else if (a == 'btRedo')
    {
        write_button_richtext(b, capRedo, "redo.gif", "redo", "", "redo_" + b);
    }
    else if (a == 'btInsert_Image')
    {
        if (isSafari)
        {
            write_button_richtext(b, capInsert_Image, "insertimage.gif", "safari_InsertImage", "", "safariimg_" + b);
        }
        else
        {
            document.write("<img align=\"absmiddle\" class=\"wysiwygbutton\" title=\"" + capInsert_Image + "\" src=\"" + wysiwyg_path + "/insertimage.gif\" onmouseover=\"this.className='wysiwygbutton_over';\" onmouseout=\"this.className='wysiwygbutton_out';\" onclick=\"add_image(" + "'" + b + "'" + ")\"><img src=\"" + wysiwyg_path + "/button_space.gif\" />");
        }
    }
    else if (a == 'btDeleteAll')
    {
        write_button_richtext(b, capDelete_All, "delete_all.gif", "delete_all", "", "deleteall_" + b);
    }
    else if (a == 'btIESpell' && isIE)
    {
        write_button_richtext(b, capIESpell, "iespell.gif", "iespell", "", "iespell_" + b);
    }
    else if (a == 'chkViewHTML')
    {
        document.write('<input title="' + capViewHTML + '" type="checkbox" id="chkSrc' + b + '" onclick="toggle_html_source(\'' + b + '\');" /><span style="font-size:10px;margin-top: 2px; margin-bottom: 0px; "> HTML</span><img src=' + wysiwyg_path + '/button_space.gif>');
    }
}
function write_button_richtext(a, b, c, d, e, f)
{
    if (!isSafari)
    {
        document.write("<img align=\"absmiddle\" id=\"" + f + "\" class=\"wysiwygbutton\" title=\"" + b + "\" src=\"" + wysiwyg_path + "/" + c + "\" onmouseover=\"this.className='wysiwygbutton_over'; hide_it('" + a + "');\" onmouseout=\"this.className='wysiwygbutton_out';\" onclick=\"format_text_a(" + "'" + a + "'" + ", '" + d + "', '" + e + "')\"><img src=\"" + wysiwyg_path + "/button_space.gif\" border=\"0\" />");
    }
    else
    {
        document.write("<img align=\"absmiddle\" id=\"" + f + "\" class=\"wysiwygbutton\" title=\"" + b + "\" src=\"" + wysiwyg_path + "/" + c + "\" onmouseover=\"this.className='wysiwygbutton_over'; hide_it('" + a + "');\" onmouseout=\"this.className='wysiwygbutton_out';\" onmousedown=\"return format_text_a(" + "'" + a + "'" + ", '" + d + "', '" + e + "')\"><img src=\"" + wysiwyg_path + "/button_space.gif\" border=\"0\" />");
    }
}

function safari_selection(a)
{
    var b = '';
    if (a.getSelection)
    {
        b = a.getSelection()
    }
    else if (a.document.getSelection)
    {
        b = a.document.getSelection()
    }
    else if (a.document.selection)
    {
        b = a.document.selection.createRange().text
    }
    return b;
}

function show_toolbar_textarea(a, b)
{
    a = a.replace(" ", "");
    if (a == 'SPACE')
    {
        document.write('<img align="absmiddle" src="' + wysiwyg_path + '/space.gif" border="0" /><img src="' + wysiwyg_path + '/button_space.gif" border="0" />');
    }
    else if (a == 'btFont_Name')
    {
        document.write("<select class=\"wysiwyg_itextarea\" size=\"1\" onchange=\"wrap_selection('FONT', '=' + this.value, '" + b + "'); this.selectedIndex='0';\"><option value=\"\">Fonts</option>");
        for (x = 0; x < array_fontname.length; x++)
        {
            array_fontname[x];
            document.write('<option value="' + array_fontname[x] + '">' + array_fontname[x] + '</option>');
        }
        document.write('</select><img src="' + wysiwyg_path + '/button_space.gif" border="0" />');
    }
    else if (a == 'btFont_Size')
    {
        document.write("<select style=\"width:52px\" class=\"wysiwyg_itextarea\" size=\"1\" onchange=\"wrap_selection('SIZE', '=' + this.value, '" + b + "'); this.selectedIndex='0';\"><option value=\"\">Size</option>");
        for (y = 1; y < 8; y++)
        {
            document.write('<option value="' + y + '">' + y + '</option>');
        }
        document.write('</select><img src="' + wysiwyg_path + '/button_space.gif" border="0" />');
    }
    else if (a == 'btFont_Color')
    {
        document.write("<select style=\"width:60px\" class=\"wysiwyg_itextarea\" size=\"1\" onchange=\"wrap_selection('COLOR', '=' + this.value, '" + b + "'); this.selectedIndex='0';\"><option value=\"\">Color</option>");
        for (z = 0; z < array_fontcolor.length; z++)
        {
            document.write("<option style='background-color:" + array_fontcolor[z] + "; color:" + array_fontcolor[z] + "' value='" + array_fontcolor[z] + "'>" + array_fontcolor[z] + "</option>")
        }
        document.write('</select><img src="' + wysiwyg_path + '/button_space.gif" border="0" />');
    }
    else if (a == 'btHighlight')
    {
        document.write("<select style=\"width:65px\" class=\"wysiwyg_itextarea\" size=\"1\" onchange=\"wrap_selection('HIGHLIGHT', '=' + this.value, '" + b + "');this.selectedIndex='0';\"><option value=\"\">HiLight</option>");
        for (i = 0; i < array_fontcolor.length; i++)
        {
            document.write("<option style='background-color:" + array_fontcolor[i] + "; color:" + array_fontcolor[i] + "' value='" + array_fontcolor[i] + "'>" + array_fontcolor[i] + "</option>")
        }
        document.write('</select><img src="' + wysiwyg_path + '/button_space.gif" border="0" />');
    }
    else if (a == 'btCut')
    {
        write_button_textarea(capCut, 'cut.gif', '', 'cut', b);
    }
    else if (a == 'btCopy')
    {
        write_button_textarea(capCopy, 'copy.gif', '', 'copy', b);
    }
    else if (a == 'btPaste')
    {
        write_button_textarea(capPaste, 'paste.gif', '', 'paste', b);
    }
    else if (a == 'btBold')
    {
        write_button_textarea(capBold, 'bold.gif', 'B', '', b);
    }
    else if (a == 'btItalic')
    {
        write_button_textarea(capItalic, 'italic.gif', 'I', '', b);
    }
    else if (a == 'btUnderline')
    {
        write_button_textarea(capUnderline, 'underline.gif', 'U', '', b);
    }
    else if (a == 'btFlash')
    {
        write_button_textarea(capFlash, 'flash.gif', 'FLASH', 'btFlash', b);
    }
    else if (a == 'btYouTube')
    {
        write_button_textarea(capYouTube, 'youtube.gif', 'FLASH', 'btYouTube', b);
    }
    else if (a == 'btGoogle')
    {
        write_button_textarea(capGoogle, 'google.gif', 'FLASH', 'btGoogle', b);
    }
    else if (a == 'btYahoo')
    {
        write_button_textarea(capYahoo, 'yahoo.gif', 'FLASH', 'btYahoo', b);
    }
    else if (a == 'btYouTube')
    {
        write_button_textarea(capYouTube, 'youtube.gif', 'FLASH', 'btYouTube', b);
    }
    else if (a == 'btAlign_Left')
    {
        write_button_textarea(capAlign_Left, 'aleft.gif', 'LEFT', '', b);
    }
    else if (a == 'btCenter')
    {
        write_button_textarea(capCenter, 'acenter.gif', 'CENTER', '', b);
    }
    else if (a == 'btAlign_Right')
    {
        write_button_textarea(capAlign_Right, 'aright.gif', 'RIGHT', '', b);
    }
    else if (a == 'btJustify')
    {
        write_button_textarea(capJustify, 'ajustify.gif', 'JUSTIFY', '', b);
    }
    else if (a == 'btBullets')
    {
        write_button_textarea(capBullets, 'listbullets.gif', 'LIST,*', 'bbBullets', b);
    }
    else if (a == 'btNumbering')
    {
        write_button_textarea(capNumbering, 'listnumber.gif', 'LIST=1,*', 'bbNumbering', b);
    }
    else if (a == 'btIncrease_Indent')
    {
        write_button_textarea(capIncrease_Indent, 'indentright.gif', 'BLOCKQUOTE', '', b);
    }
    else if (a == 'btTable')
    {
        write_button_textarea(capTable, 'table.gif', '', 'Table', b);
    }
    else if (a == 'btRemove_Format')
    {
        write_button_textarea(capRemove_Format, 'remove.gif', '', 'Removeformat', b);
    }
    else if (a == 'btHyperlink')
    {
        write_button_textarea(capHyperlink, 'createlink.gif', 'URL', 'Hyperlink', b);
    }
    else if (a == 'btHyperlink_Email')
    {
        write_button_textarea(capHyperlink_Email, 'createlink_email.gif', 'EMAIL', 'Hyperlink_Email', b);
    }
    else if (a == 'btRemovelink')
    {
        write_button_textarea(capRemovelink, 'removelink.gif', '', 'Removelink', b);
    }
    else if (a == 'btQuote')
    {
        write_button_textarea(capQuote, 'quote.gif', 'QUOTE', '', b);
    }
    else if (a == 'btCode')
    {
        write_button_textarea(capCode, 'code.gif', 'CODE', '', b);
    }
    else if (a == 'btPHP')
    {
        write_button_textarea(capPHP, 'php.gif', 'PHP', '', b);
    }
    else if (a == 'btHTML')
    {
        write_button_textarea(capHTML, 'html_tag.gif', 'HTML', '', b);
    }
    else if (a == 'btStrikethrough')
    {
        write_button_textarea(capStrikethrough, 'strikethrough.gif', 'STRIKE', '', b);
    }
    else if (a == 'btSubscript')
    {
        write_button_textarea(capSubscript, 'subscript.gif', 'SUB', '', b);
    }
    else if (a == 'btSuperscript')
    {
        write_button_textarea(capSuperscript, 'superscript.gif', 'SUP', '', b);
    }
    else if (a == 'btHorizontal')
    {
        write_button_textarea(capHorizontal, 'line.gif', 'HR', 'HR', b);
    }
    else if (a == 'btInsert_Image')
    {
        write_button_textarea(capInsert_Image, 'insertimage.gif', 'IMG', 'IMG', b);
    }
    else if (a == 'btDeleteAll')
    {
        write_button_textarea(capDelete_All, 'delete_all.gif', '', 'delete_all', b);
    }
    else if (a == 'btIESpell' && isIE)
    {
        write_button_textarea(capIESpell, 'iespell.gif', '', 'iespell', b);
    }
}

function write_button_textarea(a, b, c, d, e)
{
    document.write("<img align=\"absmiddle\" class=\"wysiwygbutton\" title=\"" + a + "\" src=\"" + wysiwyg_path + "/" + b + "\" onmouseover=\"this.className='wysiwygbutton_over';\" onmouseout=\"this.className='wysiwygbutton_out';\" onclick=\"format_text_b(" + "'" + c + "'" + ",'" + d + "','" + e + "')\"><img src=\"" + wysiwyg_path + "/button_space.gif\" border=\"0\" />");
}

function run_iespell()
{
    try
    {
        var a = new ActiveXObject('ieSpell.ieSpellExtension');
        a.CheckAllLinkedDocuments(document)
    }
    catch(exception)
    {
        if (exception.number == -2146827859)
        {
            if (confirm_js(alertNoIESpell)) window.open(IESpellURL)
        }
        else
        {
            alert_js(IESpellError)
        }
    }
}

function format_text_b(a, b, c)
{
    currentwindow = c;
    var d = c.replace('textarea_', '');
    if (isIE)
    {
        var e;
        e = document.getElementById(c);
        var f = e.document.selection
    }
    if (b == 'delete_all')
    {
        document.getElementById(c).value = '';
        document.getElementById(c).focus()
    }
    else if (b == 'iespell')
    {
        run_iespell()
    }
    else if (b == 'IMG')
    {
        imagePath = prompt(enter_image_url, 'http://');
        if ((imagePath != null) && (imagePath != ""))
        {
            write_text('[IMG]' + imagePath + '[/IMG]', d)
        }
    }
    else if (b == 'bbBullets' || b == 'bbNumbering')
    {
        var g = GetSelection(c);
        var h = '';
        if (b == 'bbBullets')
        {
            h = '[LIST]'
        }
        else
        {
            h = '[LIST=1]'
        }
        g = g.replace(/\n/g, "\n[*]");
        if (g != "")
        {
            write_text(h + "\n[*]" + g + "[/LIST]", d);
        }
        else
        {
            write_text(h + "\n" + "[*]\n[*]\n[*]\n[/LIST]", d);
        }
    }
    else if (b == 'Table')
    {
        write_text("[TABLE]\n[TR]\n[TD][/TD]\n[/TR][/TABLE]", d);
    }
    else if (b == "Hyperlink" || b == "Hyperlink_Email")
    {
        if (b == "Hyperlink")
        {
            var i = enter_url_text;
            var j = "http://"
        }
        else
        {
            var i = enter_email_text;
            var j = "email@domain.com"
        }
        var g = GetSelection(c);
        if (g != "")
        {
            var k = prompt(i, g)
        }
        else
        {
            var k = prompt(i, j)
        }
        if (k != null)
        {
            wrap_selection(a, "=" + k, c)
        }
    }
    else if (b == 'Removelink')
    {
        var l = GetSelection(c);
        mylink2 = l.toLowerCase();
        if (mylink2.indexOf("[url") != "-1" && mylink2.indexOf("[/url]") != "-1" || mylink2.indexOf("[email") != "-1" && mylink2.indexOf("[/email]") != "-1")
        {
            l = l.replace(/\[url(.*?)\]/ig, "");
            l = l.replace(/\[\/url\]/ig, "");
            l = l.replace(/\[email(.*?)\]/ig, "");
            l = l.replace(/\[\/email\]/ig, "")
        }
        write_text(l, d)
    }
    else if (b == "Removeformat")
    {
        var l = GetSelection(c);
        l = l.replace(/\[(b|u|i|strike|s|sub|sup)\]/gi, '');
        l = l.replace(/\[\/(b|u|i|strike|s|sub|sup)\]/gi, '');
        l = l.replace(/\[(font|size|color|highlight)(.*?)\]/gi, '');
        l = l.replace(/\[\/(font|size|color|highlight)\]/gi, '');
        write_text(l, d)
    }
    else if (b == 'btFlash')
    {
        var k = prompt(flash_enter_url, "http://");
        if (k == null) return false;
        var m = prompt(flash_width_number_text, flash_width_number_default);
        var n = prompt(flash_height_number_text, flash_height_number_default);
        if (k != null)
        {
            if (m == null) m = flash_width_number_default;
            if (n == null) n = flash_height_number_default;
            var o = "[" + a + "=" + m + "," + n + "]" + k + "[/" + a + "]";
            write_text(o, d)
        }
    }
    else if (b == 'btYouTube' || b == 'btGoogle' || b == 'btYahoo')
    {
        if (b == 'btYouTube')
        {
            var k = prompt(promptYouTube, URLDefaultYouTube);
            k = k.replace(/watch\?v=/gi, "v/")
        }
        else if (b == 'btGoogle')
        {
            var k = prompt(promptGoogle, URLDefaultGoogle);
            k = k.replace(/videoplay/i, "googleplayer.swf");
            k = k.replace(/\&hl=en/i, "")
        }
        else if (b == 'btYahoo')
        {
            var k = prompt(promptYahoo, URLDefaultYahoo);
            k.match(/flashvars='id=(.*?)&emailUrl=(.*?)'/i);
            k = "http://us.i1.yimg.com/cosmos.bcst.yahoo.com/player/media/swf/FLVVideoSolo.swf?id=" + RegExp.$1
        }
        if (k != null)
        {
            var o = "[" + a + "=" + flash_width_number_default + "," + flash_height_number_default + "]" + k + "[/" + a + "]";
            write_text(o, d)
        }
    }
    else if (b == 'cut')
    {
        if (isIE)
        {
            e.document.execCommand("cut", false)
        }
        else
        {
            var p = GetSelection(c);
            if (p != "")
            {
                document.getElementById("hotmem").value = p;
                write_text(" ", d)
            }
        }
    }
    else if (b == 'copy')
    {
        if (isIE)
        {
            e.document.execCommand("copy", false)
        }
        else
        {
            var p = GetSelection(c);
            if (p != "") document.getElementById("hotmem").value = p
        }
    }
    else if (b == 'paste')
    {
        if (isIE)
        {
            document.getElementById(c).focus();
            e.document.execCommand("paste", true)
        }
        else
        {
            document.getElementById("hotmem").value = parse_html_to_bbcode(document.getElementById("hotmem").value);
            write_text(document.getElementById("hotmem").value, d)
        }
    }
    else
    {
        wrap_selection(a, b, c)
    }
}

function format_text_a(a, b, c)
{
    currenteditor = a;
    if (HTML_ON == 'no')
    {
        alert_js('Please uncheck the HTML checkbox');
        return false
    }
    var d;
    if (isIE)
    {
        d = frames[a];
        var f = d.document.selection;
        if (f != null)
        {
            rng = f.createRange()
        }
    }
    else
    {
        d = document.getElementById(a).contentWindow;
        var f = d.getSelection();
        if (f != "" && f.rangeCount > 0)
        {
            rng = f.getRangeAt(f.rangeCount - 1).cloneContents();
            var g = d.document.createElement('div');
            g.appendChild(rng)
        }
    }
    if (b == 'forecolor' || b == 'hilitecolor')
    {
        parent.command = b;
        var h = forecolor_frame_width;
        if (isIE) forecolor_frame_width = forecolor_frame_width - 20;
        if (b == 'forecolor')
        {
            var i = pop_Select_Forecolor
        }
        else
        {
            var i = pop_Select_Hilitecolor
        }
        
        open_insert_pop(a, b, wysiwyg_path + "/select_color.htm", i, forecolor_frame_width, forecolor_frame_height);
        forecolor_frame_width = h
    }
    else if (b == 'safari_InsertImage')
    {
        var j = prompt(enter_image_url, 'http://');
        if ((j != null) && (j != ""))
        {
            d.document.execCommand('InsertText', false, "[IMGHOT src=" + j + " IMGHOT]");
            var k = document.getElementById(a).contentWindow.document.body.innerHTML;
            k = k.replace(/\[IMGHOT(.*?)IMGHOT\]/gi, '<img $1>');
            k = k.replace(/[\n\r]/ig, '');
            document.getElementById(a).contentWindow.document.body.innerHTML = k
        }
    }
    else if (b == 'delete_all')
    {
        if (isGecko || isSafari)
        {
            d.document.body.innerHTML = '<br>';
        }
        else
        {
            d.document.body.innerHTML = '';
        }
        d.focus()
    }
    else if (b == 'iespell')
    {
        run_iespell()
    }
    else if (b == 'flash')
    {
        var l = prompt(flash_enter_url, 'http://');
        var m = prompt(flash_width_number_text, flash_width_number_default);
        var n = prompt(flash_height_number_text, flash_height_number_default);
        if (l != null)
        {
            if (m == null || isNaN(m)) m = flash_width_number_default;
            if (n == null || isNaN(n)) n = flash_height_number_default;
            var o = "[" + b.toUpperCase() + "=" + m + "," + n + "]" + l + "[/" + b.toUpperCase() + "]";
            if (isIE)
            {
                d.document.execCommand("removeformat", false, "");
                rng.pasteHTML(" ");
                rng.pasteHTML(o)
            }
            else if (isSafari)
            {
                d.document.execCommand("InsertText", false, o)
            }
            else
            {
                d.focus();
                d.document.execCommand("InsertHTML", false, o)
            }
        }
    }
    else if (b == 'addtable')
    {
        var p = prompt("Number of Rows", "3");
        var q = prompt("Number of Columns", "2");
        if (p != null && q != null && !isNaN(p) && !isNaN(q))
        {
            var r = "<table>";
            var t = "";
            for (irow = 0; irow < p; irow++)
            {
                t += "<tr>";
                for (icol = 0; icol < q; icol++)
                {
                    t += "<td>&nbsp;</td>"
                }
                t += "</tr>"
            }
            r += t + "</table><br>";
            write_html(r, a)
        }
    }
    else if (b == 'quote' || b == 'code' || b == 'php' || b == 'html')
    {
        b = b.toUpperCase();
        var u = "";
        if (isIE)
        {
            u = rng.htmlText
        }
        else
        {
            if (f != "")
            {
                u = g.innerHTML
            }
            else
            {
                u = ""
            }
        }
        if (b == 'CODE' || b == 'PHP' || b == 'HTML')
        {
            u = u.replace(/[\n\r]/ig, '');
            u = u.replace(/<(br|p|div|li).*?>/ig, "[BR/]");
            u = u.replace(/<\/(p|div).*?>/ig, "");
            u = u.replace(/(<([^>]+)>)/ig, "");
            u = u.replace(/\[BR\/\]/ig, "<br>")
        }
        u = '[' + b + ']' + u + '[/' + b + ']';
        write_html(u, a)
    }
    else if (b == 'fontname')
    {
        parent.command = b;
        open_insert_pop(a, b, wysiwyg_path + "/select_fontface.htm", pop_Select_Font, fontname_frame_width, fontname_frame_height)
    }
    else if (b == 'fontsize')
    {
        parent.command = b;
        open_insert_pop(a, b, wysiwyg_path + "/select_fontsize.htm", pop_Select_FontSize, fontsize_frame_width, fontsize_frame_height)
    }
    else if (b == 'createlink' || b == 'createlink_email')
    {
        if (b == "createlink")
        {
            var l = prompt(enter_url_text, "http://")
        }
        else
        {
            var l = prompt(enter_email_text, "email@domain.com")
        }
        if (l != null)
        {
            var w = l.split(" ");
            l = w[0]
        }
        if (isSafari)
        {
            var x = safari_selection(d);
            if (x == "") x = l;
            var y = prompt(safari_enter_text_link, x)
        }
        if (b == "createlink_email") l = "mailto:" + l;
        if (isSafari)
        {
            if (l != null && y != null && l != "" && y != "")
            {
                d.document.execCommand("InsertText", false, "[AHOT href=" + l + "]" + y + "[/AHOT]");
                var k = document.getElementById(a).contentWindow.document.body.innerHTML;
                k = k.replace(/\[AHOT(.*?)\]/gi, '<a$1>');
                k = k.replace(/\[\/AHOT\]/gi, '</a>');
                k = k.replace(/[\n\r]/ig, '');
                document.getElementById(a).contentWindow.document.body.innerHTML = k
            }
        }
        else
        {
            try
            {
                d.document.execCommand("Unlink", false, null);
                d.document.execCommand("CreateLink", false, l)
            }
            catch(e) {}
        }
    }
    else if (b == 'paste')
    {
        if (isSafari)
        {
            alert_js(safari_paste_command)
        }
        else if (isIE)
        {
            d.focus();
            d.document.execCommand(b, true)
        }
        else
        {
            document.getElementById("hotmem").value = document.getElementById("hotmem").value.replace(/\n/g, "<br>");
            write_html(document.getElementById("hotmem").value, a)
        }
    }
    else
    {
        if (isSafari)
        {
            if (b == "inserthorizontalrule")
            {
                d.document.execCommand("InsertText", false, "[HR]");
                var k = document.getElementById(a).contentWindow.document.body.innerHTML;
                k = k.replace(/\[HR\]/g, '<hr>');
                k = k.replace(/[\n\r]/ig, '');
                document.getElementById(a).contentWindow.document.body.innerHTML = k + "<br>"
            }
            else if (b == "Strikethrough")
            {
                var x = safari_selection(d);
                d.document.execCommand("InsertText", false, "[STRIKEHOT]" + x + "[/STRIKEHOT]");
                var k = document.getElementById(a).contentWindow.document.body.innerHTML;
                k = k.replace(/\[STRIKEHOT\]/gi, '<strike>');
                k = k.replace(/\[\/STRIKEHOT\]/gi, '</strike>');
                k = k.replace(/[\n\r]/ig, '');
                document.getElementById(a).contentWindow.document.body.innerHTML = k
            }
            else if (b == "insertunorderedlist" || b == "insertorderedlist")
            {
                var z = "";
                for (var s = 0; s < 50; s++)
                {
                    var A = prompt(safari_bullets_numbering_prompt, "");
                    if (A != null && A != "")
                    {
                        z += "[LIHOT]" + A + "[/LIHOT]"
                    }
                    else
                    {
                        break
                    }
                }
                if (z != "")
                {
                    if (b == "insertunorderedlist")
                    {
                        var B = "[ULHOT]" + z
                    }
                    else
                    {
                        var B = "[OLHOT]" + z
                    }
                    if (b == "insertunorderedlist")
                    {
                        B += "[/ULHOT]"
                    }
                    else
                    {
                        B += "[/OLHOT]"
                    }
                    d.document.execCommand("InsertText", false, B);
                    var k = document.getElementById(a).contentWindow.document.body.innerHTML;
                    k = k.replace(/\[ULHOT\]/g, '<UL>');
                    k = k.replace(/\[\/ULHOT\]/g, '</UL>');
                    k = k.replace(/\[OLHOT\]/g, '<OL>');
                    k = k.replace(/\[\/OLHOT\]/g, '</OL>');
                    k = k.replace(/\[LIHOT\]/g, '<LI>');
                    k = k.replace(/\[\/LIHOT\]/g, '</LI>');
                    k = k.replace(/[\n\r]/ig, '');
                    document.getElementById(a).contentWindow.document.body.innerHTML = "<br>" + k;
                    d.focus()
                }
            }
            else
            {
                d.document.execCommand(b, false, c);
                event.preventDefault();
                event.returnValue = false
            }
        }
        else
        {
            if (!isIE && b == "cut" || !isIE && b == "copy")
            {
                f = g.innerHTML;
                if (f != "")
                {
                    document.getElementById("hotmem").value = f;
                    if (b == "cut") write_html(" ", a)
                }
            }
            else
            {
                d.document.execCommand(b, false, c)
            }
        }
    }
}

function write_html_custom(a, b, c)
{
    a = a.replace(/\n/g, "<br>");
    b = b.replace(/\n/g, "<br>");
    if (HTML_ON == 'no')
    {
        alert_js('Please uncheck the HTML checkbox');
        return false
    }
    var d;
    if (isIE)
    {
        d = frames[c];
        d.focus();
        var e = d.document.selection;
        if (e != null)
        {
            rng = e.createRange()
        }
        link = rng.htmlText;
        link = a + link + b;
        d.document.execCommand('removeformat', false, '');
        rng.pasteHTML('');
        rng.pasteHTML(link);
        d.focus()
    }
    else
    {
        d = document.getElementById(c).contentWindow;
        var e = d.getSelection();
        if (e != "" && e.rangeCount > 0)
        {
            rng = e.getRangeAt(e.rangeCount - 1).cloneContents();
            var f = d.document.createElement('div');
            f.appendChild(rng);
            e = f.innerHTML
        }
        text = a + e + b;
        d = document.getElementById(c).contentWindow;
        d.focus();
        d.document.execCommand('insertHTML', false, " ");
        d.document.execCommand("removeformat", false, "");
        d.document.execCommand('insertHTML', false, text);
        d.document.execCommand("removeformat", false, "")
    }
}

function write_html(a, b)
{
    if (HTML_ON == 'no')
    {
        alert_js('Please uncheck the HTML checkbox');
        return false
    }
    var c;
    if (isIE)
    {
        c = frames[b];
        c.focus();
        var d = c.document.selection;
        if (d != null)
        {
            rng = d.createRange()
        }
        c.document.execCommand('removeformat', false, '');
        rng.pasteHTML("");
        rng.pasteHTML(a);
        c.focus()
    }
    else if (isSafari)
    {
        c = document.getElementById(b).contentWindow;
        c.focus();
        a = a.replace(/</g, "[HOTAGOPEN]");
        a = a.replace(/>/g, "[HOTAGCLOSE]");
        c.document.execCommand('insertTEXT', false, a);
        var e = document.getElementById(b).contentWindow.document.body.innerHTML;
        e = e.replace(/\[HOTAGOPEN\]/g, '<');
        e = e.replace(/\[HOTAGCLOSE\]/g, '>');
        e = e.replace(/[\n\r]/ig, '');
        document.getElementById(b).contentWindow.document.body.innerHTML = e;
        c.focus()
    }
    else
    {
        c = document.getElementById(b).contentWindow;
        c.focus();
        c.document.execCommand('insertHTML', false, " ");
        c.document.execCommand('removeformat', false, "");
        c.document.execCommand('insertHTML', false, a);
        c.document.execCommand("removeformat", false, "");
        c.focus()
    }
}

function write_text_custom(a, b, c)
{
    if (HTML_ON == 'no')
    {
        alert_js('Please uncheck the HTML checkbox');
        return false
    }
    if (isIE)
    {
        strSelection = document.selection.createRange().text;
        document.getElementById('textarea_' + c).focus();
        document.selection.createRange().text = a + strSelection + b
    }
    else
    {
        document.getElementById('textarea_' + c).focus();
        var d = document.getElementById('textarea_' + c);
        var e = d.textLength;
        var f = d.selectionStart;
        var g = d.selectionEnd;
        if (g == 1 || g == 2) g = e;
        var h = (d.value).substring(0, f);
        var i = (d.value).substring(f, g);
        var j = (d.value).substring(g, e);
        d.value = h + a + i + b + j;
        document.getElementById('textarea_' + c).focus()
    }
}

function write_text(a, b)
{
    if (HTML_ON == 'no')
    {
        alert_js('Please uncheck the HTML checkbox');
        return false
    }
    if (isIE)
    {
        document.getElementById('textarea_' + b).focus();
        document.selection.createRange().text = a
    }
    else
    {
        document.getElementById('textarea_' + b).focus();
        var c = document.getElementById('textarea_' + b);
        var d = c.textLength;
        var e = c.selectionStart;
        var f = c.selectionEnd;
        if (f == 1 || f == 2) f = d;
        var g = (c.value).substring(0, e);
        var h = (c.value).substring(e, f);
        var i = (c.value).substring(f, d);
        c.value = g + a + i;
        document.getElementById('textarea_' + b).focus()
    }
}

function safari_insert_image(a)
{
    if (isSafari)
    {
        var b = currenteditor;
        var c = document.getElementById(b).contentWindow;
        c.focus();
        var d = document.getElementById(b).contentWindow.document.body.innerHTML;
        if (a != "")
        {
            d = d.replace(/<img>/gi, "<img src=" + a + ">")
        }
        else
        {
            d = d.replace(/<img>/gi, "")
        }
        d = d.replace(/[\n\r]/ig, '');
        document.getElementById(b).contentWindow.document.body.innerHTML = d
    }
}

function InsertSymbol(a)
{
    if (HTML_ON == 'no')
    {
        alert_js('Please uncheck the HTML checkbox');
        return false
    }
    var b = currenteditor;
    var c;
    if (a == 'BF')
    {
        a = "\\"
    }
    if (a == '<')
    {
        a = "&lt;"
    }
    if (a == '>')
    {
        a = "&gt;"
    }
    if (a == '&')
    {
        a = "&amp;"
    }
    if (isIE)
    {
        c = frames[b];
        c.focus();
        rng.collapse(false);
        rng.pasteHTML(a);
        rng.select();
        var d = c.document.selection;
        if (d != null) rng = d.createRange()
    }
    else
    {
        c = document.getElementById(b).contentWindow;
        c.focus();
        c.document.execCommand('insertHTML', false, a)
    }
}

function SetFontFormat(a, b)
{
    var c = currenteditor;
    var d;
    if (isIE)
    {
        d = frames[c];
    }
    else
    {
        d = document.getElementById(c).contentWindow;
    }
    var e = parent.command;
    if (isIE && e == 'hilitecolor' || isSafari && e == 'hilitecolor')
    {
        e = 'backcolor';
    }
    if (isIE)
    {
        var f = d.document.selection;
        if (f != null)
        {
            var g = f.createRange();
            g = rng;
            g.select();
        }
    }
    else
    {
        d.focus();
    }
    d.document.execCommand(e, false, a);
    d.focus();
}

function hide_it(a)
{
    var b;
    if (isIE)
    {
        b = frames[a];
        if (chkVK == 1)
        {
            var c = b.document.selection;
            if (c != null)
            {
                rng = c.createRange();
                rng = c.getRangeAt(c.rangeCount - 1).cloneRange();
            }
        }
    }
    else
    {
        b = document.getElementById(a).contentWindow;
    }
    b.focus();
    if (isSafari)
    {
        hot_showid2('Hoteditor_Font_Name', 'none');
        hot_showid2('Hoteditor_Font_Size', 'none');
        hot_showid2('Hoteditor_Select_Color', 'none');
    }
}

function hide_it2()
{
    var a;
    if (isIE)
    {
        a = frames[currenteditor];
        if (chkVK == 1)
        {
            var b = a.document.selection;
            if (b != null)
            {
                rng = b.createRange();
                rng = b.getRangeAt(b.rangeCount - 1).cloneRange();
            }
        }
    }
    else
    {
        a = document.getElementById(currenteditor).contentWindow;
    }
    a.focus()
}

function add_image(a)
{
    if (HTML_ON == 'no')
    {
        alert_js('Please uncheck the HTML checkbox');
        return false;
    }
    imagePath = prompt(enter_image_url, 'http://');
    if ((imagePath != null) && (imagePath != ""))
    {
        write_html('<img src="' + imagePath + '" alt="" id="" />', a);
    }
}

function get_offset_top(a)
{
    var b = a.offsetTop;
    var c = a.offsetParent;
    while (c)
    {
        b += c.offsetTop;
        c = c.offsetParent
    }
    return b;
}

function get_offset_left(a)
{
    var b = a.offsetLeft;
    var c = a.offsetParent;    
    while (c)
    {
        b += c.offsetLeft;
        c = c.offsetParent
    }
    return b;
}

function kb_handler(a)
{
    var b = a.target.id;
    if (a.ctrlKey)
    {
        var c = String.fromCharCode(a.charCode).toLowerCase();
        var d = '';
        switch (c)
        {
            case 'b':
                d = 'bold';
            break;
            case 'i':
                d = 'italic';
            break;
            case 'u':
                d = 'underline';
            break;
        };
        if (d)
        {
            format_text_a(b, d, true);
            a.preventDefault();
            a.stopPropagation();
        }
    }
}

function trim(a)
{
    if (typeof a != 'string')
    {
        return a;
    }
    var b = a;
    var c = b.substring(0, 1);
    while (c == " ")
    {
        b = b.substring(1, b.length);
        c = b.substring(0, 1);
    }
    c = b.substring(b.length - 1, b.length);
    while (c == " ")
    {
        b = b.substring(0, b.length - 1);
        c = b.substring(b.length - 1, b.length);
    }
    while (b.indexOf("  ") != -1)
    {
        b = b.substring(0, b.indexOf("  ")) + b.substring(b.indexOf("  ") + 1, b.length);
    }
    return b
}

function drag_dropns(a)
{
    if (!ns4) return;
    temp = eval(a);
    temp.captureEvents(Event.MOUSEDOWN | Event.MOUSEUP);
    temp.onmousedown = gons;
    temp.onmousemove = dragns;
    temp.onmouseup = stopns;
}

function gons(e)
{
    temp.captureEvents(Event.MOUSEMOVE);
    nsx = e.x;
    nsy = e.y;
}

function dragns(e)
{
    if (steditor == 1)
    {
        temp.moveBy(e.x - nsx, e.y - nsy);
        return false;
    }
}

function stopns()
{
    temp.releaseEvents(Event.MOUSEMOVE);
}

function drag_drop(e)
{
    if (ie4 && dragapproved)
    {
        crossobj.style.left = tempx + event.clientX - offsetx + 'px';
        crossobj.style.top = tempy + event.clientY - offsety + 'px';
        return false;
    }
    else if (ns6 && dragapproved)
    {
        crossobj.style.left = tempx + e.clientX - offsetx + 'px';
        crossobj.style.top = tempy + e.clientY - offsety + 'px';
        return false;
    }
}

function init_drag(e)
{
    crossobj = ns6 ? document.getElementById('insert_pop') : document.all.insert_pop;
    var a = ns6 ? e.target: event.srcElement;
    var b = ns6 ? 'HTML': 'BODY';
    while (a.tagName != b && a.id != 'insert_title')
    {
        a = ns6 ? a.parentNode: a.parentElement;
    }
    if (a.id == 'insert_title')
    {
        offsetx = ie4 ? event.clientX: e.clientX;
        offsety = ie4 ? event.clientY: e.clientY;
        tempx = parseInt(crossobj.style.left);
        tempy = parseInt(crossobj.style.top);
        dragapproved = true;
        document.onmousemove = drag_drop;
    }
}

document.onmousedown = init_drag;
document.onmouseup = new Function('dragapproved=false');
function close_insert_pop()
{
    chkVK = 0;
    document.getElementById('insert_pop').style.display = 'none';
}

function open_insert_pop(a, b, c, d, e, f)
{
    buttonElement = document.getElementById(b + '_' + a);
    frames['insert_elm'].location.href = c;
    var X = get_offset_left(buttonElement);
    var Y = get_offset_top(buttonElement) + buttonElement.offsetHeight;
    var g = window.innerWidth ? window.innerWidth : document.body.clientWidth;
    if (X + e > g)
    {
        X = g - e - 30;
    }
    else if (X < 0)
    {
        X = 0;
    }
    document.getElementById('insert_pop').style.left = X + 'px';
    document.getElementById('insert_pop').style.top = Y + 'px';
    document.getElementById('insert_pop').style.display = 'block';
    document.getElementById('insert_pop').style.width = e + 'px';
    if (isIE)
    {
        document.getElementById('insert_elm').style.height = f + 8 + 'px';
    }
    else
    {
        document.getElementById('insert_elm').style.height = f + 'px';
    }
    document.getElementById('change_title').innerHTML = d;
}

function NoError()
{
    return (true);
}
onerror = NoError;
function moz_wrap(a, b, c)
{
    var d = document.getElementById(c);
    var e = d.textLength;
    var f = d.selectionStart;
    var g = d.selectionEnd;
    if (g == 1 || g == 2) g = e;
    var h = (d.value).substring(0, f);
    var i = (d.value).substring(f, g);
    var j = (d.value).substring(g, e);
    if (b == 'HR')
    {
        d.value = h + "[" + a + "]" + j;
    }
    else
    {
        var k = "";
        if (a == "URL" || a == "url" || a == "EMAIL" || a == "email")
        {
            if (i == "") k = b.replace("=", "");
        }
        d.value = h + "[" + a + b + "]" + i + k + "[/" + a + "]" + j;
    }
}

function ie_wrap(a, b, c)
{
    strSelection = document.selection.createRange().text;
    document.getElementById(c).focus();
    if (b == "HR")
    {
        document.selection.createRange().text = "[" + a + "]";
    }
    else
    {
        if (strSelection != "")
        {
            document.selection.createRange().text = "[" + a + b + "]" + strSelection + "[/" + a.replace(/=(.*?)$/g, "") + "]";
        }
        else
        {
            if (a == "URL" || a == "url" || a == "EMAIL" || a == "email")
            {
                var d = b.replace("=", "");
                document.selection.createRange().text = "[" + a + b + "]" + d + "[/" + a.replace(/=(.*?)$/g, "") + "]";
            }
            else
            {
                document.selection.createRange().text = "[" + a + b + "]" + "[/" + a.replace(/=(.*?)$/g, "") + "]";
            }
        }
    }
}

function wrap_selection(a, b, c)
{
    if (isIE)
    {
        ie_wrap(a, b, c);
    }
    else
    {
        moz_wrap(a, b, c);
    }
}

function GetSelection(a)
{
    if (isIE)
    {
        return document.selection.createRange().text;
    }
    else
    {
        var b = document.getElementById(a);
        var c = b.textLength;
        var d = b.selectionStart;
        var e = b.selectionEnd;
        if (e == 1 || e == 2) e = c;
        return (b.value).substring(d, e);
    }
}

function hot_showid2(a, b)
{
    document.getElementById(a).style.display = b;
}

function hot_showid(a)
{
    var b = document.getElementById(a);
    if (b.style.display == 'block')
    {
        b.style.display = 'none';
    }
    else
    {
        b.style.display = 'block';
    }
}

function create_cookie(a, b, c)
{
    if (c)
    {
        var d = new Date();
        d.setTime(d.getTime() + (c * 24 * 60 * 60 * 1000));
        var e = "; expires=" + d.toGMTString();
    }
    else
    {
        var e = "";
    }
    document.cookie = a + "=" + b + e + "; path=/";
}

function read_cookie(a)
{
    var b = a + "=";
    var d = document.cookie.split(';');
    for (var i = 0; i < d.length; i++)
    {
        var c = d[i];
        while (c.charAt(0) == ' ')
        {
            c = c.substring(1, c.length);
        }
        if (c.indexOf(b) == 0)
        {
            return c.substring(b.length, c.length);
        }
    }
    return null;
}

if (isSafari)
{
    var getSafariSize = "";
    var Color_Title = "blue";
    var array = new Array();
    array[0] = "1";
    array[1] = "2";
    array[2] = "3";
    array[3] = "4";
    array[4] = "5";
    array[5] = "6";
    array[6] = "7";
    document.writeln("<div onclick=\"document.getElementById('Hoteditor_Font_Size').style.display='none';\" class=\"wysiwyg_popuplayer\" id=\"Hoteditor_Font_Size\" style=\"cursor:hand;cursor:pointer;display:none;position:absolute; top:0; left:0;height:" + fontsize_frame_height + ";width:" + fontsize_frame_width + "\"><table class=\"wysiwyg_popuplayer\" width=\"" + fontsize_frame_width + "\"><tr class=\"wysiwyg_popuplayer_title\"><td nowrap=\"nowrap\">Font Size</td><td><img title=\"Close\" style=\"CURSOR:hand; CURSOR:Pointer\" onmouseover=\"this.src='" + wysiwyg_path + "/close_popup_over.gif';\" onmouseout=\"this.src='" + wysiwyg_path + "/close_popup.gif';\" src=\"" + wysiwyg_path + "/close_popup.gif\" align=\"absmiddle\"></td></tr></table><div style=\"width:" + fontsize_frame_width + ";height:" + fontsize_frame_height + "\"><table class=\"wysiwyg_select\" cellpadding=\"0\" cellspacing=\"0\" width=\"" + fontsize_frame_width + "\">\n");
    for (i = 0; i < array.length; i++)
    {
        if (array[i] == "1")
        {
            getSafariSize = "8pt";
        }
        else if (array[i] == "2")
        {
            getSafariSize = "10pt";
        }
        else if (array[i] == "3")
        {
            getSafariSize = "12pt";
        }
        else if (array[i] == "4")
        {
            getSafariSize = "14pt";
        }
        else if (array[i] == "5")
        {
            getSafariSize = "18pt";
        }
        else if (array[i] == "6")
        {
            getSafariSize = "24pt";
        }
        else if (array[i] == "7")
        {
            getSafariSize = "36pt";
        }
        document.writeln("<tr><td height=\"30\" valign=\"middle\" align=\"center\"><div style=\"cursor:hand;cursor:pointer;width:100%\" onmousedown=\"document.getElementById('Hoteditor_Font_Size').style.display='none';set_font_format('" + getSafariSize + "');\" onMouseover=\"this.className='wysiwyg_select_over'\" onMouseout=\"this.className='wysiwyg_select'\"><b><font face=\"Arial\" size=" + array[i] + "\">" + array[i] + "</font></b></div></td></tr>\n\n");
    }
    document.writeln("<tr><td><br></td></tr>\n</table></div></div>\n<div onclick=\"document.getElementById('Hoteditor_Font_Name').style.display='none';\" class=\"wysiwyg_popuplayer\" id=\"Hoteditor_Font_Name\" style=\"cursor:hand;cursor:pointer;display:none;position:absolute; top:0; left:0;height:" + fontname_frame_height + ";width:" + fontname_frame_width + "\"><table class=\"wysiwyg_popuplayer\"><tr class=\"wysiwyg_popuplayer_title\"><td nowrap=\"nowrap\" width=\"" + fontname_frame_width + "\"><span style=\"float:left\">Select Font Face</span><img title=\"Close\" style=\"float:right;CURSOR:hand; CURSOR:Pointer\" onmouseover=\"this.src='" + wysiwyg_path + "/close_popup_over.gif';\" onmouseout=\"this.src='" + wysiwyg_path + "/close_popup.gif';\" src=\"" + wysiwyg_path + "/close_popup.gif\" align=\"absmiddle\"></td></table><div style=\"overflow:auto;width:" + fontname_frame_width + ";height:" + fontname_frame_height + "\"><table class=\"wysiwyg_select\" cellpadding=\"0\" cellspacing=\"0\" width=\"" + fontname_frame_width + "\" height=\"" + fontname_frame_height + "\">\n");
    for (i = 0; i < array_fontname.length; i++)
    {
        document.writeln("<tr><td><div onmousedown=\"document.getElementById('Hoteditor_Font_Name').style.display='none';set_font_format('" + array_fontname[i] + "');\" class=\"wysiwyg_select\" onMouseover=\"this.className='wysiwyg_select_over'\" onMouseout=\"this.className='wysiwyg_select'\"><font size=\"2\" face=\"" + array_fontname[i] + "\">" + array_fontname[i] + "</font></div></td></tr>\n");
    }
    document.writeln("</table></div></div>\n<div onclick=\"document.getElementById('Hoteditor_Select_Color').style.display='none';\" class=\"wysiwyg_popuplayer\" id=\"Hoteditor_Select_Color\" style=\"cursor:hand;cursor:pointer;display:none;position:absolute; top:0; left:0;\"><table class=\"wysiwyg_popuplayer\"><tr class=\"wysiwyg_popuplayer_title\"><td width=\"78px\" nowrap=\"nowrap\"><span style=\"float:left\">Color</span> <img style=\"float:right\" title=\"Close\" style=\"CURSOR:hand; CURSOR:Pointer\" onmouseover=\"this.src='" + wysiwyg_path + "/close_popup_over.gif';\" onmouseout=\"this.src='" + wysiwyg_path + "/close_popup.gif';\" src=\"" + wysiwyg_path + "/close_popup.gif\" align=\"absmiddle\"></td></tr></table><div style=\"overflow:auto;height:" + fontsize_frame_height + "px\"><table class=\"wysiwyg_select\" cellpadding=\"0\" cellspacing=\"0\">\n");
    for (i = 0; i < array_fontcolor.length; i++)
    {
        document.writeln("<tr><td><div style=\"cursor:hand;cursor:pointer;width:100%;height:20px;color:" + array_fontcolor[i] + ";background-color:" + array_fontcolor[i] + "\" onmousedown=\"document.getElementById('Hoteditor_Select_Color').style.display='none';set_font_format('" + array_fontcolor[i] + "');\" onMouseover=\"this.style.border='1px solid #F29536';\" onMouseout=\"this.style.border='0px solid #C0C0C0';\">" + array_fontcolor[i] + "</div></td></tr>\n\n");
    }
    document.writeln("</table></div></div>\n");
}

function html_entity_decode(a)
{
    a = a.replace(/&lt;/g, '<');
    a = a.replace(/&gt;/g, '>');
    a = a.replace(/&nbsp;/g, ' ');
    a = a.replace(/&amp;/g, '&');
    return a;
}

function htmlentities(a)
{
    a = a.replace(/</g, '&lt;');
    a = a.replace(/>/g, '&gt;');
    a = a.replace(/&/g, '&amp;');
    return a;
}

function parse_bbcode_to_html(a)
{
    a = a.replace(/&quot;/g, '"');
    a = a.replace(/&/g, '&amp;');
    a = a.replace(/</g, '&lt;');
    a = a.replace(/>/g, '&gt;');
    a = a.replace(/  /g, '&nbsp;&nbsp;');
    a = a.replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    a = a.replace(/\n+(\[\/list\])/gi, '[/LIST]');
    a = a.replace(/\[list\]\n+/gi, '[LIST]');
    a = a.replace(/\[list=1\]\n+/gi, '[LIST=1]');
    a = a.replace(/\[list=a\]\n+/gi, '[LIST=a]');
    a = a.replace(/\n+\[\/tr\]/gi, '[/TR]');
    a = a.replace(/\n+\[tr\]/gi, '[TR]');
    a = a.replace(/\n+\[td\]/gi, '[TD]');
    a = a.replace(/\n+\[\/table\]/gi, '[/TABLE]');
    a = a.replace(/\[\/table\]$/gi, "[/TABLE]\n");
    a = a.replace(/\n/g, '<br>');
    a = a.replace(/\[hr\]/gi, '<hr>');
    a = a.replace(/\[table\]/gi, '<table>');
    a = a.replace(/\[\/table\]/gi, '</table>');
    a = a.replace(/\[(\/|)tr\]/gi, '<$1tr>');
    a = a.replace(/\[(\/|)td\]/gi, '<$1td>');
    a = a.replace(/\[(\/|)indent\]/gi, '<$1blockquote>');
    a = a.replace(/\[(sub|sup|strike|s|blockquote|b|i|u)\]/gi, '<$1>');
    a = a.replace(/\[\/(sub|sup|strike|s|blockquote|b|i|u)\]/gi, '</$1>');
    a = a.replace(/\[font=(.*?)\]/gi, '<font face="$1">');
    a = a.replace(/\[color=(.*?)\]/gi, '<font color="$1">');
    a = a.replace(/\[size=(.*?)\]/gi, '<font size="$1">');
    a = a.replace(/\[\/(font|color|size)\]/gi, '</font>');
    a = a.replace(/\[highlight=(.*?)\]/gi, '<span style="background-color:$1">');
    a = a.replace(/\[\/highlight\]/gi, '</span>');
    a = a.replace(/\[(center|left|right|justify)\]/gi, '<div align="$1">');
    a = a.replace(/\[\/(center|left|right|justify)\]/gi, '</div>');
    a = a.replace(/\[align=(center|left|right|justify)\]/gi, '<div align="$1">');
    a = a.replace(/\[\/align\]/gi, '</div>');
    a = a.replace(/\[email=(.*?)\]/gi, '<a href="mailto:$1">');
    a = a.replace(/\[email\](.*?)\[\/email\]/gi, '<a href="mailto:$1">$1[/email]');
    a = a.replace(/\[\/email\]/gi, '</a>');
    a = a.replace(/\[url=(.*?)\]/gi, '<a href="$1">');
    a = a.replace(/\[url\](.*?)\[\/url\]/gi, '<a href="$1">$1[/url]');
    a = a.replace(/\[\/url\]/gi, '</a>');
    a = a.replace(/\[img(.*)\](.*?)\[\/img\]/gi, '<img $1 src="$2">');
    
    var b = a.match(/\[(list|list=1|list=a)\]/gi);
    
    a = a.replace(/\[list=1\]/gi, '<ol>');
    a = a.replace(/\[list=a\]/gi, '<ol style="list-style-type: lower-alpha">');
    a = a.replace(/\[list\]/gi, '<ul>');
    a = a.replace(/\[\*\]/gi, '<li>');
    a = a.replace(/<br[^>]*><li>/gi, '<li>');
    a = a.replace(/<br[^>]*> <li>/gi, '<li>');
    a = a.replace(/<br[^>]*><\/li>/gi, '</li>');
    
    if (b)
    {
        for (var i = 0; i < b.length; i++)
        {
            if (b[i].toLowerCase() == "[list]")
            {
                a = a.replace(/\[\/list\]/i, '</ul>');
            }
            else if (b[i].toLowerCase() == "[list=1]" || b[i].toLowerCase() == "[list=a]")
            {
                a = a.replace(/\[\/list\]/i, '</ol>');
            }
        }
    }
    
    if (isOpera9 || isIE)
    {
        a = a.replace(/<li>/gi, '</li><li>');
        a = a.replace(/<\/(ol|ul)>/gi, '</li></$1>');
    }
    
    if (isOpera9)
    {
        a = a.replace(/<\/table>/gi, '</tr></table>');
        a = a.replace(/<\/tr>/gi, '</td></tr>');
    }
    
    return a;
}

function analyze_html_block(a, b)
{
    var c = '';
    var d = b['style'].split(';');
    for (var j = 0; j < d.length; j++)
    {
        if (d[j] != '' && d[j] != null && d[j] != 'undefined')
        {
            var e = d[j].split(':');
            var f = e[0].toLowerCase().replace(/ /g, '');
            f = f.replace(/style=/gi, '');
            if (e[1] != '' && e[1] != null && e[1] != 'undefined')
            {
                var g = e[1].replace(/^ +| +$/g, '');
            }
            else
            {
                var g = '';
            }
            
            if (f == 'background-color')
            {
                if (g.indexOf('#') == -1)
                {
                    var h = parse_rgb_to_html(g);
                }
                else
                {
                    var h = g;
                }
                c += '[HIGHLIGHT=' + h + ']';
            }
            else if (f == 'vertical-align' && g == 'sub')
            {
                c += '[SUB]';
            }
            else if (f == 'vertical-align' && g == 'super')
            {
                c += '[SUP]';
            }
            else if (f == 'list-style-type' && g == 'lower-alpha')
            {
                c += '[LIST=a]';
            }
            else if (f == 'text-align')
            {
                g = g.toUpperCase();
                c += '[' + g + ']';
            }
            else if (f == 'margin-left' || f == 'margin-right')
            {
                g = parseInt(g) / 40;
                for (var z = 0; z < g; z++)
                {
                    c += '[BLOCKQUOTE]';
                }
            }
            else if (f == 'font-weight')
            {
                if (g.toUpperCase() == 'BOLD' || g.toUpperCase() == '700')
                {
                    c += '[B]';
                }
            }
            else if (f == 'font-style')
            {
                if (g.toUpperCase() == 'ITALIC')
                {
                    c += '[I]';
                }
            }
            else if (f == 'font-family')
            {
                c += '[FONT=' + g + ']';
            }
            else if (f == 'font-size')
            {
                if (g == '8pt' || g == '9pt' || g == 'x-small')
                {
                    c += '[SIZE=1]';
                }
                else if (g == '10pt' || g == '11pt' || g == 'small')
                {
                    c += '[SIZE=2]';
                }
                else if (g == '12pt' || g == '13pt' || g == 'medium')
                {
                    c += '[SIZE=3]';
                }
                else if (parseInt(g) >= 14 && parseInt(g) < 18 || g == 'large')
                {
                    c += '[SIZE=4]';
                }
                else if (parseInt(g) >= 18 && parseInt(g) < 24 || g == 'x-large')
                {
                    c += '[SIZE=5]';
                }
                else if (parseInt(g) >= 24 && parseInt(g) < 36 || g == 'xx-large')
                {
                    c += '[SIZE=6]';
                }
                else if (parseInt(g) >= 36 || g == '-webkit-xxx-large')
                {
                    c += '[SIZE=7]';
                }
            }
            else if (f == 'text-decoration')
            {
                if (g.toUpperCase() == 'UNDERLINE')
                {
                    c += '[U]';
                }
                else if (g.toUpperCase() == 'LINE-THROUGH')
                {
                    c += '[STRIKE]';
                }
            }
            else if (f == 'color')
            {
                if (g.indexOf('#') == -1)
                {
                    var h = parse_rgb_to_html(g);
                }
                else
                {
                    var h = g;
                }
                c += '[COLOR=' + h + ']';
            }
        }
    }
    return c;
}

function parse_html_to_bbcode(a)
{
    if (starup == '0')
    {
        if (isIE)
        {
            a = a.replace(/<\/li>/gi, "");
            a = a.replace(/<li>/gi, "[*]");
        }
        a = a.replace(/<(abbr|acronym|applet|area|base|baseFont|bdo|bgSound|big|body|button|caption|center|cite|code|col|colGroup|comment|custom|dd|del|dfn|dir|dl|dt|embed|fieldSet|frame|frameSet|head|html|ins|isIndex|kbd|label|legend|link|listing|map|marquee|menu|meta|noBR|noFrames|noScript|optGroup|option|param|plainText|pre|q|rt|ruby|samp|small|tBody|tFoot|tHead|tile|tt|wbr|xml|xmp|th|script|form|input|iframe|object|select|textarea)(.*?)>/gi, '');
        a = a.replace(/<\/(abbr|acronym|applet|area|base|baseFont|bdo|bgSound|big|body|button|caption|center|cite|code|col|colGroup|comment|custom|dd|del|dfn|dir|dl|dt|embed|fieldSet|frame|frameSet|head|html|ins|isIndex|kbd|label|legend|link|listing|map|marquee|menu|meta|noBR|noFrames|noScript|optGroup|option|param|plainText|pre|q|rt|ruby|samp|small|tBody|tFoot|tHead|title|tt|wbr|xml|xmp|th|script|form|iframe|object|select|textarea)(.*?)>/gi, '');
        a = a.replace(/\xA0/gi, ' ');
        a = a.replace(/<br[^>]*><\/div>/gi, '</div>');
        a = a.replace(/<br[^>]*>/gi, '\n');
        a = a.replace(/<hr[^>]*>/gi, '[HR]');
        a = a.replace(/<\/hr>/gi, '');
        a = a.replace(/<(ul|ol)><\/li>/gi, '<$1>');
        if (isIE || isOpera9)
        {
            a = a.replace(/<blockquote[^>]*>/gi, '<blockquote>');
        }
        a = a.replace(/  /gi, ' ');
        a = a.replace(/<p([^>]*)>/gi, '<DIV$1>');
        a = a.replace(/<\/p([^>]*)>/gi, '</DIV$1>\n');
        a = a.replace(/\t/g, '     ');
        a = a.replace(/\n /g, '\n');
    }
    else
    {
        a = htmlentities(a);
    }
    
    var b = a.split("<");
    var c = new Array();
    var e = 0;
    if (b.length > 1)
    {
        for (var i = 0; i < b.length; i++)
        {
            if (i > 0)
            {
                b[i] = '<' + b[i];
            }
            var f = b[i];
            if (f.match(/<(div|span|font|strong|b|u|i|em|var|address|h1|h2|h3|h4|h5|h6|blockquote|img|ol|ul|li|a|strike|s|sub|sup|hr|table|tr|td)( ([^>]{1,}.*?)){0,1}( {0,1}){0,1}>/i))
            {
                var g = RegExp.$1;
                var h = RegExp.$3;
                if (h.toLowerCase().indexOf("style=") != -1 && h.toLowerCase().indexOf("font-family:") != -1 && h.toLowerCase().indexOf("face=") != -1)
                {
                    h = h.replace(/face="(.*?)"/gi, "");
                }
                else if (h.toLowerCase().indexOf("style=") != -1 && h.toLowerCase().indexOf("color:") != -1 && h.toLowerCase().indexOf("color=") != -1)
                {
                    h = h.replace(/color="(.*?)"/gi, "");
                }
                h = h.replace(/(color=|size=|face=|style=)/ig, "|$1");
                h = h.replace(/('|")/g, "");
                h = h.replace(/ \|/g, "|");
                var j = h.split("|");
                var k = new Array();
                if (j != null)
                {
                    for (var z = 0; z < j.length; z++)
                    {
                        var l = j[z].split("=");
                        k[l[0].toLowerCase()] = j[z].replace(l[0].toLowerCase() + "=", "");
                    }
                }
                var m = '';
                var g = g.toLowerCase();
                if (g == "strike" || g == "s")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        m = '[STRIKE]' + analyze_html_block(g, k);
                    }
                    else
                    {
                        m = '[STRIKE]';
                    }
                }
                else if (g == "sub")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        m = '[SUB]' + analyze_html_block(g, k);
                    }
                    else
                    {
                        m = '[SUB]';
                    }
                }
                else if (g == "sup")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        m = '[SUP]' + analyze_html_block(g, k);
                    }
                    else
                    {
                        m = '[SUP]';
                    }
                }
                else if (g == "blockquote")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        m = '[BLOCKQUOTE]' + analyze_html_block(g, k);
                    }
                    else
                    {
                        m = '[BLOCKQUOTE]';
                    }
                }
                else if (g == 'a')
                {
                    var n = f.split(">");
                    var o = f.replace(/<a(.*?)href="(.*?)"/gi, "$2");
                    o = o.replace(">" + n[1], "");
                    var p = o.split(" ");
                    o = p[0];
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        if (n[1] == o)
                        {
                            m = '[URL]' + analyze_html_block(g, k);
                        }
                        else
                        {
                            m = '[URL=' + o + ']' + analyze_html_block(g, k);
                        }
                    }
                    else
                    {
                        if (n[1] == o)
                        {
                            m = '[URL]';
                        }
                        else
                        {
                            if (o.indexOf("mailto:") != -1)
                            {
                                var q = o.replace(/mailto:/i, "");
                                if (q == n[1])
                                {
                                    m = '[EMAIL]';
                                }
                                else
                                {
                                    m = '[EMAIL=' + q + ']';
                                }
                            }
                            else
                            {
                                m = '[URL=' + o + ']';
                            }
                        }
                    }
                }
                else if (g == 'li')
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        m = '[*]' + analyze_html_block(g, k);
                    }
                    else
                    {
                        m = '[*]';
                    }
                }
                else if (g == "strong" || g == "b")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        if (k['style'].toLowerCase().indexOf("font-weight: bold") != -1 || k['style'].toLowerCase().indexOf("font-weight: 700") != -1)
                        {
                            m = analyze_html_block(g, k);
                        }
                        else
                        {
                            m = '[B]' + analyze_html_block(g, k);
                        }
                    }
                    else
                    {
                        m = '[B]';
                    }
                }
                else if (g == "em" || g == "i" || g == "var" || g == "address")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        if (k['style'].toLowerCase().indexOf("font-style: italic") != -1)
                        {
                            m = analyze_html_block(g, k);
                        }
                        else
                        {
                            m = '[I]' + analyze_html_block(g, k);
                        }
                    }
                    else
                    {
                        m = '[I]';
                    }
                }
                else if (g == "u")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        if (k['style'].toLowerCase().indexOf("text-decoration: underline") != -1)
                        {
                            m = analyze_html_block(g, k);
                        }
                        else
                        {
                            m = '[U]' + analyze_html_block(g, k);
                        }
                    }
                    else
                    {
                        m = '[U]';
                    }
                }
                else if (g == "ol")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        m = analyze_html_block(g, k);
                        if (m.indexOf("[LIST=a]") == -1) m += "[LIST=1]";
                    }
                    else if (k['align'] != 'undefined' && k['align'] != null && k['align'] != '')
                    {
                        m = '[' + k['align'].toUpperCase() + ']' + "[LIST=1]";
                    }
                    else
                    {
                        m = '[LIST=1]';
                    }
                }
                else if (g == "ul")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        m = analyze_html_block(g, k) + "[LIST]";
                    }
                    else if (k['align'] != 'undefined' && k['align'] != null && k['align'] != '')
                    {
                        m = '[' + k['align'].toUpperCase() + ']' + "[LIST=1]";
                    }
                    else
                    {
                        m = '[LIST]';
                    }
                }
                else if (g == "font" || g == "h1" || g == "h2" || g == "h3" || g == "h4" || g == "h5" || g == "h6")
                {
                    if (j.length > 0)
                    {
                        for (var r in k)
                        {
                            k[r] = k[r].replace(/^ +| +$/g, "");
                            if (r == "color")
                            {
                                m += '[COLOR=' + k['color'] + ']';
                            }
                            else if (r == "size")
                            {
                                if (isNaN(parseInt(k['size']))) k['size'] = 2;
                                m += '[SIZE=' + k['size'] + ']';
                            }
                            else if (r == "face")
                            {
                                m += '[FONT=' + k['face'] + ']';
                            }
                            else if (r == "style")
                            {
                                m += analyze_html_block(g, k);
                            }
                        }
                    }
                }
                else if (g == "div" || g == "span")
                {
                    if (k['style'] != 'undefined' && k['style'] != null && k['style'] != '')
                    {
                        m = analyze_html_block(g, k);
                    }
                    else if (k['align'] != 'undefined' && k['align'] != null && k['align'] != '')
                    {
                        m = '[' + k['align'].toUpperCase() + ']';
                    }
                    else
                    {
                        m = '[HOTEDITOR_NEW_LINE]';
                    }
                }
                else if (g == "img")
                {
                    if (isSafari)
                    {
                        f = f.replace(/<img(.*?)src="(.*?)">/gi, '[IMG]$2[/IMG]');
                    }
                    else
                    {
                        f.match(/<img(.*?)src="(.*?)"(.*?)>/gi);
                        var s = RegExp.$2;
                        s = s.replace("./", "");
                        if (s.toLowerCase().substr(0, 7) != "http://")
                        {
                            var t = document.URL;
                            t = t.replace("http://", "");
                            var u = t.split("/");
                            var v = "http://";
                            for (var d = 0; d < u.length; d++)
                            {
                                if (d < u.length - 1)
                                {
                                    v += u[d] + "/";
                                }
                            }
                            f = f.replace(/\<img(.*?)src="(.*?)"(.*?)>/gi, '[IMG]' + v + s + '[/IMG]');
                        }
                        else
                        {
                            f = f.replace(/<img(.*?)style="(.*)"(.*)src="(.*?)"(.*)>/gi, '[IMG style="$2"]$4[/IMG]');
                        }
                    }
                }
                else if (g == "table")
                {
                    m = '[TABLE]';
                }
                else if (g == "tr")
                {
                    m = '[TR]';
                }
                else if (g == "td")
                {
                    m = '[TD]';
                }
                b[i] = f.replace(/(<([^>]+)>)/, m);
                if (g != "img")
                {
                    c[e] = m;
                    e++
                }
            }
            else if (f.match(/<\/(div|span|font|strong|b|u|i|em|var|address|h1|h2|h3|h4|h5|h6|blockquote|ol|ul|li|a|strike|s|sub|sup|table|tr|td)>/i))
            {
                e--;
                var w = c.pop();
                if (w != null)
                {
                    var x = "";
                    var A = w;
                    A = A.replace(/=(.*?)\]/g, "]");
                    A = A.replace(/\]/g, "],");
                    A = A.replace(/\[(.*?)\]/g, "[/$1]");
                    var B = A.split(",");
                    B.reverse();
                    for (var y = 0; y < B.length; y++)
                    {
                        x += B[y];
                    }
                    x = x.replace(/\[\/\*\]/gi, "");
                    b[i] = b[i].replace(/(<([^>]+)>)/, x);
                }
                else
                {
                    b[i] = b[i].replace(/(<([^>]+)>)/, "");
                }
            }
        }
        var C = b.join("");
    }
    else
    {
        var C = a;
    }
    
    C = C.replace(/&lt;/g, '<');
    C = C.replace(/&gt;/g, '>');
    C = C.replace(/&nbsp;/g, ' ');
    C = C.replace(/&amp;/g, '&');
    C = C.replace(/     /g, '\t');
    C = C.replace(/\[HOTEDITOR_NEW_LINE\]/g, '\n');
    C = C.replace(/\[\/HOTEDITOR_NEW_LINE\]\n+/g, '\n');
    C = C.replace(/\[\/HOTEDITOR_NEW_LINE\]/g, '\n');
    
    if (starup == "0")
    {
        C = C.replace(/\[\*\]/gi, '\n[*]');
        C = C.replace(/\n\n\[\*\]/gi, '\n[*]');
    }
    
    C = C.replace(/\[COLOR=#.\w*\]\[\/COLOR\]/gi, "");
    C = C.replace(/\[SIZE=\d\]\[\/SIZE\]/gi, "");
    C = C.replace(/\[HIGHLIGHT=#.\w*\]\[\/HIGHLIGHT\]/gi, "");
    C = C.replace(/\[B\]\[\/B\]/gi, "");
    C = C.replace(/\[U\]\[\/U\]/gi, "");
    C = C.replace(/\[I\]\[\/I\]/gi, "");
    C = C.replace(/\[LEFT\]\[\/LEFT\]/gi, "");
    C = C.replace(/\[CENTER\]\[\/CENTER\]/gi, "");
    C = C.replace(/\[RIGHT\]\[\/RIGHT\]/gi, "");
    C = C.replace(/\[JUSTIFY\]\[\/JUSTIFY\]/gi, "");
    C = C.replace(/\[BLOCKQUOTE\]\[\/BLOCKQUOTE\]/gi, "");
    C = C.replace(/\[URL\]\[\/URL\]/gi, "");
    C = C.replace(/\[EMAIL\]\[\/EMAIL\]/gi, "");
    C = C.replace(/\[STRIKE\]\[\/STRIKE\]/gi, "");
    C = C.replace(/\[SUB\]\[\/SUB\]/gi, "");
    C = C.replace(/\[SUP\]\[\/SUP\]/gi, "");
    C = C.replace(/\[IMG\]\[\/IMG\]/gi, "");
    C = C.replace(/^\n+/, "");
    C = C.replace(/\n+$/, "");
    C = C.replace(/&quot;/g, '"');
    
    var D = C.match(/\[table\]/gi);
    var E = C.match(/\[\/table\]/gi);
    
    if (D && E)
    {
        if (D.length > E.length)
        {
            C += "[/TABLE]";
        }
    }
    else if (D && !E)
    {
        C += "[/TABLE]";
    }
    if (starup == "0")
    {
        C = C.replace(/\[\/tr\]/gi, "\n[/TR]");
        C = C.replace(/\[tr\]/gi, "\n[TR]");
        C = C.replace(/\[td\]/gi, "\n[TD]");
        C = C.replace(/\[\/table\]/gi, "\n[/TABLE]");
        C = C.replace(/\[\/table\]$/gi, "[/TABLE]\n");
    }
    return C;
}

function parse_rgb_to_html(a)
{
    a = a.replace(/rgb\((.*?)\)/gi, "$1");
    a = a.replace(/ /, "");
    var c = a.split(",");
    var r = parseInt(c[0]).toString(16);
    var g = parseInt(c[1]).toString(16);
    var b = parseInt(c[2]).toString(16);
    if (r.length == 1)
    {
        r = "0" + r;
    }
    if (g.length == 1)
    {
        g = "0" + g;
    }
    if (b.length == 1)
    {
        b = "0" + b;
    }
    if (r == 'NaN' || g == 'NaN' || b == 'NaN')
    {
        return "#ffffff";
    }
    return "#" + r + g + b;
}