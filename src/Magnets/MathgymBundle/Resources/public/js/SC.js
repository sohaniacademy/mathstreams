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

// this is Streams Control..
// A globally available service container object, just like XE

SC = {};

SC.dataPing = new Date(0);
SC.allStreams = {};
SC.allStreamsById = {};

SC.strCats = ['std', 'topic', 'diff', 'other'];
SC.strCatClass = {
    'std': 'alert-danger',
    'topic': 'alert-success',
    'diff': 'alert-info',
    'other': 'alert-warning'
};


SC.allContentIds = {'p': [], 'l': []};
SC.allContent = {
    'p': {"new": [], "inprogress": [], "solved": []},
    'l': {"new": [], "inprogress": [], "solved": []},
};
SC.userStats = {'p': {}, 'l': {}};
SC.userContent = {'p': [], 'l': []};
SC.userFilters = {'p': [], 'l': []};

SC.getFiltered = function (filtStr, ctype, callbackFn) {
    if ($.inArray(ctype, ['p', 'l']) < 0) {
        return;
    }

    if (filtStr == 'all') {
        filtOb = {
            "name": "All",
            "title": "Browse all",
            "strid": "all",
            "data": [{"sid": [], "freq": 1}]
        };
    } else if (filtStr == 'home') {
        filtOb = {
            "name": "Home", "title": "Home", "strid": "home",
            "data": [{"sid": ["inprogress"], "freq": 1}, {"sid": ["new"], "freq": 1}]
        };
    } else if (filtStr == 'mine') {
        filtOb = {
            "name": "Mine", "title": "Mine", "strid": "mine",
            "data": [{"sid": ["mine"], "freq": 1}]
        };
    } else if (filtStr == 'inprogress') {
        filtOb = {
            "name": "In progress",
            "title": "In progress",
            "strid": "inprogress",
            "data": [{"sid": ["inprogress"], "freq": 1}]
        };
    } else if (filtStr == 'solved') {
        filtOb = {
            "name": "Solved", "title": "Solved", "strid": "solved",
            "data": [{"sid": ["solved"], "freq": 1}]
        };
    } else if ((/saved_[0-9]+/).test(filtStr)) {
        oldFiltOb = SC.savedFilters[ctype][filtStr.match(/[0-9]+/)[0]];
        filtOb = {
            "name": "Saved",
            "title": oldFiltOb["name"],
            "strid": "saved_" + filtStr.match(/[0-9]+/)[0],
            "data": oldFiltOb["data"]
        };
    }

    finalList = [];
    $.each(filtOb['data'], function (key, val) {
        //$scope.allStreamsById
        //strategy: keep taking intersection at each junction..
        curList = SC.allContentIds[ctype].slice();
        $.each(val['sid'], function (ikey, ival) {
            if (ival == "all") {
                //do nothing..
            } else if (ival == "mine") {
                curList = _.intersection(curList, SC.userContent[ctype]);
            } else if (ival == "solved") {
                curList = _.intersection(curList, SC.allContent[ctype]['solved']);
            } else if (ival == "inprogress") {
                curList = _.intersection(curList, SC.allContent[ctype]['inprogress']);
            } else if (ival == "new") {
                curList = _.intersection(curList, SC.allContent[ctype]['new']);
            } else if ((/^\d+$/).test(ival)) {
                curList = _.intersection(curList, SC.allStreamsById[ival]['map'][ctype]);
            }
        });
        finalList = _.union(finalList, curList);
    });

    returnData = {
        "filtOb": filtOb,
        "data": {
            "all": finalList,
            "solved": _.intersection(finalList, SC.allContent[ctype]['solved']),
            "inprogress": _.intersection(finalList, SC.allContent[ctype]['inprogress']),
            "new": _.intersection(finalList, SC.allContent[ctype]['new'])
        }
    };
    callbackFn(returnData);
    return returnData;
};

//SC.getFiltered = function (filtStr, ctype, callbackFn) {
//    //check if structures are warmed up and fairly recent..
//    curDate = new Date();
//    if (curDate - SC.dataPing > 600000) {
//        SC.loadData(function () {
//            SC.getFilteredActual(filtStr, ctype, callbackFn);
//        });
//    } else {
//        SC.getFilteredActual(filtStr, ctype, callbackFn);
//    }
//};

