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

/*
 * Overall structure of the AJAX 'engine'.. how it works.. we shall call it XE.
 *
 * On loading any page, since httpxmlrequest is false, this layout is chosen as the base for the Twig response
 * Hence, the following AJAX 'engine' gets initialized.. in addition, the chosen page content gets loaded anyway..
 *
 * All buttons/links on the page are wired to make requests through the AJAX engine with the following params:
 * { params }
 *
 * The main issue is.. when a portion of the page gets replaced..
 * any local scripts, variables, callbacks, timers etc need to be 'managed' i.e. deleted, mainly
 * and likewise, reinitialized for the new incoming portion..
 *
 * To handle this, each portion containing a script with locals, has to 'register' its dependents, or atleast, a 'destroy' callback,
 * with some global array ..
 * but portions containing other portions have to be recursively cleaned likewise..
 * Thankfully, the recursion is inherent in the DOM itself.. i.e. , we need to only scan depth first inside the tree.... from the element to be removed..
 * Scan for what, exactly.. a registered function, rather an 'anchor' or some reference to the same, from that body..
 *
 * a data-ajax-engine-handle='chat_sidebar'
 * then the engine has to invoke the destroy function for that chat_sidebar.
 * What if multiple sections want to register by the same name? Some uniqueness has to be supplied.. and back-supplied also..
 * Typically, an incremental number token should be enough.. So.. how will this thing work ?
 * <some local tag = this thing .. which local tag.. how to uniquely indenify each section's own local tage for itself.. ?
 * By a relative reference..
 * randomness to the rescue.. actually, at the twig level itself, it can generate a random unique token per snippet ..
 * how to prevent twig includes/templating from interfering with this process ? ditch, for now let's assume that twig won't interfere with this..
 *
 * then, what we want is basically..
 * script data-ajax-id='uniqId_injected_by_twig'
 *
 * clearLocaleHooks[uniqId] = function(){
 *  //code to clear the locales goeas here..
 * };
 *
 * and then..
 *
 * so that for clearing the #reg region, the AJAX engine can do..
 * $("#reg").find([data-ajax-section]).each(function(){
 *      clearLocaleHooks[$(this).attr('[data-ajax-section]]();
 * });
 *
 * likewise for initializing a new region after loading it in, AJAX engine can do..
 * function clearDOM(){}
 *
 * Massive TODO: IN future, could transform the XE implementation into a service that uses NG's $http
 * Update: cancelled: We will use ng only for its 2-way data binding feature, everything else resides outside ng
 *
 *
 // To keep things 'outside' angular:
 //angular.injector(['ng', 'main.app']).get("MyService").GetName();
 //angular.element(domElement).controller()
 //
 //last resort:
 //$scope.$apply(function(){}
 *
 */
var XE = {};
XE.descr = "ajaXEngine";
XE.mutex = 0;
XE.responseOb = {};
XE.fns = {};
XE.progressbar = null;
XE.debugLine = null;
XE.callbacks = {};
XE.xhr = null;
XE.token = '';
XE.errText = '';

XE.getDescr = function () {
    return XE.descr;
};

/**
 *
 * @param url
 * @param dataOb
 * @param targetOb
 * @param changeURL
 * @param callbackFn
 */
