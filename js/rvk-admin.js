/**
 * Script JavaScript pour l'administration du plugin RVK
 */
(function ($) {
  "use strict";

  // Initialisation lorsque le DOM est prêt
  $(document).ready(function () {
    // Ajouter une confirmation avant de vider les règles de réécriture
    $(".rvk-flush-rules-button").on("click", function (e) {
      if (
        !confirm(
          "Êtes-vous sûr de vouloir vider les règles de réécriture ? Cette action est nécessaire pour mettre à jour les URLs, mais peut prendre un moment."
        )
      ) {
        e.preventDefault();
      }
    });

    // Ajouter une confirmation avant de mettre à jour les permaliens
    $(".rvk-update-permalinks-button").on("click", function (e) {
      if (
        !confirm(
          "Êtes-vous sûr de vouloir mettre à jour tous les permaliens ? Cette action va modifier les URLs de toutes les salles existantes."
        )
      ) {
        e.preventDefault();
      }
    });

    // Ajouter une classe active au menu actuel
    if (window.location.href.indexOf("page=rvk-options") > -1) {
      $("#toplevel_page_rvk-options").addClass("current");
    }

    // Rendre les notices fermables
    $(".notice-dismiss").on("click", function () {
      $(this).closest(".notice").fadeOut();
    });

    // Initialiser les onglets si présents
    if ($(".rvk-tabs").length) {
      initTabs();
    }
  });

  /**
   * Initialiser les onglets dans l'interface d'administration
   */
  function initTabs() {
    // Cacher tous les contenus d'onglets sauf le premier
    $(".rvk-tab-content").not(":first").hide();

    // Ajouter la classe active au premier onglet
    $(".rvk-tabs li:first").addClass("active");

    // Gérer le clic sur les onglets
    $(".rvk-tabs li a").on("click", function (e) {
      e.preventDefault();

      // Supprimer la classe active de tous les onglets
      $(".rvk-tabs li").removeClass("active");

      // Ajouter la classe active à l'onglet cliqué
      $(this).parent().addClass("active");

      // Récupérer l'ID du contenu à afficher
      var tabId = $(this).attr("href");

      // Cacher tous les contenus d'onglets
      $(".rvk-tab-content").hide();

      // Afficher le contenu de l'onglet sélectionné
      $(tabId).fadeIn();
    });
  }
})(jQuery);
