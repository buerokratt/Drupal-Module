(function ($, Drupal, drupalSettings) {
  var color = drupalSettings.vp_boostai.vp_boostai_color;

  if (color && color !== '#000000') {
    var css = '#byk-va > div[style^="position"] > div {background-color: ' + color + ';}',
      head = document.head || document.getElementsByTagName('head')[0],
      style = document.createElement('style');

    head.appendChild(style);
    style.appendChild(document.createTextNode(css));
  }

  Drupal.behaviors.boostAiChatPosition = {
    attach: function (context, settings) {

      $(document).once('boostai-button').each(function () {

        var opened = drupalSettings.vp_boostai.vp_boostai_open;
        var opened_mobile = drupalSettings.vp_boostai.vp_boostai_open_mobile;

        if ($(window).outerWidth() > 992) {
          if (opened == 1) {
            $('#byk-va > div[style^="position"] > div').trigger('click');
          }
        }
        else {
          if (opened_mobile == 1) {
            $('#byk-va > div[style^="position"] > div').trigger('click');
          }
        }

        // Select the node that will be observed for mutations
        const targetNode = document.getElementById('byk-va');
        const config = { childList: true, subtree: true };
        const callback = function(mutationsList, observer) {
          // Use traditional 'for loops' for IE 11
          for(const mutation of mutationsList) {
            if (mutation.type === 'childList') {
              $(window).trigger('boostai-change');
            }
          }
        };
        const observer = new MutationObserver(callback);
        observer.observe(targetNode, config);

        $(document).on('click mousedown', '#byk-va > div > div', function () {
          $(window).trigger('boostai-change');
        })

        $(window).on('scroll resize boostai-change', function () {
          // Button class is scrambled, use direct selectors.
          var boostaiButton = $('#byk-va > div > div');
          var scrollToTopButton = $('.vp-back-top');
          var cookieElement = $('.cookie-consent-vp');
          var menuToggle = $('.navbar-toggler-tablet-vp').css('margin-bottom');
          var cookieBannerHeight = cookieElement.outerHeight();
          // If scroll to top shown.
          if (scrollToTopButton.hasClass('vp-show')) {
            $('body').addClass('scroll-button-visible');

            if (scrollToTopButton.hasClass('has-rocketchat')) {
              $('body').addClass('scroll-button-visible-rocketchat');
            }
            else {
              $('body').removeClass('scroll-button-visible-rocketchat');
            }
          }
          else {
            $('body').removeClass('scroll-button-visible');
            $('body').removeClass('scroll-button-visible-rocketchat');
          }

          // If cookie banner is visible.
          if (cookieElement.is(':visible') > 0) {
            boostaiButton.css('margin-bottom', cookieBannerHeight + 16);
          }
          else {
            boostaiButton.css('margin-bottom', menuToggle + 14);
          }
        });
      })
    }
  };
})(jQuery, Drupal, drupalSettings);
