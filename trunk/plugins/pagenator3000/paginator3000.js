(function($){
    $.fn.paginator = function (s){
        var options = {
            pagesTotal  : 1, //count all pages
            pagesSpan   : 10,  //view pages
            pageCurrent : 0,  //current page
            baseUrl     : '?page=', //link or function function (page){}
            returnOrder : false,  // 1..10 if false, 10..1 if true
            lang        : {
                next  : "Next",
                last  : "Last",
                prior : "Prior",
                first : "First",
                arrowRight : String.fromCharCode(8594),
                arrowLeft  : String.fromCharCode(8592)
            }
        };

        $.extend(options, s);

        options.pagesSpan = options.pagesSpan < options.pagesTotal ? options.pagesSpan : options.pagesTotal;
        options.pageCurrent = options.pagesTotal < options.pageCurrent ? options.pagesTotal : options.pageCurrent;
        if (!$.isFunction(options.baseUrl) && !options.baseUrl.match(/%page%/i)) {
            options.baseUrl += '%page%';
        }

        var html = {
            holder: null,
            table: null,
            trPages: null,
            trScrollBar: null,
            tdsPages: null,
            scrollBar: null,
            scrollThumb: null,
            pageCurrentMark: null
        };

        function wheel(event){
            var delta = 0;
            if (!event) event = window.event;
            if (event.wheelDelta) {
                delta = event.wheelDelta/120;
            }
            else if (event.detail) {
                delta = -event.detail/3;
            }
            if (delta && typeof handle == 'function') {
                handle(delta);
                if (event.preventDefault) {
                    event.preventDefault();
                }
                event.returnValue = false;
            }
        }

        function prepareHtml(el){
            html.holder = el;
            $(html.holder).html(makePagesTableHtml());
            html.table = $(html.holder).find('table:last');
            html.trPages = $(html.table).find('tr:first');
            html.tdsPages = $(html.trPages).find('td');
            html.scrollBar = $(html.holder).find('div.scroll_bar');
            html.scrollThumb = $(html.holder).find('div.scroll_thumb');
            html.pageCurrentMark = $(html.holder).find('div.current_page_mark');
            if (options.pagesTotal == options.pagesSpan) {
                $(html.holder).addClass('fulsize');
            }
        }

        function makePagesTableHtml(){
            var tdWidth = (100 / (options.pagesSpan + 2)) + '%';
            var isFunc = $.isFunction(options.baseUrl);

            var next_page = (parseInt(options.pageCurrent) < parseInt(options.pagesTotal) - 1) ? parseInt(options.pageCurrent) + 1 : (options.pagesTotal*1-1);
            var next  = '<a href="';
            next+= isFunc ? 'javascript:void(0)' : options.baseUrl.replace(/%page%/i, next_page);
            next += '" rel="' + next_page + '">%next%</a>';
            var last  = '<a href="';
            last +=  isFunc ? 'javascript:void(0)' : options.baseUrl.replace(/%page%/i, (options.pagesTotal*1-1));
            last += '" rel="' + (options.pagesTotal*1-1) + '">%last%</a>';

            var prior_page = (parseInt(options.pageCurrent) > 0) ? parseInt(options.pageCurrent) - 1 : 0;
            var prior = '<a href="';
            prior += isFunc ? 'javascript:void(0)' : options.baseUrl.replace(/%page%/i, prior_page);
            prior += '" rel="' + prior_page + '">%prior%</a>';
            var first = '<a href="';
            first += isFunc ? 'javascript:void(0)' : options.baseUrl.replace(/%page%/i, 0);
            first += '" rel="' + 0 + '">%first%</a>';

            if (options.returnOrder){
                var top_left       = options.lang.arrowLeft + ' ' + options.lang.next;
                var bottom_left    = options.lang.last;
                var top_right      = options.lang.prior + ' ' + options.lang.arrowRight;
                var bottom_right   = options.lang.first;

                if (options.pageCurrent < options.pagesTotal - 1){
                    top_left     = next.replace(/%next%/, top_left);
                    bottom_left  = last.replace(/%last%/, bottom_left);
                }

                if (options.pageCurrent > 0){
                    top_right    = prior.replace(/%prior%/, top_right);
                    bottom_right = first.replace(/%first%/, bottom_right);
                }
            }
            else {
                var bottom_right   = options.lang.last;
                var top_right      = options.lang.next + ' ' + options.lang.arrowRight;
                var top_left       = options.lang.arrowLeft + ' ' + options.lang.prior;
                var bottom_left    = options.lang.first;

                if (options.pageCurrent < options.pagesTotal - 1) {
                    top_right    = next.replace(/%next%/, top_right);
                    bottom_right = last.replace(/%last%/, bottom_right);
                }

                if (options.pageCurrent > 0){
                    top_left     = prior.replace(/%prior%/, top_left);
                    bottom_left  = first.replace(/%first%/, bottom_left);
                }
            }

            var html = '' +
            '<table width="100%">'+
                '<tr>' +
                    '<td class="left top">' + top_left + '</td>' +
                    '<td class="spaser"></td>' +
                    '<td rowspan="2" align="center">' +
                        '<table>' +
                            '<tr>';
                                for (var i=1; i<=options.pagesSpan; i++){
                                    html += '<td width="' + tdWidth + '"></td>';
                                }
                                html += '' +
                            '</tr>' +
                            '<tr>' +
                                '<td colspan="' + options.pagesSpan + '">' +
                                    '<div class="scroll_bar">' +
                                        '<div class="scroll_trough"></div>' +
                                        '<div class="scroll_thumb">' +
                                            '<div class="scroll_knob"></div>' +
                                        '</div>' +
                                        '<div class="current_page_mark"></div>' +
                                    '</div>' +
                                '</td>' +
                            '</tr>' +
                        '</table>' +
                    '</td>' +
                    '<td class="spaser"></td>' +
                    '<td class="right top">' + top_right + '</td>' +
                '</tr>' +
                '<tr>' +
                    '<td class="left bottom">' + bottom_left + '</td>' +
                    '<td class="spaser"></td>' +
                    '<td class="spaser"></td>' +
                    '<td class="right bottom">' + bottom_right + '</td>' +
                '</tr>' +
            '</table>';
            return html;
        }

        function initScrollThumb(){
            html.scrollThumb.widthMin = '8';
            html.scrollThumb.widthPercent = options.pagesSpan/options.pagesTotal * 100;
            html.scrollThumb.xPosPageCurrent = (options.pageCurrent + 1 - Math.round(options.pagesSpan/2))/options.pagesTotal * $(html.table).width();
            if (options.returnOrder) {
                html.scrollThumb.xPosPageCurrent = $(html.table).width() - (html.scrollThumb.xPosPageCurrent + Math.round(options.pagesSpan/2)/options.pagesTotal * $(html.table).width());
            }
            html.scrollThumb.xPos = html.scrollThumb.xPosPageCurrent;
            html.scrollThumb.xPosMin = 0;
            html.scrollThumb.xPosMax;
            html.scrollThumb.widthActual;
            setScrollThumbWidth();
        }

        function setScrollThumbWidth(){
            $(html.scrollThumb).css({width : html.scrollThumb.widthPercent + "%"});
            html.scrollThumb.widthActual = $(html.scrollThumb).width();
            if (html.scrollThumb.widthActual < html.scrollThumb.widthMin) {
                $(html.scrollThumb).css('width', html.scrollThumb.widthMin + 'px');
            }
            html.scrollThumb.xPosMax = $(html.table).width - html.scrollThumb.widthActual;
        }

        function moveScrollThumb(){
            $(html.scrollThumb).css({left : html.scrollThumb.xPos + "px"});
        }

        function initPageCurrentMark(){
            html.pageCurrentMark.widthMin = '3';
            html.pageCurrentMark.widthPercent = 100 / options.pagesTotal;
            html.pageCurrentMark.widthActual;
            setPageCurrentPointWidth();
            movePageCurrentPoint();
        }

        function setPageCurrentPointWidth(){
            $(html.pageCurrentMark).css({width : html.pageCurrentMark.widthPercent + '%'});
            html.pageCurrentMark.widthActual = $(html.pageCurrentMark).width();
            if(html.pageCurrentMark.widthActual < html.pageCurrentMark.widthMin) {
                $(html.pageCurrentMark).css("width", html.pageCurrentMark.widthMin + 'px');
            }
        }

        function movePageCurrentPoint(){
            var pos = 0;
            if(html.pageCurrentMark.widthActual < $(html.pageCurrentMark).width()){
                pos = (options.pageCurrent) / options.pagesTotal * $(html.table).width() - $(html.pageCurrentMark).width() / 2;
            }
            else {
                pos = (options.pageCurrent)/options.pagesTotal * $(html.table).width();
            }

            if (pos < 0) pos = 0;
            if (pos > ($(html.scrollBar).width() - $(html.pageCurrentMark).width())) pos = $(html.scrollBar).width() - $(html.pageCurrentMark).width();

            if (options.returnOrder) {
                pos = $(html.table).width() - pos - $(html.pageCurrentMark).width();
            }
            $(html.pageCurrentMark).css({left: pos + 'px'});
        }

        function initEvents (){
            moveScrollThumb();
            options.returnOrder ? drawReturn() : drawPages();
            $(html.scrollThumb).bind('mousedown', function(e){
                var dx = e.pageX - html.scrollThumb.xPos;
                $(document).bind('mousemove', function(e){
                    html.scrollThumb.xPos = e.pageX - dx;
                    moveScrollThumb();
                    options.returnOrder ? drawReturn() : drawPages();
                });

                $(document).bind('mouseup', function(){
                    $(document).unbind('mousemove');
                    enableSelection();
                });

                disableSelection();
            });

            if ($.isFunction(options.baseUrl)){
                $(html.holder).find('a[rel!=""]').bind('click', function (e){
                    var n = parseInt($(this).attr('rel'));
                    options.baseUrl(n);
                });
            }

            $(window).resize(function (){
                setPageCurrentPointWidth();
                movePageCurrentPoint();
                setScrollThumbWidth();
            });
        }

        function drawPages(){
            var percentFromLeft = html.scrollThumb.xPos / $(html.table).width();
            var cellFirstValue = Math.round(percentFromLeft * options.pagesTotal);
            var data = "";
            if (cellFirstValue < 0){
                cellFirstValue = 0;
                html.scrollThumb.xPos = 0;
                moveScrollThumb();
            }
            else if (cellFirstValue >= options.pagesTotal - options.pagesSpan) {
                cellFirstValue = options.pagesTotal - options.pagesSpan;
                html.scrollThumb.xPos = $(html.table).width() - $(html.scrollThumb).width();
                moveScrollThumb();
            }

            var isFunc = $.isFunction(options.baseUrl);
            for(var i=1; i <= html.tdsPages.length; i++){
                var cellCurrentValue = cellFirstValue + i;
                if((cellCurrentValue*1-1) == options.pageCurrent){
                    data = '<span> <strong>' + cellCurrentValue + '</strong> </span>';
                }
                else {
                    data = '<span> <a href="';
                    data += isFunc ? 'javascript:void(0)' : options.baseUrl.replace(/%page%/i, (cellCurrentValue*1-1));
                    data += '">' + cellCurrentValue + '</a> </span>';
                }
                $(html.tdsPages[i-1]).html(data);
                if (isFunc){
                    $(html.tdsPages[i-1]).find('a').bind('click', function (){
                        options.baseUrl(($(this).text())*1-1);
                    });
                }
            }
        }

        function drawReturn(){
            var percentFromLeft = html.scrollThumb.xPos / $(html.table).width();
            var cellFirstValue = options.pagesTotal - Math.round(percentFromLeft * options.pagesTotal);
            var data = "";
            if (cellFirstValue < options.pagesSpan){
                cellFirstValue = options.pagesSpan;
                html.scrollThumb.xPos = $(html.table).width() - $(html.scrollThumb).width();
                moveScrollThumb();
            }
            else if (cellFirstValue >= options.pagesTotal) {
                cellFirstValue = options.pagesTotal;
                html.scrollThumb.xPos = 0;
                moveScrollThumb();
            }

            var isFunc = $.isFunction(options.baseUrl);
            for(var i=1; i <= html.tdsPages.length; i++){
                var cellCurrentValue = cellFirstValue - i;
                if((cellCurrentValue*1-1) == options.pageCurrent){
                    data = '<span> <strong>' + cellCurrentValue + '</strong> </span>';
                }
                else {
                    data = '<span> <a href="';
                    data += isFunc ? 'javascript:void(0)' : options.baseUrl.replace(/%page%/i, (cellCurrentValue*1-1));
                    data += '">' + cellCurrentValue + '</a> </span>';
                }
                $(html.tdsPages[i-1]).html(data);
                if (isFunc){
                    $(html.tdsPages[i-1]).find('a').bind('click', function (){
                        options.baseUrl(($(this).text())*1-1);
                    });
                }
            }
        }

        function enableSelection(){
            document.onselectstart = function(){
                return true;
            };
        }

        function disableSelection (){
            document.onselectstart = function(){
                return false;
            };
            $(html.scrollThumb).focus();
        }

        prepareHtml(this);
        initScrollThumb();
        initPageCurrentMark();
        initEvents();
        if (window.addEventListener) {
            window.addEventListener('DOMMouseScroll', wheel, false);
        }
        window.onmousewheel = document.onmousewheel = wheel;
        
        $(this)
            .mouseover(function(){
                handle = over;
            })
            .mouseout(function(){
                handle = null;
            });

        function over(delta) {
            html.scrollThumb.xPos += 5 * delta * (-1);
            moveScrollThumb();
            options.returnOrder ? drawReturn() : drawPages();
        }
    };
})(jQuery);