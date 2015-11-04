
// TIMELINE JS - 10/15 - by AC
// uses serial scroll something, found here: https://github.com/flesler/jquery.serialScroll



// Easing equation, borrowed from jQuery easing plugin
// http://gsgd.co.uk/sandbox/jquery/easing/
jQuery.easing.easeOutQuart = function (x, t, b, c, d) {
    return -c * ((t = t / d - 1) * t * t * t - 1) + b;
};



var nd_tl = nd_tl || {};

(function(window, document, $, undefined)
{
    "use strict";

    var defaults = { 
        selectors : {
            container:          '.tl',   // timeline container
            dots:               'a.dateDot',     // dot navigation
            wrapper:            '.wrapper',
            tlcontainer:        '.timeline',      // timeline container
            tlitem:             '.timeline section',      // timeline event item
            nav:                '.navigation',                  // results
            next:               '.buttons .next',   // navigation elemnt (required for categories)
            prev:               '.buttons .prev',
        },
    };

    // all variables
    var width, 
        leftIndent, 
        leftIndentNeg, 
        sectionCount, 
        sectionWidth, 
        sectionWidthOuter, 
        wrapWidth, 
        wrapWidth2, 
        navWidth, 
        tIndex;

    nd_tl = function(options)
    {
        this.options = $.extend(true, {}, defaults, options);

        this._defaults = defaults;

        this.init();
        this.attachEvents();
    };

    nd_tl.prototype.init = function()
    {
        this.timelineYo();
    }

    nd_tl.prototype.attachEvents = function() 
    {
        var _this = this;

        $(window).on('resize', { context: this }, this.resizeTimeline);     // When the browser changes size
        
        $(this.options.selectors.dots).on('click', { context: this }, this.triggerDot);

        $(this.options.selectors.tlitem+' .date').on('click', { context: this }, this.listenClick);

        $(this.options.selectors.tlitem).swipe(
        {
            swipeLeft:function(event, direction, distance, duration, fingerCount) 
            {
                $(_this.options.selectors.tlcontainer).trigger('next');
            },
            swipeRight:function(event, direction, distance, duration, fingerCount) 
            {
                $(_this.options.selectors.tlcontainer).trigger('prev');
            }
        });
    }

    nd_tl.prototype.triggerDot = function(e)
    {
        e.preventDefault();

        var _this = e.data.context;

        tIndex = $(_this.options.selectors.dots+'.active').parent().prevAll().length;
        $(_this.options.selectors.tlitem+':eq('+tIndex+')').trigger("click");
    }

    nd_tl.prototype.listenClick = function(e)
    {
        e.preventDefault();

        var _this = e.data.context;

        tIndex = $(this).closest('section').index();
        $(_this.options.selectors.tlcontainer).trigger('goto', [ tIndex]);
    }

    nd_tl.prototype.resizeTimeline = function(e)
    {
        var _this = e.data.context;

        _this.timelineYo();
    }

    nd_tl.prototype.timelineYo = function()
    {
        width = $(window).width();
        sectionWidthOuter = $(this.options.selectors.tlitem).outerWidth(true);    // section width with margins
        sectionWidth = $(this.options.selectors.tlitem).width();                  // section width
        
        leftIndent = (width - sectionWidthOuter) / 2;                   // gets padding to center section
        leftIndentNeg = 0 - leftIndent;                                 // negates number

        $(this.options.selectors.wrapper).css({'padding-left': leftIndent + 'px'}).css({'padding-right': leftIndent + 'px'}); // adds left padding to the wrapper

        sectionCount = $(this.options.selectors.tlitem).length;         // counts up each timeline section
        wrapWidth = sectionWidthOuter * sectionCount;                   // wrapper width equation
        wrapWidth2 = wrapWidth + (leftIndent * 2);                      // adds padding to wrapWidth
        
       $(this.options.selectors.wrapper).css({'width': wrapWidth2 + 'px'});

        navWidth = this.getNavWidth();

        if (width < navWidth) 
        {
            this.centerActiveDot();

            $(this.options.selectors.nav+' ul').css({ 'width': navWidth+ 'px' });
            navWidth = width;

        } else 
        {
            $(this.options.selectors.nav).css({'width': navWidth + 'px'});
        }

        this.initSerialScroll();
    };

    nd_tl.prototype.getNavWidth = function()
    {
        navWidth = 0;
        $(this.options.selectors.nav+' ul li:not(.ad)').each(function() 
        {
            navWidth += $(this).outerWidth();
        });

        return navWidth;
    }

    nd_tl.prototype.centerNavigation = function()
    {
        var navWidth = this.getNavWidth();

        if (width < navWidth) 
        {
            this.centerActiveDot();
        }
    }

    nd_tl.prototype.centerActiveDot = function()
    {
        if (!$(this.options.selectors.dots+'.active').closest('li').hasClass('ad'))
        {
            var activeDotPosition = $(this.options.selectors.dots+'.active').position().left + ($(this.options.selectors.dots+'.active').closest('li').outerWidth() / 2);
            var activeDot = ($(this.options.selectors.container).width() / 2) - activeDotPosition;

            $(this.options.selectors.nav+' ul').css({ 'left' : activeDot+ 'px' });
        }
    }

    nd_tl.prototype.initSerialScroll = function()
    {
        var _this = this;
        /*
         * Most jQuery.serialScroll's settings, actually belong to jQuery.ScrollTo, check it's demo for an example of each option.
         * @see http://flesler.demos.com/jquery/scrollTo/
         * You can use EVERY single setting of jQuery.ScrollTo, in the settings hash you send to jQuery.serialScroll.
         */

        var startItem = $(_this.options.selectors.dots+'.active').parent().prevAll().length;
        
        $(_this.options.selectors.tlcontainer).serialScroll(
        { 
            items: 'section',
            prev: _this.options.selectors.prev,
            next: _this.options.selectors.next,
            navigation: _this.options.selectors.nav+' li a',
            offset: leftIndentNeg, //when scrolling to photo, stop 230 before reaching it (from the left) 'leftIndent'
            start: startItem, 
            duration: 500,
            force: true,
            stop: true,
            lock: false,
            cycle: false, //don't pull back once you reach the end
            easing: 'easeOutQuart', //use this easing equation for a funny effect
            jump: false, //click on the images to scroll to them,
            onBefore: function(e, elem)
            {
                $(_this.options.selectors.tlitem).removeClass('active');
                $(elem).addClass('active');
                
                tIndex = $(elem).index();

                $(_this.options.selectors.prev).removeClass('hide');  
                $(_this.options.selectors.next).removeClass('hide'); 
                if (tIndex+1 == 1)
                {
                    $(_this.options.selectors.prev).addClass('hide');
                }
                if (tIndex+1 == $(_this.options.selectors.dots).length)
                {
                    $(_this.options.selectors.next).addClass('hide');
                }

                $(_this.options.selectors.dots).removeClass('active');
                $(_this.options.selectors.dots+':eq('+tIndex+')').addClass('active');

                _this.centerNavigation();

                if (_this.isElementInViewport($('.advert:not(.tracked)')))
                {
                    o.trackPageView();
                    $($('.advert:not(.tracked)')[0]).addClass('tracked');
                }
            },
        });
    }

    nd_tl.prototype.isElementInViewport = function(el) 
    {
        if (typeof el === "object") 
        {
            el = el[0];
        }

        if (typeof el == 'undefined')
        {
            return false;
        }

        var rect = el.getBoundingClientRect();

        return rect.bottom > 0 &&
                rect.right > 0 &&
                rect.left < (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */ &&
                rect.top < (window.innerHeight || document.documentElement.clientHeight) /*or $(window).height() */;
    }

}(window, document, jQuery));

ndTimeline = new nd_tl();








