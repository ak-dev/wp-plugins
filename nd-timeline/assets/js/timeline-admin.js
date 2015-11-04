/* 
 * 
 * TIMELINE JS
 * Author: Anja Kastl
 * 
 */

var nd_admin_tl = nd_admin_tl || {};

(function(window, document, $, undefined)
{
    nd_admin_tl = function()
    {
        var _this = this;
        
        $("body").on("click", "li.timeline-post", _this.shortcode);
    };

    nd_admin_tl.prototype.modalHandler = function(e)
    {
        e.preventDefault();
    };

    nd_admin_tl.prototype.shortcode = function(e)
    {
        e.preventDefault();

        var name = $(this).data('name'),
            title = $(this).data('title');

        window.send_to_editor("[timeline id='" + name + "' title='" + title + "']");
    };

}(window, document, jQuery));

ndTimelineAdmin = new nd_admin_tl();