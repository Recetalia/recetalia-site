(function ($) {

    "use strict";

    // perloader Form js
    $(window).on('load', function () {
        $('.preload-main').delay(1000).fadeOut(800);
    });
    // Search Form js
    $('.abc').on('click', function (event) {
        event.preventDefault();
        $('#search').addClass('open');
        $('#search > form > input[type="search"]').focus();
    });
    $('#search, #search button.close').on('click keyup', function (event) {
        if (event.target == this || event.target.className == 'close' || event.keyCode == 27) {
            $(this).removeClass('open');
        }
    });
    //animation scroll js
    var html_body = $('html, body');
    $('.navbar a , .scroll-down a , .backtotop a').on('click', function () {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                html_body.animate({
                    scrollTop: target.offset().top - 10
                }, 1500);
                return false;
            }
        }
    });

    // Closes responsive menu when a scroll link is clicked
    $('.nav-link').on('click', function () {
        $('.navbar-collapse').collapse('hide');
    });

    //scorllspy js
    $('body').scrollspy({
        target: ".navbar",
        offset: 100,
    });

    //smooth scrolling js
    $(window).scroll(function () {
        var scrolling = $(this).scrollTop();
        var sticky = $('.sticky-top')
        if (scrolling >= 100) {

            $(sticky).addClass("nav-bg");

        } else {

            $(sticky).removeClass("nav-bg");
        }

        if (scrolling >= 50) {
            $('.backtotop').fadeIn(500);
        } else {
            $('.backtotop').fadeOut(500);
        }

    });
    // banner img slide js part
    $('.full_slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        dots: true,
        prevArrow: '.slidPrv',
        nextArrow: '.slidNext',
        arrows: true,
        autoplaySpeed: 3000,
        infinite: true,
        speed: 2000,
        fade: true,
        cssEase: 'linear',
    });

    // event slide js part
    $('.event-main').slick({
        slidesToShow: 2,
        slidesToScroll: 1,
        autoplay: false,
        dots: false,
        arrows: true,
        prevArrow: '.a-up',
        nextArrow: '.a-down',
        autoplaySpeed: 2500,
        infinite: true,
        vertical: true,
        verticalSwiping: true,
        responsive: [
            {
                breakpoint: 481,
                settings: {
                    slidesToShow: 1,
                    vertical: false,
                }
		}
	  ]
    });

    // comment slide js part
    $('.comment-text-main').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        dots: true,
        prevArrow: false,
        nextArrow: false,
        arrows: false,
        autoplaySpeed: 2500,
        infinite: true,
        fade: false,
        asNavFor: '.slider-img-main',
    });

    // comment slide js part
    $('.slider-img-main').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        dots: false,
        prevArrow: '.aal',
        nextArrow: '.aar',
        arrows: true,
        autoplaySpeed: 2500,
        fades: true,
        infinite: true,
        speed: 1500,
        fade: true,
        asNavFor: '.comment-text-main',
    });
    // market slide js
    $('.market-main').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000,
        speed: 2000,
        arrows: false,
        centerMode: true,
        centerPadding: '0px',
        focusOnSelect: true,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 5,
                    slidesToScroll: 1
                }
		},
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1
                }
		},
            {
                breakpoint: 577,
                settings: {
                    slidesToShow: 2,
                    centerMode: false,
                    slidesToScroll: 1
                }
        },
            {
                breakpoint: 321,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
		}
	  ]
    });
    // course part js
    $(window).on('load', function () {
        var $container = $('.portfolioContainer');
        $container.isotope({
            filter: '*',
            animationOptions: {
                duration: 750,
                easing: 'linear',
                queue: false
            }
        });

        $('.portfolioFilter a').on('click', function () {
            $('.portfolioFilter .current').removeClass('current');
            $(this).addClass('current');

            var selector = $(this).attr('data-filter');
            $container.isotope({
                filter: selector,
                animationOptions: {
                    duration: 750,
                    easing: 'linear',
                    queue: false
                }
            });
            return false;
        });
    });

    // countdown timer js 
    $('.coundown_res').countdown('2020/11/19', function (event) {
        var $this = $(this);
        $this.find('#day').html(event.strftime('<span>%D</span>'));
        $this.find('#hour').html(event.strftime('<span>%H</span>'));
        $this.find('#month').html(event.strftime('<span>%M</span>'));
        $this.find('#second').html(event.strftime('<span>%S</span>'));
    });

    //lightbox js
    $('.venobox').venobox();
    // video player js

	
	/* ==================================================
	  Emil Added!    Contact Form Validations
	================================================== */
	$('.about-contact-form').each(function() 
	{
		var formInstance = $(this);
		formInstance.submit(function() {

			var action = $(this).attr('action');

			$("#message").slideUp(750, function() {
				$('#message').hide();

				$('#submit')
					.after('<img src="/images/ajax-loader.gif" class="loader" />')
					.attr('disabled', 'disabled');

				$.post(action, {
						name: $('#name').val(),
						lastname: $('#lastname').val(),
						email: $('#email').val(),
						phone: $('#phone').val(),
						speciality: $('#speciality').val()
					},
					function(data) {
						document.getElementById('message').innerHTML = data;
						$('#message').slideDown('slow');
						$('.about-contact-form img.loader').fadeOut('slow', function() {
							$(this).remove()
						});
						$('#submit').removeAttr('disabled');
					}
				);
			});
			return false;
		});
	});

    
}(jQuery));
