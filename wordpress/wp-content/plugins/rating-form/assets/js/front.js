
/*jQuery(document).ready(function(){jQuery('[class^="rating_form_"]').each(function(){var h=jQuery(this);var o='[id^="rate_"]';var n;var e;var g;var j;var b;var k;var f;var a;var d;var c;var m=(h.find(".stylesheet_off").length===0)?false:true;if(jQuery("head").find("#rating-form-"+h.attr("class").replace("rating_form_","")+"-css").length===0&&!m){var l=document.createElement("link");l.rel="stylesheet";l.id="rating-form-"+h.attr("class").replace("rating_form_","")+"-css";l.href=rating_form_script.uploadurl+"/rating-form/css/rating_form_"+h.attr("class").replace("rating_form_","")+".css?ver="+rating_form_script.pluginversion;l.type="text/css";l.media="all";document.head.appendChild(l)}setTimeout(function(){jQuery(h).removeAttr("style")},1000);h.on("mouseenter",o,function(){if(jQuery(this).parents().eq(1).find(".rating_form_result").length===0){e=jQuery(this).parent();n=e.find(o);a=e.parent().attr("dir")=="rtl"?true:false;d=a?(jQuery(this).find("img").length>0?"-half":"-rtl-half"):"-half";f=e.find(".def").length;g=e.find('[class*="'+d+'"]').index();g=g>-1?(a?(g<=2?(f-g):(g-f)):g):-1;j=g==-1?"":e.find('[class*="'+d+'"]').attr("class").split(" ")[0];b=(n.length-n.not(".hover").length);k=n.length;n.removeClass("hover");if(n.attr("title")){jQuery(this).append('<div class="tooltip">'+jQuery(this).attr("title")+"</div>");if(jQuery(this).css("padding-right").replace(/\D/g,"")=="0"){c=(jQuery(this).width()/2+jQuery(this).outerWidth()-jQuery(this).width())}else{if(jQuery(this).css("padding-left").replace(/\D/g,"")=="0"){c=(jQuery(this).width()/2)}else{c=(jQuery(this).outerWidth()/2)}}var s=(jQuery(this).find(".tooltip").outerWidth()/2);var p=jQuery(this).find(".tooltip").css("top").replace(/\D/g,"");jQuery(this).find(".tooltip").css({top:jQuery(this).position().top-p+"px",left:jQuery(this).position().left-s+c+"px"});jQuery(this).attr("title","")}if(jQuery(this).find("img").length>0){var q=jQuery(this).find("img").attr("src");var r=q.substring(0,q.lastIndexOf("/")+1);jQuery(this).find("img").attr("src",r+"custom-full.png");if(a){jQuery(this).prevAll().find("img").attr("src",r+"custom-empty.png")}else{jQuery(this).nextAll().find("img").attr("src",r+"custom-empty.png")}if(k>3){if(a){jQuery(this).nextAll().find("img").attr("src",r+"custom-full.png")}else{jQuery(this).prevAll().find("img").attr("src",r+"custom-full.png")}}}if(g>-1){n.eq(g).attr("class",j.replace(d,""))}if(e.hasClass("empty_on")){jQuery(this).attr("class",jQuery(this).attr("class").replace("-empty",""));if(a){jQuery(this).nextAll().not(".def").attr("class",jQuery(this).attr("class").replace("-empty",""));jQuery(this).prevAll().not(".def").attr("class",jQuery(this).attr("class")+"-empty")}else{jQuery(this).prevAll().not(".def").attr("class",n.attr("class").replace("-empty",""));jQuery(this).nextAll().not(".def").attr("class",n.attr("class")+"-empty")}}jQuery(this).addClass("hover");if(k>3){if(a){jQuery(this).nextAll().not(".def").addClass("hover")}else{jQuery(this).prevAll().not(".def").addClass("hover")}}}});h.on("mouseleave",o,function(){if(jQuery(this).parents().eq(1).find(".rating_form_result").length===0){e=jQuery(this).parent();n=e.find(o).not(".def");n.removeClass("hover");if(jQuery(this).find("img").length>0){var p=jQuery(this).find("img").attr("src");var q=p.substring(0,p.lastIndexOf("/")+1);n.find("img").attr("src",q+"custom-empty.png");if(a){n.slice((k-b),k).find("img").attr("src",q+"custom-full.png")}else{n.slice(0,b).find("img").attr("src",q+"custom-full.png")}if(g>-1){n.eq(g).find("img").attr("src",q+"custom-half.png")}}if(e.hasClass("empty_on")){n.attr("class",n.attr("class").replace("-empty",""));if(a){n.slice((k-b),k).attr("class",n.attr("class").replace("-empty",""));n.slice(0,(k-b)).attr("class",n.attr("class")+"-empty")}else{n.slice(0,b).attr("class",n.attr("class").replace("-empty",""));n.slice(b,k).attr("class",n.attr("class")+"-empty")}}if(g>-1){n.eq(g).attr("class",j)}if(a){n.slice((k-b),k).addClass("hover")}else{n.slice(0,b).addClass("hover")}if(jQuery(this).parent().find(".tooltip")){jQuery(this).attr("title",jQuery(this).parent().find(".tooltip").html());jQuery(this).parent().find(".tooltip").remove()}}});var i=false;h.on("click",o,function(){var w=jQuery(this).parents().eq(1);var C=jQuery(this).parent();var z=w.find(".rating_stats");if(i){return}i=true;if(w.find(".rating_form_check").length>0){C.find(".def").hide();C.find(".rated").fadeIn("slow");setTimeout(function(){C.find(".rated").hide();C.find(".def").not(".rated").show();i=false},3000)}else{if(w.find(".rating_form_result").length===0){var y=w.attr("id");var r=jQuery(this).not("#rate_not").attr("id");var H=(jQuery(this).find(".title").length===0)?false:true;var D=(C.find(".rating_score").length===0)?false:true;var I=(C.find(".rating_total").length===0)?false:true;var G=(z.length===0)?false:true;var u=(w.find(".rf_user_stats").length===0)?false:true;var F=I===false?"":C.find(".rating_total").text().replace(/\d+/g,"");jQuery(this).parent().find(".tooltip").remove();var s=jQuery(this).attr("title")===undefined?false:true;var A=C.attr("id")===undefined?false:C.attr("id");var v=(w.find(".rating_before_content").length===0)?"":w.find(".rating_before_content").html();var t=(w.find(".rating_after_content").length===0)?"":w.find(".rating_after_content").html();var x=w.find(".redirect_on").length?true:false;var E=w.find(".rating_form").data("redirect-url");var B=w.find(".rating_form").data("redirect-target");var p=x?window.open(E,B):"";w.find('[class*="edit_rating"]').hide();var q=w.find("[class*=spinner]").length?w.find("[class*=spinner]").attr("class").match(/spinner(\d+)_on/):null;if(C.hasClass("spinner_on")){C.html('<li id="rate_not" class="cyto-spinner cyto-spin"></li>');z.addClass("update")}else{if(q===null){C.addClass("update");z.addClass("update")}else{C.html('<li id="rate_not" class="cyto-spinner'+q[1]+' cyto-spin"></li>');z.addClass("update")}}if(r!==undefined){jQuery.ajax({type:"POST",url:rating_form_script.ajaxurl,data:{action:"rating_form_rating_add",form_id:y,rated:r,title:H,score:D,total:I,stats:G,user_stats:u,tooltip:s,rates:F,edit_rating:A,before_content:v,after_content:t},success:function(Q){w.html(Q);if(w.find(".rating_form .thankyou").length){w.find(".rating_form .def").not(".thankyou").hide();setTimeout(function(){w.find(".rating_form .thankyou").remove();w.find(".rating_form .def").not(".thankyou, .rated").show();i=false},3000)}if(x){p.location}var O=y;var V=/postid_(\d*)/;var N;if(V.test(O)){N=O.match(V)[1]}var K=/commentid_(\d*)/;var M;if(K.test(O)){M=O.match(K)[1]}var U=/termid_(\d*)/;var P;if(U.test(O)){P=O.match(U)[1]}var R=/^.*(1u|1d)$/;var S="star";if(R.test(r)){S="tud"}var T="#rf_total_"+N;var J="";var L="";if(K.test(O)){T="#rf_total_"+N+"_commentid-"+M;J="_commentid-"+M}if(U.test(O)){T="#rf_total_termid-"+P+J}if(S=="tud"){T=T+"_type-tud"}if(jQuery("body").find(T).length>0){jQuery.ajax({type:"POST",url:rating_form_script.ajaxurl,data:{action:"ajax_display_rating_form_total",post_id:N,comment_id:M,term_id:P,type:S},success:function(W){jQuery("body").find(T).html(W)},error:function(W){console.log(W)}})}},error:function(J){console.log(J)}})}}}});h.on("click",".edit_rating",function(){var p=jQuery(this).parents().eq(1);var r=p.find(".rating_form");var q=jQuery(this);if(p.find(".rating_form_result").length===0){r.removeClass("cursor");r.addClass("rating_form_result");q.css("opacity","1")}else{if(p.find(".rating_form_result").length>0){r.removeClass("rating_form_result");r.addClass("cursor");q.css("opacity","0.4")}}});h.on("click",".rating_total",function(){var p=jQuery(this).parents().eq(1);var q=jQuery(this).parent();var r=p.find(".rating_stats");if(r.length){p.find(".rating_form").toggleClass("rating_stats_active");r.css("top",(p.position().top-r.outerHeight()-10));if(r.is(":visible")){r.fadeOut()}else{if(r.length>0){r.fadeIn();r.on("click",".rf_stats_close",function(){r.hide();p.find(".rating_form").removeClass("rating_stats_active")})}}}})})});*/