XE.loadSection = function (url, opts) {
    var defaults = {
        targetOb: $("#main"),
        dataOb: {},
        changeURL: true,
        callbackFn: nulFn,
    };
    _.defaults(opts, defaults);

//    url, dataOb, targetOb, changeURL, callbackFn) {
//    targetOb = ((typeof targetOb === 'undefined') || targetOb == null ) ? $("#main") : targetOb;
//    dataOb = (typeof dataOb === 'undefined') ? {} : dataOb;
//    changeURL = (typeof changeURL === 'undefined') ? true : changeURL;
//    callbackFn = (typeof callbackFn === 'undefined') ? nulFn : callbackFn;

    if (XE.mutex > 0) {
        //some indication for the user to wait, or retry..
        return;
    }

    superLog('>> LOAD ' + url);
    XE.mutex = 1;

    XE.xhr = $.ajax({
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            setLoading(opts.targetOb, "start", 15 + '%');
            xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    setLoading(opts.targetOb, "set", 15 + percentComplete * 45 + '%');
                }
            }, false);
            xhr.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    setLoading(opts.targetOb, "set", 60 + percentComplete * 40 + '%');
                }
            }, false);
            return xhr;
        },
        type: 'POST',
        url: url,
        data: $.extend(opts.dataOb, {"csrfToken": XE.token}),
        dataType: 'json',
        success: function (data) {
            XE.responseOb = data;
            if (XE.responseOb['status'] == 'OK') {
                superLog('<< LOAD ' + url);
                if (changeURL) {
                    history.pushState(null, null, url);
                }
                if (opts.targetOb != null) {
                    XE.destroyEl(opts.targetOb);
                    //targetOb.html($(targetOb).first()[0].outerHTML + XE.responseOb.body);
                    setLoading(opts.targetOb, "end", XE.responseOb['body']);
                    XE.createEl(opts.targetOb);
                }
                XE.updateEl($("body"));
                //run the array of callbacks..
                XE.runCallbacks();
                opts.callbackFn(data);
                XE.mutex = 0;
            } else {
                superLog('!< LOAD ' + url);
                setLoading(opts.targetOb, "end", null);
                setErrorModal(XE.responseOb['msg']);
                XE.mutex = 0;
            }
        },
        error: function (data) {
            superLog('!! LOAD ' + url);
            XE.responseOb = data;
            setLoading(opts.targetOb, "end", null);
            //ngDbg($("#headerAppWrapper")).setErr();
            setErrorModal(XE.responseOb);
            XE.mutex = 0;
        },
    });
};

XE.showErr = function () {
    //if(XE.responseOb.responseText)
    //    setModal(XE.responseOb.responseText);
    //else
        setModal(JSON.stringify(XE.responseOb));
    //document.body.innerHTML = ;
};

//this is a normal ajax call wrapper..
XE.postAjax = function (route, opts) {
    var defaults = {
        routeParamsOb: {},
        postDataOb: {},
        successFn: nulFn,
        errFn: nulFn,
    };
    _.defaults(opts, defaults);
//}, routeParamsOb, postDataOb, successFn, errFn) {
    path = Routing.generate(route, opts.routeParamsOb);
    superLog('>> POST ' + path);
    $.ajax({
            type: 'POST',
            url: path,
            data: $.extend(opts.postDataOb, {"csrfToken": XE.token}),
            dataType: 'json',
            success: function (data) {
                XE.responseOb = data;
                if (data.status == 'OK') {
                    superLog('<< POST ' + path);
                    opts.successFn(data);
                } else {
                    superLog('!< POST ' + path);
                    setErrorModal(XE.responseOb['msg']);
                    opts.errFn(data);
                }
            },
            error: function (data) {
                superLog('!! POST ' + path);
                XE.responseOb = data;
                setErrorModal(XE.responseOb);
                opts.errFn(data);
            },
        }
    );
};

XE.setToken = function (token) {
    XE.token = token;
};

XE.addCallback = function (key, fn) {
    XE.callbacks[key] = fn;
};
XE.delCallback = function (key) {
    delete XE.callbacks[key];
};
XE.runCallbacks = function () {
    $.each(XE.callbacks, function (key, fn) {
        fn(XE.responseOb);
    });
};

