
<?php

$servidor='localhost';
$mysql_user='recetali_receuser';
$mysql_pass='RecePass2021';
$db_main='recetali_receta';
global $mysqli;
$mysqli = new mysqli($servidor, $mysql_user, $mysql_pass, $db_main);

$items = array();
$sql = "
    SELECT `pharmacy`.*, `regions`.`name` as `regionName`, `localities`.`name` as `localityName`
    FROM `pharmacy`
	JOIN `localities` ON `localities`.`id` = `pharmacy`.`addressLocalityId`
	JOIN `regions` ON `regions`.`id` = `localities`.`region_id`
    WHERE `pharmacy`.`status`='ACTIVE'
    ORDER BY `pharmacy`.`name` ASC ";
$res = $mysqli->query($sql);
while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
    $item = array();
    $item['id'] = $row['id'];
    $item['name'] = utf8_encode($row['name']);
    $item['addressStreet'] = utf8_encode($row['addressStreet']);
	$item['addressNumber'] = utf8_encode($row['addressNumber']);
    $item['localityName'] = utf8_encode($row['localityName']);
	$item['phone'] = json_decode($row['phone'],true);

    $regionName = utf8_encode($row['regionName']);
    if (!isset($items[$regionName])) {
        $items[$regionName] = array(); // Crear un nuevo arreglo para la región si no existe
    }

    array_push($items[$regionName], $item); // Agregar la farmacia al arreglo correspondiente a la región
}
ksort($items); 

?>

<!DOCTYPE HTML>
<html lang="en-US">

<head>

    <base href="https://recetalia.com/">  


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="RECETALIA es la Plataforma de prescripción de órdenes médicas" />
    <meta name="author" content="Recetalia" />
    <title> Recetalia - Receta Digital</title>
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
		
		var items = <?php echo json_encode($items); ?>;

    document.addEventListener('DOMContentLoaded', function() {
		
		console.log('items',items)
		
        var pharmacyContainer = document.querySelector('.pharmacy-container');
        for (var regionName in items) {
            var regionItems = items[regionName];
            var regionElement = document.createElement('div');
            regionElement.className = 'region-list';
            regionElement.id = 'region-' + regionName;
            var regionHtml = '<h5 class="toggle-btn" onclick="togglePharmacyList(\'' + regionName + '\')">' + regionName + '<span class="icon"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-down" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M6 9l6 6l6 -6"></path></svg></span></h5>';

            regionHtml += '<ul class="pharmacy-list" id="pharmacy-list-' + regionName + '">';
            for (var i = 0; i < regionItems.length; i++) {
                var item = regionItems[i];
                regionHtml += '<li><h6 class="mb-0">' + item.name + '</h6>';
                regionHtml += '<p>' + item.addressStreet + ' ' + item.addressNumber + ' - ' + item.localityName + ' <br>Teléfono: <a href="tel:' +item.phone.international + '">' +item.phone.national + '</a></p></li>';
            }
            regionHtml += '</ul>';
            regionElement.innerHTML = regionHtml;
            pharmacyContainer.appendChild(regionElement);
        }

        

		document.getElementById('searchInput').addEventListener('input', function() {
			var searchTerm = this.value.toLowerCase();
			var regions = document.querySelectorAll('.region-list');

			if (searchTerm === '') {
				// Si el campo de búsqueda está vacío, cerrar todas las listas de farmacias
				for (var i = 0; i < regions.length; i++) {
					var region = regions[i];
					var pharmacyList = region.querySelector('.pharmacy-list');
					pharmacyList.style.display = 'none';
					region.classList.remove('opened');
				}
				var montevideoSection = document.getElementById('region-Montevideo');
				var montevideoPharmacyList = document.getElementById('pharmacy-list-Montevideo');

				montevideoSection.classList.add('opened');
				montevideoPharmacyList.style.display = 'block';
			} else {
				// Si el campo de búsqueda tiene texto, abrir las listas de farmacias que contienen farmacias coincidentes
				for (var i = 0; i < regions.length; i++) {
					var region = regions[i];
					var pharmacies = region.querySelectorAll('.pharmacy-list li');
					var isRegionEmpty = true;
					for (var j = 0; j < pharmacies.length; j++) {
						var pharmacy = pharmacies[j];
						var pharmacyName = pharmacy.querySelector('h6').textContent.toLowerCase();
						if (pharmacyName.includes(searchTerm)) {
							pharmacy.style.display = 'list-item';
							isRegionEmpty = false;
						} else {
							pharmacy.style.display = 'none';
						}
					}
					// Verificar si la región está vacía después de la búsqueda y ocultar si es necesario
					if (isRegionEmpty) {
						region.style.display = 'none';
						region.classList.remove('opened');
					} else {
						region.style.display = 'block';
						region.classList.add('opened');
						// Aquí es donde se expande la lista de farmacias si la región no está vacía
						var pharmacyList = region.querySelector('.pharmacy-list');
						pharmacyList.style.display = 'block';
					}
				}
			}
		});
	});
		
		
    </script>
	
	<style>
        .region-list {
        padding-left: 20px;
    }

    .region-list .toggle-btn {
        cursor: pointer;
        font-weight: bold;
        display: flex;
        width: 100%;
        border-bottom: 1px solid #e8e7e7;
        justify-content: space-between;
    }

    .region-list .toggle-btn .icon {
        transition: transform 0.3s;
    }

    .region-list.opened .toggle-btn .icon {
        transform: rotate(180deg);
    }

    .region-list .pharmacy-list {
        display: none;
        padding-left: 20px;
    }
    .region-list .pharmacy-list li p {
        font-size: 12px;
        line-height: 12px;
    }
	.region-list .pharmacy-list li p a {

        line-height: 12px;
    }
    </style>
	<script>
        function togglePharmacyList(regionId) {
            var pharmacyList = document.getElementById('pharmacy-list-' + regionId);
            var regionSection = document.getElementById('region-' + regionId);

            if (pharmacyList.style.display === 'none') {
                pharmacyList.style.display = 'block';
                regionSection.classList.add('opened');
            } else {
                pharmacyList.style.display = 'none';
                regionSection.classList.remove('opened');
            }
        }
		

        // Marcar la sección de Montevideo como abierta por defecto al cargar la página
        window.onload = function() {
            var montevideoSection = document.getElementById('region-Montevideo');
            var montevideoPharmacyList = document.getElementById('pharmacy-list-Montevideo');

            montevideoSection.classList.add('opened');
            montevideoPharmacyList.style.display = 'block';
        };
    </script>

