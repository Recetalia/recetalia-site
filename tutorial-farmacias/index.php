<!DOCTYPE HTML>
<html lang="en-US">

<head>

    <base href="https://recetalia.com/">  


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="RECETALIA es la Plataforma de prescripción de órdenes médicas" />
    <meta name="author" content="Recetalia" />
    <title> Tutorial Farmacias - Recetalia - Receta Digital</title>
    <!--  Add Favicon Icon-->
    <link rel="shortcut icon" href="images/favicon-recetalia.png" type="image/x-icon">
    <link rel="icon" href="images/favicon-recetalia.png" type="image/x-icon">
    <!-- Add All Style -->
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/owl.carousel.min.css" />
    <link rel="stylesheet" href="css/slimmenu.min.css" />
    <link rel="stylesheet" href="css/modal-video.min.css" />
    <link rel="stylesheet" href="css/animate.min.css" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="css/responsive.css" />
    <!-- Poppins Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,700" rel="stylesheet">

    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    <script>
    var onloadCallback = function() {
	grecaptcha.render('html_element', {
    'sitekey' : '6LdnY14aAAAAANJNq-fqC7aUT2lrU9C4jZvKsete'
    });
    };
    
    
    
    </script>
    
  
	
</head>
<body>
<?php include_once("../header.php");?>
    <!-- End Themeix Header -->

    <section class="feature-section bg-color3 section-spacing wow fadeIn"  data-wow-duration="1s">
        <div class="container">
            <div class="row">
                <div class="col-md-12 m-auto">
                    <div class="section-title margin-bottom-60 text-center">
                        <h4>TUTORIAL FARMACIAS</h4>
                        <!--p>Es un Proceso Rápido y Sencillo</p-->
						
						<div class="embed-responsive embed-responsive-16by9">
						  <iframe class="embed-responsive-item"  src="/videotutoriales/demo.mp4"></iframe>
						</div>
						
						<p class="text-center mt-5">
							<a href="/tutorial-farmacias.pdf" target="_blank" class="btn btn-primary">DESCARGAR PDF</a>

                    </div>
                </div>
            </div>	
        </div>
    </section>
    <!-- End Feature -->
	
	<style>
		a.btn-primary{
    background-color: #13a0b2;
    border: 0;
    border-radius: 0;
    padding: 10px 30px;
    font-size: 16px;
    font-weight: 400;
    line-height: 30px;
    text-transform: uppercase;
    color: #ffffff;
}		a.btn-primary:hover{
    background-color: #00A5A6;
}
	</style>

	
    <!-- Start Video -->
	<!--
    <section class="Video-section bg-color1 section-spacing">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="video-thumbnails">
                        <img src="images/video-bg.jpg" alt="video image" />
                        <div class="video-thumbnails-img js-modal-btn" data-video-id="7TUOI23spt0">
                            <img src="images/video-play-logo.png" alt="video play logo" />
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeIn"  data-wow-duration="1s">
                    <div class="section-title margin-bottom-30">
                        <h4>Global Network</h4>
                        <p>Lorem ipsum dolor sit amet, fusce augue, accumsan vestibulum, ante et massa eget, urna varius. Vestibulum pede id, cum mauris pharetra a, interdum dui dolor fringilla morbi, et metus convallis ultricie.</p>
                    </div>
                    <ul class="section-list list-inline">
                        <li>Dolor sit amet, dui dolor fringilla morbi ante et massa eget et metus convallis ultricie.</li>
                        <li>crytocoin dui dolor fringilla morbi.</li>
                        <li>reet metus convallis ultricie.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- End Video -->
	
    <!-- Start About Contact -->
    <section class="about-contact-section d-flex justify-content-between themeix-highlight" id="ContactSet">
        <div class="about-contact-thumbnails themeix-highlight">
        </div>
        <div class="about-contact-content section-spacing bg-color4 themeix-highlight wow fadeIn"  data-wow-duration="1s">>
            <div class="about-before-skew bg-color4"></div>
            <div class="container-fluid">
                <div class="section-title margin-bottom-60 section-inverse">
                    <span>&nbsp;</span>
                    <h4>Contacto</h4>
                    <p>Envianos tu mensaje y te contestaremos a la brevedad..</p>
                </div>
                <form class="about-contact-form" action="mailer/contact.php" method="POST">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Nombre Completo" name="name" id="name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Su Email" name="email" id="email">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea rows="4" class="form-control" placeholder="Mensaje" name="comment" id="comment"></textarea>
                                    </div>
                                </div>
								<!---
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <select class="form-control form--submit-btn">
                                            <option>General Contact</option>
                                            <option>Investment Information</option>
                                            <option>Chit Chat</option>
                                        </select>
                                    </div>
                                </div>
								-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div id="html_element"></div>
                                    

                                        <button type="submit" class="btn btn-primary" id="submit">Enviar</button>
           

                                    </div>
                                </div>
								<!-- Alert Message -->
								<div class="col-md-12 alert-notification">
									<div id="message" class="alert-msg" style="margin-top:10px; color:#FFF"></div>
								</div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- End About Contact -->

    <!-- Start Two Column -->
	<!--
    <section class="two-column-section section-spacing wow fadeIn"  data-wow-duration="1s">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="faq-accordion">
                        <div class="faq-card card">
                            <div class="card-header">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne">
                                    What is crypto coin ?
                                </button>
                            </div>
                            <div id="collapseOne" class="collapse show" data-parent="#accordion">
                                <div class="card-body">
                                    Lorem ipsum dolor sit amet, convallis rutrum quam varius enim. Fusce in sagittis, lectus enim tellus sed non purus quam, at pellentesque in justo sapien mauris. In et elit fusce.
                                </div>
                            </div>
                        </div>
                        <div class="faq-card card">
                            <div class="card-header">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo">
                                    About our company
                                </button>
                            </div>
                            <div id="collapseTwo" class="collapse">
                                <div class="card-body">
                                    Lorem ipsum dolor sit amet, convallis rutrum quam varius enim. Fusce in sagittis, lectus enim tellus sed non purus quam, at pellentesque in justo sapien mauris. In et elit fusce.
                                </div>
                            </div>
                        </div>
                        <div class="faq-card card">
                            <div class="card-header">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree">
                                    Why Choose finace ?
                                </button>
                            </div>
                            <div id="collapseThree" class="collapse">
                                <div class="card-body">
                                    Lorem ipsum dolor sit amet, convallis rutrum quam varius enim. Fusce in sagittis, lectus enim tellus sed non purus quam, at pellentesque in justo sapien mauris. In et elit fusce.
                                </div>
                            </div>
                        </div>
                        <div class="faq-card card">
                            <div class="card-header">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapsefour">
                                    Privacy Policy
                                </button>
                            </div>
                            <div id="collapsefour" class="collapse">
                                <div class="card-body">
                                    Lorem ipsum dolor sit amet, convallis rutrum quam varius enim. Fusce in sagittis, lectus enim tellus sed non purus quam, at pellentesque in justo sapien mauris. In et elit fusce.
                                </div>
                            </div>
                        </div>
                        <div class="faq-card card">
                            <div class="card-header m-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapsefive">
                                    Why join with us
                                </button>
                            </div>
                            <div id="collapsefive" class="collapse">
                                <div class="card-body pb-0">
                                    Lorem ipsum dolor sit amet, convallis rutrum quam varius enim. Fusce in sagittis, lectus enim tellus sed non purus quam, at pellentesque in justo sapien mauris. In et elit fusce.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="section-title margin-bottom-60">
                        <h4>Why Choose us</h4>
                        <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr diam nonumy eirmod tempor invidunt.</p>
                    </div>
                    <div class="choose-us-container">
                        <div class="choose-us-row">
                            <div class="choose-us-logo">
                                <img src="images/choose-logo.png" alt="choose logo" />
                            </div>
                            <div class="choose-us-content">
                                <h4>Experts Support</h4>
                                <p>Fermentum vel elit sit feugiat, quisque ante amet tortor adipiscing diam, magna penatibus </p>
                            </div>
                        </div>
                        <div class="choose-us-row">
                            <div class="choose-us-logo">
                                <img src="images/choose-logo2.png" alt="choose logo" />
                            </div>
                            <div class="choose-us-content">
                                <h4>Instant Exchange</h4>
                                <p>Fermentum vel elit sit feugiat, quisque ante amet tortor adipiscing diam, magna penatibus </p>
                            </div>
                        </div>
                        <div class="choose-us-row">
                            <div class="choose-us-logo">
                                <img src="images/choose-logo3.png" alt="choose logo" />
                            </div>
                            <div class="choose-us-content">
                                <h4>Recuring Buys</h4>
                                <p>Fermentum vel elit sit feugiat, quisque ante amet tortor adipiscing diam, magna penatibus </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Two Column -->
	
    
    <!-- Start About -->
	<!--
    <section class="about-section section-spacing bg-color1 wow fadeIn"  data-wow-duration="1s">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-10 m-auto">
                            <div class="section-title text-center margin-bottom-60">
                                <h4>Testimonials</h4>
                            </div>
                        </div>
                    </div>

                    <div class="about-author-carousel owl-carousel">
                        <div class="about-details">
                            <div class="testimonial-text text-center">
                                <p>Lorem ipsum dolor sit amet, convallis rutrum quam varius enim. Fusce in sagittis, lectus enim tellus sed non purus quam, at pellentesque in justo sapien mauris. In et elit fusce dapibus dui quis, magna laoreet consectetuer imperdiet, sed leo in ante a, tempus ultricies ut aliquet mi, etiam fames viverra ut mollis volutpat nec. Nam fermentum et, quisque vestibulum nonummy non sed fer</p>

                                <div class="testimonial-author-meta">
                                    <img src="images/tbg.jpg" alt="" />
                                    <h5>Jhon Doe</h5>
                                    <p>Finance Investor</p>
                                </div>
                            </div>
                        </div>
                        <div class="about-details">
                            <div class="testimonial-text text-center">
                                <p>Lorem ipsum dolor sit amet, convallis rutrum quam varius enim. Fusce in sagittis, lectus enim tellus sed non purus quam, at pellentesque in justo sapien mauris. In et elit fusce dapibus dui quis, magna laoreet consectetuer imperdiet, sed leo in ante a, tempus ultricies ut aliquet mi, etiam fames viverra ut mollis volutpat nec. Nam fermentum et, quisque vestibulum nonummy non sed fer</p>

                                <div class="testimonial-author-meta">
                                    <img src="images/tbg.jpg" alt="" />
                                    <h5>Robert Phew</h5>
                                    <p>Finance Investor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End About -->
    <!-- Start Testimonial -->
	<!--
    <section class="team-section section-spacing bg-color3 wow fadeIn"  data-wow-duration="1s">
        <div class="container">
            <div class="row">
                <div class="col-md-10 m-auto">
                    <div class="section-title text-center margin-bottom-60">
                        <h4>Our Experts team member</h4>
                        <p>diam accumsan wisi sed, mauris ligula turpis illo tempor ad aliquam, magna ipsum sem mauris nibh, dignissim praesent est id, tincidunt non praesent</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-carousel owl-carousel">
                <div class="testimonial-container">
                    <div class="testimonial-box">
                        <div class="testimonial-box-thumb">
                            <img src="images/testimonial-author.jpg" alt="testimonial author img" />
                        </div>
                        <div class="testimonial-box-intro">
                            <a href="services.html">
                                <h4>Jhon Doe</h4>
                            </a>
                            <p>Bitcoin Consultant</p>
                        </div>
                    </div>
                    <div class="testimonial-hover">
                        <ul class="testimonial-hover-link list-inline">
                            <li class="list-inline-item"><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        </ul>
                        <div class="testimonial-hover-intro">
                            <a href="services.html">
                                <h4>Jhon Doe</h4>
                            </a>
                            <p>Bitcoin Consultant</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-container">
                    <div class="testimonial-box">
                        <div class="testimonial-box-thumb">
                            <img src="images/testimonial-author2.jpg" alt="testimonial author img" />
                        </div>
                        <div class="testimonial-box-intro">
                            <a href="services.html">
                                <h4>Albert Hue</h4>
                            </a>
                            <p>Bitcoin Consultant</p>
                        </div>
                    </div>
                    <div class="testimonial-hover">
                        <ul class="testimonial-hover-link list-inline">
                            <li class="list-inline-item"><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        </ul>
                        <div class="testimonial-hover-intro">
                            <a href="services.html">
                                <h4>Albert Hue</h4>
                            </a>
                            <p>Bitcoin Consultant</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-container">
                    <div class="testimonial-box">
                        <div class="testimonial-box-thumb">
                            <img src="images/testimonial-author.jpg" alt="testimonial author img" />
                        </div>
                        <div class="testimonial-box-intro">
                            <a href="services.html">
                                <h4>Akama Lue</h4>
                            </a>
                            <p>Bitcoin Consultant</p>
                        </div>
                    </div>
                    <div class="testimonial-hover">
                        <ul class="testimonial-hover-link list-inline">
                            <li class="list-inline-item"><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        </ul>
                        <div class="testimonial-hover-intro">
                            <a href="services.html">
                                <h4>Akama Lue</h4>
                            </a>
                            <p>Bitcoin Consultant</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Testimonial -->
    <!-- Start Post -->
	<!--
    <section class="post-section section-spacing bg-color1 wow fadeIn"  data-wow-duration="1s">
        <div class="container">
            <div class="row">
                <div class="col-md-8 m-auto">
                    <div class="section-title text-center margin-bottom-60">
                        <h4>Our Blog Post</h4>
                        <p>diam accumsan wisi sed, mauris ligula turpis illo tempor ad aliquam, magna ipsum sem mauris nibh, dignissim praesent est id, tincidunt non praesent</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="post-wrapper">
                        <div class="post-thumb">
                            <a href="single.html"><img src="images/blog-thumb.jpg" alt="post img" /></a>
                        </div>
                        <div class="post-details">
                            <a href="single.html">
                                <h4>Business ipsum dolor siamet nibh cursus amet.</h4>
                            </a>
                            <p>Pede augue pellen tesque volut pegesed sed. Et neque rhonctri tique ultricies, etiam nunc, vitae conse ctetuer. </p>
                            <ul class="post-meta-data list-inline">
                                <li class="post-meta-date"><i class="fa fa-calendar"></i>2018 may 05 </li>
                                <li class="post-meta-comment"><i class="fa fa-comment"></i>0 Comment </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="post-wrapper">
                        <div class="post-thumb">
                            <a href="single.html"><img src="images/blog-thumb2.jpg" alt="post img" /></a>
                        </div>
                        <div class="post-details">
                            <a href="single.html">
                                <h4>Et neque rhonctritique ultricies, etiam nunc.</h4>
                            </a>
                            <p>Pede augue pellen tesque volut pegesed sed. Et neque rhonctri tique ultricies, etiam nunc, vitae conse ctetuer. </p>
                            <ul class="post-meta-data list-inline">
                                <li class="post-meta-date"><i class="fa fa-calendar"></i>2018 may 05 </li>
                                <li class="post-meta-comment"><i class="fa fa-comment"></i>0 Comment </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="post-wrapper m-0">
                        <div class="post-thumb">
                            <a href="single.html"><img src="images/blog-thumb3.jpg" alt="post img" /></a>
                        </div>
                        <div class="post-details">
                            <a href="single.html">
                                <h4>Nulla nunc lobortis aliquam amet eu, ut ultrices</h4>
                            </a>
                            <p>Pede augue pellen tesque volut pegesed sed. Et neque rhonctri tique ultricies, etiam nunc, vitae conse ctetuer. </p>
                            <ul class="post-meta-data list-inline">
                                <li class="post-meta-date"><i class="fa fa-calendar"></i>2018 may 05 </li>
                                <li class="post-meta-comment"><i class="fa fa-comment"></i>0 Comment </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Banner -->
    <!-- Start Company Ads Section -->
    <!-- <div class="company-ads-section"> -->
    <!-- <div class="container"> -->
    <!-- <div class="company-ads-carousel owl-carousel"> -->
    <!-- <div class="company-ads-wrapper"> -->
    <!-- <img src="images/company-brand.jpg" alt="company ads img" /> -->
    <!-- </div> -->
    <!-- <div class="company-ads-wrapper"> -->
    <!-- <img src="images/company-brand2.jpg" alt="company ads img" /> -->
    <!-- </div> -->
    <!-- <div class="company-ads-wrapper"> -->
    <!-- <img src="images/company-brand3.jpg" alt="company ads img" /> -->
    <!-- </div> -->
    <!-- <div class="company-ads-wrapper"> -->
    <!-- <img src="images/company-brand4.jpg" alt="company ads img" /> -->
    <!-- </div> -->
    <!-- <div class="company-ads-wrapper"> -->
    <!-- <img src="images/company-brand.jpg" alt="company ads img" /> -->
    <!-- </div> -->
    <!-- </div> -->
    <!-- </div> -->
    <!-- </div> -->
    <!-- End Company Ads Section -->
	
    <?php include_once("footer.php");?>
    <!-- Add Javascript File -->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery-modal-video.min.js"></script>
    <script src="js/isotope.pkgd.min.js"></script>
    <script src="js/jquery.slimmenu.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/custom.js"></script>
    
    
    <script src="https://videoconsulta.iwtg.com/video-chat.umd.min.js"></script>
    <link rel="stylesheet" href="https://videoconsulta.iwtg.com/video-chat.css">
    <video-chat bottom right text-color="#ffffff" primary-color="#13a0b2" public-key="QXfeToaWrocXmZosEiwJlXcoy" ></video-chat>
    
    
</body>

</html>