XE.createEl = function (el) {
    $(el.find("[ngRoot]").get()).each(function (key, val) {

        angular.bootstrap(val, [$(val).attr('ngRoot')]);
        //if (typeof XE.fns[$(val).attr('ng-controller')] === "function") {
        //    XE.fns[$(val).attr('ng-controller')]();
        //}
        ////angular.element(val).scope().$apply(); //TODO: After XE moves to ng, this may not be needed
        //if (typeof angular.element(val).scope().createHandler === "function") {
        //    angular.element(val).scope().createHandler();
        //}
    });
};
XE.destroyEl = function (el) {
    //'reset's the specified element/region/selection..
    //we will traverse DOM from the end, to ensure each child gets cleaned up before its parent
    $(el.find("[ngRoot]").get().reverse()).each(function (key, val) {
        if (typeof angular.element(val).scope().destroyHandler === "function") {
            angular.element(val).scope().destroyHandler();
        }
    });
};
XE.updateEl = function (el) {
    $(el.find("[ngRoot]").get()).each(function (key, val) {
        angular.element(val).scope().$apply();
    });
};

function setLoading(ob, cmd, val) {
    var cssPosition = $(ob).css('position'),
        pos = {},
        height = $(ob).outerHeight() + 'px';
    var width = $(ob).outerWidth() + 'px';

    if (cmd == "start") {
        //but not before checking if one already exists..
        if ($(ob).find(".loading-overlay").length > 0)
            return;
        //initialize loading overlay, progressbar..
        $wrapperTpl = $('<div class="loading-overlay" style="position:absolute; top: ' + pos.top + 'px; left: ' + pos.left + 'px; background-color: rgba(0,0,0,0.7); width: ' + width + '; height: ' + height + ';" />');
        $tpl = $('<span class="loading-overlay loading-wrapper">Loading..<span class="progbar"></span></span>');
        $wrapperTpl.html($($tpl)[0].outerHTML);

        $(ob).prepend($wrapperTpl);

        $(ob).find(".loading-overlay").css({'opacity': 0}).animate({'opacity': 1}, {'duration': 300});

        $(ob).find(".progbar").removeClass('_hide');
        $(ob).find(".progbar").css({width: val});
    } else if (cmd == "set") {
        $(ob).find(".progbar").css({width: val});
    } else if (cmd == "end") {
        $(ob).find(".progbar").css({width: '100%'});
        //$(ob).find(".progress").addClass('_hide');
        if (val !== null) {
            $(ob).html($(ob).find(".loading-overlay")[0].outerHTML + val);
        }
        $(ob).find(".loading-overlay").fadeOut(500, function () {
            $(this).remove();
        });
    }
    //self._loader.css({top: (0 * $wrapperTpl.outerHeight() / 2 + self._loader.outerHeight() / 2) + 'px'});
};

////this, to actually wire up all the 'a' links in the page via XE
/*
 variety of options supported:
 A simple traditional <a href.. >:
 if it doesn't contain data-xe-ignore directive, then make its onclick load its href into body #main
 Any other element containing data-xe-url:

 */
function addAnchorAjax() {
    $("[href], [data-xe-url], [data-xe-route]").not('.sf-toolbar a').on('click', function (event) {

        if ((typeof $(this).attr('data-xe-ignore') !== 'undefined')) {
            return;
        }

        //either href should be defined and not #-based, or data-xe-route should be defined.. else return
        if (((typeof $(this).attr('href') === 'undefined') || $(this).attr('href')[0] == "#")
            && (typeof $(this).data('xe-url') === 'undefined')
            && (typeof $(this).data('xe-route') === 'undefined')) {
            return;
        }
        thisOb = $(this);
        event.preventDefault();
        if (typeof $(this).attr('href') !== 'undefined') {
            url = $(this).attr('href');
        } else if (typeof $(this).data('xe-url') !== 'undefined') {
            url = $(this).data('xe-url');
        } else if (typeof $(this).data('xe-route') !== 'undefined') {
            //generate url from route params..
            route = $(this).data('xe-route');
            routeParamsOb = {};
            if (typeof $(this).data('xe-routeparams') !== 'undefined') {
                //superLog($(this).data('xe-routeparams'));
                routeParamsOb = $(this).data('xe-routeparams');
            }
            url = Routing.generate(route, routeParamsOb);
        }
        //todo: remove duplicate default fallback to main, already taken care of in loadSection
        targetOb = null;
        if ($(this).data('xe-target')) {
            targetOb = $($(this).data('xe-target'));
        } else {
            targetOb = $("#main");
        }
        dataOb = {};
        changeURL = true;
        if ($(this).data('xe-post')) {
            dataOb = $(this).data('xe-post');
            changeURL = false;
        }
        XE.loadSection(url, {
            targetOb: targetOb,
            dataOb: dataOb,
            changeURL: changeURL,
            callbackFn: function () {
                thisOb.blur(); //this is a hack..
            }
        });
    });
}

