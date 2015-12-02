/*
 *  This file is part of Mathstreams.
 *
 *  Copyright (c) 2015  Sohani Academy <developer@sohaniacademy.com>
 *
 *  Mathstreams is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function transViewAdv(text) {
    if (typeof text === 'undefined') return '';
    else if (text.match(/^\s*$/)) return '&nbsp;';//this is a tricky one..

    //first, deal with tags..
    textOb = domify(text);
    textOb.find("*").not("b,i,u,br,ul,ol,li,a").each(function () {
        $(this).remove();// $(this).remove();
    });

    text = textOb.html();
    //next: deal with nl2br
    //a much simpler strategy: all nl are br!
    //text = text.replace(/(?:\r\n|\r|\n)/g, '<br />');

    //since we want to support tags, may be better to do it this way, at least for now..
    text = text.replace(/(?:\r\n|\r|\n)/g, '\n');
    cursor = text.length - 1;
    while (cursor >= 0) {
        if (text[cursor] == '\n') {
            //keep tracing backwards, to check if we are after an HTML tag
            //basically we detect the closing '>' .
            // The only issue: what if a user wants to use > as the last symbol of a normal line?
            cursor2 = cursor - 1;
            doAddBr = true;
            while (cursor2 >= 0) {
                if ((/\n/).test(text[cursor2])) {
                    //if this is a newline, do add br..
                    doAddBr = true;
                    break;
                } else if ((/\s/).test(text[cursor2])) {
                    //for any other whitespace character, continue backtracing
                    cursor2--;
                    continue;
                } else if ((/>/).test(text[cursor2])) {
                    //presuming we hit an html element, don't add br..
                    doAddBr = false;
                    break;
                } else {
                    //for any other character, do add br..
                    doAddBr = true;
                    break;
                }
            }
            if (doAddBr) {
                text = text.substring(0, cursor2 + 1) + "<br />" + text.substring(cursor + 1, text.length);
                cursor = cursor2;
            } else {
                text = text.substring(0, cursor2 + 1) + text.substring(cursor, text.length);
                cursor = cursor2;
            }
        } else if ((/\s/).test(text[cursor])) {
            //need to convert consecutive whitespaces to nbsps..
            //keep tracing backwards, to check if we are after a whitespace
            cursor2 = cursor - 1;
            doAddNbsp = true;
            while (cursor2 >= 0) {
                if ((/\s/).test(text[cursor2])) {
                    //if this is a whitespace, do add nbsp..
                    doAddNbsp = true;
                    break;
                } else {
                    doAddNbsp = false;
                    break;
                }
            }
            if (doAddNbsp) {
                text = text.substring(0, cursor2 + 1) + "&nbsp;" + text.substring(cursor + 1, text.length);
                cursor = cursor2;
            } else {
                text = text.substring(0, cursor2 + 1) + text.substring(cursor, text.length);
                cursor = cursor2;
            }
        } else {
            cursor--;
            continue;
        }
    }

    return text;
}
function transView(text) {
    if (typeof text === 'undefined') return '';

    //first: deal with nl2br
    text = text.replace(/(?:\r\n|\r|\n)/g, '\n');
    cursor = text.length - 1;
    while (cursor >= 0) {
        if (text[cursor] == '\n') {
            //keep tracing backwards, to check if we are after an HTML tag
            //basically we detect the closing '>' .
            // The only issue: what if a user wants to use > as the last symbol of a normal line?
            cursor2 = cursor - 1;
            doAddBr = true;
            while (cursor2 >= 0) {
                if ((/\n/).test(text[cursor2])) {
                    //if this is a newline, do add br..
                    doAddBr = true;
                    break;
                } else if ((/\s/).test(text[cursor2])) {
                    //for any other whitespace character, continue backtracing
                    cursor2--;
                    continue;
                } else if ((/>/).test(text[cursor2])) {
                    //presuming we hit an html element, don't add br..
                    doAddBr = false;
                    break;
                } else {
                    //for any other character, do add br..
                    doAddBr = true;
                    break;
                }
            }
            if (doAddBr) {
                text = text.substring(0, cursor2 + 1) + "<br />" + text.substring(cursor + 1, text.length);
                cursor = cursor2;
            } else {
                text = text.substring(0, cursor2 + 1) + text.substring(cursor, text.length);
                cursor = cursor2;
            }
        } else if ((/\s/).test(text[cursor])) {
            //need to convert consecutive whitespaces to nbsps..
            //keep tracing backwards, to check if we are after a whitespace
            cursor2 = cursor - 1;
            doAddNbsp = true;
            while (cursor2 >= 0) {
                if ((/\s/).test(text[cursor2])) {
                    //if this is a whitespace, do add nbsp..
                    doAddNbsp = true;
                    break;
                } else {
                    doAddNbsp = false;
                    break;
                }
            }
            if (doAddNbsp) {
                text = text.substring(0, cursor2 + 1) + "&nbsp;" + text.substring(cursor + 1, text.length);
                cursor = cursor2;
            } else {
                text = text.substring(0, cursor2 + 1) + text.substring(cursor, text.length);
                cursor = cursor2;
            }
        } else {
            cursor--;
            continue;
        }
    }
    //next: add section numbers in headings:
    //and by default, remove the ids..
    textOb = domify(text);
    textOb.find('h1,h2,h3,h4,h5,h6').each(function () {
        if ((/r_h[0-9a-zA-Z_]+/).test($(this).attr('id'))) {
            $(this).prepend("<small>" + ($(this).attr('id').match(/r_h([0-9a-zA-Z_]+)/)[1]).replace("_", ".") + " </small>");
            //$(this).removeAttr("id");
        }
    });
    return textOb[0].innerHTML;
    //return text.replace(/(?:\r\n|\r|\n)/g, '<br />');
//    return text;
}

function setLabel(el, newVal, newCl) {
    el.html(newVal);
    el.removeClass('btn-default btn-primary btn-success btn-info btn-warning btn-danger');
    el.addClass('btn-' + newCl);
    return el;
}

function setClass(el, newCl) {
    el.removeClass('btn-default btn-primary btn-success btn-info btn-warning btn-danger');
    el.addClass('btn-' + newCl);
    return el;
}


function domify(text) {
    return $('<div>' + text + '</div>');
}

function domGet(ob, matcher) {
    return ob.find(matcher).html();
}

function isResponseOk(ob) {
    return (domGet(ob, "#status") == "OK");
}

function rebindDropdowns() {
    $('[data-toggle="dropdown"]').dropdown();
}

function rebindTooltips(ob, newOpts) {
    ob = (typeof ob === 'undefined' ) ? $('[data-toggle=tooltip]') : ob;
    newOpts = (typeof newOpts === 'undefined') ? {} : newOpts;
    defaultOpts = {
        style: {
            classes: 'qtip-bootstrap qtip-shadow'
        },
        position: {
            viewport: $(window),
            adjust: {
                method: 'flip flip'
            }
        }
    };
    var opts = _.extend({}, defaultOpts, newOpts);
    ob.qtip(opts);
    //if any options present in data- directives, will parse them..
}


function postFwd(url, data) {
    var form = $('<form>', {
        'action': url,
        'method': 'post'
    });
    for (var field in data) {
        form.append($('<input>', {
            'name': field,
            'value': data[field],
            'type': 'hidden',
        }));
    }
    form.appendTo('body').submit();
}

function decodeHTML(text) {
    return $("<textarea />").html(text).val();
}

function nulFn() {
}

function blinker(ob2blink) {
    ob2blink.toggleClass('blink-highlighted');
    // .delay(1000).toggleClass('blink-highlighted',400);
//    ob2blink.fadeOut(500);
//    ob2blink.fadeIn(500);
}

function blinkN(ob2blink, n) {
    ob2blink.addClass('blink-basic');
    if (n > 0) {
        blinker(ob2blink);
        setTimeout(function () {
            blinkN(ob2blink, n - 0.5);
        }, 500);
    } else {
        ob2blink.removeClass('blink-basic');
    }
}

//to help debug angular stuff..
function ngDbg(sel) {
    return angular.element(sel).scope();
}

function superLog(value) {
    console.log(value);
    window.$log = value;
}

function openPopup(url, title, pos) {
    if (pos == "rightHalf") {
        x = 0.5 * screen.availWidth;
        y = 0;
        wd = 0.5 * screen.availWidth;
        ht = screen.availHeight;

        opts = 'width=' + wd + ', height=' + ht + ', top=' + y + ', left=' + x + ', scrollbars=yes';
        window.open(url, title, opts).focus();
    } else if (pos == "fullishMain") {
        x = 0.1 * screen.availWidth;
        y = 0.1 * screen.availHeight;
        wd = 0.8 * screen.availWidth;
        ht = 0.8 * screen.availHeight;

        opts = 'width=' + wd + ', height=' + ht + ', top=' + y + ', left=' + x + ', scrollbars=yes';
        window.open(url, title, opts).focus();
    } else {
        window.open(url, title).focus();
    }
}

function decodeRNA(ob) {
    rna = ob['RNA'];
    code = ob['code'];
    if (Array.isArray(code)) {
        returnOb = [];
    } else {
        returnOb = {};
    }
    $.each(code, function (key, val) {
        newInnerOb = {};
        $.each(val, function (ikey, ival) {
            newInnerOb[rna[ikey]] = ival;
        });
        returnOb[key] = newInnerOb;
    });
    return returnOb;
}