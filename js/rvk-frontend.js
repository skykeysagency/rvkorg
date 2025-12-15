/**
 * Script JavaScript pour la partie publique du plugin RVK
 */
(function ($) {
  "use strict";

  // Initialisation lorsque le DOM est prêt
  $(document).ready(function () {
    // Initialiser le filtrage des salles si présent
    if ($(".rvk-filter-form").length) {
      initSallesFilter();
    }

    // Initialiser la galerie d'images si présente
    if ($(".rvk-gallery").length) {
      initGallery();
    }

    // Initialiser la carte si présente
    if ($("#rvk-map").length) {
      initMap();
    }
  });

  /**
   * Initialiser le filtrage des salles
   */
  function initSallesFilter() {
    // Gérer la soumission du formulaire de filtrage
    $(".rvk-filter-form").on("submit", function (e) {
      e.preventDefault();

      // Récupérer les valeurs des filtres
      var ville = $("#filter-ville").val();
      var places = $("#filter-places").val();
      var prix = $("#filter-prix").val();

      // Construire l'URL de filtrage
      var baseUrl = $(this).data("base-url");
      var queryParams = [];

      if (ville) queryParams.push("ville=" + encodeURIComponent(ville));
      if (places) queryParams.push("places=" + encodeURIComponent(places));
      if (prix) queryParams.push("prix=" + encodeURIComponent(prix));

      // Rediriger vers l'URL filtrée
      window.location.href =
        baseUrl + (queryParams.length ? "?" + queryParams.join("&") : "");
    });

    // Réinitialiser les filtres
    $(".rvk-filter-reset").on("click", function (e) {
      e.preventDefault();
      $(".rvk-filter-form")[0].reset();
      $(".rvk-filter-form").trigger("submit");
    });
  }

  /**
   * Initialiser la galerie d'images
   */
  function initGallery() {
    // Ouvrir l'image en grand au clic
    $(".rvk-gallery-item").on("click", function (e) {
      e.preventDefault();

      var imgSrc = $(this).data("full-image");
      var imgTitle = $(this).data("title");

      // Créer l'overlay de la galerie
      var overlay = $('<div class="rvk-gallery-overlay"></div>');
      var container = $('<div class="rvk-gallery-modal"></div>');
      var img = $('<img src="' + imgSrc + '" alt="' + imgTitle + '">');
      var caption = $(
        '<div class="rvk-gallery-caption">' + imgTitle + "</div>"
      );
      var closeBtn = $('<button class="rvk-gallery-close">&times;</button>');

      // Ajouter les éléments à l'overlay
      container.append(closeBtn);
      container.append(img);
      container.append(caption);
      overlay.append(container);

      // Ajouter l'overlay au body
      $("body").append(overlay);

      // Empêcher le défilement du body
      $("body").addClass("rvk-no-scroll");

      // Fermer l'overlay au clic sur le bouton ou en dehors de l'image
      closeBtn.add(overlay).on("click", function (e) {
        if (e.target === this) {
          overlay.remove();
          $("body").removeClass("rvk-no-scroll");
        }
      });

      // Empêcher la fermeture au clic sur l'image
      img.on("click", function (e) {
        e.stopPropagation();
      });
    });
  }

  /**
   * Initialiser la carte des salles
   */
  function initMap() {
    // Vérifier si l'API Google Maps est chargée
    if (typeof google === "undefined" || typeof google.maps === "undefined") {
      console.error("Google Maps API non chargée");
      return;
    }

    // Récupérer les données des salles
    var sallesData = window.rvkMapData || [];

    // Créer la carte
    var map = new google.maps.Map(document.getElementById("rvk-map"), {
      zoom: 10,
      center: { lat: 48.8566, lng: 2.3522 }, // Paris par défaut
    });

    // Ajouter les marqueurs pour chaque salle
    var bounds = new google.maps.LatLngBounds();
    var infoWindow = new google.maps.InfoWindow();

    sallesData.forEach(function (salle) {
      if (salle.lat && salle.lng) {
        var position = new google.maps.LatLng(salle.lat, salle.lng);

        var marker = new google.maps.Marker({
          position: position,
          map: map,
          title: salle.title,
        });

        // Étendre les limites de la carte
        bounds.extend(position);

        // Ajouter une infobulle au clic sur le marqueur
        google.maps.event.addListener(marker, "click", function () {
          var content =
            '<div class="rvk-map-info">' +
            "<h3>" +
            salle.title +
            "</h3>" +
            (salle.image
              ? '<img src="' + salle.image + '" alt="' + salle.title + '">'
              : "") +
            "<p>" +
            salle.address +
            "</p>" +
            '<a href="' +
            salle.url +
            '">Voir les détails</a>' +
            "</div>";

          infoWindow.setContent(content);
          infoWindow.open(map, marker);
        });
      }
    });

    // Ajuster la carte pour afficher tous les marqueurs
    if (sallesData.length > 0) {
      map.fitBounds(bounds);
    }
  }
})(jQuery);