</head>
<body>
    <header class="themeix-header">
        <div class="themeix-header-top bg-color2">
            <div class="container">
                <div class="d-flex justify-content-between themeix">
                    <div class="themeix-top-bar-left">
                        <p class="top-content"><a href="mailto:hello@recetalia.com">hello@recetalia.com</a></p>
                    </div>

                    <div class="dropdown">
                        <a class="top-sign-btn dropdown-toggle" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="dropdownMenu2" ><i class="fa fa-user"></i>Ingresar</a>
                      <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                        <!--a class="dropdown-item" href="https://pacientes.recetalia.com/login">Pacientes</a-->
                        <a class="dropdown-item" href="https://medicos.recetalia.com/login" target="_blank" rel="noopener">Médicos</a>
                        <a class="dropdown-item" href="https://farmacias.recetalia.com/login" target="_blank" rel="noopener">Farmacias</a>
                        <a class="dropdown-item" href="https://prestadores.recetalia.com/login" target="_blank" rel="noopener">Prestadores</a>
                      </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="top-login-modal modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="tmx-loginform" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tmx-loginform">Area de Acceso</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="login-form-modal">
                            <form action="#" method="get">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                                <input type="text" class="form-control" placeholder="Nombre">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                                <input type="password" class="form-control" placeholder="Password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                                <button type="submit" class="btn btn-primary login-btn">Ingreso</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="message">No está registrado? <a href="#">Cree una cuenta</a></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="themeix-header-navigation bg-color">
            <div class="container">
                <div class="d-flex justify-content-between themeix">
                    <div class="themeix-logo">
                        <a class="themeix-brand" href="/"><img src="/images/header-brand.png" alt="Recetalia" /></a>
                    </div>
                    <nav class="themeix-menu">
                        <ul id="navigation-menu" class="slimmenu">
                            <li><a href="/">Inicio</a></li>
							<!--
                            <li><a href="about.html">About</a></li>
                            <li>
                                <a href="#">Blog </a>
                                <ul>
                                    <li><a href="blog-left-sidebar.html">Blog  Left Sidebar</a></li>
                                    <li><a href="blog-right-sidebar.html">Blog Right Sidebar</a></li>
                                    <li><a href="single.html">Blog Details</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#">Pages </a>
                                <ul>
                                    <li><a href="services-details.html">Service Details</a></li>
                                    <li><a href="#">Projects</a></li>
                                    <li><a href="projects-details.html">Project Details</a></li>
                                    <li><a href="single.html">Blog Details</a></li>
                                    <li><a href="about.html">About</a></li>
                                    <li><a href="404.html">404 Page</a></li>
                                </ul>
                            </li>
							-->

                            <li><a href="#ContactSet">Contacto</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    
    <!-- Start Services -->
    <section class="services-section padding-60-0 bg-color3 section-spacing">
        <div class="container">
            <div class="row">
                <div class="col-md-12 m-auto">
                    <div class="section-title margin-bottom-60 text-center">
                        <h4>Farmacias habilitadas</h4>
                    </div>
                </div>
            </div>
			
			<div class="row">
                <div class="col-md-8 col-lg-6 mx-auto wow fadeIn" data-wow-duration="1s">
					<div class="d-flex align-items-center pb-4">
						<input type="text" id="searchInput" placeholder="Buscar farmacia" class="form-control mr-1">
						<button onclick="search()" class="btn btn-primary">Buscar</button>
					</div>

				</div>
			</div>

            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto wow fadeIn" data-wow-duration="1s">
                    <div class="pharmacy-container"></div>
                </div>
            </div>
        </div>
    </section>
	
    <!-- Start Footer -->
    <footer class="footer-section">
        <div class="footer-widget-container section-spacing bg-color4">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="footer-widget">
                            <div class="footer-logo">
                                <a href="index.html"><img src="/images/footer-brand.png" alt="footer logo img" /></a>
                            </div>
                            <p>
							<i class="fa fa-envelope mr-2" aria-hidden="true"></i> hello@recetalia.com<br>
							<i class="fa fa-map-marker mr-2" aria-hidden="true"></i> WTC Montevideo, Torre III, Piso 12.<br>
							<i class="fa fa-phone fa-2 mr-2" aria-hidden="true"></i> 093 800 024</p>
							<!--
                            <h5>Subscribe</h5>
                            <form class="footer-subscribe " action="#">
                                <div class="input-group">
                                    <input type="email" class="form-control" placeholder="Your Email">
                                    <span class="input-group-btn">
                              <button class="btn btn-primary" type="submit"><i class="fa fa-long-arrow-right"></i></button>
                              </span>
                                </div>
                            </form>
							-->
							<!--
                            <ul class="footer-social-link list-inline">
                                <li class="list-inline-item"><a href="#"><i class="fa fa-facebook"></i></a></li>
                                <li class="list-inline-item"><a href="#"><i class="fa fa-twitter"></i></a></li>
                                <li class="list-inline-item"><a href="#"><i class="fa fa-linkedin"></i></a></li>
                                <li class="list-inline-item"><a href="#"><i class="fa fa-youtube"></i></a></li>
                            </ul>
							--->
                        </div>
                    </div>
					<!--
                    <div class="col-lg-2 col-md-6">
                        <div class="footer-widget">
                            <h4>Usefull Link </h4>
                            <ul class="footer-usefull-link list-inline">
                                <li><a href="#">Consulting</a></li>
                                <li><a href="#">Help Line</a></li>
                                <li><a href="#">About Us</a></li>
                                <li><a href="#">Services</a></li>
                                <li><a href="#">Project</a></li>
                                <li><a href="#">Meet Team</a></li>
                            </ul>
                        </div>
                    </div>
					-->
					<!--
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget">
                            <h4>Our Blog Post</h4>
                            <ul class="footer-post-link list-inline">
                                <li>
                                    <a href="#">Finance project  amet quis tullam cursus, metus .</a>
                                    <span>05 may 2017</span>
                                </li>
                                <li>
                                    <a href="#">Helex  is Your Best quis tullam cursus, metus .</a>
                                    <span>05 may 2017</span>
                                </li>
                                <li>
                                    <a href="#">Established sed fact will  quis tullam cursus, metus .</a>
                                    <span>05 may 2017</span>
                                </li>
                            </ul>
                        </div>
                    </div>
					-->
					<!--
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-widget">
                            <h4>Contact  Form</h4>
                        </div>
                        <form class="footer-contact-form">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Name">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Email Address">
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" rows="5" placeholder="Message"></textarea>
                            </div>
                            <button type="submit" class="btn-style1 btn-color1 btn btn-primary">Submit</button>
                        </form>
                    </div>
					-->
					
                </div>
            </div>
        </div>
        <div class="footer-intro-container bg-color5">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mt-3 mb-3"><a href="/terms" class="small">Términos y condiciones</a> <span class="text-secondary">|</span> <a href="/privacy" class="small">Política de privacidad</a></div>
                    <div class="col-md-4 small text-secondary mt-4 mb-3 text-center">&copy; Copyright - 2022 Recetalia</div>
                    <div class="col-md-4 mt-3 mb-3"><center><a href="https://www.iwtg.com/"><img style="max-width: 150px;" src="/iwtg/powered-by-iwtg.png" alt="IWTG"></a></center></div>
                </div>
                
                
            </div>
        </div>
    </footer>
    <!-- End Footer -->    <!-- Add Javascript File -->
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