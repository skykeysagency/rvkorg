/**
 * Frontend JavaScript pour le plugin Formules Custom Plugin
 *
 * Ce fichier contient les fonctionnalités JavaScript pour le frontend du site
 */

(function ($) {
  "use strict";

  // Initialisation lorsque le DOM est prêt
  $(document).ready(function () {
    console.log("FCPFrontend: Initialisation...");
    // Initialiser les fonctionnalités du plugin
    FCPFrontend.init();
  });

  // Objet principal pour les fonctionnalités frontend
  var FCPFrontend = {
    // Initialisation des fonctionnalités
    init: function () {
      console.log("FCPFrontend.init: Démarrage de l'initialisation");
      this.setupEventListeners();
      this.initializeComponents();

      // Initialiser la galerie d'images si présente
      if ($(".fcp-gallery").length || $(".rvk-gallery").length) {
        console.log("FCPFrontend.init: Initialisation de la galerie");
        this.initGallery();
      }

      // Initialiser les modals
      console.log("FCPFrontend.init: Initialisation des modals");
      this.initModals();

      console.log("FCPFrontend.init: Initialisation terminée");
    },

    // Configuration des écouteurs d'événements
    setupEventListeners: function () {
      // Exemple d'écouteur d'événement pour les boutons de formulaire
      $(".fcp-form-submit").on("click", this.handleFormSubmit);

      // Écouteur pour les filtres de portfolio
      $(".fcp-portfolio-filter").on("click", this.handlePortfolioFilter);
    },

    // Initialisation des composants UI
    initializeComponents: function () {
      // Initialiser les sliders si présents
      if ($(".fcp-slider").length > 0) {
        this.initializeSliders();
      }
    },

    // Gestion de la soumission des formulaires
    handleFormSubmit: function (e) {
      // Empêcher la soumission par défaut
      e.preventDefault();

      var $form = $(this).closest("form");
      var formData = $form.serialize();

      // Exemple d'appel AJAX
      $.ajax({
        url: fcp_vars.ajax_url,
        type: "POST",
        data: {
          action: "fcp_form_submit",
          form_data: formData,
          nonce: fcp_vars.nonce,
        },
        success: function (response) {
          if (response.success) {
            $form
              .find(".fcp-form-message")
              .html(
                '<div class="fcp-success">' + response.data.message + "</div>"
              );
          } else {
            $form
              .find(".fcp-form-message")
              .html(
                '<div class="fcp-error">' + response.data.message + "</div>"
              );
          }
        },
        error: function () {
          $form
            .find(".fcp-form-message")
            .html(
              '<div class="fcp-error">Une erreur s\'est produite. Veuillez réessayer.</div>'
            );
        },
      });
    },

    // Gestion du filtrage du portfolio
    handlePortfolioFilter: function (e) {
      e.preventDefault();

      var filter = $(this).data("filter");

      // Mettre à jour la classe active
      $(".fcp-portfolio-filter").removeClass("active");
      $(this).addClass("active");

      // Filtrer les éléments
      if (filter === "all") {
        $(".fcp-portfolio-item").fadeIn();
      } else {
        $(".fcp-portfolio-item").hide();
        $('.fcp-portfolio-item[data-category="' + filter + '"]').fadeIn();
      }
    },

    // Initialisation des sliders
    initializeSliders: function () {
      // Code pour initialiser les sliders (dépend de la bibliothèque utilisée)
      // Exemple avec Slick Slider
      if (typeof $.fn.slick !== "undefined") {
        $(".fcp-slider").slick({
          dots: true,
          infinite: true,
          speed: 500,
          slidesToShow: 1,
          adaptiveHeight: true,
        });
      }
    },

    // Initialisation de la galerie avec modal
    initGallery: function () {
      // Ouvrir l'image en grand au clic
      $(".fcp-gallery-item, .rvk-gallery-item").on("click", function (e) {
        e.preventDefault();

        var imgSrc =
          $(this).data("full-image") ||
          $(this).attr("href") ||
          $(this).find("img").attr("src");
        var imgTitle =
          $(this).data("title") ||
          $(this).attr("title") ||
          $(this).find("img").attr("alt") ||
          "";

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
        if (imgTitle) {
          container.append(caption);
        }
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

        // Fermer la modal avec la touche Echap
        $(document).on("keydown.gallery", function (e) {
          if (e.key === "Escape") {
            overlay.remove();
            $("body").removeClass("rvk-no-scroll");
            $(document).off("keydown.gallery");
          }
        });
      });
    },

    // Initialisation des modals
    initModals: function () {
      console.log("FCPFrontend.initModals: Initialisation des modals");

      // Compter les boutons de modal
      var modalButtonCount = $(".fcp-modal-button").length;
      var chooseButtonCount = $(".fcp-choose-button").length;
      console.log(
        "FCPFrontend.initModals: Nombre de boutons de modal trouvés: " +
          modalButtonCount
      );
      console.log(
        "FCPFrontend.initModals: Nombre de boutons de choix trouvés: " +
          chooseButtonCount
      );

      // Lister les modals disponibles
      $(".fcp-modal").each(function () {
        console.log(
          "FCPFrontend.initModals: Modal trouvée: #" + $(this).attr("id")
        );
      });

      // Ouvrir la modal au clic sur les boutons
      $(".fcp-modal-button").on("click", function (e) {
        e.preventDefault();
        console.log("FCPFrontend.initModals: Clic sur un bouton de modal");

        // Récupérer l'ID de la modal à ouvrir
        var modalId =
          $(this).data("modal") ||
          $(this).data("modal-target") ||
          $(this).attr("href");

        if (!modalId) {
          console.error("FCPFrontend.initModals: Aucun ID de modal spécifié");
          return;
        }

        // Si l'ID ne commence pas par #, ajouter #
        if (modalId.charAt(0) !== "#") {
          modalId = "#" + modalId;
        }

        console.log(
          "FCPFrontend.initModals: Ouverture de la modal: " + modalId
        );

        // Vérifier si la modal existe
        var $modal = $(modalId);

        if ($modal.length === 0) {
          console.error(
            "FCPFrontend.initModals: Modal non trouvée: " + modalId
          );
          return;
        }

        // Afficher la modal avec animation
        $modal.css("display", "block");

        // Forcer un reflow pour que la transition fonctionne
        $modal[0].offsetHeight;

        // Ajouter la classe active pour déclencher l'animation
        $modal.addClass("active");

        // Ajouter la classe pour empêcher le défilement
        $("body").addClass("modal-open");

        // Gérer la fermeture de la modal
        $(".fcp-modal-close, .fcp-modal").on("click", function (e) {
          if (e.target === this || $(e.target).hasClass("fcp-modal-close")) {
            // Fermer avec animation
            $modal.removeClass("active");

            // Attendre la fin de l'animation avant de cacher
            setTimeout(function () {
              $modal.css("display", "none");
              $("body").removeClass("modal-open");
            }, 300); // Durée de la transition
          }
        });

        // Fermer la modal avec la touche Echap
        $(document).on("keydown.modal", function (e) {
          if (e.key === "Escape") {
            // Fermer avec animation
            $modal.removeClass("active");

            // Attendre la fin de l'animation avant de cacher
            setTimeout(function () {
              $modal.css("display", "none");
              $("body").removeClass("modal-open");
              $(document).off("keydown.modal");
            }, 300); // Durée de la transition
          }
        });

        // Empêcher la propagation des clics dans le contenu de la modal
        $(".fcp-modal-content").on("click", function (e) {
          e.stopPropagation();
        });
      });

      // Gestion spécifique pour les boutons de choix de formule
      $(".fcp-choose-button").on("click", function (e) {
        e.preventDefault();
        console.log(
          "FCPFrontend.initModals: Clic sur un bouton de choix de formule"
        );

        // Récupérer les données de la formule
        var formuleTitle = $(this).data("formule-title") || "";
        var formulePrice = $(this).data("formule-price") || "";
        var formuleId = $(this).data("formule-id") || "";
        var formuleSlug = $(this).data("formule-slug") || "";
        var postTitle = $(this).data("post-title") || "";

        console.log("FCPFrontend.initModals: Formule choisie: " + formuleTitle);
        console.log("FCPFrontend.initModals: Prix: " + formulePrice);
        console.log("FCPFrontend.initModals: Post title: " + postTitle);

        // Mettre à jour les champs du formulaire
        if ($("#formule-choisie-titre").length) {
          // Utiliser le titre du post s'il est disponible, sinon utiliser le titre de la formule
          var displayText = "";

          if (postTitle) {
            displayText = postTitle + " - " + formuleTitle;
          } else {
            displayText = formuleTitle;
          }

          $("#formule-choisie-titre").text(displayText);
        }

        if (formuleId && $('input[name="formule_id"]').length) {
          $('input[name="formule_id"]').val(formuleId);
        }

        if (formulePrice && $('input[name="formule_price"]').length) {
          $('input[name="formule_price"]').val(formulePrice);
        }

        if (formuleSlug && $('input[name="formule_slug"]').length) {
          $('input[name="formule_slug"]').val(formuleSlug);
        }

        // Mettre à jour les nouveaux champs cachés
        if ($("#formule-nom-input").length) {
          // Stocker uniquement le nom de la formule sans le prix
          var fullFormuleName = "";
          if (postTitle) {
            fullFormuleName = postTitle + " - " + formuleTitle;
          } else {
            fullFormuleName = formuleTitle;
          }
          $("#formule-nom-input").val(fullFormuleName);
        }

        if (formulePrice && $("#formule-prix-input").length) {
          // Nettoyer le prix pour éviter la duplication du symbole €
          var cleanPrice = formulePrice;
          // Si le prix contient déjà le symbole €, le supprimer
          if (cleanPrice.indexOf("€") !== -1) {
            cleanPrice = cleanPrice.replace("€", "").trim();
          }
          $("#formule-prix-input").val(cleanPrice + " €");
        }

        if (formuleId && $("#formule-choisie-input").length) {
          $("#formule-choisie-input").val(formuleId);
        }

        // Ouvrir la modal de contact
        var $modal = $("#modal-formule-contact");

        if ($modal.length === 0) {
          console.error("FCPFrontend.initModals: Modal de contact non trouvée");
          return;
        }

        console.log("FCPFrontend.initModals: Ouverture de la modal de contact");

        // Afficher la modal avec animation
        $modal.css("display", "block");

        // Forcer un reflow pour que la transition fonctionne
        $modal[0].offsetHeight;

        // Ajouter la classe active pour déclencher l'animation
        $modal.addClass("active");

        // Ajouter la classe pour empêcher le défilement
        $("body").addClass("modal-open");

        // Gérer la fermeture de la modal
        $(".fcp-modal-close, .fcp-modal").on("click", function (e) {
          if (e.target === this || $(e.target).hasClass("fcp-modal-close")) {
            // Fermer avec animation
            $modal.removeClass("active");

            // Attendre la fin de l'animation avant de cacher
            setTimeout(function () {
              $modal.css("display", "none");
              $("body").removeClass("modal-open");
            }, 300); // Durée de la transition
          }
        });

        // Fermer la modal avec la touche Echap
        $(document).on("keydown.modal", function (e) {
          if (e.key === "Escape") {
            // Fermer avec animation
            $modal.removeClass("active");

            // Attendre la fin de l'animation avant de cacher
            setTimeout(function () {
              $modal.css("display", "none");
              $("body").removeClass("modal-open");
              $(document).off("keydown.modal");
            }, 300); // Durée de la transition
          }
        });

        // Empêcher la propagation des clics dans le contenu de la modal
        $(".fcp-modal-content").on("click", function (e) {
          e.stopPropagation();
        });
      });
    },
  };
})(jQuery);