function wireSaveMachine(inputArea, saveButton, saveRoute, saveParamsFn, preSaveFn, dataFn, successFn) {
//saveButton.css('height','2em');
    saveButton.on("click", function () {
        if (wireSaveMachine.savingStatus == 1)
            return;
        preSaveFn();
        setLabel(saveButton, 'Saving..', 'info');
        saveButton.css('cursor', 'wait');
        wireSaveMachine.savingStatus = 1;
        //routeParamsOb: {},
        //postDataOb: {},
        //successFn: null,
        //    errFn: null,
        //
        XE.postAjax(saveRoute, {
            routeParamsOb: saveParamsFn(),
            postDataOb: dataFn(),
            successFn: function (dataOb) {
                wireSaveMachine.lastSavedData = inputArea.val();
                setLabel(saveButton, 'Saved', 'success');
                saveButton.css('cursor', 'pointer');
                successFn(dataOb);
                if (wireSaveMachine.savePendingStatus > 0) {
                    //tell user, need to re-save!
                    wireSaveMachine.savePendingStatus = 0;
                    setLabel(saveButton, 'Click to save', 'warning');
                    saveButton.css('cursor', 'pointer');
                }
                wireSaveMachine.savingStatus = 0;
            },
            errFn: function (dataOb) {
                setLabel(saveButton, 'Error', 'danger');
                saveButton.css('cursor', 'pointer');
                wireSaveMachine.savingStatus = 0;
            }
        });
    });
    //init state:
    wireSaveMachine.lastSavedData = inputArea.val();
    wireSaveMachine.savingStatus = 0;
    wireSaveMachine.savePendingStatus = 0;
    setLabel(saveButton, 'Saved', 'success');
    saveButton.css('cursor', 'pointer');
    texSetSrcHook(function () {
            //compare data?
            if (inputArea.val() != wireSaveMachine.lastSavedData) {
                //alert(inputArea.val() + "::" + wireSaveMachine.lastSavedData);
                if (wireSaveMachine.savingStatus > 0) {
                    wireSaveMachine.savePendingStatus = 1;
                } else {
                    setLabel(saveButton, 'Click to save', 'warning');
                    saveButton.css('cursor', 'pointer');
                }
            } else {
                //cancel saveStatus, if possible..
                //todo: need to check how well this plays with ajax requests already in flight
                if (wireSaveMachine.savingStatus > 0) {
                    wireSaveMachine.savePendingStatus = 0;
                } else {
                    setLabel(saveButton, 'Saved', 'success');
                    saveButton.css('cursor', 'pointer');
                }
            }
        }
    );
}

$(function () {
    XE.addCallback('anchorHandler', addAnchorAjax);
    //XE.addCallback('dragHandler', function () {
    //    $(".ui-resizable-s").attr('data-toggle', 'tooltip');
    //    $(".ui-resizable-s").attr('data-placement', 'top');
    //    $(".ui-resizable-s").attr('data-title', 'Drag to resize');
    //
    //    //todo: will add more classes as they are used
    //});
    //XE.addCallback('affixHandler', function(){
    //    $("[data-spy=affix]").affix();
    //});
    XE.addCallback('tooltipHandler', function () {
        rebindTooltips()
    });

    XE.createEl($('body'));
    XE.runCallbacks();
});