jQuery(document).ready(function() {
    jQuery('[class^="rating_form_"]').each(function() {
        var h = jQuery(this);
        var o = '[id^="rate_"]';
        var n;
        var e;
        var g;
        var j;
        var b;
        var k;
        var f;
        var a;
        var d;
        var c;
        var m = (h.find(".stylesheet_off").length === 0) ? false : true;
        if (jQuery("head").find("#rating-form-" + h.attr("class").replace("rating_form_", "") + "-css").length === 0 && !m) {
            var l = document.createElement("link");
            l.rel = "stylesheet";
            l.id = "rating-form-" + h.attr("class").replace("rating_form_", "") + "-css";
            l.href = rating_form_script.uploadurl + "/rating-form/css/rating_form_" + h.attr("class").replace("rating_form_", "") + ".css?ver=" + rating_form_script.pluginversion;
            l.type = "text/css";
            l.media = "all";
            document.head.appendChild(l)
        }
        setTimeout(function() {
            jQuery(h).removeAttr("style")
        }, 1000);
        h.on("mouseenter", o, function() {
            if (jQuery(this).parents().eq(1).find(".rating_form_result").length === 0) {
                e = jQuery(this).parent();
                n = e.find(o);
                a = e.parent().attr("dir") == "rtl" ? true : false;
                d = a ? (jQuery(this).find("img").length > 0 ? "-half" : "-rtl-half") : "-half";
                f = e.find(".def").length;
                g = e.find('[class*="' + d + '"]').index();
                g = g > -1 ? (a ? (g <= 2 ? (f - g) : (g - f)) : g) : -1;
                j = g == -1 ? "" : e.find('[class*="' + d + '"]').attr("class").split(" ")[0];
                b = (n.length - n.not(".hover").length);
                k = n.length;
                n.removeClass("hover");
                if ( n.attr("title") && jQuery(this).find(".tooltip").length == 0 ) {

                    //if( jQuery(this).find(".tooltip").length > 0 ) return;

                    jQuery(this).append('<div class="tooltip">' + jQuery(this).attr("title") + "</div>");
                    if (jQuery(this).css("padding-right").replace(/\D/g, "") == "0") {
                        c = (jQuery(this).width() / 2 + jQuery(this).outerWidth() - jQuery(this).width())
                    } else {
                        if (jQuery(this).css("padding-left").replace(/\D/g, "") == "0") {
                            c = (jQuery(this).width() / 2)
                        } else {
                            c = (jQuery(this).outerWidth() / 2)
                        }
                    }
                    var s = (jQuery(this).find(".tooltip").outerWidth() / 2);
                    var p = jQuery(this).find(".tooltip").css("top").replace(/\D/g, "");
                    jQuery(this).find(".tooltip").css({
                        top: jQuery(this).position().top - p + "px",
                        left: jQuery(this).position().left - s + c + "px"
                    });
                    jQuery(this).attr("title", "")
                }
                if (jQuery(this).find("img").length > 0) {
                    var q = jQuery(this).find("img").attr("src");
                    var r = q.substring(0, q.lastIndexOf("/") + 1);
                    jQuery(this).find("img").attr("src", r + "custom-full.png");
                    if (a) {
                        jQuery(this).prevAll().find("img").attr("src", r + "custom-empty.png")
                    } else {
                        jQuery(this).nextAll().find("img").attr("src", r + "custom-empty.png")
                    }
                    if (k > 3) {
                        if (a) {
                            jQuery(this).nextAll().find("img").attr("src", r + "custom-full.png")
                        } else {
                            jQuery(this).prevAll().find("img").attr("src", r + "custom-full.png")
                        }
                    }
                }
                if (g > -1) {
                    n.eq(g).attr("class", j.replace(d, ""))
                }
                if (e.hasClass("empty_on")) {
                    jQuery(this).attr("class", jQuery(this).attr("class").replace("-empty", ""));
                    if (a) {
                        jQuery(this).nextAll().not(".def").attr("class", jQuery(this).attr("class").replace("-empty", ""));
                        jQuery(this).prevAll().not(".def").attr("class", jQuery(this).attr("class") + "-empty")
                    } else {
                        jQuery(this).prevAll().not(".def").attr("class", n.attr("class").replace("-empty", ""));
                        jQuery(this).nextAll().not(".def").attr("class", n.attr("class") + "-empty")
                    }
                }
                jQuery(this).addClass("hover");
                if (k > 3) {
                    if (a) {
                        jQuery(this).nextAll().not(".def").addClass("hover")
                    } else {
                        jQuery(this).prevAll().not(".def").addClass("hover")
                    }
                }
            }
        });
        h.on("mouseleave", o, function() {
            if (jQuery(this).parents().eq(1).find(".rating_form_result").length === 0) {
                e = jQuery(this).parent();
                n = e.find(o).not(".def");
                n.removeClass("hover");
                if (jQuery(this).find("img").length > 0) {
                    var p = jQuery(this).find("img").attr("src");
                    var q = p.substring(0, p.lastIndexOf("/") + 1);
                    n.find("img").attr("src", q + "custom-empty.png");
                    if (a) {
                        n.slice((k - b), k).find("img").attr("src", q + "custom-full.png")
                    } else {
                        n.slice(0, b).find("img").attr("src", q + "custom-full.png")
                    }
                    if (g > -1) {
                        n.eq(g).find("img").attr("src", q + "custom-half.png")
                    }
                }
                if (e.hasClass("empty_on")) {
                    n.attr("class", n.attr("class").replace("-empty", ""));
                    if (a) {
                        n.slice((k - b), k).attr("class", n.attr("class").replace("-empty", ""));
                        n.slice(0, (k - b)).attr("class", n.attr("class") + "-empty")
                    } else {
                        n.slice(0, b).attr("class", n.attr("class").replace("-empty", ""));
                        n.slice(b, k).attr("class", n.attr("class") + "-empty")
                    }
                }
                if (g > -1) {
                    n.eq(g).attr("class", j)
                }
                if (a) {
                    n.slice((k - b), k).addClass("hover")
                } else {
                    n.slice(0, b).addClass("hover")
                }
                if (jQuery(this).parent().find(".tooltip")) {
                    jQuery(this).attr("title", jQuery(this).parent().find(".tooltip").html());
                    jQuery(this).parent().find(".tooltip").remove()
                }
            }
        });
        var i = false;
        h.on("click", o, function() {
            var w = jQuery(this).parents().eq(1);
            var C = jQuery(this).parent();
            var z = w.find(".rating_stats");
            if (i) {
                return
            }
            i = true;
            if (w.find(".rating_form_check").length > 0) {
                C.find(".def").hide();
                C.find(".rated").fadeIn("slow");
                setTimeout(function() {
                    //console.log('rated 3000');
                    C.find(".rated").hide();
                    C.find(".def").not(".rated").show();
                    i = false
                }, 10000)
            } else {
                if (w.find(".rating_form_result").length === 0) {
                    var y = w.attr("id");
                    var r = jQuery(this).not("#rate_not").attr("id");
                    var H = (jQuery(this).find(".title").length === 0) ? false : true;
                    var D = (C.find(".rating_score").length === 0) ? false : true;
                    var I = (C.find(".rating_total").length === 0) ? false : true;
                    var G = (z.length === 0) ? false : true;
                    var u = (w.find(".rf_user_stats").length === 0) ? false : true;
                    var F = I === false ? "" : C.find(".rating_total").text().replace(/\d+/g, "");
                    jQuery(this).parent().find(".tooltip").remove();
                    var s = jQuery(this).attr("title") === undefined ? false : true;
                    var A = C.attr("id") === undefined ? false : C.attr("id");
                    var v = (w.find(".rating_before_content").length === 0) ? "" : w.find(".rating_before_content").html();
                    var t = (w.find(".rating_after_content").length === 0) ? "" : w.find(".rating_after_content").html();
                    var x = w.find(".redirect_on").length ? true : false;
                    var E = w.find(".rating_form").data("redirect-url");
                    var B = w.find(".rating_form").data("redirect-target");
                    var p = x ? window.open(E, B) : "";
                    w.find('[class*="edit_rating"]').hide();
                    var q = w.find("[class*=spinner]").length ? w.find("[class*=spinner]").attr("class").match(/spinner(\d+)_on/) : null;
                    if (C.hasClass("spinner_on")) {
                        C.html('<li id="rate_not" class="cyto-spinner cyto-spin"></li>');
                        z.addClass("update")
                    } else {
                        if (q === null) {
                            C.addClass("update");
                            z.addClass("update")
                        } else {
                            C.html('<li id="rate_not" class="cyto-spinner' + q[1] + ' cyto-spin"></li>');
                            z.addClass("update")
                        }
                    }
                    if (r !== undefined) {
                        jQuery.ajax({
                            type: "POST",
                            url: rating_form_script.ajaxurl,
                            data: {
                                action: "rating_form_rating_add",
                                form_id: y,
                                rated: r,
                                title: H,
                                score: D,
                                total: I,
                                stats: G,
                                user_stats: u,
                                tooltip: s,
                                rates: F,
                                edit_rating: A,
                                before_content: v,
                                after_content: t
                            },
                            success: function(Q) {
                                w.html(Q);
                                if (w.find(".rating_form .thankyou").length) {
                                    w.find(".rating_form .def").not(".thankyou").hide();
                                    setTimeout(function() {
                                        w.find(".rating_form .thankyou").remove();
                                        w.find(".rating_form .def").not(".thankyou, .rated").show();
                                        i = false
                                    }, 10000)
                                }
                                if (x) {
                                    p.location
                                }
                                var O = y;
                                var V = /postid_(\d*)/;
                                var N;
                                if (V.test(O)) {
                                    N = O.match(V)[1]
                                }
                                var K = /commentid_(\d*)/;
                                var M;
                                if (K.test(O)) {
                                    M = O.match(K)[1]
                                }
                                var U = /termid_(\d*)/;
                                var P;
                                if (U.test(O)) {
                                    P = O.match(U)[1]
                                }
                                var R = /^.*(1u|1d)$/;
                                var S = "star";
                                if (R.test(r)) {
                                    S = "tud"
                                }
                                var T = "#rf_total_" + N;
                                var J = "";
                                var L = "";
                                if (K.test(O)) {
                                    T = "#rf_total_" + N + "_commentid-" + M;
                                    J = "_commentid-" + M
                                }
                                if (U.test(O)) {
                                    T = "#rf_total_termid-" + P + J
                                }
                                if (S == "tud") {
                                    T = T + "_type-tud"
                                }
                                if (jQuery("body").find(T).length > 0) {
                                    jQuery.ajax({
                                        type: "POST",
                                        url: rating_form_script.ajaxurl,
                                        data: {
                                            action: "ajax_display_rating_form_total",
                                            post_id: N,
                                            comment_id: M,
                                            term_id: P,
                                            type: S
                                        },
                                        success: function(W) {
                                            jQuery("body").find(T).html(W)
                                        },
                                        error: function(W) {
                                            console.log(W)
                                        }
                                    })
                                }
                            },
                            error: function(J) {
                                console.log(J)
                            }
                        })
                    }
                }
            }
        });
        h.on("click", ".edit_rating", function() {
            var p = jQuery(this).parents().eq(1);
            var r = p.find(".rating_form");
            var q = jQuery(this);
            if (p.find(".rating_form_result").length === 0) {
                r.removeClass("cursor");
                r.addClass("rating_form_result");
                q.css("opacity", "1")
            } else {
                if (p.find(".rating_form_result").length > 0) {
                    r.removeClass("rating_form_result");
                    r.addClass("cursor");
                    q.css("opacity", "0.4")
                }
            }
        });
        h.on("click", ".rating_total, .rating_score", function() {
            var p = jQuery(this).parents().eq(1);
            var q = jQuery(this).parent();
            var r = p.find(".rating_stats");
            if (r.length) {
                p.find(".rating_form").toggleClass("rating_stats_active");
                r.css("top", (p.position().top - r.outerHeight() - 10));
                if (r.is(":visible")) {
                    r.fadeOut()
                } else {
                    if (r.length > 0) {
                        r.fadeIn();
                        r.on("click", ".rf_stats_close", function() {
                            r.hide();
                            p.find(".rating_form").removeClass("rating_stats_active")
                        })
                    }
                }
            }
        })
    })
});
