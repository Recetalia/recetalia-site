(function($) {

    "use strict";
	
	/**************
      Emil Added!   para el smooth scroll del boton contactar
    ***************/
	$(document).ready(function() {
	  $('a[href^="#"]').click(function() {
		var destino = $(this.hash);
		if (destino.length == 0) {
		  destino = $('a[name="' + this.hash.substr(1) + '"]');
		}
		if (destino.length == 0) {
		  destino = $('html');
		}
		$('html, body').animate({ scrollTop: destino.offset().top }, 500);
		return false;
	  });
	});

	/* ==================================================
	  Emil Added!    Contact Form Validations
	================================================== */
	$('.about-contact-form').each(function() 
	{
        var formInstance = $(this);

        

		formInstance.submit(function() {

            if(grecaptcha.getResponse() == "") {
                
                alert("Marca la casilla de verificación");
              } 
              else {
            

                var action = $(this).attr('action');

                $("#message").slideUp(750, function() {
                    $('#message').hide();

                    $('#submit')
                        .after('<img src="/images/ajax-loader.gif" class="loader" />')
                        .attr('disabled', 'disabled');

                    $.post(action, {
                            name: $('#name').val(),
                            email: $('#email').val(),
                            comment: $('#comment').val()
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
            }
			return false;
		});
	});

    /**************
      Fixed Navigation
    ***************/
    $(window).scroll(function() {
        if ($(this).scrollTop() > 90) {
            $(".themeix-header-navigation").addClass("fixed-navigation");
        } else {
            $(".themeix-header-navigation").removeClass("fixed-navigation");
        }
    });
	
	/* WOW JS*/
	
    var wow = new WOW(
	  {
	  boxClass:     'wow',      // default
	  animateClass: 'animated', // default
	  offset:       0,          // default
	  mobile:       true,       // default
	  live:         true        // default
	}
	)
	wow.init();
    /*****************
         Main-Menu
    *********************/

    $('.slimmenu').slimmenu({
        resizeWidth: '800',
        collapserTitle: 'Main Menu',
        animSpeed: 'medium',
        indentChildren: true,
    });
    /******************
      Banner Slider
    ********************/

    $('.banner-carousel').owlCarousel({
        autoplay: true,
        autoplaySpeed: 3000,
        mouseDrag: false,
        loop: true,
        nav: true,
        dots: false,
        responsive: {
            0: {
                items: 1,
            },
            1000: {
                items: 1,
            }
        }
    })

    /***********************
      About Author Slider
    *************************/

    $('.about-author-carousel').owlCarousel({
        autoplay: false,
        mouseDrag: false,
        nav: true,
        dots: false,
        responsive: {
            0: {
                items: 1,
            },
            1000: {
                items: 1,
            }
        }
    })

    /************************
      Company Adds Slider
    ***************************/

    $('.company-ads-carousel').owlCarousel({
        autoplay: true,
        autoplaySpeed: 3000,
        margin: 30,
        loop: true,
        nav: false,
        dots: false,
        responsive: {
            0: {
                items: 2,
            },
            798: {
                items: 3,
            },
            1000: {
                items: 4,
            }
        }
    })

    /************************
       Testimonial Slider
    *************************/

    $('.testimonial-carousel').owlCarousel({
        autoplay: true,
        autoplaySpeed: 3000,
        margin: 30,
        loop: true,
        nav: false,
        dots: true,
        responsive: {
            0: {
                items: 1,
            },
            798: {
                items: 2,
            },
            1000: {
                items: 3,
            }
        }
    })

    /**********************
       Video Modal
    **********************/

    $(".js-modal-btn").modalVideo();

    /********************
     Portfolio Project
    ********************/

    var $grid = $('.project-grid').isotope({
        itemSelector: '.project-item',
        layoutMode: 'fitRows'
    });
    var filterFns = {
        numberGreaterThan50: function() {
            var number = $(this).find('.number').text();
            return parseInt(number, 10) > 50;
        },
        ium: function() {
            var name = $(this).find('.name').text();
            return name.match(/ium$/);
        }
    };
    $('.project-filters').on('click', 'button', function() {
        var filterValue = $(this).attr('data-filter');
        filterValue = filterFns[filterValue] || filterValue;
        $grid.isotope({
            filter: filterValue
        });
    });

    /**********************
      Google Map
    **********************/

    function initMap() {

        if ($("#contact-map").length > 0) {

            var map = new google.maps.Map(document.getElementById('contact-map'), {
                zoom: 4,
                center: {
                    lat: -33,
                    lng: 151
                }
            });

            var image = 'images/google-map-icon.png';
            var beachMarker = new google.maps.Marker({
                position: {
                    lat: -33.890,
                    lng: 151.274
                },
                map: map,
                icon: image
            });

        }
    }

    initMap();




})(jQuery);