SC.getContentDetails = function (cids, ctype, callbackFn) {
    if (ctype == 'p') {
        route = 'problemdetails';
    } else {
        route = 'lessondetails';
    }
    XE.postAjax(route, {
        postDataOb: {'cids': cids},
        successFn: function (data) {
            callbackFn(data);
        }
    });
};
//
//SC.getContentDetails = function (cids, ctype, callbackFn) {
//    //check if structures are warmed up and fairly recent..
//    curDate = new Date();
//    if (curDate - SC.dataPing > 600000) {
//        SC.loadData(function () {
//            SC.getContentDetailsActual(cids, ctype, callbackFn);
//        });
//    } else {
//        SC.getContentDetailsActual(cids, ctype);
//    }
//};

SC.safeFun = function(f){
    var args = Array.prototype.slice.call(arguments);
    curDate = new Date();
    if (curDate - SC.dataPing > 600000) {
        SC.loadData(function () {
            f.apply(this, args.slice(1));
        });
    } else {
        f.apply(this, args.slice(1));
    }
};

SC.updateStreamsById = function () {
    SC.allStreamsById = {};
    $.each(SC.allStreams, function (cat, arr) {
        $.each(arr, function (key, str) {
            SC.allStreamsById[str['id']] = str;
        });
    });
};

SC.loadData = function (callbackFn) {
    XE.postAjax('getmaps', {
        successFn: function (data) {
            SC.savedFilters = data['savedFilters'];
            SC.fullMap = decodeRNA(data['fullMap']);
            SC.allStreams = {
                'std': [],
                'topic': [],
                'diff': [],
                'other': [],
                'special': [],
            };
            //from fullMap, build the other structures..
            SC.allContentIds = {'p': [], 'l': []};

            $.each(SC.fullMap, function (key, val) {
                if ($.inArray(val['cat'], SC.strCats) < 0) {
                    val['cat'] = 'other';
                }
                SC.allStreams[val['cat']].push(val);
                SC.allStreamsById[val['id']] = val;

                SC.allContentIds['p'] = _.union(SC.allContentIds['p'], val['map']['p']);
                SC.allContentIds['l'] = _.union(SC.allContentIds['l'], val['map']['l']);
            });
            SC.allStreams['special'].push({"id": "mine", "name": "mine", "cat": "special"});
            SC.allStreams['special'].push({
                "id": "inprogress",
                "name": "in progress",
                "cat": "special"
            });
            SC.allStreams['special'].push({
                "id": "solved",
                "name": "solved",
                "cat": "special"
            });
            SC.allStreams['special'].push({"id": "new", "name": "new", "cat": "special"});

            //from userStats, decode the list of in-progress problems..
            SC.userStats['l'] = decodeRNA(data['userStats']['l']);
            SC.userStats['p'] = decodeRNA(data['userStats']['p']);

            SC.allContent['p']["new"] = [];
            SC.allContent['l']["new"] = [];
            SC.allContent['p']["inprogress"] = [];
            SC.allContent['l']["inprogress"] = [];
            SC.allContent['p']["solved"] = [];
            SC.allContent['l']["solved"] = [];

            //curList = _.difference($scope.allContentIds, _.union($scope.inprogress, $scope.solved));
            $.each(['p','l'],function(okey,ctype) {
                $.each(SC.allContentIds[ctype], function (key, val) {
                    strVal = val.toString();
                    if ($.inArray(strVal, Object.keys(SC.userStats[ctype])) >= 0) {
                        if (SC.userStats[ctype][val]['status'] == 1) {
                            SC.allContent[ctype]['inprogress'].push(val);
                        } else if (SC.userStats[ctype][val]['status'] == 2) {
                            SC.allContent[ctype]['solved'].push(val);
                        }
                    } else {
                        SC.allContent[ctype]['new'].push(val);
                    }
                });
            });

            SC.userContent = data['userContent'];

            //setLoading($("#content-panel"), "end", null);
            SC.dataPing = new Date();

            callbackFn();
        },
        errFn: function (data) {
            //setLoading($("#content-panel"), "end", null);
        }
    });
};

$(function () {
    //SC.loadData(nulFn);
});